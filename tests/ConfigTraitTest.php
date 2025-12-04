<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests;

use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\Exceptions\TableException;
use FediE2EE\PKDServer\Tables\{
    Actors,
    TOTP
};
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use FediE2EE\PKDServer\Traits\ConfigTrait;

#[CoversNothing]
class ConfigTraitTest extends TestCase
{
    use HttpTestTrait;

    public function testTable(): void
    {
        $mock = new class() {
            use ConfigTrait;
        };
        $mock->injectConfig($this->getConfig());
        $this->assertInstanceOf(Actors::class, $mock->table('Actors'));
        $this->assertInstanceOf(TOTP::class, $mock->table('TOTP'));

        // Test caching
        $first = $mock->table('Actors');
        $second = $mock->table('Actors');
        $this->assertSame($first, $second);
    }

    public function testTableException(): void
    {
        $mock = new class() {
            use ConfigTrait;
        };
        $mock->injectConfig($this->getConfig());
        $this->expectException(TableException::class);
        $mock->table('UnknownTable');
    }

    public function testConfig(): void
    {
        $mock = new class() {
            use ConfigTrait;
        };
        $config = $this->getConfig();
        $mock->injectConfig($config);
        $this->assertSame($config, $mock->config());
    }

    public function testWebFinger(): void
    {
        $mock = new class() {
            use ConfigTrait;
        };
        $mock->injectConfig($this->getConfig());
        $this->assertInstanceOf(WebFinger::class, $mock->webfinger());

        // Test caching
        $first = $mock->webfinger();
        $second = $mock->webfinger();
        $this->assertSame($first, $second);
    }
}
