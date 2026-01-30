<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer;

use function ceil;
use function log;

abstract class Math
{
    public static function getHighVolumeCutoff(int $numLeaves): int
    {
        return ($numLeaves + 1) >> 1;
    }

    public static function getLowVolumeCutoff(int $numLeaves): int
    {
        if ($numLeaves < 81) {
            return 1;
        }
        $log2 = (log($numLeaves) / log(2));
        return (int) ($numLeaves - ceil(2 * $log2 * $log2));
    }
}
