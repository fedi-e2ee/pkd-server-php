<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Integration;

use DateMalformedStringException;
use DateTimeImmutable;
use FediE2EE\PKD\Crypto\{
    AttributeEncryption\AttributeKeyMap,
    Merkle\IncrementalTree,
    Protocol\Handler,
    SecretKey,
    SymmetricKey
};
use FediE2EE\PKD\Crypto\Protocol\Actions\{
    AddKey,
    RevokeKey
};
use FediE2EE\PKD\Crypto\Revocation;
use FediE2EE\PKD\Crypto\Exceptions\{
    BundleException,
    CryptoException,
    InputException,
    JsonException,
    NetworkException,
    NotImplementedException,
    ParserException
};
use FediE2EE\PKDServer\RequestHandlers\Api\{
    Actor,
    ListKeys,
};
use FediE2EE\PKDServer\{
    ActivityPub\WebFinger,
    AppCache,
    Dependency\WrappedEncryptedRow,
    Math,
    Meta\Params,
    Protocol,
    Protocol\KeyWrapping,
    Protocol\RewrapConfig,
    ServerConfig,
    Table,
    TableCache
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    ConcurrentException,
    DependencyException,
    ProtocolException,
    TableException,
};
use FediE2EE\PKDServer\Tables\{
    Actors,
    AuxData,
    MerkleState,
    Peers,
    PublicKeys
};
use FediE2EE\PKDServer\Tables\Records\{
    Actor as ActorRecord,
    ActorKey,
    MerkleLeaf,
    Peer as PeerRecord
};
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use JsonException as BaseJsonException;
use GuzzleHttp\Exception\GuzzleException;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use ParagonIE\HPKE\HPKEException;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
use ReflectionClass;
use GuzzleHttp\Psr7\Response;
use ReflectionException;
use SodiumException;

#[CoversClass(ListKeys::class)]
#[CoversClass(Protocol::class)]
#[CoversClass(WebFinger::class)]
#[UsesClass(Actor::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(ActorRecord::class)]
#[UsesClass(Actors::class)]
#[UsesClass(AuxData::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(Params::class)]
#[UsesClass(MerkleState::class)]
#[UsesClass(PublicKeys::class)]
#[UsesClass(TableCache::class)]
#[UsesClass(Protocol\Payload::class)]
#[UsesClass(ServerConfig::class)]
#[UsesClass(Table::class)]
#[UsesClass(WrappedEncryptedRow::class)]
#[UsesClass(Math::class)]
#[UsesClass(KeyWrapping::class)]
#[UsesClass(Peers::class)]
#[UsesClass(RewrapConfig::class)]
#[UsesClass(PeerRecord::class)]
class ActorLifecycleTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    public function setUp(): void
    {
        $this->config = $this->getConfig();
        $this->truncateTables();
    }

    public function tearDown(): void
    {
        Handler::$wf = null;
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws CertaintyException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws HPKEException
     * @throws InputException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function testOtherActionsWithPeerRewrapping(): void
    {
        $config = $this->getConfig();
        $db = $config->getDb();
        $this->truncateTables();

        // 1. Setup peer
        $peerKey = SecretKey::generate();
        $rewrapConfig = [
            'cs' => $config->getHPKE()->cs->getSuiteName(),
            'ek' => Base64UrlSafe::encodeUnpadded($peerKey->getPublicKey()->getBytes())
        ];

        $db->insert('pkd_peers', [
            'uniqueid' => 'replica-1',
            'hostname' => 'replica1.example.org',
            'publickey' => $peerKey->getPublicKey()->toString(),
            'replicate' => 1,
            'cosign' => 1,
            'rewrap' => json_encode($rewrapConfig),
            'incrementaltreestate' => Base64UrlSafe::encodeUnpadded(
                (new IncrementalTree([], $config->getParams()->hashAlgo))->toJson()
            ),
            'latestroot' => '',
            'created' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
            'modified' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
        ]);
        $peerId = (int) $db->lastInsertId();

        // 2. Setup actor and add key
        $canonical = 'https://example.net/users/alice';
        $canonical2 = 'https://example.org/users/bob';
        $keypair = SecretKey::generate();

        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);

        $wf = new WebFinger($config);
        $wf->setCanonicalForTesting($canonical, $canonical);
        $wf->setCanonicalForTesting($canonical2, $canonical2);

        // Mock for pkd-crypto side
        Handler::$wf = new class($canonical, $canonical2) extends \FediE2EE\PKD\Crypto\ActivityPub\WebFinger {
            public function __construct(private string $c1, private string $c2) {}
            public function canonicalize(string $actor): string
            {
                if (str_contains($actor, 'alice')) {
                    return $this->c1;
                }
                if (str_contains($actor, 'bob')) {
                    return $this->c2;
                }
                return $actor;
            }
        };

        $this->table('PublicKeys')->setWebFinger($wf);
        $this->table('AuxData')->setWebFinger($wf);
        $protocol->setWebFinger($wf);

        $this->addKeyForActor($canonical, $keypair, $protocol, $config);

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $serverHpke = $config->getHPKE();
        $handler = new Handler();

        // 3. MoveIdentity
        $db->safeQuery("DELETE FROM pkd_merkle_leaf_rewrapped_keys");
        $latestRoot = $merkleState->getLatestRoot();
        // Ensure they are exactly what we expect
        $oldActor = $canonical;
        $newActor = $canonical2;
        $moveIdentity = new \FediE2EE\PKD\Crypto\Protocol\Actions\MoveIdentity($oldActor, $newActor);
        $akm = new AttributeKeyMap();
        $akm->addKey('old-actor', SymmetricKey::generate());
        $akm->addKey('new-actor', SymmetricKey::generate());
        $bundle = $handler->handle($moveIdentity->encrypt($akm), $keypair, $akm, $latestRoot);
        $encrypted = $handler->hpkeEncrypt($bundle, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->moveIdentity($encrypted, $newActor);
        $rewrapped = $db->run("SELECT * FROM pkd_merkle_leaf_rewrapped_keys WHERE peer = ?", $peerId);
        $this->assertNotEmpty($rewrapped, 'Rewrapped keys should have been stored for the peer after moveIdentity');

        // 4. Fireproof (required before UndoFireproof)
        $db->safeQuery("DELETE FROM pkd_merkle_leaf_rewrapped_keys");
        $latestRoot = $merkleState->getLatestRoot();
        $fireproof = new \FediE2EE\PKD\Crypto\Protocol\Actions\Fireproof($canonical2);
        $akm = new AttributeKeyMap();
        $akm->addKey('actor', SymmetricKey::generate());
        $bundle = $handler->handle($fireproof->encrypt($akm), $keypair, $akm, $latestRoot);
        $encrypted = $handler->hpkeEncrypt($bundle, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->fireproof($encrypted, $canonical2);
        $rewrapped = $db->run("SELECT * FROM pkd_merkle_leaf_rewrapped_keys WHERE peer = ?", $peerId);
        $this->assertNotEmpty($rewrapped, 'Rewrapped keys should have been stored for the peer after fireproof');

        // 5. UndoFireproof
        $db->safeQuery("DELETE FROM pkd_merkle_leaf_rewrapped_keys");
        $latestRoot = $merkleState->getLatestRoot();
        $undoFireproof = new \FediE2EE\PKD\Crypto\Protocol\Actions\UndoFireproof($canonical2);
        $akm = new AttributeKeyMap();
        $akm->addKey('actor', SymmetricKey::generate());
        $bundle = $handler->handle($undoFireproof->encrypt($akm), $keypair, $akm, $latestRoot);
        $encrypted = $handler->hpkeEncrypt($bundle, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->undoFireproof($encrypted, $canonical2);
        $rewrapped = $db->run("SELECT * FROM pkd_merkle_leaf_rewrapped_keys WHERE peer = ?", $peerId);
        $this->assertNotEmpty($rewrapped, 'Rewrapped keys should have been stored for the peer after undoFireproof');

        // 6. Checkpoint (Doesn't have symmetric keys, so rewrap won't happen)
        $latestRoot = $merkleState->getLatestRoot();
        $emptyRoot = $config->getParams()->getEmptyTreeRoot();
        $checkpoint = new \FediE2EE\PKD\Crypto\Protocol\Actions\Checkpoint(
            'from.example.org',
            $emptyRoot,
            SecretKey::generate()->getPublicKey(),
            'to.example.org',
            $emptyRoot
        );
        $akm = new AttributeKeyMap();
        // Checkpoints are not encrypted but still handled by Handler
        $bundle = $handler->handle($checkpoint, $keypair, $akm, $latestRoot);
        $protocol->checkpoint($bundle->toString());
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     * @throws CertaintyException
     */
    public function testAddKeyWithPeerRewrapping(): void
    {
        $config = $this->getConfig();
        $db = $config->getDb();

        // 1. Setup peer
        $peerKey = SecretKey::generate();
        $rewrapConfig = [
            'cs' => $config->getHPKE()->cs->getSuiteName(),
            'ek' => Base64UrlSafe::encodeUnpadded($peerKey->getPublicKey()->getBytes())
        ];

        $db->insert('pkd_peers', [
            'uniqueid' => 'replica-1',
            'hostname' => 'replica1.example.org',
            'publickey' => $peerKey->getPublicKey()->toString(),
            'replicate' => 1,
            'cosign' => 1,
            'rewrap' => json_encode($rewrapConfig),
            'incrementaltreestate' => Base64UrlSafe::encodeUnpadded(
                (new \FediE2EE\PKD\Crypto\Merkle\IncrementalTree([], $config->getParams()->hashAlgo))->toJson()
            ),
            'latestroot' => '',
            'created' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
            'modified' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
        ]);
        $peerId = (int) $db->lastInsertId();

        // 2. Setup actor and add key
        [, $canonical] = $this->makeDummyActor('example.net');
        $keypair = SecretKey::generate();

        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);
        $protocol->setWebFinger($this->createWebFingerMock($config, $canonical));

        $this->addKeyForActor($canonical, $keypair, $protocol, $config);

        // 3. Verify rewrapped keys
        $rewrapped = $db->run("SELECT * FROM pkd_merkle_leaf_rewrapped_keys WHERE peer = ?", $peerId);
        $this->assertNotEmpty($rewrapped, 'Rewrapped keys should have been stored for the peer');
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     * @throws CertaintyException
     */
    public function testRevokeKeyWithPeerRewrapping(): void
    {
        $config = $this->getConfig();
        $db = $config->getDb();
        $this->truncateTables();

        // 1. Setup peer
        $peerKey = SecretKey::generate();
        $rewrapConfig = [
            'cs' => $config->getHPKE()->cs->getSuiteName(),
            'ek' => Base64UrlSafe::encodeUnpadded($peerKey->getPublicKey()->getBytes())
        ];

        $db->insert('pkd_peers', [
            'uniqueid' => 'replica-1',
            'hostname' => 'replica1.example.org',
            'publickey' => $peerKey->getPublicKey()->toString(),
            'replicate' => 1,
            'cosign' => 1,
            'rewrap' => json_encode($rewrapConfig),
            'incrementaltreestate' => Base64UrlSafe::encodeUnpadded(
                (new \FediE2EE\PKD\Crypto\Merkle\IncrementalTree([], $config->getParams()->hashAlgo))->toJson()
            ),
            'latestroot' => '',
            'created' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
            'modified' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
        ]);
        $peerId = (int) $db->lastInsertId();

        // 2. Setup actor and add two keys
        [, $canonical] = $this->makeDummyActor('example.net');
        $keypair1 = SecretKey::generate();
        $keypair2 = SecretKey::generate();

        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);
        $protocol->setWebFinger($this->createWebFingerMock($config, $canonical, 4));

        // Add key1 (self-signed)
        $this->addKeyForActor($canonical, $keypair1, $protocol, $config);
        $this->assertNotInTransaction();

        // Add key2 (signed by key1)
        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $serverHpke = $config->getHPKE();
        $handler = new Handler();

        $latestRoot = $merkleState->getLatestRoot();
        $addKey2 = new AddKey($canonical, $keypair2->getPublicKey());
        $akm2 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $bundle2 = $handler->handle($addKey2->encrypt($akm2), $keypair1, $akm2, $latestRoot);
        $encrypted2 = $handler->hpkeEncrypt($bundle2, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->addKey($encrypted2, $canonical);
        $this->assertNotInTransaction();

        // Clear rewrapped keys from addKey to check revokeKey's work
        $db->safeQuery("DELETE FROM pkd_merkle_leaf_rewrapped_keys");

        // 3. Revoke key2 (signed by key1)
        $latestRoot = $merkleState->getLatestRoot();
        $revokeKey = new RevokeKey($canonical, $keypair2->getPublicKey());
        $akm = new AttributeKeyMap();
        $akm->addKey('actor', SymmetricKey::generate());
        $akm->addKey('public-key', SymmetricKey::generate());
        $bundle = $handler->handle($revokeKey->encrypt($akm), $keypair1, $akm, $latestRoot);
        $encrypted = $handler->hpkeEncrypt($bundle, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->revokeKey($encrypted, $canonical);

        // 4. Verify rewrapped keys
        $rewrapped = $db->run("SELECT * FROM pkd_merkle_leaf_rewrapped_keys WHERE peer = ?", $peerId);
        $this->assertNotEmpty($rewrapped, 'Rewrapped keys should have been stored for the peer after revokeKey');
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     * @throws CertaintyException
     */
    public function testFireproofWithPeerRewrapping(): void
    {
        $config = $this->getConfig();
        $db = $config->getDb();
        $this->truncateTables();

        // 1. Setup peer
        $peerKey = SecretKey::generate();
        $rewrapConfig = [
            'cs' => $config->getHPKE()->cs->getSuiteName(),
            'ek' => Base64UrlSafe::encodeUnpadded($peerKey->getPublicKey()->getBytes())
        ];

        $db->insert('pkd_peers', [
            'uniqueid' => 'replica-1',
            'hostname' => 'replica1.example.org',
            'publickey' => $peerKey->getPublicKey()->toString(),
            'replicate' => 1,
            'cosign' => 1,
            'rewrap' => json_encode($rewrapConfig),
            'incrementaltreestate' => Base64UrlSafe::encodeUnpadded(
                (new \FediE2EE\PKD\Crypto\Merkle\IncrementalTree([], $config->getParams()->hashAlgo))->toJson()
            ),
            'latestroot' => '',
            'created' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
            'modified' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
        ]);
        $peerId = (int) $db->lastInsertId();

        // 2. Setup actor and add key
        [, $canonical] = $this->makeDummyActor('example.net');
        $keypair = SecretKey::generate();

        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);
        $protocol->setWebFinger($this->createWebFingerMock($config, $canonical, 2));

        $this->addKeyForActor($canonical, $keypair, $protocol, $config);

        // Clear rewrapped keys from addKey to check fireproof's work
        $db->safeQuery("DELETE FROM pkd_merkle_leaf_rewrapped_keys");

        // 3. Fireproof
        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $latestRoot = $merkleState->getLatestRoot();
        $serverHpke = $config->getHPKE();
        $handler = new Handler();
        $fireproof = new \FediE2EE\PKD\Crypto\Protocol\Actions\Fireproof($canonical, new DateTimeImmutable(), $keypair->getPublicKey());
        $akm = new AttributeKeyMap();
        $akm->addKey('actor', SymmetricKey::generate());
        $akm->addKey('public-key', SymmetricKey::generate());
        $bundle = $handler->handle($fireproof->encrypt($akm), $keypair, $akm, $latestRoot);
        $encrypted = $handler->hpkeEncrypt($bundle, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->fireproof($encrypted, $canonical);

        // 4. Verify rewrapped keys
        $rewrapped = $db->run("SELECT * FROM pkd_merkle_leaf_rewrapped_keys WHERE peer = ?", $peerId);
        $this->assertNotEmpty($rewrapped, 'Rewrapped keys should have been stored for the peer after fireproof');
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     * @throws CertaintyException
     */
    public function testAddAuxDataWithPeerRewrapping(): void
    {
        $config = $this->getConfig();
        $db = $config->getDb();
        $this->truncateTables();

        // 1. Setup peer
        $peerKey = SecretKey::generate();
        $rewrapConfig = [
            'cs' => $config->getHPKE()->cs->getSuiteName(),
            'ek' => Base64UrlSafe::encodeUnpadded($peerKey->getPublicKey()->getBytes())
        ];

        $db->insert('pkd_peers', [
            'uniqueid' => 'replica-1',
            'hostname' => 'replica1.example.org',
            'publickey' => $peerKey->getPublicKey()->toString(),
            'replicate' => 1,
            'cosign' => 1,
            'rewrap' => json_encode($rewrapConfig),
            'incrementaltreestate' => Base64UrlSafe::encodeUnpadded(
                (new \FediE2EE\PKD\Crypto\Merkle\IncrementalTree([], $config->getParams()->hashAlgo))->toJson()
            ),
            'latestroot' => '',
            'created' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
            'modified' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
        ]);
        $peerId = (int) $db->lastInsertId();

        // 2. Setup actor and add key
        [, $canonical] = $this->makeDummyActor('example.net');
        $keypair = SecretKey::generate();

        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);
        $protocol->setWebFinger($this->createWebFingerMock($config, $canonical, 3));

        $this->addKeyForActor($canonical, $keypair, $protocol, $config);

        // Clear rewrapped keys from addKey
        $db->safeQuery("DELETE FROM pkd_merkle_leaf_rewrapped_keys");

        // 3. Add AuxData
        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $latestRoot = $merkleState->getLatestRoot();
        $serverHpke = $config->getHPKE();
        $handler = new Handler();
        $addAuxData = new \FediE2EE\PKD\Crypto\Protocol\Actions\AddAuxData($canonical, 'test-v1', 'some data');
        $akm = new AttributeKeyMap();
        $akm->addKey('actor', SymmetricKey::generate());
        $akm->addKey('aux-data', SymmetricKey::generate());
        $bundle = $handler->handle($addAuxData->encrypt($akm), $keypair, $akm, $latestRoot);
        $encrypted = $handler->hpkeEncrypt($bundle, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->addAuxData($encrypted, $canonical);

        // 4. Verify rewrapped keys
        $rewrapped = $db->run("SELECT * FROM pkd_merkle_leaf_rewrapped_keys WHERE peer = ?", $peerId);
        $this->assertNotEmpty($rewrapped, 'Rewrapped keys should have been stored for the peer after addAuxData');

        // 5. Revoke AuxData
        $db->safeQuery("DELETE FROM pkd_merkle_leaf_rewrapped_keys");
        $latestRoot2 = $merkleState->getLatestRoot();
        $revokeAuxData = new \FediE2EE\PKD\Crypto\Protocol\Actions\RevokeAuxData($canonical, 'test-v1', 'some data');
        $akm2 = new AttributeKeyMap();
        $akm2->addKey('actor', SymmetricKey::generate());
        $akm2->addKey('aux-data', SymmetricKey::generate());
        $bundle2 = $handler->handle($revokeAuxData->encrypt($akm2), $keypair, $akm2, $latestRoot2);
        $encrypted2 = $handler->hpkeEncrypt($bundle2, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->revokeAuxData($encrypted2, $canonical);

        // Verify rewrapped keys again
        $rewrapped = $db->run("SELECT * FROM pkd_merkle_leaf_rewrapped_keys WHERE peer = ?", $peerId);
        $this->assertNotEmpty($rewrapped, 'Rewrapped keys should have been stored for the peer after revokeAuxData');
    }

    /**
     * @return void
     * @throws ArrayKeyException
     * @throws BaseJsonException
     * @throws BlindIndexNotFoundException
     * @throws BundleException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws HPKEException
     * @throws InputException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     */
    public function testAddAndRevoke(): void
    {
        Handler::$wf = null;
        [$actorId, $canonical] = $this->makeDummyActor('example.net');

        // Generate key pair for alice
        $keypair1 = SecretKey::generate();

        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);
        $webFinger = new WebFinger($config, $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], '{"subject":"' . $canonical . '"}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"subject":"' . $canonical . '"}')
        ]));
        $protocol->setWebFinger($webFinger);

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $latestRoot1 = $merkleState->getLatestRoot();

        $serverHpke = $config->getHPKE();
        $handler = new Handler();

        // 1. AddKey (self-signed)
        $addKey1 = new AddKey($canonical, $keypair1->getPublicKey());
        $akm1 = new AttributeKeyMap()
            ->addRandomKey('actor')
            ->addRandomKey('public-key');
        $encryptedMsg1 = $addKey1->encrypt($akm1);
        $bundle1 = $handler->handle($encryptedMsg1, $keypair1, $akm1, $latestRoot1);
        $encryptedForServer1 = $handler->hpkeEncrypt(
            $bundle1,
            $serverHpke->encapsKey,
            $serverHpke->cs,
        );
        $this->assertNotInTransaction();
        $protocol->addKey($encryptedForServer1, $canonical);

        // Verify with HTTP request
        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/keys');
        $request = $request->withAttribute('actor_id', $actorId);

        $reflector = new ReflectionClass(ListKeys::class);
        $actorHandler = $reflector->newInstanceWithoutConstructor();
        $actorHandler->injectConfig($config);
        $actorHandler->setWebFinger($webFinger);

        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($actorHandler);
        }

        // Test the HTTP response
        $response = $actorHandler->handle($request);
        $this->assertNotInTransaction();
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('public-keys', $body);
        $this->assertCount(1, $body['public-keys']);
        $this->assertSame($keypair1->getPublicKey()->toString(), $body['public-keys'][0]['public-key']);

        // 2. RevokeKeyThirdParty (uses revocation token to revoke last key)
        $revocation = new Revocation();
        $token = $revocation->revokeThirdParty($keypair1);

        // RevokeKeyThirdParty uses a minimal bundle: just action + revocation-token
        $revokeJson = json_encode([
            'action' => 'RevokeKeyThirdParty',
            'revocation-token' => $token,
        ]);
        $this->assertNotInTransaction();
        $protocol->revokeKeyThirdParty($revokeJson);
        $this->assertNotInTransaction();

        // Verify with HTTP request
        $response = $actorHandler->handle($request);
        $this->assertNotInTransaction();
        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('error', $body);
        $this->assertSame('Actor not found or has no registered public keys', $body['error']);
        $this->assertNotInTransaction();
    }
}
