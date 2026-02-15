<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Integration;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Exceptions\{
    BundleException,
    CryptoException,
    InputException,
    JsonException as CryptoJsonException,
    NetworkException,
    NotImplementedException
};
use FediE2EE\PKD\Crypto\Protocol\Actions\{
    AddAuxData,
    AddKey,
    BurnDown as BurnDownAction,
    Fireproof,
    RevokeAuxData,
    UndoFireproof
};
use FediE2EE\PKD\Crypto\Protocol\Handler;
use GuzzleHttp\Exception\GuzzleException;
use FediE2EE\PKD\Crypto\{
    SecretKey,
    SymmetricKey
};
use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\{
    AppCache,
    Dependency\HPKE,
    Dependency\WrappedEncryptedRow,
    Math,
    Protocol,
    Protocol\KeyWrapping,
    Protocol\Payload,
    Protocol\RewrapConfig,
    ServerConfig,
    Table,
    TableCache
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\Tables\{
    Actors,
    AuxData,
    MerkleState,
    Peers,
    PublicKeys,
    TOTP
};
use FediE2EE\PKDServer\Tables\Records\{
    Actor,
    ActorKey,
    MerkleLeaf,
    Peer
};
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use GuzzleHttp\Psr7\Response;
use JsonException;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\HPKE\HPKEException;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    DataProvider,
    Group,
    UsesClass
};
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;
use SodiumException;
use Throwable;
use const FediE2EE\PKDServer\PKD_SERVER_ROOT;

#[CoversClass(Protocol::class)]
#[Group('test-vectors')]
#[UsesClass(Actor::class)]
#[UsesClass(Actors::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(AuxData::class)]
#[UsesClass(KeyWrapping::class)]
#[UsesClass(Math::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(MerkleState::class)]
#[UsesClass(Payload::class)]
#[UsesClass(Peer::class)]
#[UsesClass(Peers::class)]
#[UsesClass(PublicKeys::class)]
#[UsesClass(RewrapConfig::class)]
#[UsesClass(ServerConfig::class)]
#[UsesClass(Table::class)]
#[UsesClass(TableCache::class)]
#[UsesClass(TOTP::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(WrappedEncryptedRow::class)]
class VectorsTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    private const string TEST_VECTORS_PATH = PKD_SERVER_ROOT . '/tests/TestVectors/test-vectors.json';

    /** @var array<string, mixed>|null */
    private static ?array $testVectors = null;

    /** @var array<string, SecretKey> Identity secret keys by actor URL */
    private array $identityKeys = [];

    /** @var array<string, int> Track how many keys have been added per actor */
    private array $actorKeyCount = [];

    /**
     * @throws JsonException
     */
    private static function loadTestVectors(): array
    {
        if (self::$testVectors !== null) {
            return self::$testVectors;
        }

        if (!file_exists(self::TEST_VECTORS_PATH)) {
            throw new RuntimeException(
                'Test vectors not found at: ' . self::TEST_VECTORS_PATH . "\n" .
                'Please ensure the public-key-directory-specification repository is available.'
            );
        }

        $content = file_get_contents(self::TEST_VECTORS_PATH);
        if ($content === false) {
            throw new RuntimeException('Failed to read test vectors file');
        }

        self::$testVectors = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        return self::$testVectors;
    }

    /**
     * @throws JsonException
     */
    public static function provideTestCases(): iterable
    {
        $vectors = self::loadTestVectors();

        foreach ($vectors['test-cases'] as $testCase) {
            yield $testCase['name'] => [$testCase['name']];
        }
    }

    /**
     * @throws DependencyException
     * @throws SodiumException
     */
    public function setUp(): void
    {
        $this->config = $this->getConfig();
        // Clear table cache to avoid stale state between tests
        TableCache::instance()->clearCache();
        $this->truncateTables();
        Handler::$wf = null;
        $this->identityKeys = [];
        $this->actorKeyCount = [];
    }

    public function tearDown(): void
    {
        Handler::$wf = null;
        $this->identityKeys = [];
        $this->actorKeyCount = [];
    }

    /**
     * @throws CacheException
     * @throws CertaintyException
     * @throws DependencyException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws SodiumException
     * @throws TableException
     */
    #[DataProvider('provideTestCases')]
    public function testVectorCase(string $testCaseName): void
    {
        $vectors = self::loadTestVectors();
        $testCase = $this->findTestCase($vectors, $testCaseName);

        if ($testCase === null) {
            $this->markTestSkipped("Test case '{$testCaseName}' not found in vectors");
        }

        $this->truncateTables();

        // Load identity keys from test vector
        $this->loadIdentityKeys($testCase['identities']);

        // Set up WebFinger mock for all identities
        $webFinger = $this->createWebFingerMockForIdentities($testCase['identities']);

        // Create Protocol instance
        $this->clearOldTransaction($this->config);
        $protocol = new Protocol($this->config);
        $protocol->setWebFinger($webFinger);

        /** @var PublicKeys $pkTable */
        $pkTable = $this->table('PublicKeys');
        $pkTable->setWebFinger($webFinger);

        /** @var AuxData $auxDataTable */
        $auxDataTable = $this->table('AuxData');
        $auxDataTable->setWebFinger($webFinger);

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');

        // Execute each step
        $steps = 0;
        foreach ($testCase['steps'] as $stepIndex => $step) {
            $description = $step['description'] ?? "Step {$stepIndex}";
            $expectFail = $step['expect-fail'] ?? false;

            // Parse protocol message to get action details
            $protocolMessage = json_decode(
                $step['protocol-message'],
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            // Execute the step
            $exception = null;
            try {
                $this->clearOldTransaction($this->config);
                $this->executeStep(
                    $protocol,
                    $merkleState,
                    $step,
                    $protocolMessage,
                    $this->config
                );
            } catch (Throwable $e) {
                $exception = $e;
            }
            $this->clearOldTransaction($this->config);

            if ($expectFail) {
                $this->assertNotNull(
                    $exception,
                    "Step {$stepIndex} ({$description}) should have failed but succeeded"
                );
                $steps++;
            } else {
                if ($exception !== null) {
                    $trace = $exception->getTraceAsString();
                    $this->fail(
                        "Step {$stepIndex} ({$description}) failed unexpectedly: " .
                        $exception->getMessage() . "\nStack trace:\n" . $trace
                    );
                } else {
                    $steps++;
                }
            }
        }
        $this->assertCount($steps, $testCase['steps'], 'All steps executed');
    }

    private function findTestCase(array $vectors, string $name): ?array
    {
        foreach ($vectors['test-cases'] as $testCase) {
            if ($testCase['name'] === $name) {
                return $testCase;
            }
        }
        return null;
    }

    /**
     * @throws CryptoException
     */
    private function loadIdentityKeys(array $identities): void
    {
        foreach ($identities as $actorUrl => $identity) {
            $secretKeyBytes = Base64UrlSafe::decodeNoPadding(
                $identity['ed25519']['secret-key']
            );
            $this->identityKeys[$actorUrl] = new SecretKey($secretKeyBytes, 'ed25519');
        }
    }

    /**
     * @throws CacheException
     * @throws CertaintyException
     * @throws DependencyException
     * @throws InvalidArgumentException
     * @throws SodiumException
     */
    private function createWebFingerMockForIdentities(array $identities): WebFinger
    {
        $responses = [];
        for ($i = 0; $i < 100; $i++) {
            foreach ($identities as $actorUrl => $identity) {
                $responses[] = new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['subject' => $actorUrl])
                );
            }
        }

        $webFinger = new WebFinger($this->config, $this->getMockClient($responses));

        foreach ($identities as $actorUrl => $identity) {
            $webFinger->setCanonicalForTesting($actorUrl, $actorUrl);
        }

        Handler::$wf = new class($identities) extends \FediE2EE\PKD\Crypto\ActivityPub\WebFinger {
            /** @param array<string, array<string, mixed>> $identities */
            public function __construct(private readonly array $identities) {}

            public function canonicalize(string $actor): string
            {
                foreach ($this->identities as $actorUrl => $identity) {
                    if ($actorUrl === $actor) {
                        return $actorUrl;
                    }
                }
                return $actor;
            }
        };

        return $webFinger;
    }

    private function extractActorFromDescription(array $step, array $protocolMessage): string
    {
        $description = $step['description'] ?? '';
        $action = $protocolMessage['action'];

        // BurnDown: "BurnDown {target} by {operator}" - return target
        if ($action === 'BurnDown' && preg_match('/BurnDown (https?:\/\/[^\s]+) by/', $description, $m)) {
            return $m[1];
        }

        // Other actions: extract first URL
        if (preg_match('/(https?:\/\/[^\s]+)/', $description, $matches)) {
            return $matches[1];
        }

        throw new RuntimeException("Could not extract actor from: {$description}");
    }

    private function extractOperatorFromDescription(array $step): string
    {
        $description = $step['description'] ?? '';
        if (preg_match('/by (https?:\/\/[^\s]+)/', $description, $matches)) {
            return $matches[1];
        }
        throw new RuntimeException("Could not extract operator from: {$description}");
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws CryptoException
     * @throws CryptoJsonException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws HPKEException
     * @throws InputException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    private function executeStep(
        Protocol $protocol,
        MerkleState $merkleState,
        array $step,
        array $protocolMessage,
        ServerConfig $config
    ): void {
        $action = $protocolMessage['action'];
        $actor = $this->extractActorFromDescription($step, $protocolMessage);

        if (!isset($this->identityKeys[$actor])) {
            // For BurnDown, the signer is the operator
            if ($action === 'BurnDown') {
                $operator = $this->extractOperatorFromDescription($step);
                if (!isset($this->identityKeys[$operator])) {
                    throw new RuntimeException("No identity key for operator: {$operator}");
                }
            } else {
                throw new RuntimeException("No identity key for actor: {$actor}");
            }
        }

        $latestRoot = $merkleState->getLatestRoot();
        $serverHpke = $config->getHPKE();
        $handler = new Handler();

        match ($action) {
            'AddKey' => $this->executeAddKey($protocol, $handler, $serverHpke, $actor, $latestRoot),
            'Fireproof' => $this->executeFireproof($protocol, $handler, $serverHpke, $actor, $latestRoot),
            'UndoFireproof' => $this->executeUndoFireproof($protocol, $handler, $serverHpke, $actor, $latestRoot),
            'BurnDown' => $this->executeBurnDown($protocol, $handler, $step, $actor, $latestRoot),
            'AddAuxData' => $this->executeAddAuxData($protocol, $handler, $serverHpke, $actor, $step, $latestRoot),
            'RevokeAuxData' => $this->executeRevokeAuxData(
                $protocol,
                $handler,
                $serverHpke,
                $actor,
                $step,
                $latestRoot
            ),
            default => throw new RuntimeException("Unhandled action: {$action}")
        };
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws CryptoException
     * @throws CryptoJsonException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws HPKEException
     * @throws InputException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    private function executeAddKey(
        Protocol $protocol,
        Handler $handler,
        HPKE $serverHpke,
        string $actor,
        string $latestRoot
    ): void {
        // Track how many keys have been added for this actor
        $currentKeyCount = $this->actorKeyCount[$actor] ?? 0;

        // Primary identity key is always used for signing
        $signingKey = $this->identityKeys[$actor];

        // Determine which public key to add
        if ($currentKeyCount === 0) {
            // First AddKey: self-signed, use the primary key
            $newKeyPair = $signingKey;
        } else {
            // Subsequent AddKey: use an additional key from identities
            $additionalKeyName = "{$actor}:key:{$currentKeyCount}";
            if (!isset($this->identityKeys[$additionalKeyName])) {
                throw new RuntimeException(
                    "Missing additional key {$additionalKeyName} for subsequent AddKey"
                );
            }
            $newKeyPair = $this->identityKeys[$additionalKeyName];
        }

        $addKey = new AddKey($actor, $newKeyPair->getPublicKey());
        $akm = (new AttributeKeyMap())
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $bundle = $handler->handle($addKey->encrypt($akm), $signingKey, $akm, $latestRoot);
        $encrypted = $handler->hpkeEncrypt($bundle, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->addKey($encrypted, $actor);

        // Increment key count after successful add
        $this->actorKeyCount[$actor] = $currentKeyCount + 1;
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws CryptoException
     * @throws CryptoJsonException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws HPKEException
     * @throws InputException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    private function executeFireproof(
        Protocol $protocol,
        Handler $handler,
        HPKE $serverHpke,
        string $actor,
        string $latestRoot
    ): void {
        $keypair = $this->identityKeys[$actor];
        $fireproof = new Fireproof($actor);
        $akm = (new AttributeKeyMap())
            ->addKey('actor', SymmetricKey::generate());
        $bundle = $handler->handle($fireproof->encrypt($akm), $keypair, $akm, $latestRoot);
        $encrypted = $handler->hpkeEncrypt($bundle, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->fireproof($encrypted, $actor);
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws CryptoException
     * @throws CryptoJsonException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws HPKEException
     * @throws InputException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    private function executeUndoFireproof(
        Protocol $protocol,
        Handler $handler,
        HPKE $serverHpke,
        string $actor,
        string $latestRoot
    ): void {
        $keypair = $this->identityKeys[$actor];
        $undoFireproof = new UndoFireproof($actor);
        $akm = (new AttributeKeyMap())
            ->addKey('actor', SymmetricKey::generate());
        $bundle = $handler->handle($undoFireproof->encrypt($akm), $keypair, $akm, $latestRoot);
        $encrypted = $handler->hpkeEncrypt($bundle, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->undoFireproof($encrypted, $actor);
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws CryptoException
     * @throws CryptoJsonException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws HPKEException
     * @throws InputException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    private function executeBurnDown(
        Protocol $protocol,
        Handler $handler,
        array $step,
        string $targetActor,
        string $latestRoot
    ): void {
        $operator = $this->extractOperatorFromDescription($step);
        $operatorKey = $this->identityKeys[$operator];

        $otp = '00000000';
        $burnDown = new BurnDownAction($targetActor, $operator, null, $otp);
        $akm = (new AttributeKeyMap())
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('operator', SymmetricKey::generate());
        $bundle = $handler->handle($burnDown->encrypt($akm), $operatorKey, $akm, $latestRoot);

        // OTP is a top-level Bundle field (not part of the signed/encrypted message)
        $bundleData = json_decode($bundle->toJson(), true);
        $bundleData['otp'] = $otp;
        $bundleJson = json_encode($bundleData, JSON_UNESCAPED_SLASHES);
        $protocol->burnDown($bundleJson, $operator);
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws CryptoException
     * @throws CryptoJsonException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws HPKEException
     * @throws InputException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    private function executeAddAuxData(
        Protocol $protocol,
        Handler $handler,
        HPKE $serverHpke,
        string $actor,
        array $step,
        string $latestRoot
    ): void {
        $keypair = $this->identityKeys[$actor];
        $description = $step['description'] ?? '';
        if (preg_match('/\(([^)]+)\)/', $description, $matches)) {
            $auxType = $matches[1];
        } else {
            $auxType = 'age-v1';
        }
        $auxData = 'age1ql3z7hjy54pw3hyww5ayyfg7zqgvc7w3j2elw8zmrj2kg5sfn9aqmcac8p';

        $addAuxData = new AddAuxData($actor, $auxType, $auxData);
        $akm = (new AttributeKeyMap())
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('aux-data', SymmetricKey::generate());
        $bundle = $handler->handle($addAuxData->encrypt($akm), $keypair, $akm, $latestRoot);
        $encrypted = $handler->hpkeEncrypt($bundle, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->addAuxData($encrypted, $actor);
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws CryptoException
     * @throws CryptoJsonException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws HPKEException
     * @throws InputException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    private function executeRevokeAuxData(
        Protocol $protocol,
        Handler $handler,
        HPKE $serverHpke,
        string $actor,
        array $step,
        string $latestRoot
    ): void {
        $keypair = $this->identityKeys[$actor];

        // Extract aux-type from description
        $description = $step['description'] ?? '';
        if (preg_match('/\(([^)]+)\)/', $description, $matches)) {
            $auxType = $matches[1];
        } else {
            $auxType = 'age-v1';
        }
        // For revocation, we need the aux-data to identify what to revoke
        $auxData = 'age1ql3z7hjy54pw3hyww5ayyfg7zqgvc7w3j2elw8zmrj2kg5sfn9aqmcac8p';

        $revokeAuxData = new RevokeAuxData($actor, $auxType, $auxData);
        $akm = (new AttributeKeyMap())
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('aux-data', SymmetricKey::generate());
        $bundle = $handler->handle($revokeAuxData->encrypt($akm), $keypair, $akm, $latestRoot);
        $encrypted = $handler->hpkeEncrypt($bundle, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->revokeAuxData($encrypted, $actor);
    }
}
