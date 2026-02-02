<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Exceptions\NotImplementedException;
use FediE2EE\PKDServer\AppCache;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\RequestHandlers\Api\Info;
use FediE2EE\PKDServer\ServerConfig;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use FediE2EE\PKDServer\Traits\ConfigTrait;
use JsonException;
use PHPUnit\Framework\TestCase;
use SodiumException;

#[CoversClass(Info::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(ServerConfig::class)]
class InfoTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    public function testHandle(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $request = $this->makeGetRequest('/api/info');
        $handler = new Info();
        $handler->injectConfig($config);
        $response = $handler->handle($request);

        $contents = $response->getBody()->getContents();
        $decoded = json_decode($contents, true);

        // Verify the response has exactly the expected keys (catches ArrayItem mutations)
        $this->assertArrayHasKey('!pkd-context', $decoded);
        $this->assertArrayHasKey('current-time', $decoded);
        $this->assertArrayHasKey('actor', $decoded);
        $this->assertArrayHasKey('public-key', $decoded);
        $this->assertCount(4, $decoded, 'Response should have exactly 4 keys');

        // Verify values
        $this->assertLessThanOrEqual(time(), (int) $decoded['current-time']);
        $this->assertIsNumeric($decoded['current-time']);
        $this->assertSame($config->getSigningKeys()->publicKey->toString(), $decoded['public-key']);
        $this->assertSame('fedi-e2ee:v1/api/info', $decoded['!pkd-context']);
        $params = $config->getParams();
        $expectedActor = $params->actorUsername . '@' . $params->hostname;
        $this->assertSame($expectedActor, $decoded['actor']);
        $this->assertNotInTransaction();
    }
}
