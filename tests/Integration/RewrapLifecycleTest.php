<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Integration;

use DateMalformedStringException;
use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    JsonException,
    NotImplementedException,
    ParserException
};
use FediE2EE\PKD\Crypto\Protocol\{
    Actions\AddKey,
    Handler,
    HPKEAdapter
};
use FediE2EE\PKD\Crypto\{
    SecretKey,
    SymmetricKey
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\{
    ActivityPub\WebFinger,
    AppCache,
    Dependency\WrappedEncryptedRow,
    Math,
    Protocol,
    Protocol\KeyWrapping,
    Protocol\Payload,
    Protocol\RewrapConfig,
    ServerConfig,
    Table,
    TableCache,
    Traits\ConfigTrait
};
use FediE2EE\PKDServer\Tables\{
    Actors,
    AuxData,
    MerkleState,
    Peers,
    PublicKeys,
    ReplicaActors,
    ReplicaAuxData,
    ReplicaHistory,
    ReplicaPublicKeys
};
use FediE2EE\PKDServer\Tables\Records\{
    Actor as ActorRecord,
    ActorKey,
    MerkleLeaf,
    Peer as PeerRecord,
    Peer,
    ReplicaLeaf
};
use FediE2EE\PKDServer\RequestHandlers\Api\{
    HistorySince,
    HistoryView
};
use FediE2EE\PKDServer\Scheduled\Witness;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use GuzzleHttp\Psr7\Response;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\HPKE\HPKEException;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
use SodiumException;

#[CoversClass(HistorySince::class)]
#[CoversClass(HistoryView::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(ActorRecord::class)]
#[UsesClass(Actors::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(AuxData::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(MerkleState::class)]
#[UsesClass(Payload::class)]
#[UsesClass(PublicKeys::class)]
#[UsesClass(Protocol::class)]
#[UsesClass(KeyWrapping::class)]
#[UsesClass(Peers::class)]
#[UsesClass(PeerRecord::class)]
#[UsesClass(ReplicaActors::class)]
#[UsesClass(ReplicaAuxData::class)]
#[UsesClass(ReplicaHistory::class)]
#[UsesClass(ReplicaPublicKeys::class)]
#[UsesClass(ReplicaLeaf::class)]
#[UsesClass(RewrapConfig::class)]
#[UsesClass(ServerConfig::class)]
#[UsesClass(Table::class)]
#[UsesClass(TableCache::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(Math::class)]
#[UsesClass(WrappedEncryptedRow::class)]
class RewrapLifecycleTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    public function setUp(): void
    {
        $this->config = $this->getConfig();
        $this->truncateTables();
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
    public function testRewrapLifecycle(): void
    {
        $this->assertNotInTransaction();
        $config = $this->getConfig();
        $db = $config->getDb();

        // 1. Set up the server as its own peer
        /** @var Peers $peersTable */
        $peersTable = $this->table('Peers');
        $serverHpke = $config->getHPKE();
        $rewrapConfig = RewrapConfig::from($serverHpke->cs, $serverHpke->encapsKey);

        $peer = $peersTable->create(
            $config->getSigningKeys()->publicKey,
            $config->getParams()->hostname,
            false, // cosign
            true,  // replicate
            $rewrapConfig
        );
        $peerUniqueId = $peer->uniqueId;

        // 2. Add a new record
        [, $canonical] = $this->makeDummyActor();
        $keypair = SecretKey::generate();
        $protocol = new Protocol($config);
        $webFinger = new WebFinger($config, $this->getMockClient([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{"subject":"' . $canonical . '"}'
            )
        ]));
        $protocol->setWebFinger($webFinger);

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $latestRootBefore = $merkleState->getLatestRoot();

        $handler = new Handler();
        $addKey = new AddKey($canonical, $keypair->getPublicKey());
        $akm = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg = $addKey->encrypt($akm);
        $bundle = $handler->handle($encryptedMsg, $keypair, $akm, $latestRootBefore);
        $encryptedForServer = $handler->hpkeEncrypt(
            $bundle,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );

        $this->assertNotInTransaction();
        $protocol->addKey($encryptedForServer, $canonical);
        $latestRootAfter = $merkleState->getLatestRoot();
        $this->assertNotEquals($latestRootBefore, $latestRootAfter);

        // 3. Verify re-wrapped keys in DB
        $rewrappedKeys = $db->run(
            "SELECT * FROM pkd_merkle_leaf_rewrapped_keys WHERE peer = ?",
            $peer->getPrimaryKey()
        );
        $this->assertGreaterThanOrEqual(
            2,
            count($rewrappedKeys),
            'Should have 2 re-wrapped keys (actor and public-key)'
        );

        // 4. Verify history/since
        $sinceHandler = new HistorySince();
        $this->assertTrue($sinceHandler->clearCache());
        $requestSince = $this->makeGetRequest('/api/history/since/' . urlencode($latestRootBefore))
            ->withAttribute('hash', $latestRootBefore);
        $responseSince = $sinceHandler->handle($requestSince);
        $this->assertEquals(200, $responseSince->getStatusCode());
        $bodySince = json_decode($responseSince->getBody()->getContents(), true);

        $this->assertArrayHasKey('records', $bodySince);
        $found = false;
        foreach ($bodySince['records'] as $record) {
            if ($record['merkle-root'] === $latestRootAfter) {
                $this->assertArrayHasKey('rewrapped-keys', $record);
                $this->assertArrayHasKey($peerUniqueId, $record['rewrapped-keys']);
                $this->assertArrayHasKey('actor', $record['rewrapped-keys'][$peerUniqueId]);
                $this->assertArrayHasKey('public-key', $record['rewrapped-keys'][$peerUniqueId]);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Record not found in history/since');

        // 5. Verify history/view
        $viewHandler = new HistoryView();
        $requestView = $this->makeGetRequest('/api/history/view/' . urlencode($latestRootAfter))
            ->withAttribute('hash', $latestRootAfter);
        $responseView = $viewHandler->handle($requestView);
        $this->assertEquals(200, $responseView->getStatusCode());
        $bodyView = json_decode($responseView->getBody()->getContents(), true);

        $this->assertArrayHasKey('rewrapped-keys', $bodyView);
        $this->assertArrayHasKey($peerUniqueId, $bodyView['rewrapped-keys']);
        $this->assertArrayHasKey('actor', $bodyView['rewrapped-keys'][$peerUniqueId]);
        $this->assertArrayHasKey('public-key', $bodyView['rewrapped-keys'][$peerUniqueId]);

        // 6. Verify we can decrypt the re-wrapped keys
        $actorRewrapped = $bodyView['rewrapped-keys'][$peerUniqueId]['actor'];
        $adapter = new HPKEAdapter($serverHpke->cs);
        $decryptedActorKey = $adapter->open($serverHpke->decapsKey, $serverHpke->encapsKey, $actorRewrapped);
        $this->assertEquals($akm->getKey('actor')->getBytes(), $decryptedActorKey);

        // 7. Run Witness task
        $this->assertNotInTransaction();

        // Mock the usual Witness scheduled task:
        $witness = new class($config, $bodySince) extends Witness {
            public function __construct($config, private array $bodySince)
            {
                parent::__construct($config);
            }

            public function getHashesSince(Peer $peer): array
            {
                return $this->bodySince;
            }

            protected function cosign(Peer $peer, string $cosigned, string $expectedMerkleRoot): bool
            {
                return true;
            }

            public function run(): void
            {
                /** @var Peers $peersTable */
                $peersTable = $this->table('Peers');
                foreach ($peersTable->listAll() as $peer) {
                    $this->witnessFor($peer);
                }
            }
        };

        $witness->run();

        // 8. Verify replication occurred
        /** @var ReplicaHistory $replicaHistory */
        $replicaHistory = $this->table('ReplicaHistory');
        $replicatedLeaves = $db->run(
            "SELECT * FROM pkd_replica_history WHERE peer = ?",
            $peer->getPrimaryKey()
        );
        $this->assertCount(1, $replicatedLeaves, 'Should have 1 replicated leaf');
        $this->assertEquals($latestRootAfter, $replicatedLeaves[0]['root']);
    }
}
