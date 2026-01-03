<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use FediE2EE\PKDServer\Math;

#[CoversClass(Math::class)]
class MathTest extends TestCase
{
    public static function highVolume(): array
    {
        return [
            [0, 0],
            [1, 1],
            [14, 7],
            [15, 8],
            [16, 8],
        ];
    }

    public static function lowVolume(): array
    {
        return [
            [0, 1],
            [1, 1],
            [2, 1],
            [3, 1],
            [80, 1],
            [96, 9],
            [128, 30],
            [256, 128],
            [1000, 801],
            [1000_000, 999205],
        ];
    }

    #[DataProvider('highVolume')]
    public function testHighVolume(int $numLeaves, int $expected): void
    {
        $this->assertSame($expected, Math::getHighVolumeCutoff($numLeaves));
    }

    #[DataProvider('lowVolume')]
    public function testLowVolume(int $numLeaves, int $expected): void
    {
        $this->assertSame($expected, Math::getLowVolumeCutoff($numLeaves));
    }
}
