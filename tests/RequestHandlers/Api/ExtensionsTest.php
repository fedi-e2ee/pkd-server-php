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
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Extensions::class)]
#[UsesClass(ServerConfig::class)]
class ExtensionsTest extends TestCase
{
    use HttpTestTrait;

    /**
     * @throws Exception
     */
    public function testHandle(): void
    {
        $config = $this->getConfig();
        $reflector = new ReflectionClass(Extensions::class);
        $extensionsHandler = $reflector->newInstanceWithoutConstructor();
        $extensionsHandler->injectConfig($config);

        $request = $this->makeGetRequest('/api/extensions');
        $response = $extensionsHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/extensions', $body['!pkd-context']);
        $this->assertIsArray($body['extensions']);
        // These are added via autoload-phpunit.php:
        $this->assertTrue(in_array('test-v1', $body['extensions'], true));
        $this->assertFalse(in_array('test', $body['extensions'], true));
    }
}
