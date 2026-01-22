<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RateLimit;

use DateInterval;
use DateTime;
use DateMalformedIntervalStringException;
use FediE2EE\PKDServer\Exceptions\RateLimitException;
use FediE2EE\PKDServer\RateLimit\{
    DefaultRateLimiting,
    RateLimitData
};
use FediE2EE\PKDServer\RateLimit\Storage\{
    Filesystem,
    Redis
};
use FediE2EE\PKDServer\ServerConfig;
use PHPUnit\Framework\Attributes\{
    BeforeClass,
    CoversClass,
    DataProvider,
    UsesClass
};
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultRateLimiting::class)]
#[UsesClass(RateLimitData::class)]
#[UsesClass(RateLimitException::class)]
#[UsesClass(Redis::class)]
#[UsesClass(Filesystem::class)]
#[UsesClass(ServerConfig::class)]
class DefaultRateLimitingTest extends TestCase
{
    protected ?DefaultRateLimiting $defaultRateLimiting = null;

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
        $zero = new DateInterval('PT0S');
        return [
            [100,  0, $zero],
            [100,  1, (function () use ($zero) {
                $c = clone $zero;
                $c->f = 100000;
                return $c;
            })()],
            [100,  2, (function () use ($zero) {
                $c = clone $zero;
                $c->f = 200000;
                return $c;
            })()],
            [100,  3, (function () use ($zero) {
                $c = clone $zero;
                $c->f = 400000;
                return $c;
            })()],
            [100,  4, (function () use ($zero) {
                $c = clone $zero;
                $c->f = 800000;
                return $c;
            })()],
            [100,  5, (function () use ($zero) {
                $c = new DateInterval('PT1S');
                $c->f = 600000;
                return $c;
            })()],
            [100,  6, (function () use ($zero) {
                $c = new DateInterval('PT3S');
                $c->f = 200000;
                return $c;
            })()],
            [100,  7, (function () use ($zero) {
                $c = new DateInterval('PT6S');
                $c->f = 400000;
                return $c;
            })()],
            [100,  8, (function () use ($zero) {
                $c = new DateInterval('PT12S');
                $c->f = 800000;
                return $c;
            })()],
            [100,  9, (function () use ($zero) {
                $c = new DateInterval('PT25S');
                $c->f = 600000;
                return $c;
            })()],
            [100, 10, (function () use ($zero) {
                $c = new DateInterval('PT51S');
                $c->f = 200000;
                return $c;
            })()],
            [100, 11, (function () use ($zero) {
                $c = new DateInterval('PT102S');
                $c->f = 400000;
                return $c;
            })()],
            [100, 12, (function () use ($zero) {
                $c = new DateInterval('PT204S');
                $c->f = 800000;
                return $c;
            })()],
            [100, 13, (function () use ($zero) {
                $c = new DateInterval('PT409S');
                $c->f = 600000;
                return $c;
            })()],
            [100, 14, (function () use ($zero) {
                $c = new DateInterval('PT819S');
                $c->f = 200000;
                return $c;
            })()],
            [50, 15, (function () use ($zero) {
                $c = new DateInterval('PT819S');
                $c->f = 200000;
                return $c;
            })()],
            // C-C-C-Combo breaker!
            [100, 15, (function () use ($zero) {
                $c = new DateInterval('PT1638S');
                $c->f = 400000;
                return $c;
            })()],
            [100, 16, (function () use ($zero) {
                $c = new DateInterval('PT3276S');
                $c->f = 800000;
                return $c;
            })()],
            [100, 17, (function () use ($zero) {
                $c = new DateInterval('PT6553S');
                $c->f = 600000;
                return $c;
            })()],
        ];
    }

    /**
     * @throws DateMalformedIntervalStringException
     */
    #[DataProvider('intervals')]
    public function testGetIntervalFromFailureCount(
        int $baseDelayMilliseconds,
        int $failure,
        DateInterval $expected
    ): void {
        $now = new DateTime('NOW');
        $drl = $this->getDefaultRateLimit()->withBaseDelay($baseDelayMilliseconds);
        $actual = $drl->getIntervalFromFailureCount($failure);
        $left = $now->add($expected);
        $right = $now->add($actual);
        $this->assertEquals($left, $right);
    }
}
