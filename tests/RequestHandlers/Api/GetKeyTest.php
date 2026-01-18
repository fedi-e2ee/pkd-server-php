<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use Exception;
use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    JsonException,
    NotImplementedException,
    ParserException
};
use FediE2EE\PKD\Crypto\SecretKey;
use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\{
    AppCache,
    Dependency\WrappedEncryptedRow,
    Exceptions\CacheException,
    Exceptions\DependencyException,
    Exceptions\ProtocolException,
    Exceptions\TableException,
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
use FediE2EE\PKDServer\RequestHandlers\Api\{
    GetKey,
    ListKeys
};
use FediE2EE\PKDServer\Tables\{
    Actors,
    MerkleState,
    Peers,
    PublicKeys
};
use FediE2EE\PKDServer\Tables\Records\{
    Actor,
    ActorKey,
    Peer,
    MerkleLeaf
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

#[CoversClass(GetKey::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(WrappedEncryptedRow::class)]
#[UsesClass(Protocol::class)]
#[UsesClass(KeyWrapping::class)]
#[UsesClass(Peers::class)]
#[UsesClass(Peer::class)]
#[UsesClass(Payload::class)]
#[UsesClass(ListKeys::class)]
#[UsesClass(ServerConfig::class)]
#[UsesClass(Table::class)]
#[UsesClass(TableCache::class)]
#[UsesClass(Actors::class)]
#[UsesClass(MerkleState::class)]
#[UsesClass(PublicKeys::class)]
#[UsesClass(Actor::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(Redirect::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(Math::class)]
#[UsesClass(RewrapConfig::class)]
class GetKeyTest extends TestCase
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
        $webFinger = $this->createWebFingerMock($config, $canonical, 2);
        $protocol->setWebFinger($webFinger);

        $this->addKeyForActor($canonical, $keypair, $protocol, $config);

        // First get the key ID via ListKeys
        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/keys');
        $request = $request->withAttribute('actor_id', $actorId);
        $listKeysHandler = $this->instantiateHandler(ListKeys::class, $config, $webFinger);
        $response = $listKeysHandler->handle($request);
        $body = json_decode($response->getBody()->getContents(), true);
        $keyId = $body['public-keys'][0]['key-id'];

        // Now test GetKey
        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/key/' . $keyId);
        $request = $request->withAttribute('actor_id', $actorId);
        $request = $request->withAttribute('key_id', $keyId);

        $getKeyHandler = $this->instantiateHandler(GetKey::class, $config, $webFinger);
        $response = $getKeyHandler->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/actor/key-info', $body['!pkd-context']);
        $this->assertSame($canonical, $body['actor-id']);
        $this->assertSame($keypair->getPublicKey()->toString(), $body['public-key']);
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

        $reflector = new ReflectionClass(GetKey::class);
        $handler = $reflector->newInstanceWithoutConstructor();
        $handler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($handler);
        }

        $request = $this->makeGetRequest('/api/actor//key/some-key-id')
            ->withAttribute('actor_id', '')
            ->withAttribute('key_id', 'some-key-id');
        $response = $handler->handle($request);

        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
        $this->assertLessThan(400, $response->getStatusCode());
        $this->assertNotInTransaction();

        $locations = $response->getHeader('Location');
        $this->assertIsArray($locations);
        $this->assertCount(1, $locations);
        $location = array_shift($locations);
        $this->assertSame('/api', $location);
    }

    /**
     * @throws CertaintyException
     * @throws DependencyException
     * @throws InvalidArgumentException
     * @throws RandomException
     * @throws ReflectionException
     * @throws SodiumException
     */
    public function testEmptyKeyIdRedirects(): void
    {
        [$actorId, $canonical] = $this->makeDummyActor('example.com');
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $webFinger = $this->createWebFingerMock($config, $canonical, 1);

        $handler = $this->instantiateHandler(GetKey::class, $config, $webFinger);

        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/key/')
            ->withAttribute('actor_id', $actorId)
            ->withAttribute('key_id', '');
        $response = $handler->handle($request);

        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
        $this->assertLessThan(400, $response->getStatusCode());
        $this->assertNotInTransaction();

        $locations = $response->getHeader('Location');
        $this->assertIsArray($locations);
        $this->assertCount(1, $locations);
        $location = array_shift($locations);
        $this->assertSame('/api/actor/' . urlencode($canonical) . '/keys', $location);
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

        $reflector = new ReflectionClass(GetKey::class);
        $handler = $reflector->newInstanceWithoutConstructor();
        $handler->injectConfig($config);
        $handler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($handler);
        }

        $request = $this->makeGetRequest('/api/actor/test@example.com/key/some-key')
            ->withAttribute('actor_id', 'test@example.com')
            ->withAttribute('key_id', 'some-key');
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

        $reflector = new ReflectionClass(GetKey::class);
        $handler = $reflector->newInstanceWithoutConstructor();
        $handler->injectConfig($config);
        $handler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($handler);
        }

        $request = $this->makeGetRequest('/api/actor/' . urlencode($nonExistentActor) . '/key/some-key')
            ->withAttribute('actor_id', $nonExistentActor)
            ->withAttribute('key_id', 'some-key');
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
    public function testKeyNotFoundRedirects(): void
    {
        [$actorId, $canonical] = $this->makeDummyActor('example.com');
        $keypair = SecretKey::generate();
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $protocol = new Protocol($config);
        $webFinger = $this->createWebFingerMock($config, $canonical, 2);
        $protocol->setWebFinger($webFinger);

        $this->addKeyForActor($canonical, $keypair, $protocol, $config);
        $handler = $this->instantiateHandler(GetKey::class, $config, $webFinger);
        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/key/nonexistent-key')
            ->withAttribute('actor_id', $actorId)
            ->withAttribute('key_id', 'nonexistent-key');
        $response = $handler->handle($request);

        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
        $this->assertLessThan(400, $response->getStatusCode());
        $this->assertNotInTransaction();

        $locations = $response->getHeader('Location');
        $this->assertIsArray($locations);
        $this->assertCount(1, $locations);
        $location = array_shift($locations);
        $this->assertSame('/api/actor/' . urlencode($canonical) . '/keys', $location);
    }
}
