<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use Exception;
use FediE2EE\PKD\Crypto\Protocol\Actions\AddKey;
use FediE2EE\PKD\Crypto\{
    AttributeEncryption\AttributeKeyMap,
    Protocol\Handler,
    SecretKey,
    SymmetricKey
};
use FediE2EE\PKDServer\RequestHandlers\Api\{
    HistoryView
};
use FediE2EE\PKDServer\{ActivityPub\WebFinger,
    AppCache,
    Dependency\WrappedEncryptedRow,
    Math,
    Protocol,
    Protocol\KeyWrapping,
    Protocol\Payload,
    ServerConfig,
    Table,
    TableCache};
use FediE2EE\PKDServer\Tables\{
    Actors,
    MerkleState,
    Peers,
    PublicKeys
};
use FediE2EE\PKDServer\Tables\Records\{
    Actor,
    ActorKey,
    MerkleLeaf
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

#[CoversClass(HistoryView::class)]
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
#[UsesClass(Math::class)]
class HistoryViewTest extends TestCase
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
        $akm = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg = $addKey->encrypt($akm);
        $bundle = $handler->handle($encryptedMsg, $keypair, $akm, $latestRoot);
        $encryptedForServer = $handler->hpkeEncrypt(
            $bundle,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $protocol->addKey($encryptedForServer, $canonical);
        $newRoot = $merkleState->getLatestRoot();

        $reflector = new ReflectionClass(HistoryView::class);
        $viewHandler = $reflector->newInstanceWithoutConstructor();
        $viewHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($viewHandler);
        }

        $request = $this->makeGetRequest('/api/history/view/' . urlencode($newRoot));
        $request = $request->withAttribute('hash', $newRoot);
        $response = $viewHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/history/view', $body['!pkd-context']);

        // Verify all expected response fields
        $this->assertArrayHasKey('created', $body);
        $this->assertArrayHasKey('encrypted-message', $body);
        $this->assertArrayHasKey('inclusion-proof', $body);
        $this->assertIsArray($body['inclusion-proof']);
        $this->assertArrayHasKey('message', $body);
        $this->assertArrayHasKey('merkle-root', $body);
        $this->assertSame($newRoot, $body['merkle-root']);
        $this->assertArrayHasKey('rewrapped-keys', $body);
        $this->assertArrayHasKey('witnesses', $body);
        $this->assertIsArray($body['witnesses']);

        $this->assertNotInTransaction();
    }

    /**
     * Test that missing hash returns 400 error.
     *
     * @throws Exception
     */
    public function testMissingHash(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $reflector = new ReflectionClass(HistoryView::class);
        $viewHandler = $reflector->newInstanceWithoutConstructor();
        $viewHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($viewHandler);
        }

        // Request without hash attribute
        $request = $this->makeGetRequest('/api/history/view/');
        $response = $viewHandler->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertNotInTransaction();
    }

    /**
     * Test that unknown hash returns 404 error.
     *
     * @throws Exception
     */
    public function testUnknownHash(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $reflector = new ReflectionClass(HistoryView::class);
        $viewHandler = $reflector->newInstanceWithoutConstructor();
        $viewHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($viewHandler);
        }

        // Use a hash that doesn't exist
        $fakeHash = str_repeat('a', 64);
        $request = $this->makeGetRequest('/api/history/view/' . $fakeHash);
        $request = $request->withAttribute('hash', $fakeHash);
        $response = $viewHandler->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertNotInTransaction();
    }
}
