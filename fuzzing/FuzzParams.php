<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Fuzzing;

use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\Meta\Params;
use PhpFuzzer\Config;
use TypeError;

/** @var Config $config */

require_once dirname(__DIR__) . '/vendor/autoload.php';

$config->setTarget(function (string $input): void {
    $decoded = json_decode($input, true);

    // Test hash algorithm validation
    $hashAlgos = [
        'sha256',   // Valid
        'sha384',   // Valid
        'sha512',   // Valid
        'blake2b',  // Valid
        'md5',      // Invalid (insecure)
        'sha1',     // Invalid (insecure)
        'crc32',    // Invalid (not a hash)
        $input,     // Fuzzed value
    ];

    foreach ($hashAlgos as $algo) {
        try {
            $params = new Params(hashAlgo: $algo);
            // If we got here, the algo was accepted
            assert(in_array($algo, ['sha256', 'sha384', 'sha512', 'blake2b'], true));
        } catch (DependencyException) {
            // Expected for invalid algorithms
        } catch (TypeError) {
            // Expected for non-string input
        }
    }

    // Test hostname validation with fuzzed values
    $hostnames = [
        'localhost',
        'example.com',
        'sub.example.com',
        '192.168.1.1',
        '',
        'invalid hostname',
        '-invalid.com',
        'invalid-.com',
        str_repeat('a', 64) . '.com',
    ];
    // Also try the raw input as hostname
    if (strlen($input) > 0 && strlen($input) <= 253) {
        $hostnames[] = $input;
    }

    foreach ($hostnames as $hostname) {
        try {
            $params = new Params(hostname: $hostname);
            // If we got here, the hostname was accepted
            assert(filter_var($hostname, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false);
        } catch (DependencyException) {
            // Expected for invalid hostnames
        } catch (TypeError) {
            // Expected for non-string input
        }
    }
});
