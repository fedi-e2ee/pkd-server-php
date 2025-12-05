<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests;

use FediE2EE\PKDServer\AppCache;
use FediE2EE\PKDServer\Meta\Params;
use FediE2EE\PKDServer\ServerConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AppCache::class)]
#[UsesClass(ServerConfig::class)]
#[UsesClass(Params::class)]
class AppCacheTest extends TestCase
{
    use HttpTestTrait;

    protected function getConfiguredCache(): AppCache
    {
        $conf = $this->getConfig();
        return new AppCache($conf);
    }

    protected function getCaches(): array
    {
        $conf = $this->getConfig();
        $params = $conf->getParams();

        // Make a clone with the wrong key:
        $clone = new ServerConfig(new Params(
            hashAlgo: $params->hashAlgo,
            otpMaxLife: $params->otpMaxLife,
            actorUsername: $params->actorUsername,
            hostname: $params->hostname,
            cacheKey: bin2hex(random_bytes(32)),
        ));
        $clone
            ->withCACertFetch($conf->getCACertFetch())
            ->withCipherSweet($conf->getCipherSweet())
            ->withDatabase($conf->getDb())
            ->withHPKE($conf->getHPKE())
            ->withLogger($conf->getLogger())
            ->withOptionalRedisClient($conf->getRedis())
            ->withRouter($conf->getRouter())
            ->withSigningKeys($conf->getSigningKeys())
            ->withTwig($conf->getTwig())
        ;

        return [$this->getConfiguredCache(), new AppCache($clone)];
    }

    public function testDeriveKey(): void
    {
        [$good, $bad] = $this->getCaches();
        foreach (['a', 'b', 'c', 'foo', 'ab', 'bar'] as $i) {
            $a = $good->deriveKey($i);
            $b = $bad->deriveKey($i);
            $this->assertNotSame($a, $b);
        }
    }

    public function testCache(): void
    {
        $cache = $this->getConfiguredCache();
        $misses = 0;
        for ($i = 0; $i < 100; ++$i) {
            $out = $cache->cache('foo', function () use (&$misses) {
                ++$misses;
                // usleep(50000);
                return 'bar';
            });
        }
        $this->assertSame('bar', $out);
        $this->assertSame(1, $misses);
    }
}
