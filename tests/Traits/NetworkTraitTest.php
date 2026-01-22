<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Traits;

use FediE2EE\PKDServer\Traits\NetworkTrait;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class NetworkTraitTest extends TestCase
{
    public function getDummyClass(): object
    {
        $class = new class() {
            use NetworkTrait;
        };
        return new $class();
    }

    public static function ipv4Provider(): array
    {
        return [
            ['127.0.0.1', 32, '127.0.0.1/32'],
            ['255.255.255.255', 32, '255.255.255.255/32'],
            ['255.255.255.255', 31, '255.255.255.254/31'],
            ['255.255.255.255', 30, '255.255.255.252/30'],
            ['255.255.255.255', 29, '255.255.255.248/29'],
            ['255.255.255.255', 28, '255.255.255.240/28'],
            ['255.255.255.255', 27, '255.255.255.224/27'],
            ['255.255.255.255', 26, '255.255.255.192/26'],
            ['255.255.255.255', 25, '255.255.255.128/25'],
            ['255.255.255.255', 24, '255.255.255.0/24'],
            ['255.255.255.255', 23, '255.255.254.0/23'],
            ['255.255.255.255', 22, '255.255.252.0/22'],
            ['255.255.255.255', 21, '255.255.248.0/21'],
            ['255.255.255.255', 20, '255.255.240.0/20'],
            ['255.255.255.255', 19, '255.255.224.0/19'],
            ['255.255.255.255', 18, '255.255.192.0/18'],
            ['255.255.255.255', 17, '255.255.128.0/17'],
            ['255.255.255.255', 16, '255.255.0.0/16'],
            ['255.255.255.255', 15, '255.254.0.0/15'],
            ['255.255.255.255', 14, '255.252.0.0/14'],
            ['255.255.255.255', 13, '255.248.0.0/13'],
            ['255.255.255.255', 12, '255.240.0.0/12'],
            ['255.255.255.255', 11, '255.224.0.0/11'],
            ['255.255.255.255', 10, '255.192.0.0/10'],
            ['255.255.255.255', 9, '255.128.0.0/9'],
            ['255.255.255.255', 8, '255.0.0.0/8'],
            ['255.255.255.255', 7, '254.0.0.0/7'],
            ['255.255.255.255', 6, '252.0.0.0/6'],
            ['255.255.255.255', 5, '248.0.0.0/5'],
            ['255.255.255.255', 4, '240.0.0.0/4'],
            ['255.255.255.255', 3, '224.0.0.0/3'],
            ['255.255.255.255', 2, '192.0.0.0/2'],
            ['255.255.255.255', 1, '128.0.0.0/1'],
            ['255.255.255.255', 0, '0.0.0.0/0'],
        ];
    }

    public static function ipv6Provider(): array
    {
        return [
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 128, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128'],
            ['FFFF:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF', 127, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe/127'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 127, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe/127'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 126, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffc/126'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 125, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fff8/125'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 124, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fff0/124'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 123, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffe0/123'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 122, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffc0/122'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 121, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ff80/121'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 120, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ff00/120'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 116, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:f000/116'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 115, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:e000/115'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 114, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:c000/114'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 113, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:8000/113'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 112, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:0/112'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 108, 'ffff:ffff:ffff:ffff:ffff:ffff:fff0:0/108'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 104, 'ffff:ffff:ffff:ffff:ffff:ffff:ff00:0/104'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 102, 'ffff:ffff:ffff:ffff:ffff:ffff:fc00:0/102'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 101, 'ffff:ffff:ffff:ffff:ffff:ffff:f800:0/101'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 100, 'ffff:ffff:ffff:ffff:ffff:ffff:f000:0/100'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 96, 'ffff:ffff:ffff:ffff:ffff:ffff::/96'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 88, 'ffff:ffff:ffff:ffff:ffff:ff00::/88'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 80, 'ffff:ffff:ffff:ffff:ffff::/80'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 72, 'ffff:ffff:ffff:ffff:ff00::/72'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 64, 'ffff:ffff:ffff:ffff::/64'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 56, 'ffff:ffff:ffff:ff00::/56'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 48, 'ffff:ffff:ffff::/48'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 32, 'ffff:ffff::/32'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 24, 'ffff:ff00::/24'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 16, 'ffff::/16'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 8, 'ff00::/8'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 4, 'f000::/4'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 3, 'e000::/3'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 2, 'c000::/2'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 1, '8000::/1'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 0, '::/0'],
            ['0000:0000:0000:0000:0000:0000:0000:0000', 0, '::/0'],
            ['0000:0000:0000:0000:0000:0000:0000:0000', 1, '::/1'],
            ['0000:0000:0000:0000:0000:0000:0000:0000', 8, '::/8'],
            ['0000:0000:0000:0000:0000:0000:0000:0000', 16, '::/16'],
            ['0000:0000:0000:0000:0000:0000:0000:0000', 24, '::/24'],
            ['0000:0000:0000:0000:0000:0000:0000:0000', 32, '::/32'],
            ['0000:0000:0000:0000:0000:0000:0000:0000', 64, '::/64'],
            ['0000:0000:0000:0000:0000:0000:0000:0000', 128, '::/128'],
        ];
    }

    #[DataProvider("ipv4Provider")]
    public function testIpv4Mask(string $input, int $maskBits, string $expected): void
    {
        $dummy = $this->getDummyClass();
        $this->assertSame($expected, $dummy->ipv4Mask($input, $maskBits));
    }

    #[DataProvider("ipv6Provider")]
    public function testIpv6Mask(string $input, int $maskBits, string $expected): void
    {
        $dummy = $this->getDummyClass();
        $this->assertSame($expected, $dummy->ipv6Mask($input, $maskBits));
    }
}
