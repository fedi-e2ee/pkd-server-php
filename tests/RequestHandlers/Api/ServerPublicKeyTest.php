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
        $reflector = new ReflectionClass(ServerPublicKey::class);
        $spkHandler = $reflector->newInstanceWithoutConstructor();
        $spkHandler->injectConfig($config);

        $request = $this->makeGetRequest('/api/server-public-key');
        $response = $spkHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/server-public-key', $body['!pkd-context']);
        $this->assertNotEmpty($body['hpke-public-key']);
    }
}
