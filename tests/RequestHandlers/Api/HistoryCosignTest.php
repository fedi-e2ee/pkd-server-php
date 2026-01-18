<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Exceptions\{
    JsonException,
    NotImplementedException
};
use FediE2EE\PKDServer\{
    AppCache,
    Dependency\WrappedEncryptedRow,
    Exceptions\DependencyException,
    Math,
    Protocol\KeyWrapping,
    Protocol\Payload,
    Protocol\RewrapConfig,
    ServerConfig,
    Table,
    TableCache
};
use FediE2EE\PKDServer\RequestHandlers\Api\HistoryCosign;
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
    AllowMockObjectsWithoutExpectations,
    CoversClass,
    UsesClass
};
use ParagonIE\ConstantTime\Base64UrlSafe;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;
use Random\RandomException;
use ReflectionClass;
use ReflectionException;
use SodiumException;

#[CoversClass(HistoryCosign::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(WrappedEncryptedRow::class)]
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
class HistoryCosignTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    /**
     * @throws ReflectionException
     */
    protected function createHandler(): HistoryCosign
    {
        $config = $this->getConfig();
        $reflector = new ReflectionClass(HistoryCosign::class);
        $handler = $reflector->newInstanceWithoutConstructor();
        $handler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($handler);
        }
        return $handler;
    }

    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws SodiumException
     */
    public function testRejectsGetRequest(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $handler = $this->createHandler();
        $request = $this->makeGetRequest('/api/history/cosign/test-hash')
            ->withAttribute('hash', 'test-hash');
        $response = $handler->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('POST', $body['error']);
        $this->assertNotInTransaction();
    }

    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws SodiumException
     */
    public function testRejectsEmptyHash(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $handler = $this->createHandler();
        $request = $this->makePostRequest('/api/history/cosign/', '{}')
            ->withHeader('Content-Type', 'application/json')
            ->withAttribute('hash', '');
        $response = $handler->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('hash', strtolower($body['error']));
        $this->assertNotInTransaction();
    }

    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws SodiumException
     */
    public function testRejectsWrongContentType(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $handler = $this->createHandler();
        $request = $this->makePostRequest('/api/history/cosign/test-hash', '{}')
            ->withHeader('Content-Type', 'text/plain')
            ->withAttribute('hash', 'test-hash');
        $response = $handler->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('Content-Type', $body['error']);
        $this->assertNotInTransaction();
    }

    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws SodiumException
     */
    public function testRejectsEmptyBody(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $handler = $this->createHandler();
        $request = $this->makePostRequest('/api/history/cosign/test-hash', '')
            ->withHeader('Content-Type', 'application/json')
            ->withAttribute('hash', 'test-hash');
        $response = $handler->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('body', strtolower($body['error']));
        $this->assertNotInTransaction();
    }

    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws SodiumException
     */
    public function testRejectsInvalidJson(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $handler = $this->createHandler();
        $request = $this->makePostRequest('/api/history/cosign/test-hash', 'not-json')
            ->withHeader('Content-Type', 'application/json')
            ->withAttribute('hash', 'test-hash');
        $response = $handler->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('JSON', $body['error']);
        $this->assertNotInTransaction();
    }

    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws SodiumException
     */
    public function testRejectsMissingWitness(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $handler = $this->createHandler();
        $request = $this->makePostRequest(
            '/api/history/cosign/test-hash',
            json_encode(['cosigned' => 'test-signature'])
        )
            ->withHeader('Content-Type', 'application/json')
            ->withAttribute('hash', 'test-hash');
        $response = $handler->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('witness', strtolower($body['error']));
        $this->assertNotInTransaction();
    }

    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws SodiumException
     */
    public function testRejectsMissingCosigned(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $handler = $this->createHandler();
        $request = $this->makePostRequest(
            '/api/history/cosign/test-hash',
            json_encode(['witness' => 'test-witness'])
        )
            ->withHeader('Content-Type', 'application/json')
            ->withAttribute('hash', 'test-hash');
        $response = $handler->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('cosigned', strtolower($body['error']));
        $this->assertNotInTransaction();
    }

    /**
     * @return void
     * @throws DependencyException
     * @throws JsonException
     * @throws MockException
     * @throws NotImplementedException
     * @throws RandomException
     * @throws ReflectionException
     * @throws SodiumException
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testSuccessfulCosign(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $merkleRoot = 'pkd-mr-v1:' . Base64UrlSafe::encodeUnpadded(random_bytes(32));

        // Create a mock for MerkleState that accepts the cosignature
        $merkleStateMock = $this->createMock(MerkleState::class);
        $merkleStateMock->method('addWitnessCosignature')
            ->with('test-witness-unique-id', $merkleRoot, 'test-cosignature-data')
            ->willReturn(true);

        // Instantiate the handler without calling constructor
        $reflector = new ReflectionClass(HistoryCosign::class);
        $cosignHandler = $reflector->newInstanceWithoutConstructor();
        $cosignHandler->injectConfig($config);

        // Replace the real MerkleState with our mock
        $merkleStateProp = $reflector->getProperty('merkleState');
        $merkleStateProp->setValue($cosignHandler, $merkleStateMock);

        $request = $this->makePostRequest(
            '/api/history/cosign/' . urlencode($merkleRoot),
            json_encode([
                'witness' => 'test-witness-unique-id',
                'cosigned' => 'test-cosignature-data'
            ])
        )
            ->withHeader('Content-Type', 'application/json')
            ->withAttribute('hash', $merkleRoot);
        $response = $cosignHandler->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/history/cosign', $body['!pkd-context']);
        $this->assertArrayHasKey('status', $body);
        $this->assertTrue($body['status']);
        $this->assertArrayHasKey('current-time', $body);
        $this->assertIsString($body['current-time']);
        $this->assertNotInTransaction();
    }
}
