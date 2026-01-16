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
use FediE2EE\PKD\Crypto\Exceptions\CryptoException;
use FediE2EE\PKD\Crypto\Protocol\Actions\AddKey;
use FediE2EE\PKD\Crypto\Protocol\Handler;
use FediE2EE\PKD\Crypto\SecretKey;
use FediE2EE\PKD\Crypto\SymmetricKey;
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
    TableException,
};
use FediE2EE\PKDServer\RequestHandlers\Api\Replicas;
use FediE2EE\PKDServer\Tables\{
    Actors,
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
use PHPUnit\Framework\Attributes\{
    BeforeClass,
    CoversClass,
    UsesClass
};
use PHPUnit\Framework\TestCase;
use SodiumException;

#[CoversClass(Replicas::class)]
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
#[UsesClass(TOTP::class)]
#[UsesClass(Actor::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(Math::class)]
#[UsesClass(RewrapConfig::class)]
#[UsesClass(Peer::class)]
class ReplicaInfoTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    public string $replicaId = '';
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
    }

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
