<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Traits;

use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\Exceptions\TableException;
use FediE2EE\PKDServer\Tables\{
    Actors,
    TOTP
};
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use PHPUnit\Framework\Attributes\{
    CoversNothing,
    DataProvider
};
use PHPUnit\Framework\TestCase;
use JsonException as BaseJsonException;

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

    public static function jsonDecodeProvider(): array
    {
        return [
            ['{"test":"foo"}', true],
            ['"test","foo"}]', false],
            [random_bytes(40), false],
        ];
    }

    #[DataProvider("jsonDecodeProvider")]
    public function testJsonDecode(string $input, bool $expectPass): void
    {
        $mock = new class() {
            use ConfigTrait;
        };
        if (!$expectPass) {
            $this->expectException(BaseJsonException::class);
        }
        $this->assertIsArray($mock->jsonDecode($input));
    }

    #[DataProvider("jsonDecodeProvider")]
    public function testJsonDecodeObject(string $input, bool $expectPass): void
    {
        $mock = new class() {
            use ConfigTrait;
        };
        if (!$expectPass) {
            $this->expectException(BaseJsonException::class);
        }
        $this->assertIsObject($mock->jsonDecodeObject($input));
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
