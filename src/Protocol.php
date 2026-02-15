<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer;

use DateMalformedStringException;
use FediE2EE\PKD\Crypto\{
    Protocol\HPKEAdapter,
    Protocol\Parser
};
use FediE2EE\PKD\Crypto\Exceptions\{
    BundleException,
    CryptoException,
    JsonException,
    NotImplementedException,
    ParserException,
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    ConcurrentException,
    DependencyException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\{
    ActivityPub\ActivityStream,
    ActivityPub\WebFinger,
    Protocol\KeyWrapping,
    Protocol\Payload,
    Traits\ConfigTrait
};
use FediE2EE\PKDServer\Tables\{
    AuxData,
    MerkleState,
    PublicKeys,
    Records\ActorKey
};
use GuzzleHttp\Client;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\HPKE\HPKEException;
use Random\RandomException;
use SodiumException;
use function property_exists;

/**
 * This class defines the process for which records are updated in the Public Key Directory.
 */
class Protocol
{
    use ConfigTrait;

    protected Parser $parser;
    protected ?WebFinger $webFinger = null;

    /**
     * @throws DependencyException
     */
    public function __construct(?ServerConfig $config)
    {
        if (is_null($config)) {
            throw new DependencyException('config not injected');
        }
        $this->config = $config;
        $this->parser = new Parser();
    }

    /**
     * @return array{action: string, result: bool|ActorKey, latest-root: string}
     *
     * @throws BundleException
     * @throws CacheException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function process(ActivityStream $enqueued, bool $isActivityPub = true): array
    {
        $envelope = $this->parseOuterEnvelope($enqueued);

        $hpke = $this->config()->getHPKE();
        $adapter = new HPKEAdapter($hpke->cs);
        $wasEncrypted = $adapter->isHpkeCiphertext($envelope['payload']);
        if ($wasEncrypted !== $envelope['encrypted']) {
            throw new ProtocolException(
                'Client confusion: an HPKE encrypted payload was sent as plaintext.'
            );
        }

        $raw = $wasEncrypted
            ? $adapter->open($hpke->decapsKey, $hpke->encapsKey, $envelope['payload'])
            : $envelope['payload'];

        $parsed = $isActivityPub
            ? $this->parser->parseUnverifiedForActivityPub($raw)
            : $this->parser->parseUnverified($raw);

        $payload = new Payload($parsed->getMessage(), $parsed->getKeyMap(), $raw);
        $action = $parsed->getMessage()->getAction();

        // Explicitly reject BurnDown if received over ActivityPub.
        if ($isActivityPub && ($action === 'BurnDown')) {
            throw new ProtocolException('BurnDown is not allowed over ActivityPub.');
        }

        // Route the request based on whether it was encrypted or not:
        $result = $wasEncrypted
            ? $this->routeEncryptedAction($action, $payload, $envelope['actor'])
            : $this->routePlaintextAction($action, $payload, $envelope['actor']);

        // Notice that Checkpoint is allowed, but not required, to be HPKE encrypted.
        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $merkleRoot = $merkleState->getLatestRoot();
        if (!empty($result)) {
            $this->wrapLocalKeys($payload);
        }
        return ['action' => $action, 'result' => $result, 'latest-root' => $merkleRoot];
    }

    /**
     * Determine which field contains the message payload based on !pkd-context.
     *
     * @param array<string, mixed> $outerJson
     * @return array{field: string, encrypted: bool}
     *
     * @throws ProtocolException
     */
    protected function resolveEncryptionField(array $outerJson): array
    {
        $contextImpliesEncryption = match($outerJson['!pkd-context']) {
            'fedi-e2ee:v1-encrypted-message' => true,
            'fedi-e2ee:v1-plaintext-message' => false,
            default => throw new ProtocolException('Invalid !pkd-context value'),
        };
        if ($contextImpliesEncryption) {
            if (!array_key_exists('encrypted-message', $outerJson)) {
                throw new ProtocolException('No "encrypted-message" was set.');
            }
            if (array_key_exists('plaintext-message', $outerJson)) {
                throw new ProtocolException('Unexpected "plaintext-message".');
            }
            return ['field' => 'encrypted-message', 'encrypted' => true];
        }
        if (!array_key_exists('plaintext-message', $outerJson)) {
            throw new ProtocolException('No "plaintext-message" was set.');
        }
        if (array_key_exists('encrypted-message', $outerJson)) {
            throw new ProtocolException('Unexpected "encrypted-message".');
        }
        return ['field' => 'plaintext-message', 'encrypted' => false];
    }

    /**
     * Parse and validate the outer ActivityStream envelope.
     *
     * @return array{actor: string, payload: string, encrypted: bool}
     * @throws ProtocolException
     */
    protected function parseOuterEnvelope(ActivityStream $enqueued): array
    {
        if (!$enqueued->isDirectMessage()) {
            throw new ProtocolException('Only direct messages are allowed.');
        }
        // We already verified the HTTP Message Signature in the Inbox ResponseHandler.
        // If it was enqueued, we can assume the signature was valid.
        if (!property_exists($enqueued->object, 'content')) {
            throw new ProtocolException('Missing content in ActivityStream object');
        }
        /** @var string $activityContent */
        $activityContent = $enqueued->object->content;
        $outerJson = json_decode($activityContent, true);
        if (!is_array($outerJson)) {
            throw new ProtocolException('Only JSON objects are allowed.');
        }
        if (!array_key_exists('!pkd-context', $outerJson)) {
            throw new ProtocolException('No !pkd-context was set.');
        }
        if (!array_key_exists('actor', $outerJson)) {
            throw new ProtocolException('No actor was set.');
        }
        $outerActor = $outerJson['actor'];
        if (!is_string($outerActor)) {
            throw new ProtocolException('Only strings are allowed for actor IDs.');
        }

        $encryption = $this->resolveEncryptionField($outerJson);
        $fieldToUse = $encryption['field'];
        if (!array_key_exists($fieldToUse, $outerJson) || !is_string($outerJson[$fieldToUse])) {
            throw new ProtocolException('Message payload must be a string');
        }
        return [
            'actor' => $outerActor,
            'payload' => $outerJson[$fieldToUse],
            'encrypted' => $encryption['encrypted'],
        ];
    }

    /**
     * Route an encrypted protocol action to the appropriate table handler.
     *
     * @throws CacheException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    protected function routeEncryptedAction(
        string $action,
        Payload $payload,
        string $outerActor,
    ): ActorKey|bool {
        /** @var PublicKeys $publicKeyTable */
        $publicKeyTable = $this->table('PublicKeys');
        /** @var AuxData $auxDataTable */
        $auxDataTable = $this->table('AuxData');
        return match ($action) {
            'AddAuxData' => $auxDataTable->addAuxData($payload, $outerActor),
            'AddKey' => $publicKeyTable->addKey($payload, $outerActor),
            'Checkpoint' => $publicKeyTable->checkpoint($payload),
            'Fireproof' => $publicKeyTable->fireproof($payload, $outerActor),
            'MoveIdentity' => $publicKeyTable->moveIdentity($payload, $outerActor),
            'RevokeAuxData' => $auxDataTable->revokeAuxData($payload, $outerActor),
            'RevokeKey' => $publicKeyTable->revokeKey($payload, $outerActor),
            'UndoFireproof' => $publicKeyTable->undoFireproof($payload, $outerActor),
            'BurnDown' =>
                throw new ProtocolException('BurnDown MUST NOT be HPKE-encrypted'),
            default =>
                throw new ProtocolException('Unknown action: ' . $action),
        };
    }

    /**
     * Route a plaintext protocol action to the appropriate table handler.
     *
     * @throws CacheException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    protected function routePlaintextAction(
        string $action,
        Payload $payload,
        string $outerActor,
    ): bool {
        /** @var PublicKeys $publicKeyTable */
        $publicKeyTable = $this->table('PublicKeys');
        return match ($action) {
            'BurnDown' => $publicKeyTable->burndown($payload, $outerActor),
            'Checkpoint' => $publicKeyTable->checkpoint($payload),
            'AddAuxData', 'AddKey', 'Fireproof', 'MoveIdentity', 'RevokeAuxData', 'RevokeKey', 'UndoFireproof' =>
                throw new ProtocolException('This action MUST be HPKE-encrypted: ' . $action),
            default =>
                throw new ProtocolException('Unknown action: ' . $action),
        };
    }

    /**
     * @param Payload $payload
     * @return void
     *
     * @throws CacheException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws SodiumException
     * @throws TableException
     */
    protected function wrapLocalKeys(Payload $payload): void
    {
        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');

        $keyWrapping = new KeyWrapping($this->config);
        $merkleRoot = $merkleState->getLatestRoot();

        // $keyWrapping->localKeyWrap($merkleRoot, $payload->keyMap);
        $keyWrapping->rewrapSymmetricKeys($merkleRoot, $payload->keyMap);
        if ($this->config()->getDb()->inTransaction()) {
            $this->config()->getDb()->commit();
        }
    }

    /**
     * @throws DependencyException
     * @throws SodiumException
     * @throws CertaintyException
     */
    public function webfinger(?Client $http = null): WebFinger
    {
        if (!is_null($this->webFinger)) {
            return $this->webFinger;
        }
        return new WebFinger($this->config(), $http, $this->config()->getCaCertFetch());
    }

    /**
     * This is intended for mocking in unit tests
     *
     * @param WebFinger $wf
     * @return self
     */
    public function setWebFinger(WebFinger $wf): self
    {
        $this->webFinger = $wf;
        return $this;
    }

    /**
     * @throws BundleException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     */
    protected function hpkeUnwrap(string $arbitrary): Payload
    {
        $hpke = $this->config()->getHPKE();
        $raw = new HPKEAdapter($hpke->cs)
            ->open($hpke->decapsKey, $hpke->encapsKey, $arbitrary);
        $parsed = $this->parser->parseUnverified($raw);
        return new Payload($parsed->getMessage(), $parsed->getKeyMap(), $raw);
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function addKey(string $body, string $outerActor): ActorKey
    {
        $payload = $this->hpkeUnwrap($body);
        /** @var PublicKeys $table */
        $table = $this->table('PublicKeys');
        $return = $table->addKey($payload, $outerActor);
        $this->wrapLocalKeys($payload);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function revokeKey(string $body, string $outerActor): ActorKey
    {
        $payload = $this->hpkeUnwrap($body);
        /** @var PublicKeys $table */
        $table = $this->table('PublicKeys');
        $return = $table->revokeKey($payload, $outerActor);
        $this->wrapLocalKeys($payload);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function revokeKeyThirdParty(string $body): bool
    {
        try {
            $parsed = $this->parser->parseUnverified($body);
            $payload = new Payload($parsed->getMessage(), $parsed->getKeyMap(), $body);
        } catch (ParserException $e) {
            throw new ProtocolException('Invalid bundle for RevokeKeyThirdParty', 0, $e);
        }

        /** @var PublicKeys $table */
        $table = $this->table('PublicKeys');
        $return = $table->revokeKeyThirdParty($payload);
        $this->wrapLocalKeys($payload);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function moveIdentity(string $body, string $outerActor): bool
    {
        $payload = $this->hpkeUnwrap($body);
        /** @var PublicKeys $table */
        $table = $this->table('PublicKeys');
        $return = $table->moveIdentity($payload, $outerActor);
        $this->wrapLocalKeys($payload);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws BundleException
     * @throws CryptoException
     */
    protected function parsePlaintext(string $body): Payload
    {
        $parsed = $this->parser->parseUnverified($body);
        return new Payload($parsed->getMessage(), $parsed->getKeyMap(), $body);
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function burnDown(string $body, string $outerActor): bool
    {
        $hpke = $this->config()->getHPKE();
        if (new HPKEAdapter($hpke->cs)->isHpkeCiphertext($body)) {
            throw new ProtocolException('BurnDown MUST NOT be encrypted.');
        }
        // BurnDown messages are NOT HPKE-encrypted, parse directly
        $payload = $this->parsePlaintext($body);
        /** @var PublicKeys $table */
        $table = $this->table('PublicKeys');
        $return = $table->burnDown($payload, $outerActor);
        $this->wrapLocalKeys($payload);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function fireproof(string $body, string $outerActor): bool
    {
        $payload = $this->hpkeUnwrap($body);
        /** @var PublicKeys $table */
        $table = $this->table('PublicKeys');
        $return = $table->fireproof($payload, $outerActor);
        $this->wrapLocalKeys($payload);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function undoFireproof(string $body, string $outerActor): bool
    {
        $payload = $this->hpkeUnwrap($body);
        /** @var PublicKeys $table */
        $table = $this->table('PublicKeys');
        $return = $table->undoFireproof($payload, $outerActor);
        $this->wrapLocalKeys($payload);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function addAuxData(string $body, string $outerActor): bool
    {
        $payload = $this->hpkeUnwrap($body);
        /** @var AuxData $table */
        $table = $this->table('AuxData');
        $return = $table->addAuxData($payload, $outerActor);
        $this->wrapLocalKeys($payload);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function revokeAuxData(string $body, string $outerActor): bool
    {
        $payload = $this->hpkeUnwrap($body);
        /** @var AuxData $table */
        $table = $this->table('AuxData');
        $return = $table->revokeAuxData($payload, $outerActor);
        $this->wrapLocalKeys($payload);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function checkpoint(string $body): bool
    {
        try {
            $parsed = $this->parser->parseUnverified($body);
            $payload = new Payload($parsed->getMessage(), $parsed->getKeyMap(), $body);
        } catch (ParserException $e) {
            throw new ProtocolException('Invalid bundle for Checkpoint', 0, $e);
        }
        /** @var PublicKeys $table */
        $table = $this->table('PublicKeys');
        $return = $table->checkpoint($payload);
        $this->wrapLocalKeys($payload);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws DependencyException
     */
    protected function cleanUpAfterAction(): void
    {
        $this->config()->getDb()->exec(
            "UPDATE pkd_merkle_state SET lock_challenge = ''"
        );
    }
}
