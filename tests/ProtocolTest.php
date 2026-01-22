<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests;

use DateMalformedStringException;
use DateTimeImmutable;
use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Exceptions\{
    BundleException,
    CryptoException,
    JsonException,
    NetworkException,
    NotImplementedException,
    ParserException
};
use FediE2EE\PKD\Crypto\Protocol\{
    Actions\AddAuxData,
    Actions\AddKey,
    Actions\BurnDown,
    Actions\Checkpoint,
    Actions\Fireproof,
    Actions\MoveIdentity,
    Actions\RevokeAuxData,
    Actions\RevokeKey,
    Actions\RevokeKeyThirdParty,
    Actions\UndoFireproof,
    Handler
};
use ParagonIE\Certainty\Exception\CertaintyException;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
use FediE2EE\PKD\Crypto\{
    Merkle\IncrementalTree,
    Revocation,
    SecretKey,
    SymmetricKey
};
use FediE2EE\PKDServer\Traits\ConfigTrait;
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\{
    AppCache,
    Math,
    Protocol,
    Protocol\RewrapConfig,
    ServerConfig,
    Table,
    TableCache
};
use FediE2EE\PKDServer\Protocol\{
    KeyWrapping,
    Payload,
};
use FediE2EE\PKDServer\Tables\{
    Actors,
    AuxData,
    MerkleState,
    Peers,
    PublicKeys,
    Records\Actor as ActorRecord,
    Records\ActorKey,
    Records\MerkleLeaf,
    Records\Peer,
    TOTP
};
use JsonException as BaseJsonException;
use ParagonIE\CipherSweet\Exception\{
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use ParagonIE\HPKE\HPKEException;
use ParagonIE\ConstantTime\Base64UrlSafe;
use FediE2EE\PKDServer\ActivityPub\ActivityStream;
use PHPUnit\Framework\{
    Attributes\CoversClass,
    Attributes\UsesClass,
    TestCase
};
use SodiumException;

#[CoversClass(Protocol::class)]
#[UsesClass(ActivityStream::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(ActorRecord::class)]
#[UsesClass(Actors::class)]
#[UsesClass(AuxData::class)]
#[UsesClass(KeyWrapping::class)]
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
#[UsesClass(Math::class)]
class ProtocolTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    protected Protocol $protocol;

    /**
     * @throws DependencyException
     */
    public function setUp(): void
    {
        $this->config = $this->getConfig();
        $this->protocol = new Protocol($this->config);
    }

    /**
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    protected function addTestPeer(): void
    {
        $peerKey = SecretKey::generate();
        $serverHpke = $this->config->getHPKE();
        $rewrapConfig = [
            'cs' => $serverHpke->cs->getSuiteName(),
            'ek' => Base64UrlSafe::encodeUnpadded($peerKey->getPublicKey()->getBytes())
        ];
        $tree = new IncrementalTree([], $this->config->getParams()->hashAlgo);
        $this->config->getDb()->insert('pkd_peers', [
            'uniqueid' => 'peer-test',
            'hostname' => 'peer-test.example.com',
            'publickey' => $peerKey->getPublicKey()->toString(),
            'replicate' => 1,
            'cosign' => 1,
            'rewrap' => json_encode($rewrapConfig),
            'incrementaltreestate' => Base64UrlSafe::encodeUnpadded($tree->toJson()),
            'latestroot' => '',
            'created' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
            'modified' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
        ]);
    }

    protected function assertKeyRewrapped(string $merkleRoot, string $message): void
    {
        $rewrapped = $this->config->getDb()->exists(
            "SELECT count(rw.rewrappedkeyid) 
                FROM pkd_merkle_leaf_rewrapped_keys rw
                JOIN pkd_merkle_leaves ml
                    ON rw.leaf = ml.merkleleafid 
                WHERE ml.root = ?",
            $merkleRoot
        );
        $this->assertTrue($rewrapped, $message);
    }

    /**
     * @throws BaseJsonException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function testAddAndRevoke(): void
    {
        $this->clearOldTransaction($this->config);
        $this->truncateTables();
        $this->addTestPeer();
        [$actor, $canonical] = $this->makeDummyActor();

        // Generate two key pairs for alice
        $keypair1 = SecretKey::generate();
        $keypair2 = SecretKey::generate();

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $latestRoot1 = $merkleState->getLatestRoot();
        $this->assertMatchesRegularExpression('#^pkd-mr-v1:[A-Za-z0-9-_]+$#', $latestRoot1);

        $serverHpke = $this->config->getHPKE();
        $handler = new Handler();

        // 1. First AddKey (self-signed)
        $addKey1 = new AddKey($canonical, $keypair1->getPublicKey());
        $akm1 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg1 = $addKey1->encrypt($akm1);
        $bundle1 = $handler->handle($encryptedMsg1, $keypair1, $akm1, $latestRoot1);

        $encryptedForServer1 = $handler->hpkeEncrypt(
            $bundle1,
            $serverHpke->encapsKey,
            $serverHpke->cs,
        );
        $this->assertNotInTransaction();
        $result1 = $this->protocol->addKey($encryptedForServer1, $canonical);
        $this->assertTrue($result1->trusted);
        $keyId1 = $result1->keyID;
        $this->ensureMerkleStateUnlocked();

        // Update latest merkle root
        $latestRoot2 = $merkleState->getLatestRoot();
        $this->assertNotSame($latestRoot1, $latestRoot2);
        $this->assertKeyRewrapped($latestRoot2, 'Key should be rewrapped after first addKey');

        // 2. Second AddKey (signed by key 1)
        $addKey2 = new AddKey($canonical, $keypair2->getPublicKey());
        $akm2 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg2 = $addKey2->encrypt($akm2);
        $bundle2 = $handler->handle($encryptedMsg2, $keypair1, $akm2, $latestRoot2);

        $encryptedForServer2 = $handler->hpkeEncrypt(
            $bundle2,
            $serverHpke->encapsKey,
            $serverHpke->cs,
        );

        $this->assertNotInTransaction();
        $result2 = $this->protocol->addKey($encryptedForServer2, $canonical);
        $this->assertTrue($result2->trusted);
        $this->assertNotNull($result2->keyID);
        $keyId2 = $result2->keyID;
        $this->ensureMerkleStateUnlocked();

        /** @var PublicKeys $pkTable */
        $pkTable = $this->table('PublicKeys');
        $this->assertCount(2, $pkTable->getPublicKeysFor($canonical));
        $latestRoot3 = $merkleState->getLatestRoot();
        $this->assertKeyRewrapped($latestRoot3, 'Key should be rewrapped after second addKey');

        // 3. RevokeKey (signed by key 1)
        $this->assertNotSame($latestRoot1, $latestRoot3);
        $this->assertNotSame($latestRoot2, $latestRoot3);

        $revokeKey = new RevokeKey($canonical, $keypair2->getPublicKey());
        $akm3 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());

        $encryptedMsg3 = $revokeKey->encrypt($akm3);
        $bundle3 = $handler->handle($encryptedMsg3, $keypair1, $akm3, $latestRoot3);

        $encryptedForServer3 = $handler->hpkeEncrypt(
            $bundle3,
            $serverHpke->encapsKey,
            $serverHpke->cs,
        );

        $this->assertNotInTransaction();
        $result3 = $this->protocol->revokeKey($encryptedForServer3, $canonical);
        $this->assertFalse($result3->trusted);
        $this->assertCount(1, $pkTable->getPublicKeysFor($canonical));
        $this->ensureMerkleStateUnlocked();
        $latestRoot4 = $merkleState->getLatestRoot();
        $this->assertKeyRewrapped($latestRoot4, 'Key should be rewrapped after revokeKey');
    }

    /**
     * @throws BaseJsonException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function testMoveIdentity(): void
    {
        $this->clearOldTransaction($this->config);
        $this->truncateTables();
        $this->addTestPeer();
        [$oldActor, $canonical] = $this->makeDummyActor();
        [$newActor, $canonical2] = $this->makeDummyActor();
        $wf = new WebFinger($this->config);
        $wf->setCanonicalForTesting($oldActor, $canonical);
        $wf->setCanonicalForTesting($newActor, $canonical2);
        $wf->setCanonicalForTesting($canonical, $canonical);
        $wf->setCanonicalForTesting($canonical2, $canonical2);
        $this->protocol->setWebFinger($wf);
        $this->table('PublicKeys')->setWebFinger($wf);

        $keypair = SecretKey::generate();
        $keypair2 = SecretKey::generate();

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $latestRoot1 = $merkleState->getLatestRoot();

        $serverHpke = $this->config->getHPKE();
        $handler = new Handler();

        // 1. AddKey (self-signed)
        $addKey = new AddKey($canonical, $keypair->getPublicKey());
        $akm1 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg1 = $addKey->encrypt($akm1);
        $bundle1 = $handler->handle($encryptedMsg1, $keypair, $akm1, $latestRoot1);

        $encryptedForServer1 = $handler->hpkeEncrypt(
            $bundle1,
            $serverHpke->encapsKey,
            $serverHpke->cs,
        );
        $this->assertNotInTransaction();
        $result1 = $this->protocol->addKey($encryptedForServer1, $canonical);
        $keyId = $result1->keyID;
        $this->ensureMerkleStateUnlocked();

        /** @var PublicKeys $pkTable */
        $pkTable = $this->table('PublicKeys');
        $this->assertCount(1, $pkTable->getPublicKeysFor($canonical));

        // 2. Add second key
        $latestRoot2 = $merkleState->getLatestRoot();
        $addKey2 = new AddKey($canonical, $keypair2->getPublicKey());
        $akm2 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg2 = $addKey2->encrypt($akm2);
        $bundle2 = $handler->handle($encryptedMsg2, $keypair, $akm2, $latestRoot2, $keyId);
        $encryptedForServer2 = $handler->hpkeEncrypt(
            $bundle2,
            $serverHpke->encapsKey,
            $serverHpke->cs,
        );
        $this->assertNotInTransaction();
        $result2 = $this->protocol->addKey($encryptedForServer2, $canonical);
        $keyId2 = $result2->keyID;
        $this->assertCount(2, $pkTable->getPublicKeysFor($canonical));
        $this->ensureMerkleStateUnlocked();


        // 3. MoveIdentity
        $latestRoot3 = $merkleState->getLatestRoot();
        $moveIdentity = new MoveIdentity($canonical, $canonical2);
        $akm3 = new AttributeKeyMap()
            ->addKey('old-actor', SymmetricKey::generate())
            ->addKey('new-actor', SymmetricKey::generate());
        $encryptedMsg3 = $moveIdentity->encrypt($akm3);
        $bundle3 = $handler->handle($encryptedMsg3, $keypair2, $akm3, $latestRoot3, $keyId2);

        $encryptedForServer3 = $handler->hpkeEncrypt(
            $bundle3,
            $serverHpke->encapsKey,
            $serverHpke->cs,
        );
        $this->assertNotInTransaction();
        $result = $this->protocol->moveIdentity($encryptedForServer3, $canonical);
        $this->assertTrue($result);
        $this->assertCount(0, $pkTable->getPublicKeysFor($canonical));
        $this->assertCount(2, $pkTable->getPublicKeysFor($canonical2));
        $latestRoot4 = $merkleState->getLatestRoot();
        $this->assertKeyRewrapped($latestRoot4, 'Key should be rewrapped after moveIdentity');
        $this->ensureMerkleStateUnlocked();
    }

    /**
     * @throws BaseJsonException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function testBurnDown(): void
    {
        $this->clearOldTransaction($this->config);
        $this->truncateTables();
        $this->addTestPeer();
        [, $canonActor] = $this->makeDummyActor();
        [, $canonOperator] = $this->makeDummyActor();

        $actorKey = SecretKey::generate();
        $operatorKey = SecretKey::generate();

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $serverHpke = $this->config->getHPKE();
        $handler = new Handler();

        // 1. AddKey for actor
        $latestRoot1 = $merkleState->getLatestRoot();
        $addKey1 = new AddKey($canonActor, $actorKey->getPublicKey());
        $akm1 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg1 = $addKey1->encrypt($akm1);
        $bundle1 = $handler->handle($encryptedMsg1, $actorKey, $akm1, $latestRoot1);
        $encryptedForServer1 = $handler->hpkeEncrypt(
            $bundle1,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $this->protocol->addKey($encryptedForServer1, $canonActor);
        $this->ensureMerkleStateUnlocked();

        // 2. AddKey for operator
        $latestRoot2 = $merkleState->getLatestRoot();
        $addKey2 = new AddKey($canonOperator, $operatorKey->getPublicKey());
        $akm2 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg2 = $addKey2->encrypt($akm2);
        $bundle2 = $handler->handle($encryptedMsg2, $operatorKey, $akm2, $latestRoot2);
        $encryptedForServer2 = $handler->hpkeEncrypt(
            $bundle2,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $result2 = $this->protocol->addKey($encryptedForServer2, $canonOperator);
        $operatorKeyId = $result2->keyID;
        $this->ensureMerkleStateUnlocked();

        /** @var PublicKeys $pkTable */
        $pkTable = $this->table('PublicKeys');
        $this->assertCount(1, $pkTable->getPublicKeysFor($canonActor));

        // 3. BurnDown
        $latestRoot3 = $merkleState->getLatestRoot();
        $burnDown = new BurnDown($canonActor, $canonOperator);
        $akm3 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('operator', SymmetricKey::generate());
        $encryptedMsg3 = $burnDown->encrypt($akm3);
        $bundle3 = $handler->handle($encryptedMsg3, $operatorKey, $akm3, $latestRoot3, $operatorKeyId);
        $encryptedForServer3 = $handler->hpkeEncrypt(
            $bundle3,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('BurnDown MUST NOT be encrypted.');
        try {
            $this->assertTrue($this->protocol->burnDown($encryptedForServer3, $canonOperator));
        } finally {
            $this->ensureMerkleStateUnlocked();
        }
        $this->assertCount(0, $pkTable->getPublicKeysFor($canonActor));
        $latestRoot4 = $merkleState->getLatestRoot();
        $this->assertKeyRewrapped($latestRoot4, 'Key should be rewrapped after burnDown');
    }

    /**
     * @throws CacheException
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function testFireproof(): void
    {
        $this->clearOldTransaction($this->config);
        $this->truncateTables();
        $this->addTestPeer();
        [, $canonActor] = $this->makeDummyActor();
        [, $canonOperator] = $this->makeDummyActor();

        $actorKey = SecretKey::generate();
        $operatorKey = SecretKey::generate();

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $serverHpke = $this->config->getHPKE();
        $handler = new Handler();

        // 1. AddKey for actor
        $latestRoot1 = $merkleState->getLatestRoot();
        $addKey1 = new AddKey($canonActor, $actorKey->getPublicKey());
        $akm1 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg1 = $addKey1->encrypt($akm1);
        $bundle1 = $handler->handle($encryptedMsg1, $actorKey, $akm1, $latestRoot1);
        $encryptedForServer1 = $handler->hpkeEncrypt(
            $bundle1,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $result1 = $this->protocol->addKey($encryptedForServer1, $canonActor);
        $actorKeyId = $result1->keyID;
        $this->ensureMerkleStateUnlocked();

        // 2. AddKey for operator
        $latestRoot2 = $merkleState->getLatestRoot();
        $addKey2 = new AddKey($canonOperator, $operatorKey->getPublicKey());
        $akm2 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg2 = $addKey2->encrypt($akm2);
        $bundle2 = $handler->handle($encryptedMsg2, $operatorKey, $akm2, $latestRoot2);
        $encryptedForServer2 = $handler->hpkeEncrypt(
            $bundle2,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $result2 = $this->protocol->addKey($encryptedForServer2, $canonOperator);
        $operatorKeyId = $result2->keyID;
        $this->ensureMerkleStateUnlocked();

        // 3. Fireproof
        $latestRoot3 = $merkleState->getLatestRoot();
        $fireproof = new Fireproof($canonActor);
        $akm3 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate());
        $encryptedMsg3 = $fireproof->encrypt($akm3);
        $bundle3 = $handler->handle($encryptedMsg3, $actorKey, $akm3, $latestRoot3);
        $encryptedForServer3 = $handler->hpkeEncrypt(
            $bundle3,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $result = $this->protocol->fireproof($encryptedForServer3, $canonActor);
        $this->assertTrue($result);
        $latestRoot4 = $merkleState->getLatestRoot();
        $this->assertKeyRewrapped($latestRoot4, 'Key should be rewrapped after fireproof');
        $this->ensureMerkleStateUnlocked();

        // 4. BurnDown (should fail)
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('BurnDown MUST NOT be encrypted.');
        $latestRoot5 = $merkleState->getLatestRoot();
        $burnDown = new BurnDown($canonActor, $canonOperator);
        $akm4 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('operator', SymmetricKey::generate());
        $encryptedMsg4 = $burnDown->encrypt($akm4);
        $bundle4 = $handler->handle($encryptedMsg4, $operatorKey, $akm4, $latestRoot5);
        $encryptedForServer4 = $handler->hpkeEncrypt(
            $bundle4,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        try {
            $this->protocol->burnDown($encryptedForServer4, $canonOperator);
            $this->assertNotInTransaction();
        } catch (NetworkException $e) {
            throw new ProtocolException($e->getMessage(), $e->getCode(), $e);
        } finally {
            $this->ensureMerkleStateUnlocked();
            $this->clearOldTransaction($this->config);
        }
    }

    /**
     * @throws CacheException
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function testUndoFireproof(): void
    {
        $this->clearOldTransaction($this->config);
        $this->truncateTables();
        $this->addTestPeer();
        [$actor, $canonicalActor] = $this->makeDummyActor();
        [$operator, $canonicalOperator] = $this->makeDummyActor();

        $actorKey = SecretKey::generate();
        $operatorKey = SecretKey::generate();

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $serverHpke = $this->config->getHPKE();
        $handler = new Handler();

        // 1. AddKey for actor
        $latestRoot1 = $merkleState->getLatestRoot();
        $addKey1 = new AddKey($canonicalActor, $actorKey->getPublicKey());
        $akm1 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg1 = $addKey1->encrypt($akm1);
        $bundle1 = $handler->handle($encryptedMsg1, $actorKey, $akm1, $latestRoot1);
        $encryptedForServer1 = $handler->hpkeEncrypt(
            $bundle1,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $result1 = $this->protocol->addKey($encryptedForServer1, $canonicalActor);
        $this->clearOldTransaction($this->config);
        $actorKeyId = $result1->keyID;
        $this->ensureMerkleStateUnlocked();

        // 2. AddKey for operator
        $latestRoot2 = $merkleState->getLatestRoot();
        $addKey2 = new AddKey($canonicalOperator, $operatorKey->getPublicKey());
        $akm2 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg2 = $addKey2->encrypt($akm2);
        $bundle2 = $handler->handle($encryptedMsg2, $operatorKey, $akm2, $latestRoot2);
        $encryptedForServer2 = $handler->hpkeEncrypt(
            $bundle2,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $result2 = $this->protocol->addKey($encryptedForServer2, $canonicalOperator);
        $this->clearOldTransaction($this->config);
        $operatorKeyId = $result2->keyID;
        $this->ensureMerkleStateUnlocked();

        // 3. Fireproof
        $latestRoot3 = $merkleState->getLatestRoot();
        $fireproof = new Fireproof($canonicalActor);
        $akm3 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate());
        $encryptedMsg3 = $fireproof->encrypt($akm3);
        $bundle3 = $handler->handle($encryptedMsg3, $actorKey, $akm3, $latestRoot3, $actorKeyId);
        $encryptedForServer3 = $handler->hpkeEncrypt(
            $bundle3,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $result = $this->protocol->fireproof($encryptedForServer3, $canonicalActor);
        $this->clearOldTransaction($this->config);
        $this->assertTrue($result);
        $latestRoot4 = $merkleState->getLatestRoot();
        $this->assertKeyRewrapped($latestRoot4, 'Key should be rewrapped after fireproof');
        $this->ensureMerkleStateUnlocked();

        // 4. UndoFireproof
        $latestRoot5 = $merkleState->getLatestRoot();
        $undoFireproof = new UndoFireproof($canonicalActor);
        $akm4 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate());
        $encryptedMsg4 = $undoFireproof->encrypt($akm4);
        $bundle4 = $handler->handle($encryptedMsg4, $actorKey, $akm4, $latestRoot5, $actorKeyId);
        $encryptedForServer4 = $handler->hpkeEncrypt(
            $bundle4,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $result = $this->protocol->undoFireproof($encryptedForServer4, $canonicalActor);
        $this->clearOldTransaction($this->config);
        $this->assertTrue($result);
        $latestRoot6 = $merkleState->getLatestRoot();
        $this->assertKeyRewrapped($latestRoot6, 'Key should be rewrapped after undoFireproof');
        $this->ensureMerkleStateUnlocked();

        // 5. BurnDown (should succeed)
        $latestRoot7 = $merkleState->getLatestRoot();
        $burnDown = new BurnDown($canonicalActor, $canonicalOperator);
        $akm5 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('operator', SymmetricKey::generate());
        $encryptedMsg5 = $burnDown->encrypt($akm5);
        $bundle5 = $handler->handle($encryptedMsg5, $operatorKey, $akm5, $latestRoot7, $operatorKeyId);
        $encryptedForServer5 = $handler->hpkeEncrypt(
            $bundle5,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('BurnDown MUST NOT be encrypted.');
        try {
            $this->assertTrue(
                $this->protocol->burnDown($encryptedForServer5, $canonicalOperator)
            );
        } finally {
            $this->ensureMerkleStateUnlocked();
            $this->clearOldTransaction($this->config);
        }
        $this->assertNotInTransaction();
    }

    /**
     * @throws CacheException
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function testAddAuxData(): void
    {
        $this->clearOldTransaction($this->config);
        $this->truncateTables();
        $this->addTestPeer();
        [$actor, $canonEve] = $this->makeDummyActor();
        $actorKey = SecretKey::generate();

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $serverHpke = $this->config->getHPKE();
        $handler = new Handler();

        // 1. AddKey for actor
        $latestRoot1 = $merkleState->getLatestRoot();
        $addKey1 = new AddKey($canonEve, $actorKey->getPublicKey());
        $akm1 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg1 = $addKey1->encrypt($akm1);
        $bundle1 = $handler->handle($encryptedMsg1, $actorKey, $akm1, $latestRoot1);
        $encryptedForServer1 = $handler->hpkeEncrypt(
            $bundle1,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $result1 = $this->protocol->addKey($encryptedForServer1, $canonEve);
        $this->ensureMerkleStateUnlocked();
        $this->assertNotInTransaction();

        // 2. AddAuxData
        $latestRoot2 = $merkleState->getLatestRoot();
        $addAuxData = new AddAuxData($canonEve, 'test', 'test');
        $akm2 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('aux-type', SymmetricKey::generate())
            ->addKey('aux-data', SymmetricKey::generate());
        $encryptedMsg2 = $addAuxData->encrypt($akm2);
        $bundle2 = $handler->handle($encryptedMsg2, $actorKey, $akm2, $latestRoot2);
        $encryptedForServer2 = $handler->hpkeEncrypt(
            $bundle2,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $result = $this->protocol->addAuxData($encryptedForServer2, $canonEve);
        $this->assertTrue($result);
        $latestRoot3 = $merkleState->getLatestRoot();
        $this->assertKeyRewrapped($latestRoot3, 'Key should be rewrapped after addAuxData');
        $this->ensureMerkleStateUnlocked();

        // 3. RevokeAuxData
        $latestRoot4 = $merkleState->getLatestRoot();
        $revokeAuxData = new RevokeAuxData($canonEve, 'test', 'test');
        $akm3 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('aux-type', SymmetricKey::generate())
            ->addKey('aux-data', SymmetricKey::generate());
        $encryptedMsg3 = $revokeAuxData->encrypt($akm3);
        $bundle3 = $handler->handle($encryptedMsg3, $actorKey, $akm3, $latestRoot4);
        $encryptedForServer3 = $handler->hpkeEncrypt(
            $bundle3,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $result = $this->protocol->revokeAuxData($encryptedForServer3, $canonEve);
        $this->assertTrue($result);
        $latestRoot5 = $merkleState->getLatestRoot();
        $this->assertKeyRewrapped($latestRoot5, 'Key should be rewrapped after revokeAuxData');
        $this->ensureMerkleStateUnlocked();
        $this->assertNotInTransaction();
    }

    /**
     * @throws BundleException
     * @throws CacheException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function testCheckpoint(): void
    {
        $this->clearOldTransaction($this->config);
        $this->truncateTables();
        $this->addTestPeer();
        $directory = 'example.net';
        $directoryKey = SecretKey::generate();

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $serverHpke = $this->config->getHPKE();
        $handler = new Handler();

        // 1. Checkpoint
        $latestRoot1 = $merkleState->getLatestRoot();
        $checkpoint = new Checkpoint(
            'https://' . $directory,
            $latestRoot1,
            $directoryKey->getPublicKey(),
            'https://' . 'example.com',
            $latestRoot1
        );

        $hpke = $this->config->getHPKE();
        $this->table('Peers')->create(
            $this->config->getSigningKeys()->publicKey,
            'localhost',
            false,
            true, // replicate
            RewrapConfig::from($hpke->cs, $hpke->encapsKey)
        );

        $akm = new AttributeKeyMap();
        $akm->addKey('test', SymmetricKey::generate());

        $bundle = $handler->handle($checkpoint, $directoryKey, $akm, $latestRoot1);
        $this->assertNotInTransaction();
        $result = $this->protocol->checkpoint($bundle->toString());
        $this->assertTrue($result);
        $this->assertNotInTransaction();
        $this->ensureMerkleStateUnlocked();

        // Verify that the key was rewrapped in the database
        $latestRoot2 = $merkleState->getLatestRoot();
        $this->assertKeyRewrapped($latestRoot2, 'Key should be rewrapped after checkpoint');
    }

    /**
     * @throws BaseJsonException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function testRevokeKeyThirdParty(): void
    {
        $this->clearOldTransaction($this->config);
        $this->truncateTables();
        $this->addTestPeer();
        [$actor, $canonical] = $this->makeDummyActor();
        $keypair = SecretKey::generate();

        $protocol = new Protocol($this->config);
        $this->addKeyForActor($canonical, $keypair, $protocol, $this->config);
        $this->ensureMerkleStateUnlocked();

        /** @var PublicKeys $pkTable */
        $pkTable = $this->table('PublicKeys');
        $this->assertCount(1, $pkTable->getPublicKeysFor($canonical));

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $latestRoot = $merkleState->getLatestRoot();

        // Create revocation token
        $revocation = new Revocation();
        $token = $revocation->revokeThirdParty($keypair);

        $revokeAction = new RevokeKeyThirdParty($token);
        $handler = new Handler();
        $bundle = $handler->handle($revokeAction, $keypair, new AttributeKeyMap(), $latestRoot);

        $result = $this->protocol->revokeKeyThirdParty($bundle->toString());
        $this->assertTrue($result);
        $this->assertCount(0, $pkTable->getPublicKeysFor($canonical));
        $this->ensureMerkleStateUnlocked();
    }
}
