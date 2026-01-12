<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use Exception;
use FediE2EE\PKDServer\AppCache;
use FediE2EE\PKDServer\RequestHandlers\Api\ServerPublicKey;
use FediE2EE\PKDServer\ServerConfig;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(ServerPublicKey::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(ServerConfig::class)]
class ServerPublicKeyTest extends TestCase
{
    use HttpTestTrait;

    /**
     * @throws Exception
     */
    public function testHandle(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $reflector = new ReflectionClass(ServerPublicKey::class);
        $spkHandler = $reflector->newInstanceWithoutConstructor();
        $spkHandler->injectConfig($config);

        $request = $this->makeGetRequest('/api/server-public-key');
        $response = $spkHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/server-public-key', $body['!pkd-context']);
        $this->assertNotEmpty($body['hpke-public-key']);

        // Verify current-time field
        $this->assertArrayHasKey('current-time', $body);
        $this->assertIsString($body['current-time']);

        // Verify hpke-ciphersuite field
        $this->assertArrayHasKey('hpke-ciphersuite', $body);
        $this->assertIsString($body['hpke-ciphersuite']);
        // Should be in format Curve25519_sha256_ChaChaPoly (or similar)
        $this->assertMatchesRegularExpression('/^Curve25519_sha\d+_\w+$/', $body['hpke-ciphersuite']);
    }

    /**
     * Test cipherSuiteString returns correct format.
     *
     * @throws Exception
     */
    public function testCipherSuiteString(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $reflector = new ReflectionClass(ServerPublicKey::class);
        $spkHandler = $reflector->newInstanceWithoutConstructor();
        $spkHandler->injectConfig($config);

        $hpke = $config->getHPKE();
        $cipherSuite = $spkHandler->cipherSuiteString($hpke->cs);

        $this->assertIsString($cipherSuite);
        $this->assertNotEmpty($cipherSuite);
        // Verify it contains expected parts
        $this->assertStringContainsString('Curve25519', $cipherSuite);
        $this->assertStringContainsString('_', $cipherSuite);
        // Should have exactly 3 parts separated by underscores
        $parts = explode('_', $cipherSuite);
        $this->assertCount(3, $parts);
    }
}
