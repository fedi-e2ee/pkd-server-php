<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RateLimit;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateMalformedIntervalStringException;
use FediE2EE\PKDServer\Exceptions\{
    DependencyException,
    RateLimitException,
};
use FediE2EE\PKDServer\Interfaces\{
    LimitingHandlerInterface,
    RateLimitStorageInterface
};
use FediE2EE\PKDServer\RateLimit\{
    DefaultRateLimiting,
    RateLimitData
};
use FediE2EE\PKDServer\RateLimit\Storage\{
    Filesystem,
    Redis
};
use FediE2EE\PKDServer\ServerConfig;
use Random\RandomException;
use GuzzleHttp\Psr7\{
    Response,
    ServerRequest
};
use PHPUnit\Framework\Attributes\{
    CoversClass,
    DataProvider,
    UsesClass
};
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(DefaultRateLimiting::class)]
#[UsesClass(RateLimitData::class)]
#[UsesClass(RateLimitException::class)]
#[UsesClass(Redis::class)]
#[UsesClass(Filesystem::class)]
#[UsesClass(ServerConfig::class)]
class DefaultRateLimitingTest extends TestCase
{
    protected ?DefaultRateLimiting $defaultRateLimiting = null;
    /**
     * @return RateLimitStorageInterface
     * @throws DependencyException
     * @throws RandomException
     */
    private function getStorage(): RateLimitStorageInterface
    {
        $path = dirname(__DIR__, 2) . '/tmp/test/rate-limiting-' . bin2hex(random_bytes(8));
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        return new Filesystem($path);
    }

    #[BeforeClass]
    public function getDefaultRateLimit(): DefaultRateLimiting
    {
        if (is_null($this->defaultRateLimiting)) {
            $redisClient = $GLOBALS['pkdConfig']->getRedis();
            $storage = !is_null($redisClient)
                ? new Redis($redisClient, random_bytes(32))
                : new Filesystem(dirname(__DIR__) . '/tmp/test/rate-limiting');

            $this->defaultRateLimiting = new DefaultRateLimiting(
                storage: $storage,
                enabled: true,
                baseDelay: 100,    // milliseconds
                trustedProxies: [], // IP addresses that can set X-Forwarded-For
            );
        }
        return $this->defaultRateLimiting;
    }

    public static function intervals(): array
    {
        return [
            [100,  -2, 0],
            [100,  -1, 0],
            [100,  0, 0],
            [50,   1, 50],
            [1000, 1, 1000],
            [100,  1, 100],
            [100,  2, 200],
            [100,  3, 400],
            [100,  4, 800],
            [100,  5, 1600],
            [100,  6, 3200],
            [100,  7, 6400],
            [100,  8, 12800],
            [100,  9, 25600],
            [100, 10, 51200],
            [100, 11, 102400],
            [100, 12, 204800],
            [100, 13, 409600],
            [100, 14, 819200],
            [50, 15, 819200],
            [100, 15, 1638400],
            [100, 16, 3276800],
            [100, 17, 6553600],
            [100, 18, 13107200],
            [100, 19, 26214400],
            [100, 20, 52428800],
            [100, 21, 104857600],
        ];
    }

    /**
     * @throws DateMalformedIntervalStringException
     * @throws DependencyException
     * @throws RandomException
     */
    #[DataProvider('intervals')]
    public function testGetIntervalFromFailureCount(
        int $baseDelayMilliseconds,
        int $failure,
        int $expectedMs
    ): void {
        $drl = (new DefaultRateLimiting($this->getStorage()))->withBaseDelay($baseDelayMilliseconds);
        $actual = $drl->getIntervalFromFailureCount($failure);

        $now = new DateTime('@0');
        $then = (clone $now)->add($actual);
        $diffMs = ($then->getTimestamp() * 1000) + (int)($then->format('u') / 1000);
        if ($failure < 1) {
            $this->assertSame(0, $diffMs);
        } else {
            $this->assertNotSame(0, $diffMs);
        }

        $this->assertEquals($expectedMs, $diffMs, "Failed for baseDelay={$baseDelayMilliseconds}, failure={$failure}");
        $leftover = $expectedMs % 1000;
        $this->assertSame($leftover, (int) $then->format('u') / 1000);

        // Let's handle cap logic.
        if ($failure > 0) {
            $start = new DateTimeImmutable('NOW');
            $cap = new DateInterval('PT43200S');
            $drl2 = $drl->withMaxTimeout('ip', $cap);
            $time = $drl2->getPenaltyTime(
                new RateLimitData(failures: $failure, lastFailTime: $start, cooldownStart: $start),
                'ip'
            );
            $time2 = $drl2->getPenaltyTime(
                new RateLimitData(failures: $failure, lastFailTime: $start, cooldownStart: $start),
                'domain'
            );
            $left = $start->add($cap);
            if ($diffMs > 43_200_000) {
                // We expect it to be capped.
                $this->assertEquals(
                    $left->format('Y-m-d H:i:s'), // expected time
                    $time->format('Y-m-d H:i:s'), // penalty time
                );
            } else {
                // We expect it to not be capped.
                $this->assertNotEquals(
                    $left->format('Y-m-d H:i:s'), // expected time
                    $time->format('Y-m-d H:i:s'), // penalty time
                );
            }
            // domain is not capped
            $this->assertNotEquals(
                $left->format('Y-m-d H:i:s'), // expected time
                $time2->format('Y-m-d H:i:s'), // penalty time
            );

            // Make sure cooldown time is expected
            $restart = new DateTimeImmutable('NOW');
            $initial = new RateLimitData(failures: $failure - 1, lastFailTime: $restart, cooldownStart: $restart);
            $interval = $drl->getIntervalFromFailureCount($failure);
            $next = $restart->add($interval);
            $increased = $drl->increaseFailures($initial);
            $actual = $drl->getPenaltyTime($increased, 'ip');
            $this->assertEquals(
                $next->format('Y-m-d H:i:s'),
                $actual->format('Y-m-d H:i:s'),
                'Rate-limited until'
            );
            $coolDownExpected = $restart->add($interval)->add($interval)->add($interval);
            $this->assertSame(
                $coolDownExpected->format('Y-m-d H:i:s'),
                $increased->getCooldownStart()->format('Y-m-d H:i:s'),
                'Cooldown Start'
            );
        }
    }

    /**
     * @throws DependencyException
     * @throws RandomException
     */
    public function testImmutability(): void
    {
        $drl = $this->getDefaultRateLimit();
        $baseDelay = $drl->getBaseDelay();

        $new = $drl->withBaseDelay($baseDelay + 101);

        $this->assertNotSame($drl, $new);
        $this->assertSame($baseDelay, $drl->getBaseDelay());
        $this->assertSame($baseDelay + 101, $new->getBaseDelay());

        $new2 = $new->withBaseDelay($baseDelay - 1);
        $this->assertSame($baseDelay - 1, $new2->getBaseDelay());
    }

    /**
     * @throws DependencyException
     * @throws RandomException
     */
    public function testIsEnabled(): void
    {
        $storage = $this->getStorage();
        $drl = new DefaultRateLimiting($storage, enabled: true);
        $this->assertTrue($drl->isEnabled());

        $disabled = new DefaultRateLimiting($storage, enabled: false);
        $this->assertFalse($disabled->isEnabled());
    }

    /**
     * @throws DependencyException
     * @throws RandomException
     */
    public function testConstructorDefaults(): void
    {
        $drl = new DefaultRateLimiting($this->getStorage());

        $this->assertTrue($drl->isEnabled());
        $this->assertSame(100, $drl->getBaseDelay());

        $this->assertTrue($drl->shouldEnforce('actor'));
        $this->assertTrue($drl->shouldEnforce('domain'));
        $this->assertTrue($drl->shouldEnforce('ip'));

        $request = new ServerRequest('GET', '/', [], null, '1.1', ['REMOTE_ADDR' => '192.168.1.123']);
        $this->assertSame('192.168.1.123/32', $drl->getRequestSubnet($request));

        $requestV6 = new ServerRequest('GET', '/', [], null, '1.1', ['REMOTE_ADDR' => '2001:db8::1234']);
        $this->assertSame('2001:db8::/64', $drl->getRequestSubnet($requestV6));
    }

    /**
     * @throws DependencyException
     * @throws RandomException
     */
    public function testConstructorExplicit(): void
    {
        $drl = new DefaultRateLimiting(
            storage: $this->getStorage(),
            enabled: false,
            baseDelay: 200,
            ipv4MaskBits: 24,
            ipv6MaskBits: 48,
            shouldEnforceDomain: false,
            shouldEnforceActor: false
        );
        $this->assertFalse($drl->isEnabled());
        $this->assertSame(200, $drl->getBaseDelay());
        $this->assertFalse($drl->shouldEnforce('actor'));
        $this->assertFalse($drl->shouldEnforce('domain'));

        $request = new ServerRequest('GET', '/', [], null, '1.1', ['REMOTE_ADDR' => '192.168.1.123']);
        $this->assertSame('192.168.1.0/24', $drl->getRequestSubnet($request));

        $requestV6 = new ServerRequest('GET', '/', [], null, '1.1', ['REMOTE_ADDR' => '2001:db8:aaaa:bbbb::1234']);
        $this->assertSame('2001:db8:aaaa::/48', $drl->getRequestSubnet($requestV6));
    }

    /**
     * @throws DependencyException
     * @throws RandomException
     */
    public function testBoundaryMaskBits(): void
    {
        $storage = $this->getStorage();

        $drl31 = new DefaultRateLimiting($storage, ipv4MaskBits: 31);
        $request = new ServerRequest('GET', '/', [], null, '1.1', ['REMOTE_ADDR' => '192.168.1.123']);
        $this->assertSame('192.168.1.122/31', $drl31->getRequestSubnet($request));

        $drl32 = new DefaultRateLimiting($storage, ipv4MaskBits: 32);
        $this->assertSame('192.168.1.123/32', $drl32->getRequestSubnet($request));

        $drl63 = new DefaultRateLimiting($storage, ipv6MaskBits: 63);
        $requestV6 = new ServerRequest('GET', '/', [], null, '1.1', ['REMOTE_ADDR' => '2001:db8::1']);
        $this->assertSame('2001:db8::/63', $drl63->getRequestSubnet($requestV6));

        $drl128 = new DefaultRateLimiting($storage, ipv6MaskBits: 128);
        $this->assertSame('2001:db8::1/128', $drl128->getRequestSubnet($requestV6));
    }

    /**
     * @throws DateMalformedIntervalStringException
     * @throws DependencyException
     * @throws RandomException
     * @throws RateLimitException
     */
    public function testEnforceRateLimit(): void
    {
        $storage = $this->getStorage();
        $drl = new DefaultRateLimiting($storage);

        $request = new ServerRequest('GET', '/', [], null, '1.1', ['REMOTE_ADDR' => '192.168.1.123']);

        /** @var RequestHandlerInterface & LimitingHandlerInterface $handler */
        $handler = new class() implements RequestHandlerInterface, LimitingHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
            public function getEnabledRateLimits(): array
            {
                return ['ip'];
            }
        };

        // No penalty yet
        $drl->enforceRateLimit($request, $handler);
        $this->assertTrue(true); // Didn't throw

        // Record penalty
        $drl->getStorage()->delete('ip', '192.168.1.123/32');
        $drl->recordPenalty('ip', '192.168.1.123/32');
        $this->assertNotNull($drl->getStorage()->get('ip', '192.168.1.123/32'));

        // Should now throw
        $this->expectException(RateLimitException::class);
        $drl->enforceRateLimit($request, $handler);
    }

    /**
     * @throws DateMalformedIntervalStringException
     * @throws DependencyException
     * @throws RandomException
     */
    public function testGetPenaltyTime(): void
    {
        $drl = $this->getDefaultRateLimit();

        $this->assertNull($drl->getPenaltyTime(null, 'ip'));

        $now = new DateTimeImmutable('NOW');
        $data = (new RateLimitData(1))->withLastFailTime($now);

        $penaltyTime = $drl->getPenaltyTime($data, 'ip');
        $this->assertInstanceOf(DateTimeImmutable::class, $penaltyTime);
        $this->assertGreaterThan($now, $penaltyTime);

        // Test with maxTimeouts
        $max = new DateInterval('PT1S');
        $drlMax = new DefaultRateLimiting($this->getStorage(), maxTimeouts: ['ip' => $max], baseDelay: 2000);
        $penaltyTimeMax = $drlMax->getPenaltyTime($data, 'ip');
        $this->assertEquals($now->add($max), $penaltyTimeMax);
    }

    /**
     * @throws DateMalformedIntervalStringException
     * @throws DependencyException
     * @throws RandomException
     */
    public function testGetCooledDown(): void
    {
        $drl = $this->getDefaultRateLimit();

        $old = new DateTimeImmutable('2000-01-01 00:00:00');
        $data = (new RateLimitData(5))->withCooldownStart($old);

        $cooled = $drl->getCooledDown($data);
        $this->assertLessThan(5, $cooled->failures);
        $this->assertGreaterThan($old, $cooled->getCooldownStart());
    }

    /**
     * @throws DateMalformedIntervalStringException
     * @throws DependencyException
     * @throws RandomException
     */
    public function testIncreaseFailures(): void
    {
        $drl = $this->getDefaultRateLimit();

        $data = $drl->increaseFailures(null);
        $this->assertGreaterThan(0, $data->failures);
        $this->assertSame(1, $data->failures);

        $data2 = $drl->increaseFailures($data);
        $this->assertSame(2, $data2->failures);
        $this->assertGreaterThanOrEqual($data->getLastFailTime(), $data2->getLastFailTime());

        $data3 = $drl->increaseFailures($data2);
        $this->assertSame(3, $data3->failures);
        $this->assertGreaterThanOrEqual($data2->getLastFailTime(), $data3->getLastFailTime());
    }

    /**
     * @throws DependencyException
     * @throws RandomException
     */
    public function testShouldEnforceUnknownType(): void
    {
        $drl = $this->getDefaultRateLimit();
        $this->expectException(DependencyException::class);
        $drl->shouldEnforce('unknown');
    }

    /**
     * @throws DependencyException
     * @throws RandomException
     */
    public function testProcessTTL(): void
    {
        $drl = $this->getDefaultRateLimit();

        $this->assertSame(100, $drl->processTTL(null));
        $this->assertSame(123, $drl->processTTL(123));
        $this->assertSame(3600, $drl->processTTL(new DateInterval('PT1H')));

        $inv = new DateInterval('PT1H');
        $inv->invert = 1;
        $this->assertSame(-3600, $drl->processTTL($inv));
    }
}
