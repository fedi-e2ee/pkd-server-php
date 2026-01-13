<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer;

use FediE2EE\PKD\Crypto\{
    Exceptions\CryptoException,
    Exceptions\NotImplementedException,
    Exceptions\ParserException,
    Protocol\HPKEAdapter,
    Protocol\Parser
};
use FediE2EE\PKDServer\Exceptions\{
    DependencyException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\{
    ActivityPub\ActivityStream,
    ActivityPub\WebFinger,
    Protocol\Payload,
    Traits\ConfigTrait
};
use GuzzleHttp\Client;
use FediE2EE\PKDServer\Tables\{
    AuxData,
    MerkleState,
    PublicKeys,
    Records\ActorKey
};
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\HPKE\HPKEException;
use SodiumException;

/**
 * This class defines the process for which records are updated in the Public Key Directory.
 */
class Protocol
{
    use ConfigTrait;

    protected Parser $parser;
    protected ?WebFinger $webFinger = null;

    public function __construct(?ServerConfig $config)
    {
        if (is_null($config)) {
            throw new DependencyException('config not injected');
        }
        $this->config = $config;
        $this->parser = new Parser();
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws Exceptions\CacheException
     * @throws HPKEException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function process(ActivityStream $enqueued, bool $isActivityPub = true): array
    {
        // Initialize some Table classes for handling SQL:
        /** @var PublicKeys $publicKeyTable */
        $publicKeyTable = $this->table('PublicKeys');
        /** @var AuxData $auxDataTable */
        $auxDataTable = $this->table('AuxData');
        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');

        if (!$enqueued->isDirectMessage()) {
            throw new ProtocolException('Only direct messages are allowed.');
        }
        // We already verified te HTTP Message Signature in the Inbox ResponseHandler.
        // If it was enqueued, we can assume the signature was valid.

        // First, parse the outer JSON envelope (per v0.3.0+ of the specification)
        $outerJson = json_decode($enqueued->object->content, true);
        if (!is_array($outerJson)) {
            throw new ProtocolException('Only JSON objects are allowed.');
        }
        if (!array_key_exists('!pkd-context', $outerJson)) {
            throw new ProtocolException('No !pkd-context was set.');
        }
        if (!array_key_exists('actor', $outerJson)) {
            throw new ProtocolException('No actor was set.');
        }

        // Let's figure out, from the context, whether encryption was expected:
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
            $fieldToUse = 'encrypted-message';
        } else {
            if (!array_key_exists('plaintext-message', $outerJson)) {
                throw new ProtocolException('No "plaintext-message" was set.');
            }
            if (array_key_exists('encrypted-message', $outerJson)) {
                throw new ProtocolException('Unexpected "encrypted-message".');
            }
            $fieldToUse = 'plaintext-message';
        }

        // Is this encrypted?
        $hpke = $this->config->getHPKE();
        $adapter = new HPKEAdapter($hpke->cs);
        $wasEncrypted = $adapter->isHpkeCiphertext($outerJson[$fieldToUse]);

        // Let's bail out if anything strange is happening:
        if ($wasEncrypted !== $contextImpliesEncryption) {
            throw new ProtocolException('Client confusion: an HPKE encrypted payload was sent as plaintext.');
        }

        // If it was encrypted, we should decrypt it:
        if ($wasEncrypted) {
            $raw = $adapter->open($hpke->decapsKey, $hpke->encapsKey, $outerJson[$fieldToUse]);
        } else {
            $raw = $outerJson[$fieldToUse];
        }

        // Parse the plaintext, grab the action parameter;
        if ($isActivityPub) {
            $parsed = $this->parser->parseForActivityPub($raw);
        } else {
            $parsed = $this->parser->parseUnverified($raw);
        }
        $payload = new Payload($parsed->getMessage(), $parsed->getKeyMap(), $raw);
        $action = $parsed->getMessage()->getAction();
        $outerActor = $outerJson['actor'];
        if (!is_string($outerActor)) {
            throw new ProtocolException('Only strings are allowed for actor IDs.');
        }

        // Explicitly reject BurnDown if received over ActivityPub.
        if ($isActivityPub && ($action === 'BurnDown')) {
            throw new ProtocolException('BurnDown is not allowed over ActivityPub.');
        }

        // Route the request based on whether it was encrypted or not:
        if ($wasEncrypted) {
            $result = match ($action) {
                // These actions are allowed to be encrypted:
                'AddAuxData' => $auxDataTable->addAuxData($payload, $outerActor),
                'AddKey' => $publicKeyTable->addKey($payload, $outerActor),
                'Checkpoint' => $publicKeyTable->checkpoint($payload),
                'Fireproof' => $publicKeyTable->fireproof($payload, $outerActor),
                'MoveIdentity' => $publicKeyTable->moveIdentity($payload, $outerActor),
                'RevokeAuxData' => $auxDataTable->revokeAuxData($payload, $outerActor),
                'RevokeKey' => $publicKeyTable->revokeKey($payload, $outerActor),
                'UndoFireproof' => $publicKeyTable->undoFireproof($payload, $outerActor),
                // BurnDown MUST NOT be encrypted:
                'BurnDown' =>
                    throw new ProtocolException('BurnDown MUST NOT be HPKE-encrypted'),
                // Unknown:
                default =>
                    throw new ProtocolException('Unknown action: ' . $action),
            };
        } else {
            $result = match ($action) {
                // These actions are allowed to be plaintext:
                'BurnDown' => $publicKeyTable->burndown($payload, $outerActor),
                'Checkpoint' => $publicKeyTable->checkpoint($payload),
                // These actions MUST be encrypted:
                'AddAuxData', 'AddKey', 'Fireproof', 'MoveIdentity', 'RevokeAuxData', 'RevokeKey', 'UndoFireproof' =>
                    throw new ProtocolException('This action MUST be HPKE-encrypted: ' . $action),
                default =>
                    throw new ProtocolException('Unknown action: ' . $action),
            };
        }
        // You'll notice that Checkpoint is allowed, but not require, to be HPKE encrypted.
        $merkleRoot = $merkleState->getLatestRoot();

        // Return the results as an array so other processes can shape a response:
        return ['action' => $action, 'result' => $result, 'latest-root' => $merkleRoot];
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
        return new WebFinger($this->config, $http, $this->config->getCaCertFetch());
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
     * @throws DependencyException
     * @throws CryptoException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws HPKEException
     * @throws SodiumException
     */
    protected function hpkeUnwrap(string $arbitrary): Payload
    {
        $hpke = $this->config->getHPKE();
        $raw = new HPKEAdapter($hpke->cs)
            ->open($hpke->decapsKey, $hpke->encapsKey, $arbitrary);
        $parsed = $this->parser->parseUnverified($raw);
        return new Payload($parsed->getMessage(), $parsed->getKeyMap(), $raw);
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws ProtocolException
     * @throws TableException
     * @throws HPKEException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws SodiumException
     */
    public function addKey(string $body, string $outerActor): ActorKey
    {
        $payload = $this->hpkeUnwrap($body);
        $table = new PublicKeys($this->config);
        $return = $table->addKey($payload, $outerActor);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws ProtocolException
     * @throws TableException
     * @throws HPKEException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws SodiumException
     */
    public function revokeKey(string $body, string $outerActor): ActorKey
    {
        $payload = $this->hpkeUnwrap($body);
        $table = new PublicKeys($this->config);
        $return = $table->revokeKey($payload, $outerActor);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
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

        $table = new PublicKeys($this->config);
        $return = $table->revokeKeyThirdParty($payload);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function moveIdentity(string $body, string $outerActor): bool
    {
        $payload = $this->hpkeUnwrap($body);
        $table = new PublicKeys($this->config);
        $return = $table->moveIdentity($payload, $outerActor);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function burnDown(string $body, string $outerActor): bool
    {
        $hpke = $this->config->getHPKE();
        if (new HPKEAdapter($hpke->cs)->isHpkeCiphertext($body)) {
            throw new ProtocolException('BurnDown MUST NOT be encrypted.');
        }
        $payload = $this->hpkeUnwrap($body);
        $table = new PublicKeys($this->config);
        $return = $table->burnDown($payload, $outerActor);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function fireproof(string $body, string $outerActor): bool
    {
        $payload = $this->hpkeUnwrap($body);
        $table = new PublicKeys($this->config);
        $return = $table->fireproof($payload, $outerActor);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function undoFireproof(string $body, string $outerActor): bool
    {
        $payload = $this->hpkeUnwrap($body);
        $table = new PublicKeys($this->config);
        $return = $table->undoFireproof($payload, $outerActor);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function addAuxData(string $body, string $outerActor): bool
    {
        $payload = $this->hpkeUnwrap($body);
        $table = new AuxData($this->config);
        $return = $table->addAuxData($payload, $outerActor);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function revokeAuxData(string $body, string $outerActor): bool
    {
        $payload = $this->hpkeUnwrap($body);
        $table = new AuxData($this->config);
        $return = $table->revokeAuxData($payload, $outerActor);
        $this->cleanUpAfterAction();
        return $return;
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
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
        $table = new PublicKeys($this->config);
        $return = $table->checkpoint($payload);
        $this->cleanUpAfterAction();
        return $return;
    }

    protected function cleanUpAfterAction(): void
    {
        $this->config->getDb()->exec(
            "UPDATE pkd_merkle_state SET lock_challenge = ''"
        );
    }
}
