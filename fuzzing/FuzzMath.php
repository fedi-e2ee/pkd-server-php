<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Fuzzing;

use FediE2EE\PKDServer\Math;
use PhpFuzzer\Config;

/** @var Config $config */

require_once dirname(__DIR__) . '/vendor/autoload.php';

$config->setTarget(function (string $input): void {
    // Extract integer values from fuzzed input
    $decoded = json_decode($input, true);

    // Test getHighVolumeCutoff with various values
    if (is_array($decoded) && isset($decoded['numLeaves']) && is_int($decoded['numLeaves'])) {
        $numLeaves = $decoded['numLeaves'];

        // Only test non-negative values (valid for this function)
        if ($numLeaves >= 0 && $numLeaves <= PHP_INT_MAX - 1) {
            $highCutoff = Math::getHighVolumeCutoff($numLeaves);
            assert(is_int($highCutoff));
            assert($highCutoff >= 0);
            // Property: high cutoff is approximately half of numLeaves + 1
            assert($highCutoff === ($numLeaves + 1) >> 1);
        }
    }

    // Test getLowVolumeCutoff with various values
    if (is_array($decoded) && isset($decoded['numLeaves']) && is_int($decoded['numLeaves'])) {
        $numLeaves = $decoded['numLeaves'];

        // Only test non-negative values
        if ($numLeaves >= 0 && $numLeaves <= 1000000) {
            $lowCutoff = Math::getLowVolumeCutoff($numLeaves);
            assert(is_int($lowCutoff));

            // Property: for small trees (< 81), low cutoff is always 1
            if ($numLeaves < 81) {
                assert($lowCutoff === 1);
            } else {
                // Property: low cutoff is less than numLeaves
                assert($lowCutoff < $numLeaves);
                // Property: low cutoff is non-negative
                assert($lowCutoff >= 0);
            }
        }
    }

    // Test with raw integer extraction from bytes
    if (strlen($input) >= 8) {
        $values = unpack('P', substr($input, 0, 8));
        if (is_array($values)) {
            $numLeaves = abs($values[1] ?? 0);

            // Clamp to reasonable range to avoid overflow
            $numLeaves = min($numLeaves, PHP_INT_MAX >> 1);

            $highCutoff = Math::getHighVolumeCutoff($numLeaves);
            assert(is_int($highCutoff));

            // Only test lowCutoff for reasonable sizes (log2 calculations)
            if ($numLeaves <= 10000000) {
                $lowCutoff = Math::getLowVolumeCutoff($numLeaves);
                assert(is_int($lowCutoff));
            }
        }
    }

    // Test relationship between high and low cutoffs
    if (is_array($decoded) && isset($decoded['numLeaves']) && is_int($decoded['numLeaves'])) {
        $numLeaves = $decoded['numLeaves'];

        if ($numLeaves >= 81 && $numLeaves <= 100000) {
            $high = Math::getHighVolumeCutoff($numLeaves);
            $low = Math::getLowVolumeCutoff($numLeaves);

            // Property: both cutoffs should be within bounds
            assert($high >= 0);
            assert($high <= $numLeaves);
            assert($low >= 0);
            assert($low <= $numLeaves);
        }
    }

    // Test consistency: same input should give same output
    if (is_array($decoded) && isset($decoded['numLeaves']) && is_int($decoded['numLeaves'])) {
        $numLeaves = abs($decoded['numLeaves']) % 100000;

        $high1 = Math::getHighVolumeCutoff($numLeaves);
        $high2 = Math::getHighVolumeCutoff($numLeaves);
        assert($high1 === $high2);

        $low1 = Math::getLowVolumeCutoff($numLeaves);
        $low2 = Math::getLowVolumeCutoff($numLeaves);
        assert($low1 === $low2);
    }

    // Test monotonicity: larger trees should have larger cutoffs (generally)
    if (is_array($decoded) && isset($decoded['n1'], $decoded['n2'])) {
        $n1 = abs((int) $decoded['n1']) % 10000;
        $n2 = abs((int) $decoded['n2']) % 10000;

        if ($n1 < $n2) {
            $high1 = Math::getHighVolumeCutoff($n1);
            $high2 = Math::getHighVolumeCutoff($n2);
            // High cutoff should be monotonically increasing
            assert($high1 <= $high2);
        }
    }
});
