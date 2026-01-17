<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use Exception;
use FediE2EE\PKD\Crypto\SecretKey;
use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\{
    AppCache,
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
use FediE2EE\PKDServer\RequestHandlers\Api\ListKeys;
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
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use PHPUnit\Framework\TestCase;

#[CoversClass(ListKeys::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(WebFinger::class)]
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
#[UsesClass(Actor::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(Peer::class)]
#[UsesClass(Math::class)]
#[UsesClass(RewrapConfig::class)]
class ListKeysTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    /**
     * @throws Exception
     */
    public function testHandle(): void
    {
        [$actorId, $canonical] = $this->makeDummyActor('example.com');
        $keypair = SecretKey::generate();
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $protocol = new Protocol($config);
        $webFinger = $this->createWebFingerMock($config, $canonical);
        $protocol->setWebFinger($webFinger);

        $this->addKeyForActor($canonical, $keypair, $protocol, $config);

        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/keys');
        $request = $request->withAttribute('actor_id', $actorId);

        $handler = $this->instantiateHandler(ListKeys::class, $config, $webFinger);
        $response = $handler->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/actor/get-keys', $body['!pkd-context']);
        $this->assertSame($canonical, $body['actor-id']);
        $this->assertCount(1, $body['public-keys']);
        $this->assertSame($keypair->getPublicKey()->toString(), $body['public-keys'][0]['public-key']);
        $this->assertNotInTransaction();
    }
}
