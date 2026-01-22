<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\SecretKey;
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
#[UsesClass(Redirect::class)]
class ListKeysTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

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

        $reflector = new ReflectionClass(ListKeys::class);
        $handler = $reflector->newInstanceWithoutConstructor();
        $handler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($handler);
        }

        $request = $this->makeGetRequest('/api/actor//keys')
            ->withAttribute('actor_id', '');
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

        $reflector = new ReflectionClass(ListKeys::class);
        $handler = $reflector->newInstanceWithoutConstructor();
        $handler->injectConfig($config);
        $handler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($handler);
        }

        $request = $this->makeGetRequest('/api/actor/test@example.com/keys')
            ->withAttribute('actor_id', 'test@example.com');
        $response = $handler->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('A WebFinger error occurred', $body['error']);
        $this->assertStringStartsWith('A WebFinger error occurred: ', $body['error']);
        $this->assertNotSame(
            'A WebFinger error occurred: ',
            $body['error'],
            'WebFinger error must actually have information!'
        );
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

        [$nonExistentActor, $canonical] = $this->makeDummyActor('nonexistent-example.com');
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
        $handler = new ListKeys();
        $handler->injectConfig($config);
        $handler->setWebFinger($webFinger);
        $request = $this->makeGetRequest('/api/actor/' . urlencode($nonExistentActor) . '/keys')
            ->withAttribute('actor_id', $nonExistentActor);
        $response = $handler->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('error', $body);
        $this->assertSame('Actor not found', $body['error']);
        $this->assertNotInTransaction();
    }
}
