<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use Exception;
use FediE2EE\PKD\Crypto\Protocol\Actions\{
    AddAuxData,
    AddKey
};
use FediE2EE\PKD\Crypto\{
    AttributeEncryption\AttributeKeyMap,
    Protocol\Handler,
    SecretKey,
    SymmetricKey
};
use FediE2EE\PKDServer\RequestHandlers\Api\{
    GetAuxData,
    ListAuxData
};
use FediE2EE\PKDServer\{ActivityPub\WebFinger,
    AppCache,
    Dependency\WrappedEncryptedRow,
    Math,
    Protocol,
    Protocol\KeyWrapping,
    Protocol\Payload,
    Protocol\RewrapConfig,
    ServerConfig,
    Table,
    TableCache};
use FediE2EE\PKDServer\Tables\{
    Actors,
    AuxData,
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
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(GetAuxData::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(WrappedEncryptedRow::class)]
#[UsesClass(Protocol::class)]
#[UsesClass(KeyWrapping::class)]
#[UsesClass(Peers::class)]
#[UsesClass(Payload::class)]
#[UsesClass(ListAuxData::class)]
#[UsesClass(ServerConfig::class)]
#[UsesClass(Table::class)]
#[UsesClass(TableCache::class)]
#[UsesClass(Actors::class)]
#[UsesClass(AuxData::class)]
#[UsesClass(MerkleState::class)]
#[UsesClass(PublicKeys::class)]
#[UsesClass(Actor::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(Peer::class)]
#[UsesClass(Math::class)]
#[UsesClass(RewrapConfig::class)]
class GetAuxDataTest extends TestCase
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
        $webFinger = new WebFinger($config, $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], '{"subject":"' . $canonical . '"}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"subject":"' . $canonical . '"}')
        ]));
        $protocol->setWebFinger($webFinger);

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $latestRoot = $merkleState->getLatestRoot();

        $serverHpke = $config->getHPKE();
        $handler = new Handler();

        // Add a key
        $addKey = new AddKey($canonical, $keypair->getPublicKey());
        $akm = (new AttributeKeyMap())
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg = $addKey->encrypt($akm);
        $bundle = $handler->handle($encryptedMsg, $keypair, $akm, $latestRoot);
        $encryptedForServer = $handler->hpkeEncrypt(
            $bundle,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $protocol->addKey($encryptedForServer, $canonical);
        $this->assertNotInTransaction();
        $this->ensureMerkleStateUnlocked();

        // Add aux data
        $addAux = new AddAuxData($canonical, 'test', 'test-data');
        $akm = (new AttributeKeyMap())
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('aux-type', SymmetricKey::generate())
            ->addKey('aux-data', SymmetricKey::generate());
        $encryptedMsg = $addAux->encrypt($akm);
        $bundle = $handler->handle($encryptedMsg, $keypair, $akm, $latestRoot);
        $encryptedForServer = $handler->hpkeEncrypt(
            $bundle,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $protocol->addAuxData($encryptedForServer, $canonical);
        $this->assertNotInTransaction();

        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/auxiliary');
        $request = $request->withAttribute('actor_id', $actorId);
        $reflector = new ReflectionClass(\FediE2EE\PKDServer\RequestHandlers\Api\ListAuxData::class);
        $listAuxDataHandler = $reflector->newInstanceWithoutConstructor();
        $listAuxDataHandler->injectConfig($config);
        $listAuxDataHandler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($listAuxDataHandler);
        }
        $response = $listAuxDataHandler->handle($request);
        $body = json_decode($response->getBody()->getContents(), true);
        $auxId = $body['auxiliary'][0]['aux-id'];

        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/auxiliary/' . $auxId);
        $request = $request->withAttribute('actor_id', $actorId);
        $request = $request->withAttribute('aux_data_id', $auxId);

        $reflector = new ReflectionClass(GetAuxData::class);
        $getAuxDataHandler = $reflector->newInstanceWithoutConstructor();
        $getAuxDataHandler->injectConfig($config);
        $getAuxDataHandler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($getAuxDataHandler);
        }

        $response = $getAuxDataHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/actor/get-aux', $body['!pkd-context']);
        $this->assertSame($canonical, $body['actor-id']);
        $this->assertSame('test', $body['aux-type']);
        $this->assertSame('test-data', $body['aux-data']);
        $this->assertNotInTransaction();
    }
}
