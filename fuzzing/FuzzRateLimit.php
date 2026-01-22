<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Fuzzing;

use DateInterval;
use DateMalformedIntervalStringException;
use DateMalformedStringException;
use DateTimeImmutable;
use FediE2EE\PKD\Crypto\Exceptions\InputException;
use FediE2EE\PKDServer\Interfaces\RateLimitStorageInterface;
use FediE2EE\PKDServer\RateLimit\DefaultRateLimiting;
use FediE2EE\PKDServer\RateLimit\RateLimitData;
use JsonException;
use PhpFuzzer\Config;
use TypeError;

/** @var Config $config */

require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Simple in-memory rate limit storage for testing.
 */
$memoryStorage = new class implements RateLimitStorageInterface {
    private array $data = [];

    public function get(string $type, string $identifier): ?RateLimitData
    {
        return $this->data[$type][$identifier] ?? null;
    }

    public function set(string $type, string $identifier, RateLimitData $data): bool
    {
        $this->data[$type][$identifier] = $data;
        return true;
    }

    public function delete(string $type, string $identifier): bool
    {
        unset($this->data[$type][$identifier]);
        return true;
    }

    public function clear(): void
    {
        $this->data = [];
    }
};

$config->setTarget(function (string $input) use ($memoryStorage): void {
    $memoryStorage->clear();

    // Test RateLimitData::fromJson with fuzzed JSON
    try {
        $data = RateLimitData::fromJson($input);
        assert($data instanceof RateLimitData);
        assert(is_int($data->failures));
        assert($data->getLastFailTime() instanceof DateTimeImmutable);
        assert($data->getCooldownStart() instanceof DateTimeImmutable);
    } catch (JsonException|DateMalformedStringException|InputException|TypeError) {
        // Expected for malformed input
    }

    // Test RateLimitData construction with fuzzed values
    try {
        $decoded = json_decode($input, true);
        if (is_array($decoded)) {
            $failures = $decoded['failures'] ?? 0;
            if (is_int($failures)) {
                $data = new RateLimitData($failures);

                // Test immutable methods
                $increased = $data->failure();
                assert($increased->failures === $failures + 1);

                $withFailures = $data->withFailures($failures + 10);
                assert($withFailures->failures === $failures + 10);

                // Test JSON serialization round-trip
                $serialized = json_encode($data->jsonSerialize());
                assert(is_string($serialized));
            }
        }
    } catch (TypeError) {
        // Expected for malformed input
    }

    // Test DefaultRateLimiting with various baseDelay values
    try {
        $decoded = json_decode($input, true);
        if (is_array($decoded)) {
            $baseDelay = $decoded['baseDelay'] ?? 100;
            if (is_int($baseDelay) && $baseDelay >= 1 && $baseDelay <= 100000) {
                $rateLimiter = new DefaultRateLimiting(
                    $memoryStorage,
                    true,
                    $baseDelay
                );

                assert($rateLimiter->getBaseDelay() === $baseDelay);

                // Test interval calculation with various failure counts
                for ($i = 0; $i <= 20; $i++) {
                    $interval = $rateLimiter->getIntervalFromFailureCount($i);
                    assert($interval instanceof DateInterval);
                }
            }
        }
    } catch (DateMalformedIntervalStringException|TypeError) {
        // Expected for edge cases
    }

    // Test processTTL with various inputs
    try {
        $rateLimiter = new DefaultRateLimiting($memoryStorage, true, 100);

        // Test with null
        $result = $rateLimiter->processTTL(null);
        assert(is_int($result));
        assert($result === 100);

        // Test with integer
        $decoded = json_decode($input, true);
        if (is_array($decoded) && isset($decoded['ttl']) && is_int($decoded['ttl'])) {
            $result = $rateLimiter->processTTL($decoded['ttl']);
            assert(is_int($result));
            assert($result === $decoded['ttl']);
        }

        // Test with DateInterval
        if (is_array($decoded) && isset($decoded['interval']) && is_string($decoded['interval'])) {
            try {
                $interval = new DateInterval($decoded['interval']);
                $result = $rateLimiter->processTTL($interval);
                assert(is_int($result));
            } catch (DateMalformedIntervalStringException) {
                // Expected for invalid interval strings
            }
        }
    } catch (TypeError) {
        // Expected for malformed input
    }

    // Test getPenaltyTime with various failure counts
    try {
        $rateLimiter = new DefaultRateLimiting($memoryStorage, true, 100);

        $decoded = json_decode($input, true);
        if (is_array($decoded) && isset($decoded['failures']) && is_int($decoded['failures'])) {
            $failures = abs($decoded['failures']) % 50;
            $data = new RateLimitData($failures);

            $penalty = $rateLimiter->getPenaltyTime($data, 'ip');
            assert(is_null($penalty) || $penalty instanceof DateTimeImmutable);
        }
    } catch (DateMalformedIntervalStringException|TypeError) {
        // Expected for edge cases
    }

    // Test getCooledDown
    try {
        $rateLimiter = new DefaultRateLimiting($memoryStorage, true, 100);

        $decoded = json_decode($input, true);
        if (is_array($decoded) && isset($decoded['failures']) && is_int($decoded['failures'])) {
            $failures = abs($decoded['failures']) % 30;
            if ($failures > 0) {
                $data = new RateLimitData($failures);
                $cooledDown = $rateLimiter->getCooledDown($data);
                assert($cooledDown instanceof RateLimitData);
                assert($cooledDown->failures <= $data->failures);
            }
        }
    } catch (DateMalformedIntervalStringException|TypeError) {
        // Expected for edge cases
    }

    // Test withMaxTimeout
    try {
        $rateLimiter = new DefaultRateLimiting($memoryStorage, true, 100);

        $decoded = json_decode($input, true);
        if (is_array($decoded) && isset($decoded['timeout']) && is_string($decoded['timeout'])) {
            try {
                $interval = new DateInterval($decoded['timeout']);
                $withTimeout = $rateLimiter->withMaxTimeout('ip', $interval);
                assert($withTimeout instanceof DefaultRateLimiting);

                // Test that max timeout is enforced
                $data = new RateLimitData(30); // High failure count
                $penalty = $withTimeout->getPenaltyTime($data, 'ip');
                assert(is_null($penalty) || $penalty instanceof DateTimeImmutable);
            } catch (DateMalformedIntervalStringException) {
                // Expected for invalid interval strings
            }
        }
    } catch (DateMalformedIntervalStringException|TypeError) {
        // Expected for edge cases
    }

    try {
        $rateLimiter = new DefaultRateLimiting($memoryStorage);
        $rateLimiter->shouldEnforce($input);
        // If we got here with an unknown type, it's unexpected
        assert(in_array($input, ['ip', 'actor', 'domain'], true));
    } catch (\Exception) {
        // Expected for unknown types
    }
});
