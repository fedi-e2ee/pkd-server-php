<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Meta;

use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\Meta\Params;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Params::class)]
class ParamsTest extends TestCase
{
    public static function hashAlgosProvider(): array
    {
        return [
            ['', false],
            ['adler32', false],
            ['md5', false],
            ['blake2b', true],
            ['sha256', true],
            ['sha384', true],
            ['sha512', true],
            ['sha512/224', true],
            ['sha512/256', true],
            ['sha3-256', true],
            ['sha3-384', true],
            ['sha3-512', true],
        ];
    }

    #[DataProvider('hashAlgosProvider')]
    public function testInvalidHashAlgo(string $hashAlgo, bool $expectPass = false): void
    {
        if (!$expectPass) {
            $this->expectException(DependencyException::class);
            $this->expectExceptionMessage('Disallowed hash algorithm');
        }
        $this->assertInstanceOf(Params::class, new Params($hashAlgo));
    }

    public static function invalidMaxLifeProvider(): array
    {
        return [
            [-1, false],
            [0, false],
            [1, false],
            [2, true],
            [30, true],
            [300, true],
            [301, false],
            [PHP_INT_MAX, false]
        ];
    }

    #[DataProvider("invalidMaxLifeProvider")]
    public function testOtpLifetime(int $maxLife, bool $expectPass = false): void
    {
        if (!$expectPass) {
            $this->expectException(DependencyException::class);
        }
        $this->assertInstanceOf(Params::class, new Params(otpMaxLife: $maxLife));
    }

    public static function invalidHttpCacheTtlProvider(): array
    {
        return [
            [-1, false],
            [0, false],
            [1, true],
            [2, true],
            [3, true],
            [30, true],
            [299, true],
            [300, true],
            [301, false],
            [PHP_INT_MAX, false]
        ];
    }

    #[DataProvider("invalidHttpCacheTtlProvider")]
    public function testHttpCacheTtl(int $ttl, bool $expectPass = false): void
    {
        if (!$expectPass) {
            $this->expectException(DependencyException::class);
        }
        $this->assertInstanceOf(Params::class, new Params(httpCacheTtl: $ttl));
    }

    public static function hostnameProvider(): array
    {
        return [
            ['localhost', true],
            ['furry.engineer', true],
            ['', false],
            ["\x0A", false],
        ];
    }

    #[DataProvider("hostnameProvider")]
    public function testHostname(string $host, bool $expectPass = false): void
    {
        if (!$expectPass) {
            $this->expectException(DependencyException::class);
        }
        $this->assertInstanceOf(Params::class, new Params(hostname: $host));
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

    public function testDefaults(): void
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
