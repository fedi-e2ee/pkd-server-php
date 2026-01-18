<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests;

use DateInterval;
use FediE2EE\PKDServer\{
    AppCache,
    Exceptions\DependencyException,
    ServerConfig
};
use FediE2EE\PKDServer\Meta\Params;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
use SodiumException;

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

    /**
     * @return void
     * @throws SodiumException
     */
    public function testDeriveKeyFormat(): void
    {
        $conf = $this->getConfig();
        $cache = new AppCache($conf, 'test-namespace');
        $key = $cache->deriveKey('input-value');

        // Key should start with namespace and colon
        $this->assertStringStartsWith('test-namespace:', $key);

        // Key should contain hex characters after the colon
        $parts = explode(':', $key, 2);
        $this->assertCount(2, $parts);
        $this->assertSame('test-namespace', $parts[0]);
        $this->assertTrue(ctype_xdigit($parts[1]), 'Hash part should be hex');
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     */
    public function testSetReturnsTrue(): void
    {
        $cache = $this->getConfiguredCache();
        $result = $cache->set('test-key', 'test-value');
        $this->assertTrue($result, 'set() should return true on success');

        // Verify the value was actually set
        $retrieved = $cache->get('test-key');
        $this->assertSame('test-value', $retrieved);
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

    /**
     * @throws InvalidArgumentException
     * @throws SodiumException
     */
    public function testCacheWithTTL(): void
    {
        $cache = $this->getConfiguredCache();
        $out = $cache->cache('foo-ttl', function () {
            return 'bar-ttl';
        }, 3600);
        $this->assertSame('bar-ttl', $out);
        $this->assertTrue($cache->has($cache->deriveKey('foo-ttl')));
    }

    public function testProcessTTL(): void
    {
        $cache = $this->getConfiguredCache();

        // Null uses default
        $this->assertSame(60, $cache->processTTL(null));

        // Integer returns as is
        $this->assertSame(123, $cache->processTTL(123));

        // DateInterval
        $interval = new DateInterval('PT1H');
        $this->assertSame(3600, $cache->processTTL($interval));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testCacheMissReturnValue(): void
    {
        $cache = $this->getConfiguredCache();
        $val = $cache->cache('miss-key', function () {
            return 'miss-value';
        });
        $this->assertSame('miss-value', $val, 'cache() should return fallback value on miss');
    }

    /**
     * @return void
     * @throws RandomException
     */
    public function testConstructorInitializesInMemoryCache(): void
    {
        $conf = $this->getConfig();
        $namespace = 'unique-ns-' . bin2hex(random_bytes(8));

        // This will call constructor
        new AppCache($conf, $namespace);

        // Use reflection to check static inMemoryCache
        $ref = new \ReflectionClass(AppCache::class);
        $prop = $ref->getProperty('inMemoryCache');
        $prop->setAccessible(true);
        $inMemoryCache = $prop->getValue();

        $this->assertArrayHasKey($namespace, $inMemoryCache);
        $this->assertIsArray($inMemoryCache[$namespace]);
        $this->assertEmpty($inMemoryCache[$namespace]);
    }

    public function testParamsCacheTtlTooSmall(): void
    {
        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('HTTP cache TTL cannot be less than 1 second');
        new Params(hashAlgo: 'sha256', httpCacheTtl: 0);
    }

    public function testParamsCacheTtlJustSmallEnough(): void
    {
        $this->assertInstanceOf(Params::class, new Params(httpCacheTtl: 2));
    }

    public function testParamsCacheTtlJustLargeEnough(): void
    {
        $this->assertInstanceOf(Params::class, new Params(httpCacheTtl: 300));
    }

    public function testParamsCacheTtlTooLarge(): void
    {
        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('HTTP cache TTL cannot be greater than 300 seconds');
        new Params(httpCacheTtl: 301);
    }

    public function testDefaultParams(): void
    {
        $params = new Params();
        $this->assertSame('sha256', $params->hashAlgo);
        $this->assertSame(120, $params->otpMaxLife);
        $this->assertSame('pubkeydir', $params->actorUsername);
        $this->assertSame('localhost', $params->hostname);
        $this->assertSame('', $params->cacheKey);
        $this->assertSame(60, $params->httpCacheTtl);
    }
}
