<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use FediE2EE\PKDServer\ActivityPub\{
    ActivityStream,
    WebFinger
};
use DateMalformedStringException;
use FediE2EE\PKDServer\Dependency\{
    EasyDBHandler,
    WrappedEncryptedRow
};
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
    ProtocolMessageInterface
};
use FediE2EE\PKD\Crypto\{Merkle\InclusionProof, SecretKey, SymmetricKey};
use GuzzleHttp\Psr7\Response;
use FediE2EE\PKDServer\Protocol\{
    KeyWrapping,
    Payload,
    RewrapConfig
};
use FediE2EE\PKDServer\{AppCache,
    Math,
    Protocol,
    RequestHandlers\Api\ReplicaInfo,
    Scheduled\Witness,
    ServerConfig,
    Table,
    TableCache,
    Tests\HttpTestTrait,
    Traits\ConfigTrait};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\Tables\{
    Actors,
    MerkleState,
    Peers,
    PublicKeys,
    ReplicaActors,
    ReplicaAuxData,
    ReplicaHistory,
    ReplicaPublicKeys,
    TOTP
};
use FediE2EE\PKDServer\Tables\Records\{
    Actor,
    ActorKey,
    MerkleLeaf,
    Peer,
    ReplicaActor,
    ReplicaLeaf
};
use JsonException as BaseJsonException;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use ParagonIE\HPKE\HPKEException;
use PHPUnit\Framework\Attributes\{
    AllowMockObjectsWithoutExpectations,
    BeforeClass,
    CoversClass,
    UsesClass
};
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
use SodiumException;

#[CoversClass(ReplicaInfo::class)]
#[UsesClass(ActivityStream::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(EasyDBHandler::class)]
#[UsesClass(WrappedEncryptedRow::class)]
#[UsesClass(Protocol::class)]
#[UsesClass(KeyWrapping::class)]
#[UsesClass(Peers::class)]
#[UsesClass(Payload::class)]
#[UsesClass(ServerConfig::class)]
#[UsesClass(Table::class)]
#[UsesClass(TableCache::class)]
#[UsesClass(Actors::class)]
#[UsesClass(MerkleState::class)]
#[UsesClass(PublicKeys::class)]
#[UsesClass(ReplicaActors::class)]
#[UsesClass(ReplicaAuxData::class)]
#[UsesClass(ReplicaHistory::class)]
#[UsesClass(ReplicaPublicKeys::class)]
#[UsesClass(TOTP::class)]
#[UsesClass(Actor::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(ReplicaActor::class)]
#[UsesClass(ReplicaLeaf::class)]
#[UsesClass(Math::class)]
#[UsesClass(RewrapConfig::class)]
#[UsesClass(Peer::class)]
class ReplicaInfoTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    public string $replicaId = '';

    public function tearDown(): void
    {
        TableCache::instance()->clearCache();
    }

    public function injectTable(string $name, Table $table): void
    {
        TableCache::instance()->storeTable($name, $table);
    }
    public array $dummy = [];

    /**
     * @throws CacheException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws SodiumException
     * @throws TableException
     */
    #[BeforeClass]
    public function beforeTests(): void
    {
        $hpke = $this->config()->getHPKE();
        $this->truncateTables();

        // First, create a new peer that we replicate.
        // For simplicity, we replicate ourselves!
        $peersTable = $this->table('Peers');
        if (!($peersTable instanceof Peers)) {
            $this->fail('peers table is not the right type');
        }
        /** @var Peer $newPeer */
        $newPeer = $peersTable->create(
            $this->config->getSigningKeys()->publicKey,
            'localhost',
            false,
            true,
            RewrapConfig::from($hpke->cs, $hpke->encapsKey)
        );
        $this->replicaId = $newPeer->uniqueId;

        // Now, let's make sure we have some records stored:
        [$canonical, $sk] = $this->makeAndStoreDummyActor();

        // Next, let's kick off replication:
        $witness = new Witness($this->config);
        $witness->run();

        // Now let's store the dummy info:
        $this->dummy = [$canonical, $sk];
    }

    /**
     * @throws CacheException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws SodiumException
     * @throws TableException
     */
    public function testHandle(): void
    {
        if (empty($this->replicaId)) {
            $this->beforeTests();
        }

        $request = $this->makeGetRequest('/api/replicas/' . $this->replicaId)
            ->withAttribute('replica_id', $this->replicaId);
        $replicaInfoHandler = new ReplicaInfo();
        $response = $replicaInfoHandler->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $contents = $response->getBody()->getContents();
        $decoded = json_decode($contents, true);

        $this->assertArrayHasKey('!pkd-context', $decoded);
        $this->assertArrayHasKey('time', $decoded);
        $this->assertArrayHasKey('replica-urls', $decoded);
        $this->assertIsString($decoded['!pkd-context']);
        $this->assertIsString($decoded['time']);
        $this->assertIsArray($decoded['replica-urls']);
        $this->assertSame('fedi-e2ee:v1/api/replica-info', $decoded['!pkd-context']);

        $encoded = urlencode($this->replicaId);
        $this->assertContains('/api/replicas/' . $encoded . '/actor/:actor_id', $decoded['replica-urls']);
        $this->assertContains('/api/replicas/' . $encoded . '/actor/:actor_id/keys', $decoded['replica-urls']);
        $this->assertContains('/api/replicas/' . $encoded . '/actor/:actor_id/keys/key/:key_id', $decoded['replica-urls']);
        $this->assertContains('/api/replicas/' . $encoded . '/actor/:actor_id/auxiliary', $decoded['replica-urls']);
        $this->assertContains('/api/replicas/' . $encoded . '/actor/:actor_id/auxiliary/:aux_data_id', $decoded['replica-urls']);
        $this->assertContains('/api/replicas/' . $encoded . '/history', $decoded['replica-urls']);
        $this->assertContains('/api/replicas/' . $encoded . '/history/since/:last_hash', $decoded['replica-urls']);
    }

    public function testHistoryEndpoints(): void
    {
        if (empty($this->replicaId)) {
            $this->beforeTests();
        }
        $peer = $this->table('Peers')->getPeerByUniqueId($this->replicaId);
        $historyTable = $this->table('ReplicaHistory');

        // Insert root0 first
        $leaf0 = $historyTable->createLeaf([
            'merkle-root' => 'root0',
            'publickeyhash' => 'pkh0',
            'contenthash' => 'ch0',
            'signature' => 'sig0',
            'encrypted-message' => 'msg0',
            'created' => date('Y-m-d H:i:s'),
        ], 'cosig0', new InclusionProof(0, []));
        $historyTable->save($peer, $leaf0);

        // Then insert root1
        $leaf = $historyTable->createLeaf([
            'merkle-root' => 'root1',
            'publickeyhash' => 'pkh1',
            'contenthash' => 'ch1',
            'signature' => 'sig1',
            'encrypted-message' => 'msg1',
            'created' => date('Y-m-d H:i:s'),
        ], 'cosig1', new InclusionProof(1, []));
        $historyTable->save($peer, $leaf);

        $request = $this->makeGetRequest('/api/replicas/' . $this->replicaId . '/history')
            ->withAttribute('replica_id', $this->replicaId);
        $handler = new ReplicaInfo();
        $response = $handler->history($request);

        $this->assertSame(200, $response->getStatusCode());
        $decoded = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($decoded);
        $this->assertIsString($decoded['!pkd-context']);
        $this->assertSame('fedi-e2ee:v1/api/replica/history', $decoded['!pkd-context']);
        $this->assertIsArray($decoded['records']);
        $this->assertSame($this->replicaId, $decoded['replica-id']);
        $this->assertGreaterThanOrEqual(2, count($decoded['records']));
        $this->assertSame('root1', $decoded['records'][0]['merkle-root']);

        // Test historySince
        $requestSince = $this->makeGetRequest('/api/replicas/' . $this->replicaId . '/history/since/root0')
            ->withAttribute('replica_id', $this->replicaId)
            ->withAttribute('hash', new class() {
                public function __toString(): string
                {
                    return 'root0';
                }
            });

        $responseSince = $handler->historySince($requestSince);
        $this->assertSame(200, $responseSince->getStatusCode());
        $decodedSince = json_decode($responseSince->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/replica/history', $decodedSince['!pkd-context']);
        $this->assertSame($this->replicaId, $decodedSince['replica-id']);
        $this->assertCount(1, $decodedSince['records']);
        $this->assertSame('root1', $decodedSince['records'][0]['merkle-root']);
    }

    /**
     * @throws CacheException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     * @throws TableException
     * @throws BaseJsonException
     * @throws Exception
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testActorEndpoints(): void
    {
        if (empty($this->replicaId)) {
            $this->beforeTests();
        }
        $peer = $this->table('Peers')->getPeerByUniqueId($this->replicaId);
        /** @var ReplicaActors $replicaActors */
        $replicaActors = $this->table('ReplicaActors');

        $akm = new AttributeKeyMap();
        $akm->addKey('actor', SymmetricKey::generate());
        $payload = new Payload(
            $this->createStub(ProtocolMessageInterface::class),
            $akm,
            '{}'
        );

        $actorId = $replicaActors->createForPeer($peer, 'actor1', $payload);

        $request = $this->makeGetRequest('/api/replicas/' . $this->replicaId . '/actor/actor1')
            ->withAttribute('replica_id', $this->replicaId)
            ->withAttribute('actor_id', new class() {
                public function __toString(): string
                {
                    return 'actor1';
                }
            });
        $handler = new ReplicaInfo();
        $response = $handler->actor($request);

        $this->assertSame(200, $response->getStatusCode());
        $decoded = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/replica/actor/info', $decoded['!pkd-context']);
        $this->assertSame('actor1', $decoded['actor-id']);
        $this->assertSame(0, $decoded['count-keys']);

        // Test actorKeys
        $requestKeys = $this->makeGetRequest('/api/replicas/' . $this->replicaId . '/actor/actor1/keys')
            ->withAttribute('replica_id', $this->replicaId)
            ->withAttribute('actor_id', 'actor1');
        $responseKeys = $handler->actorKeys($requestKeys);
        $this->assertSame(200, $responseKeys->getStatusCode());
        $decodedKeys = json_decode($responseKeys->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/replica/actor/get-keys', $decodedKeys['!pkd-context']);
        $this->assertSame('actor1', $decodedKeys['actor-id']);
        $this->assertIsArray($decodedKeys['public-keys']);
        $this->assertEmpty($decodedKeys['public-keys']);

        // Test actorKey
        $requestKey = $this->makeGetRequest('/api/replicas/' . $this->replicaId . '/actor/actor1/keys/key/key1')
            ->withAttribute('replica_id', $this->replicaId)
            ->withAttribute('actor_id', 'actor1')
            ->withAttribute('key_id', 'key1');

        // Mock ReplicaPublicKeys lookup
        $mockKeys = $this->createMock(ReplicaPublicKeys::class);
        $mockKeys->method('lookup')->willReturn(['publickey' => 'pub1', 'created' => 'now']);
        $this->injectTable('ReplicaPublicKeys', $mockKeys);

        $responseKey = $handler->actorKey($requestKey);
        $this->assertSame(200, $responseKey->getStatusCode());
        $decodedKey = json_decode($responseKey->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/replica/actor/get-key', $decodedKey['!pkd-context']);
        $this->assertSame('actor1', $decodedKey['actor-id']);
        $this->assertSame('pub1', $decodedKey['publickey']);

        // Test actorAuxiliary
        $requestAux = $this->makeGetRequest('/api/replicas/' . $this->replicaId . '/actor/actor1/auxiliary')
            ->withAttribute('replica_id', $this->replicaId)
            ->withAttribute('actor_id', 'actor1');

        $mockAux = $this->createMock(ReplicaAuxData::class);
        $mockAux->method('getAuxDataForActor')->willReturn([['auxdatatype' => 'type1', 'auxdatavalue' => 'val1']]);
        $this->injectTable('ReplicaAuxData', $mockAux);

        $responseAux = $handler->actorAuxiliary($requestAux);
        $this->assertSame(200, $responseAux->getStatusCode());
        $decodedAux = json_decode($responseAux->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/replica/actor/list-auxiliary', $decodedAux['!pkd-context']);
        $this->assertSame('actor1', $decodedAux['actor-id']);
        $this->assertCount(1, $decodedAux['auxiliary']);

        // Test actorAuxiliaryItem
        $requestAuxItem = $this->makeGetRequest('/api/replicas/' . $this->replicaId . '/actor/actor1/auxiliary/aux1')
            ->withAttribute('replica_id', $this->replicaId)
            ->withAttribute('actor_id', 'actor1')
            ->withAttribute('aux_data_id', 'aux1');

        $mockAux->method('getAuxDataById')->willReturn(['auxdatatype' => 'type1', 'auxdatavalue' => 'val1']);

        $responseAuxItem = $handler->actorAuxiliaryItem($requestAuxItem);
        $this->assertSame(200, $responseAuxItem->getStatusCode());
        $decodedAuxItem = json_decode($responseAuxItem->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/replica/actor/get-auxiliary', $decodedAuxItem['!pkd-context']);
        $this->assertSame('actor1', $decodedAuxItem['actor-id']);
        $this->assertSame('type1', $decodedAuxItem['auxdatatype']);
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
    protected function makeAndStoreDummyActor(): array
    {
        [, $canonical] = $this->makeDummyActor();
        $SecretKey = SecretKey::generate();
        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);
        $webFinger = new WebFinger($config, $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], '{"subject":"' . $canonical . '"}')
        ]));
        $protocol->setWebFinger($webFinger);
        $merkleState = $this->table('MerkleState');
        $latestRoot = $merkleState->getLatestRoot();

        $serverHpke = $config->getHPKE();
        $handler = new Handler();

        // Add a key
        $addKey = new AddKey($canonical, $SecretKey->getPublicKey());
        $akm = (new AttributeKeyMap())
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg = $addKey->encrypt($akm);
        $bundle = $handler->handle($encryptedMsg, $SecretKey, $akm, $latestRoot);
        $encryptedForServer = $handler->hpkeEncrypt(
            $bundle,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $protocol->addKey($encryptedForServer, $canonical);
        $this->assertNotInTransaction();
        return [$canonical, $SecretKey];
    }
}
