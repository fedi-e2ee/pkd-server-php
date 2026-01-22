<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Meta;

use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\Meta\Params;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Params::class)]
class ParamsTest extends TestCase
{
    public function testDefaults(): void
    {
        $params = new Params();
        $this->assertSame('sha256', $params->hashAlgo);
        $this->assertSame('sha256', $params->getHashFunction());
        $this->assertSame(120, $params->otpMaxLife);
        $this->assertSame(120, $params->getOtpMaxLife());
        $this->assertSame('pubkeydir', $params->actorUsername);
        $this->assertSame('pubkeydir', $params->getActorUsername());
        $this->assertSame('localhost', $params->hostname);
        $this->assertSame('localhost', $params->getHostname());
        $this->assertSame('', $params->cacheKey);
        $this->assertSame('', $params->getCacheKey());
        $this->assertSame(60, $params->httpCacheTtl);
        $this->assertSame(60, $params->getHttpCacheTtl());
    }

    public function testInvalidHashAlgo(): void
    {
        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('Disallowed hash algorithm');
        new Params(hashAlgo: 'md5');
    }

    public function testValidHashAlgo(): void
    {
        $this->assertInstanceOf(Params::class, new Params(hashAlgo: 'sha512'));
    }

    public function testOtpMaxLifeTooSmall(): void
    {
        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('OTP max life cannot be less than 2 seconds');
        new Params(otpMaxLife: 1);
    }

    public function testOtpMaxLifeBoundaryLow(): void
    {
        $this->assertInstanceOf(Params::class, new Params(otpMaxLife: 2));
    }

    public function testOtpMaxLifeBelowBoundaryLow(): void
    {
        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('OTP max life cannot be less than 2 seconds');
        new Params(otpMaxLife: 1);
    }

    public function testOtpMaxLifeBoundaryHigh(): void
    {
        $this->assertInstanceOf(Params::class, new Params(otpMaxLife: 300));
    }

    public function testOtpMaxLifeTooLarge(): void
    {
        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('OTP max life cannot be larger than 300 seconds');
        new Params(otpMaxLife: 301);
    }

    public function testHttpCacheTtlTooSmall(): void
    {
        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('HTTP cache TTL cannot be less than 1 second');
        new Params(httpCacheTtl: 0);
    }

    public function testHttpCacheTtlBoundaryLow(): void
    {
        $this->assertInstanceOf(Params::class, new Params(httpCacheTtl: 1));
    }

    public function testHttpCacheTtlBelowBoundaryLow(): void
    {
        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('HTTP cache TTL cannot be less than 1 second');
        new Params(httpCacheTtl: 0);
    }

    public function testHttpCacheTtlBoundaryHigh(): void
    {
        $this->assertInstanceOf(Params::class, new Params(httpCacheTtl: 300));
    }

    public function testHttpCacheTtlTooLarge(): void
    {
        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('HTTP cache TTL cannot be greater than 300 seconds');
        new Params(httpCacheTtl: 301);
    }

    public function testInvalidHostname(): void
    {
        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('Hostname is not valid');
        new Params(hostname: 'invalid_hostname');
    }

    public function testValidHostname(): void
    {
        $this->assertInstanceOf(Params::class, new Params(hostname: 'furry.engineer'));
    }

    public function testConstructorExplicit(): void
    {
        $params = new Params(
            hashAlgo: 'sha512',
            otpMaxLife: 30,
            actorUsername: 'alice',
            hostname: 'example.com',
            cacheKey: 'test-key',
            httpCacheTtl: 100
        );
        $this->assertSame('sha512', $params->hashAlgo);
        $this->assertSame(30, $params->otpMaxLife);
        $this->assertSame('alice', $params->actorUsername);
        $this->assertSame('example.com', $params->hostname);
        $this->assertSame('test-key', $params->cacheKey);
        $this->assertSame(100, $params->httpCacheTtl);
    }
}
