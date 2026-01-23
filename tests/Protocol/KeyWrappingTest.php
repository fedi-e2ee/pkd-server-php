<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Protocol;

use FediE2EE\PKD\Crypto\Exceptions\{CryptoException,
    InputException,
    JsonException,
    NotImplementedException,
    ParserException};
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use FediE2EE\PKD\Crypto\Merkle\IncrementalTree;
use FediE2EE\PKD\Crypto\SecretKey;
use FediE2EE\PKDServer\{
    ActivityPub\WebFinger,
    AppCache,
    Math,
    Meta\Params,
    Protocol\KeyWrapping,
    Protocol,
    Protocol\RewrapConfig,
    ServerConfig,
    Table,
    TableCache
};
use FediE2EE\PKDServer\Dependency\{
    HPKE,
    SigningKeys,
    WrappedEncryptedRow
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    ProtocolException,
    TableException,
};
use FediE2EE\PKDServer\Protocol\Payload;
use FediE2EE\PKDServer\Tables\{
    Actors,
    MerkleState,
    Peers,
    PublicKeys
};
use FediE2EE\PKDServer\Tables\Records\{
    Actor,
    ActorKey,
    MerkleLeaf,
    Peer
};
use ParagonIE\ConstantTime\Base64UrlSafe;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\HPKE\HPKEException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
use SodiumException;

#[CoversClass(KeyWrapping::class)]
#[UsesClass(ServerConfig::class)]
#[UsesClass(Protocol::class)]
#[UsesClass(Payload::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(Actors::class)]
#[UsesClass(Peers::class)]
#[UsesClass(PublicKeys::class)]
#[UsesClass(MerkleState::class)]
#[UsesClass(Actor::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(Table::class)]
#[UsesClass(TableCache::class)]
#[UsesClass(Params::class)]
#[UsesClass(HPKE::class)]
#[UsesClass(SigningKeys::class)]
#[UsesClass(WrappedEncryptedRow::class)]
#[UsesClass(Math::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(RewrapConfig::class)]
#[UsesClass(Peer::class)]
class KeyWrappingTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    /**
     * @throws DependencyException
     * @throws SodiumException
     */
    public function setUp(): void
    {
        $this->config = $this->getConfig();
        $this->truncateTables();
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
    public function testDecryptAndGetRewrappedCaching(): void
    {
        $keyWrapping = new KeyWrapping($this->config);

        // Add a peer so rewrapSymmetricKeys has someone to rewrap for
        $peerKey = SecretKey::generate();
        $serverHpke = $this->config->getHPKE();
        $rewrapConfig = [
            'cs' => $serverHpke->cs->getSuiteName(),
            'ek' => Base64UrlSafe::encodeUnpadded($peerKey->getPublicKey()->getBytes())
        ];
        $tree = new IncrementalTree([], $this->config->getParams()->hashAlgo);
        $this->config->getDb()->insert('pkd_peers', [
            'uniqueid' => 'peer1',
            'hostname' => 'peer1.example.com',
            'publickey' => $peerKey->getPublicKey()->toString(),
            'replicate' => 1,
            'cosign' => 1,
            'rewrap' => json_encode($rewrapConfig),
            'incrementaltreestate' => Base64UrlSafe::encodeUnpadded($tree->toJson()),
            'latestroot' => '',
            'created' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
            'modified' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
        ]);

        [, $canonical] = $this->makeDummyActor();
        $keypair = SecretKey::generate();

        $protocol = new Protocol($this->config);
        $this->addKeyForActor($canonical, $keypair, $protocol, $this->config);

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $root = $merkleState->getLatestRoot();

        // Get the wrapped keys for this root
        $wrappedKeys = $this->config->getDb()->cell(
            "SELECT wrappedkeys FROM pkd_merkle_leaves WHERE root = ?",
            $root
        );

        $this->assertNotEmpty($wrappedKeys);

        // First call - should populate cache
        [$message1, $rewrapped1] = $keyWrapping->decryptAndGetRewrapped($root, $wrappedKeys);
        $this->assertNotNull($message1);
        $this->assertNotEmpty($rewrapped1, 'Rewrapped keys should not be empty');
        $this->assertArrayHasKey('peer1', $rewrapped1);

        $cache = $this->appCache('key-wrapping-decrypt');
        $lookupKey = $root . ':' . $wrappedKeys;
        $deriveKey = $cache->deriveKey($lookupKey);

        $this->assertTrue($cache->has($deriveKey), 'Cache should have the key after first call');
        $cachedValue = $cache->get($deriveKey);
        $this->assertIsString($cachedValue);

        $decoded = json_decode($cachedValue, true);
        $this->assertEquals([$message1, $rewrapped1], $decoded);

        // Second call - should use cache
        [$message2, $rewrapped2] = $keyWrapping->decryptAndGetRewrapped($root, $wrappedKeys);
        $this->assertEquals($message1, $message2);
        $this->assertEquals($rewrapped1, $rewrapped2);
    }

    /**
     * @throws CacheException
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DateMalformedStringException
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
    public function testRewrapSymmetricKeysRetrievesLocalKeys(): void
    {
        $keyWrapping = new KeyWrapping($this->config);

        // Add a peer
        $peerKey = SecretKey::generate();
        $serverHpke = $this->config->getHPKE();
        $rewrapConfig = [
            'cs' => $serverHpke->cs->getSuiteName(),
            'ek' => Base64UrlSafe::encodeUnpadded($peerKey->getPublicKey()->getBytes())
        ];
        $tree = new IncrementalTree([], $this->config->getParams()->hashAlgo);
        $this->config->getDb()->insert('pkd_peers', [
            'uniqueid' => 'peer2',
            'hostname' => 'peer2.example.com',
            'publickey' => $peerKey->getPublicKey()->toString(),
            'replicate' => 1,
            'cosign' => 1,
            'rewrap' => json_encode($rewrapConfig),
            'incrementaltreestate' => Base64UrlSafe::encodeUnpadded($tree->toJson()),
            'latestroot' => '',
            'created' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
            'modified' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
        ]);

        [, $canonical] = $this->makeDummyActor();
        $keypair = SecretKey::generate();

        $protocol = new Protocol($this->config);
        $this->addKeyForActor($canonical, $keypair, $protocol, $this->config);

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $root = $merkleState->getLatestRoot();

        // Clear rewrapped keys for this root to test rewrap
        $leafId = $this->config->getDb()->cell("SELECT merkleleafid FROM pkd_merkle_leaves WHERE root = ?", $root);
        $this->config->getDb()->delete('pkd_merkle_leaf_rewrapped_keys', ['leaf' => $leafId]);

        // Call rewrapSymmetricKeys with NULL keyMap - should trigger retrieveLocalWrappedKeys
        $keyWrapping->rewrapSymmetricKeys($root, null);

        // Verify rewrap occurred
        $rewrapped = $this->config->getDb()->exists(
            "SELECT count(*) FROM pkd_merkle_leaf_rewrapped_keys WHERE leaf = ?",
            $leafId
        );
        $this->assertTrue($rewrapped, 'Key should be rewrapped after rewrapSymmetricKeys(null)');
    }

    /**
     * @throws DependencyException
     * @throws InputException
     */
    public function testGetRewrappedForMultipleAttributes(): void
    {
        $keyWrapping = new KeyWrapping($this->config);
        $db = $this->config->getDb();
        $sk = SecretKey::generate();

        // 1. Setup a leaf
        $db->insert('pkd_merkle_leaves', [
            'root' => 'root-multi',
            'contents' => '...',
            'wrappedkeys' => '...',
            'created' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
        ]);
        $leafId = $db->lastInsertId();

        // 2. Setup a peer
        $db->insert('pkd_peers', [
            'uniqueid' => 'peer-multi',
            'hostname' => 'multi.example.com',
            'publickey' => $sk->getPublicKey()->toString(),
            'replicate' => 1,
            'cosign' => 0,
            'latestroot' => '',
            'created' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
            'modified' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
        ]);
        $peerId = $db->lastInsertId();

        // 3. Insert multiple rewrapped keys for the SAME peer and SAME leaf
        $db->insert('pkd_merkle_leaf_rewrapped_keys', [
            'leaf' => $leafId,
            'peer' => $peerId,
            'pkdattrname' => 'attr1',
            'rewrapped' => 'rewrapped1'
        ]);
        $db->insert('pkd_merkle_leaf_rewrapped_keys', [
            'leaf' => $leafId,
            'peer' => $peerId,
            'pkdattrname' => 'attr2',
            'rewrapped' => 'rewrapped2'
        ]);

        $result = $keyWrapping->getRewrappedFor('root-multi');

        $this->assertArrayHasKey('peer-multi', $result);
        $this->assertCount(2, $result['peer-multi']);
        $this->assertSame('rewrapped1', $result['peer-multi']['attr1']);
        $this->assertSame('rewrapped2', $result['peer-multi']['attr2']);

        $sk2 = SecretKey::generate();
        // 4. Setup ANOTHER peer
        $db->insert('pkd_peers', [
            'uniqueid' => 'peer-another',
            'hostname' => 'another.example.com',
            'publickey' => $sk2->getPublicKey()->toString(),
            'replicate' => 1,
            'cosign' => 0,
            'latestroot' => '',
            'created' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
            'modified' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
        ]);
        $peerId2 = $db->lastInsertId();
        $db->insert('pkd_merkle_leaf_rewrapped_keys', [
            'leaf' => $leafId,
            'peer' => $peerId2,
            'pkdattrname' => 'attr1',
            'rewrapped' => 'rewrapped3'
        ]);

        $result2 = $keyWrapping->getRewrappedFor('root-multi');
        $this->assertCount(2, $result2);
        $this->assertArrayHasKey('peer-multi', $result2);
        $this->assertArrayHasKey('peer-another', $result2);
    }

    /**
     * @throws DependencyException
     * @throws SodiumException
     */
    public function testDifferentKeysResultInDifferentCacheEntries(): void
    {
        $cache = $this->appCache('key-wrapping-decrypt');

        $root1 = 'root1';
        $wrapped1 = 'wrapped1';
        $root2 = 'root2';
        $wrapped2 = 'wrapped2';

        $key1 = $cache->deriveKey($root1 . ':' . $wrapped1);
        $key2 = $cache->deriveKey($root1 . ':' . $wrapped2);
        $key3 = $cache->deriveKey($root2 . ':' . $wrapped1);

        $this->assertNotEquals($key1, $key2);
        $this->assertNotEquals($key1, $key3);
        $this->assertNotEquals($key2, $key3);
    }
}
