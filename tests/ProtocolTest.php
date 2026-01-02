<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests;

use DateMalformedStringException;
use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    JsonException,
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
    Actions\UndoFireproof,
    Handler
};
use FediE2EE\PKD\Crypto\{
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
    Protocol,
    ServerConfig,
    Table,
    TableCache
};
use FediE2EE\PKDServer\Protocol\Payload;
use FediE2EE\PKDServer\Tables\{
    Actors,
    AuxData,
    MerkleState,
    PublicKeys,
    Records\Actor as ActorRecord,
    Records\ActorKey,
    Records\MerkleLeaf,
    TOTP
};
use JsonException as BaseJsonException;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use ParagonIE\HPKE\HPKEException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SodiumException;

#[CoversClass(Protocol::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(ActorRecord::class)]
#[UsesClass(Actors::class)]
#[UsesClass(AuxData::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(MerkleState::class)]
#[UsesClass(Payload::class)]
#[UsesClass(PublicKeys::class)]
#[UsesClass(ServerConfig::class)]
#[UsesClass(Table::class)]
#[UsesClass(TableCache::class)]
#[UsesClass(TOTP::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(WrappedEncryptedRow::class)]
class ProtocolTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    protected Protocol $protocol;

    public function setUp(): void
    {
        $this->config = $this->getConfig();
        $this->protocol = new Protocol($this->config);
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
     * @throws SodiumException
     * @throws TableException
     */
    public function testAddAndRevoke(): void
    {
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
        $result1 = $this->protocol->addKey($encryptedForServer1, $canonical);
        $this->assertTrue($result1->trusted);
        $keyId1 = $result1->keyID;

        // Update latest merkle root
        $latestRoot2 = $merkleState->getLatestRoot();
        $this->assertNotSame($latestRoot1, $latestRoot2);
        $this->assertMatchesRegularExpression('#^pkd-mr-v1:[A-Za-z0-9-_]+$#', $latestRoot2);

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

        $result2 = $this->protocol->addKey($encryptedForServer2, $canonical);
        $this->assertTrue($result2->trusted);
        $this->assertNotNull($result2->keyID);
        $keyId2 = $result2->keyID;

        /** @var PublicKeys $pkTable */
        $pkTable = $this->table('PublicKeys');
        $this->assertCount(2, $pkTable->getPublicKeysFor($canonical));

        // 3. RevokeKey (signed by key 1)
        $latestRoot3 = $merkleState->getLatestRoot();
        $this->assertNotSame($latestRoot1, $latestRoot3);
        $this->assertNotSame($latestRoot2, $latestRoot3);
        $this->assertMatchesRegularExpression('#^pkd-mr-v1:[A-Za-z0-9-_]+$#', $latestRoot3);

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

        $result3 = $this->protocol->revokeKey($encryptedForServer3, $canonical);
        $this->assertFalse($result3->trusted);
        $this->assertCount(1, $pkTable->getPublicKeysFor($canonical));
    }

    /**
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
     * @throws SodiumException
     * @throws TableException
     * @throws DateMalformedStringException
     * @throws BaseJsonException
     */
    public function testMoveIdentity(): void
    {
        [$oldActor, $canonical] = $this->makeDummyActor();
        [$newActor, $canonical2] = $this->makeDummyActor();
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
        $result1 = $this->protocol->addKey($encryptedForServer1, $canonical);
        $keyId = $result1->keyID;

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
        $result2 = $this->protocol->addKey($encryptedForServer2, $canonical);
        $keyId2 = $result2->keyID;
        $this->assertCount(2, $pkTable->getPublicKeysFor($canonical));


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
        $result = $this->protocol->moveIdentity($encryptedForServer3, $canonical2);
        $this->assertTrue($result);
        $this->assertCount(0, $pkTable->getPublicKeysFor($canonical));
        $this->assertCount(2, $pkTable->getPublicKeysFor($canonical2));
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
     * @throws SodiumException
     * @throws TableException
     */
    public function testBurnDown(): void
    {
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
        $this->protocol->addKey($encryptedForServer1, $canonActor);

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
        $result2 = $this->protocol->addKey($encryptedForServer2, $canonOperator);
        $operatorKeyId = $result2->keyID;

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
        $result = $this->protocol->burnDown($encryptedForServer3, $canonOperator);
        $this->assertTrue($result);
        $this->assertCount(0, $pkTable->getPublicKeysFor($canonActor));
    }

    /**
     * @throws CacheException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function testFireproof(): void
    {
        [$actor, $canonActor] = $this->makeDummyActor();
        [$operator, $canonOperator] = $this->makeDummyActor();

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
        $result1 = $this->protocol->addKey($encryptedForServer1, $canonActor);
        $actorKeyId = $result1->keyID;

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
        $result2 = $this->protocol->addKey($encryptedForServer2, $canonOperator);
        $operatorKeyId = $result2->keyID;

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
        $result = $this->protocol->fireproof($encryptedForServer3, $canonActor);
        $this->assertTrue($result);

        // 4. BurnDown (should fail)
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Actor is fireproof');
        $latestRoot4 = $merkleState->getLatestRoot();
        $burnDown = new BurnDown($canonActor, $canonOperator);
        $akm4 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('operator', SymmetricKey::generate());
        $encryptedMsg4 = $burnDown->encrypt($akm4);
        $bundle4 = $handler->handle($encryptedMsg4, $operatorKey, $akm4, $latestRoot4);
        $encryptedForServer4 = $handler->hpkeEncrypt(
            $bundle4,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->protocol->burnDown($encryptedForServer4, $canonOperator);
    }

    /**
     * @throws CacheException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function testUndoFireproof(): void
    {
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
        $result1 = $this->protocol->addKey($encryptedForServer1, $canonicalActor);
        $actorKeyId = $result1->keyID;

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
        $result2 = $this->protocol->addKey($encryptedForServer2, $canonicalOperator);
        $operatorKeyId = $result2->keyID;

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
        $result = $this->protocol->fireproof($encryptedForServer3, $canonicalActor);
        $this->assertTrue($result);

        // 4. UndoFireproof
        $latestRoot4 = $merkleState->getLatestRoot();
        $undoFireproof = new UndoFireproof($canonicalActor);
        $akm4 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate());
        $encryptedMsg4 = $undoFireproof->encrypt($akm4);
        $bundle4 = $handler->handle($encryptedMsg4, $actorKey, $akm4, $latestRoot4, $actorKeyId);
        $encryptedForServer4 = $handler->hpkeEncrypt(
            $bundle4,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $result = $this->protocol->undoFireproof($encryptedForServer4, $canonicalActor);
        $this->assertTrue($result);

        // 5. BurnDown (should succeed)
        $latestRoot5 = $merkleState->getLatestRoot();
        $burnDown = new BurnDown($canonicalActor, $canonicalOperator);
        $akm5 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('operator', SymmetricKey::generate());
        $encryptedMsg5 = $burnDown->encrypt($akm5);
        $bundle5 = $handler->handle($encryptedMsg5, $operatorKey, $akm5, $latestRoot5, $operatorKeyId);
        $encryptedForServer5 = $handler->hpkeEncrypt(
            $bundle5,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $result = $this->protocol->burnDown($encryptedForServer5, $canonicalOperator);
        $this->assertTrue($result);
    }

    /**
     * @throws CacheException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function testAddAuxData(): void
    {
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
        $result1 = $this->protocol->addKey($encryptedForServer1, $canonEve);

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
        $result = $this->protocol->addAuxData($encryptedForServer2, $canonEve);
        $this->assertTrue($result);

        // 3. RevokeAuxData
        $latestRoot3 = $merkleState->getLatestRoot();
        $revokeAuxData = new RevokeAuxData($canonEve, 'test', 'test');
        $akm3 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('aux-type', SymmetricKey::generate())
            ->addKey('aux-data', SymmetricKey::generate());
        $encryptedMsg3 = $revokeAuxData->encrypt($akm3);
        $bundle3 = $handler->handle($encryptedMsg3, $actorKey, $akm3, $latestRoot3);
        $encryptedForServer3 = $handler->hpkeEncrypt(
            $bundle3,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $result = $this->protocol->revokeAuxData($encryptedForServer3, $canonEve);
        $this->assertTrue($result);
    }

    /**
     * @throws CacheException
     * @throws CryptoException
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function testCheckpoint(): void
    {
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
        $empty = new AttributeKeyMap();
        $bundle = $handler->handle($checkpoint, $directoryKey, $empty, $latestRoot1);
        $result = $this->protocol->checkpoint($bundle->toString());
        $this->assertTrue($result);
    }
}
