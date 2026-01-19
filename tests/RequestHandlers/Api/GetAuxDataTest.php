<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

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
use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    JsonException,
    NotImplementedException,
    ParserException
};
use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\{
    AppCache,
    Dependency\WrappedEncryptedRow,
    Math,
    Protocol,
    Protocol\KeyWrapping,
    Protocol\Payload,
    Protocol\RewrapConfig,
    Redirect,
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
use FediE2EE\PKDServer\RequestHandlers\Api\{
    GetAuxData,
    ListAuxData
};
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
use ReflectionException;
use SodiumException;

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
#[UsesClass(Redirect::class)]
class GetAuxDataTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
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

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     */
    public function testEmptyActorIdRedirects(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $reflector = new ReflectionClass(GetAuxData::class);
        $handler = $reflector->newInstanceWithoutConstructor();
        $handler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($handler);
        }

        $request = $this->makeGetRequest('/api/actor//auxiliary/some-aux-id')
            ->withAttribute('actor_id', '')
            ->withAttribute('aux_data_id', 'some-aux-id');
        $response = $handler->handle($request);

        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
        $this->assertLessThan(400, $response->getStatusCode());
        $this->assertNotInTransaction();
    }

    /**
     * @throws CertaintyException
     * @throws DependencyException
     * @throws InvalidArgumentException
     * @throws RandomException
     * @throws ReflectionException
     * @throws SodiumException
     */
    public function testEmptyAuxDataIdRedirects(): void
    {
        [$actorId, $canonical] = $this->makeDummyActor('example.com');
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $webFinger = $this->createWebFingerMock($config, $canonical, 1);

        $handler = $this->instantiateHandler(GetAuxData::class, $config, $webFinger);

        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/auxiliary/')
            ->withAttribute('actor_id', $actorId)
            ->withAttribute('aux_data_id', '');
        $response = $handler->handle($request);

        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
        $this->assertLessThan(400, $response->getStatusCode());
        $this->assertNotInTransaction();
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     */
    public function testWebFingerErrorReturnsError(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $webFinger = new WebFinger($config, $this->getMockClient([
            new Response(500, [], 'Internal Server Error')
        ]));

        $reflector = new ReflectionClass(GetAuxData::class);
        $handler = $reflector->newInstanceWithoutConstructor();
        $handler->injectConfig($config);
        $handler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($handler);
        }

        $request = $this->makeGetRequest('/api/actor/test@example.com/auxiliary/some-aux')
            ->withAttribute('actor_id', 'test@example.com')
            ->withAttribute('aux_data_id', 'some-aux');
        $response = $handler->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('WebFinger', $body['error']);
        $this->assertNotInTransaction();
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws RandomException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     */
    public function testActorNotFoundReturns404(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $nonExistentActor = 'nonexistent' . bin2hex(random_bytes(8)) . '@example.com';
        $canonical = 'https://example.com/users/nonexistent' . bin2hex(random_bytes(8));

        $webFinger = new WebFinger($config, $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'subject' => 'acct:' . $nonExistentActor,
                'links' => [
                    [
                        'rel' => 'self',
                        'type' => 'application/activity+json',
                        'href' => $canonical
                    ]
                ]
            ])),
        ]));

        $reflector = new ReflectionClass(GetAuxData::class);
        $handler = $reflector->newInstanceWithoutConstructor();
        $handler->injectConfig($config);
        $handler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($handler);
        }

        $request = $this->makeGetRequest('/api/actor/' . urlencode($nonExistentActor) . '/auxiliary/some-aux')
            ->withAttribute('actor_id', $nonExistentActor)
            ->withAttribute('aux_data_id', 'some-aux');
        $response = $handler->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('not found', strtolower($body['error']));
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
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     */
    public function testAuxDataNotFoundReturns404(): void
    {
        [$actorId, $canonical] = $this->makeDummyActor('example.com');
        $keypair = SecretKey::generate();
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $protocol = new Protocol($config);
        $webFinger = $this->createWebFingerMock($config, $canonical, 2);
        $protocol->setWebFinger($webFinger);

        // Add a key to ensure actor exists
        $this->addKeyForActor($canonical, $keypair, $protocol, $config);

        $handler = $this->instantiateHandler(GetAuxData::class, $config, $webFinger);

        // Request with a non-existent aux data ID
        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/auxiliary/nonexistent-aux')
            ->withAttribute('actor_id', $actorId)
            ->withAttribute('aux_data_id', 'nonexistent-aux');
        $response = $handler->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('not found', strtolower($body['error']));
        $this->assertNotInTransaction();
    }
}
