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
            ['sha256', true],
        ];
    }

    #[DataProvider('hashAlgosProvider')]
    public function testInvalidHashAlgo(string $hashAlgo, bool $expectPass = false): void
    {
        if (!$expectPass) {
            $this->expectException(DependencyException::class);
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
        $this->assertInstanceOf(Params::class, new Params(hashAlgo: 'sha256', otpMaxLife: $maxLife));
    }

    public static function invalidHttpCacheTtlProvider(): array
    {
        return [
            [-1, false],
            [0, false],
            [1, true],
            [2, true],
            [30, true],
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
        $this->assertInstanceOf(Params::class, new Params(hashAlgo: 'sha256', httpCacheTtl: $ttl));
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
        $this->assertInstanceOf(Params::class, new Params(hashAlgo: 'sha256', otpMaxLife: 120, hostname: $host));
    }
}
