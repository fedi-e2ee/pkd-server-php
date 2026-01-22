<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Fuzzing;

use FediE2EE\PKDServer\Exceptions\NetTraitException;
use FediE2EE\PKDServer\Traits\NetworkTrait;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use PhpFuzzer\Config;
use TypeError;

/** @var Config $config */

require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Anonymous class that uses NetworkTrait for testing.
 */
$testClass = new class {
    use NetworkTrait;
};

$config->setTarget(function (string $input) use ($testClass): void {
    // Test IPv4 mask with various mask bits
    try {
        if (strlen($input) >= 5) {
            $maskBits = ord($input[0]) % 64 - 16; // Range: -16 to 47 (to test boundaries)
            $potentialIp = substr($input, 1, 15);
            if (filter_var($potentialIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $result = $testClass->ipv4Mask($potentialIp, $maskBits);
                assert(is_string($result));
            }
        }
    } catch (NetTraitException) {
        // Expected for invalid mask bits
    }

    // Test IPv6 mask with various mask bits
    try {
        if (strlen($input) >= 40) {
            $maskBits = ord($input[0]) % 160 - 16; // Range: -16 to 143 (to test boundaries)
            $potentialIp = substr($input, 1, 39);
            if (filter_var($potentialIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $result = $testClass->ipv6Mask($potentialIp, $maskBits);
                assert(is_string($result));
            }
        }
    } catch (NetTraitException) {
        // Expected for invalid mask bits
    }

    // Test X-Forwarded-For parsing
    try {
        $stream = new Stream('php://memory', 'rw');
        $stream->write($input);
        $stream->rewind();

        $request = new ServerRequest(
            ['REMOTE_ADDR' => '127.0.0.1'],
            [],
            '/',
            'POST',
            $stream,
            ['X-Forwarded-For' => $input]
        );

        // Test with trusted proxy
        $result = $testClass->extractIPFromRequest($request, ['127.0.0.1']);
        assert(is_string($result));

        // Test without trusted proxy
        $result = $testClass->extractIPFromRequest($request, []);
        assert(is_string($result));
    } catch (TypeError) {
        // Expected for malformed input
    }

    // Test getRequestIPSubnet with fuzzed IP in REMOTE_ADDR
    try {
        $decoded = json_decode($input, true);
        if (is_array($decoded)) {
            $remoteAddr = $decoded['ip'] ?? $input;
            $v4Bits = ($decoded['v4'] ?? 32) % 64;
            $v6Bits = ($decoded['v6'] ?? 128) % 192;

            $stream = new Stream('php://memory', 'rw');
            $request = new ServerRequest(
                ['REMOTE_ADDR' => $remoteAddr],
                [],
                '/',
                'POST',
                $stream
            );

            $result = $testClass->getRequestIPSubnet(
                $request,
                [],
                $v4Bits,
                $v6Bits
            );
            assert(is_string($result));
        }
    } catch (TypeError|NetTraitException) {
        // Expected for malformed input
    }

    // Test getRequestActor with fuzzed JSON body
    try {
        $stream = new Stream('php://memory', 'rw');
        $stream->write($input);
        $stream->rewind();

        $request = new ServerRequest(
            ['REMOTE_ADDR' => '127.0.0.1'],
            [],
            '/',
            'POST',
            $stream
        );

        $actor = $testClass->getRequestActor($request);
        assert(is_null($actor) || is_string($actor));

        // Also test domain extraction
        $stream->rewind();
        $domain = $testClass->getRequestDomain($request);
        assert(is_null($domain) || is_string($domain));
    } catch (TypeError) {
        // Expected for malformed input
    }

    // Test stringToByteArray and byteArrayToString round-trip
    try {
        $array = $testClass->stringToByteArray($input);
        $result = $testClass->byteArrayToString($array);
        assert($result === $input);
    } catch (TypeError) {
        // Expected for edge cases
    }
});
