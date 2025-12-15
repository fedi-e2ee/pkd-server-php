<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Traits;

use FediE2EE\PKDServer\Exceptions\ProtocolException;
use FediE2EE\PKDServer\ServerConfig;
use FediE2EE\PKDServer\Traits\TOTPTrait;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class TOTPTraitTest extends TestCase
{
    public function getDummyClass(): object
    {
        return new class() {
            use TOTPTrait;
            private ServerConfig $config;
            public function __construct()
            {
                $this->config = $GLOBALS['pkdConfig'];
            }
        };
    }

    public function testOrd(): void
    {
        $class = $this->getDummyClass();
        for ($i = 0; $i < 256; ++$i) {
            $c = chr($i);
            $x = ord($c);
            $this->assertSame($i, $x);
            $this->assertSame($x, $class::ord($c));
        }
    }

    public function testThrowIFOutsideWindow(): void
    {
        $maxLife = $GLOBALS['pkdConfig']->getParams()->otpMaxLife;
        $class = $this->getDummyClass();

        // Should not throw:
        for ($i = 0; $i < $maxLife; ++$i) {
            $class->throwIfTimeOutsideWindow(time() - $i);
        }
        $class->throwIfTimeOutsideWindow(time() - $maxLife + 1);
        $this->expectException(ProtocolException::class);
        $class->throwIfTimeOutsideWindow(time() - $maxLife - 1);
    }

    public function testGenerateTotp(): void
    {
        $class = $this->getDummyClass();
        $this->assertSame('83564927', $class::generateTOTP(str_repeat('A', 20), 1765761080));
        $this->assertSame('92908804', $class::generateTOTP(str_repeat('A', 20), 1765761140));
        $this->assertSame('58640969', $class::generateTOTP(str_repeat('B', 20), 1765761080));
        $this->assertSame('86765410', $class::generateTOTP(str_repeat('B', 20), 1765761140));
    }
}
