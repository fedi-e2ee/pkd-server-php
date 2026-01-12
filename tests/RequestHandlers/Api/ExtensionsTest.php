<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use Exception;
use FediE2EE\PKDServer\RequestHandlers\Api\Extensions;
use FediE2EE\PKDServer\ServerConfig;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use FediE2EE\PKDServer\Traits\ConfigTrait;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Extensions::class)]
#[UsesClass(ServerConfig::class)]
class ExtensionsTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    /**
     * @throws Exception
     */
    public function testHandle(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $reflector = new ReflectionClass(Extensions::class);
        $extensionsHandler = $reflector->newInstanceWithoutConstructor();
        $extensionsHandler->injectConfig($config);

        $this->assertNotInTransaction();
        $request = $this->makeGetRequest('/api/extensions');
        $response = $extensionsHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/extensions', $body['!pkd-context']);
        // Verify current-time is a string (kills CastString mutation)
        $this->assertArrayHasKey('current-time', $body);
        $this->assertIsString($body['current-time']);
        $this->assertIsArray($body['extensions']);
        // These are added via autoload-phpunit.php:
        $this->assertTrue(in_array('test-v1', $body['extensions'], true));
        $this->assertFalse(in_array('test', $body['extensions'], true));
        $this->assertNotInTransaction();
    }
}
