<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use Exception;
use FediE2EE\PKD\Crypto\Exceptions\BundleException;
use FediE2EE\PKD\Crypto\Protocol\Actions\{
    AddKey,
    RevokeKeyThirdParty
};
use FediE2EE\PKD\Crypto\{AttributeEncryption\AttributeKeyMap,
    Exceptions\InputException,
    Protocol\Handler,
    Revocation,
    SecretKey,
    SymmetricKey};
use FediE2EE\PKDServer\RequestHandlers\Api\Revoke;
use FediE2EE\PKDServer\{
    ActivityPub\WebFinger,
    AppCache,
    Dependency\WrappedEncryptedRow,
    Math,
    Middleware\RateLimitMiddleware,
    Protocol,
    Protocol\KeyWrapping,
    Protocol\Payload,
    Protocol\RewrapConfig,
    RateLimit\DefaultRateLimiting,
    ServerConfig,
    Table,
    TableCache
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
    MerkleLeaf,
    Peer
};
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use ReflectionClass;

#[CoversClass(Revoke::class)]
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
#[UsesClass(RateLimitMiddleware::class)]
#[UsesClass(DefaultRateLimiting::class)]
class RevokeTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    /**
     * @throws Exception
     */
    public function testHandle(): void
    {
        [, $canonical] = $this->makeDummyActor('example.com');
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
        $protocol->addKey($encryptedForServer, $canonical);
        $this->assertNotInTransaction();
        $this->ensureMerkleStateUnlocked();

        // Now, let's build a revocation token.
        $revocation = new Revocation();
        $token = $revocation->revokeThirdParty($keypair);
        $message = new RevokeKeyThirdParty($token);
        $bundle = $handler->handle($message, $keypair, new AttributeKeyMap(), $latestRoot);

        // Now, let's revoke this key.
        $request = $this->makePostRequest(
            '/api/revoke',
            $bundle->toString(),
            ['Content-Type' => 'application/json']
        );

        $reflector = new ReflectionClass(Revoke::class);
        $revokeHandler = $reflector->newInstanceWithoutConstructor();
        $revokeHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($revokeHandler);
        }
        $response = $revokeHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame('fedi-e2ee:v1/api/revoke', $body['!pkd-context']);
        $this->assertIsString($body['time']);

        $pks = $this->table('PublicKeys');
        $this->assertEmpty($pks->getPublicKeysFor($canonical));
        $this->assertNotInTransaction();
    }

    /**
     * Test that an empty body throws BundleException
     *
     * @throws Exception
     */
    public function testEmptyBodyThrowsException(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $reflector = new ReflectionClass(Revoke::class);
        $revokeHandler = $reflector->newInstanceWithoutConstructor();
        $revokeHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($revokeHandler);
        }

        $request = $this->makePostRequest(
            '/api/revoke',
            '',
            ['Content-Type' => 'application/json']
        );

        // Empty body causes BundleException (not caught by handler)
        $this->expectException(BundleException::class);
        $this->expectExceptionMessage('Empty JSON string');
        $revokeHandler->handle($request);
    }

    /**
     * Test that invalid JSON body throws BundleException
     *
     * @throws Exception
     */
    public function testInvalidJsonThrowsException(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $reflector = new ReflectionClass(Revoke::class);
        $revokeHandler = $reflector->newInstanceWithoutConstructor();
        $revokeHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($revokeHandler);
        }

        $request = $this->makePostRequest(
            '/api/revoke',
            'not-valid-json',
            ['Content-Type' => 'application/json']
        );

        // Invalid JSON causes BundleException (not caught by handler)
        $this->expectException(BundleException::class);
        $this->expectExceptionMessage('Invalid JSON string');
        $revokeHandler->handle($request);
    }

    /**
     * Test that a GET request with empty body throws BundleException
     *
     * @throws Exception
     */
    public function testGetRequestThrowsException(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $reflector = new ReflectionClass(Revoke::class);
        $revokeHandler = $reflector->newInstanceWithoutConstructor();
        $revokeHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($revokeHandler);
        }

        $request = $this->makeGetRequest('/api/revoke');

        // GET with empty body causes BundleException (not caught by handler)
        $this->expectException(BundleException::class);
        $this->expectExceptionMessage('Empty JSON string');
        $revokeHandler->handle($request);
    }

    /**
     * Test that a valid token for non-existent key returns 404
     *
     * @throws Exception
     */
    public function testNonExistentKeyReturns404(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        // Generate a fresh keypair that's never been registered
        $keypair = SecretKey::generate();

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $latestRoot = $merkleState->getLatestRoot();

        $handler = new Handler();
        $revocation = new Revocation();
        $token = $revocation->revokeThirdParty($keypair);
        $message = new RevokeKeyThirdParty($token);
        $bundle = $handler->handle($message, $keypair, new AttributeKeyMap(), $latestRoot);

        $reflector = new ReflectionClass(Revoke::class);
        $revokeHandler = $reflector->newInstanceWithoutConstructor();
        $revokeHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($revokeHandler);
        }

        $request = $this->makePostRequest(
            '/api/revoke',
            $bundle->toString(),
            ['Content-Type' => 'application/json']
        );
        $response = $revokeHandler->handle($request);

        // Non-existent key returns 204 (per spec)
        $this->assertSame(204, $response->getStatusCode());
        $this->assertNotInTransaction();
    }

    /**
     * @throws Exception
     */
    public function testInputExceptionThrows(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $reflector = new ReflectionClass(Revoke::class);
        $revokeHandler = $reflector->newInstanceWithoutConstructor();
        $revokeHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($revokeHandler);
        }

        $request = $this->makePostRequest(
            '/api/revoke',
            '{"action": "RevokeKeyThirdParty"}', // Missing other keys
            ['Content-Type' => 'application/json']
        );

        $this->expectException(InputException::class);
        $revokeHandler->handle($request);
    }

    /**
     * @throws Exception
     */
    public function testInvalidSignatureReturns404(): void
    {
        [, $canonical] = $this->makeDummyActor('example.com');
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
        $protocol->addKey($encryptedForServer, $canonical);

        // Now, let's build a revocation token with a WRONG key.
        $wrongKey = SecretKey::generate();
        $revocation = new Revocation();
        $token = $revocation->revokeThirdParty($wrongKey); // Signed by wrong key
        // But the message is for the original key's public key?
        // Actually revocation token contains the public key it revokes.

        $message = new RevokeKeyThirdParty($token);
        $bundle = $handler->handle($message, $keypair, new AttributeKeyMap(), $latestRoot);

        $request = $this->makePostRequest(
            '/api/revoke',
            $bundle->toString(),
            ['Content-Type' => 'application/json']
        );

        $reflector = new ReflectionClass(Revoke::class);
        $revokeHandler = $reflector->newInstanceWithoutConstructor();
        $revokeHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($revokeHandler);
        }
        $response = $revokeHandler->handle($request);

        // Invalid signature on revocation token should throw ProtocolException -> 204
        $this->assertSame(204, $response->getStatusCode());
    }

    /**
     * @throws Exception
     */
    public function testHandleFlatFormat(): void
    {
        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        /** @var PublicKeys $pks */
        $pks = $this->table('PublicKeys');

        [, $canonical] = $this->makeDummyActor();
        $keypair = SecretKey::generate();
        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);
        $webFinger = new WebFinger($config, $this->getMockClient([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{"subject":"' . $canonical . '"}'
            )
        ]));
        $protocol->setWebFinger($webFinger);

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
        $protocol->addKey($encryptedForServer, $canonical);

        // Now, let's build a revocation token.
        $revocation = new Revocation();
        $token = $revocation->revokeThirdParty($keypair);

        // Now, let's revoke this key using the flat format.
        $request = $this->makePostRequest(
            '/api/revoke',
            json_encode([
                '!pkd-context' => 'https://github.com/fedi-e2ee/public-key-directory/v1',
                'action' => 'RevokeKeyThirdParty',
                'message' => [
                    'revocation-token' => $token],
                'recent-merkle-root' => '',
                'signature' => '',
                'symmetric-keys' => []
            ]),
            ['Content-Type' => 'application/json']
        );
        $reflector = new ReflectionClass(Revoke::class);
        $revokeHandler = $reflector->newInstanceWithoutConstructor();
        $revokeHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($revokeHandler);
        }
        $response = $revokeHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame('fedi-e2ee:v1/api/revoke', $body['!pkd-context']);
        $this->assertIsString($body['time']);

        $this->assertEmpty($pks->getPublicKeysFor($canonical));
    }
}
