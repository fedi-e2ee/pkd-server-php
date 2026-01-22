<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RateLimit;

use DateInterval;
use DateMalformedIntervalStringException;
use DateTimeImmutable;
use FediE2EE\PKDServer\Exceptions\{
    DependencyException,
    RateLimitException
};
use FediE2EE\PKDServer\Interfaces\{
    LimitingHandlerInterface,
    RateLimitInterface,
    RateLimitStorageInterface
};
use FediE2EE\PKDServer\Traits\NetworkTrait;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DefaultRateLimiting implements RateLimitInterface
{
    use NetworkTrait;

    /**
     * @param RateLimitStorageInterface $storage
     * @param bool $enabled
     * @param int $baseDelay
     * @param array $trustedProxies
     * @param int $ipv4MaskBits
     * @param int $ipv6MaskBits
     * @param bool $shouldEnforceDomain
     * @param bool $shouldEnforceActor
     * @param array<string, DateInterval> $maxTimeouts
     */
    public function __construct(
        private RateLimitStorageInterface $storage,
        private bool $enabled = true,
        private int $baseDelay = 100,
        private array $trustedProxies = [],
        private int $ipv4MaskBits = 32,
        private int $ipv6MaskBits = 64,
        private bool $shouldEnforceDomain = true,
        private bool $shouldEnforceActor = true,
        private array $maxTimeouts = [],
    ) {}

    #[Override]
    public function getStorage(): RateLimitStorageInterface
    {
        return $this->storage;
    }

    #[Override]
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    #[Override]
    public function getBaseDelay(): int
    {
        return $this->baseDelay;
    }

    public function withBaseDelay(int $baseDelay): static
    {
        $self = clone $this;
        $self->baseDelay = $baseDelay;
        return $self;
    }

    #[Override]
    public function getRequestSubnet(ServerRequestInterface $request): string
    {
        // Defer to trait method
        return $this->getRequestIPSubnet(
            $request,
            $this->trustedProxies,
            $this->ipv4MaskBits,
            $this->ipv6MaskBits
        );
    }

    /**
     * @throws DependencyException
     */
    #[Override]
    public function shouldEnforce(string $type): bool
    {
        return match ($type) {
            'ip' => true,
            'actor' => $this->shouldEnforceActor,
            'domain' => $this->shouldEnforceDomain,
            default => throw new DependencyException('Unknown type: ' . $type),
        };
    }

    /**
     * @throws RateLimitException
     * @throws DateMalformedIntervalStringException
     */
    #[Override]
    public function enforceRateLimit(
        ServerRequestInterface $request,
        RequestHandlerInterface & LimitingHandlerInterface $handler
    ): void {
        // Possible targets:
        $lookups = [
            'actor' => $this->getRequestActor($request),
            'ip' => $this->getRequestSubnet($request),
            'domain' => $this->getRequestDomain($request)
        ];
        $now = (new DateTimeImmutable('NOW'));

        // Iterate over the actual configured targets:
        /** @var string $target */
        foreach ($handler->getEnabledRateLimits() as $target) {
            $penalty = $this->storage->get($target, $lookups[$target]);
            if ($penalty->failures < 1) {
                // This should be treated the same as NULL.
                $this->storage->delete($target, $lookups[$target]);
                continue;
            }
            // This request should be rate-limited. When is the next request allowed?
            $expires = $this->getPenaltyTime($penalty, $target);
            if (!is_null($expires)) {
                // You didn't wait long enough!
                $ex = new RateLimitException('Please calm down. You are rate-limited.');
                $ex->rateLimitedUntil = $expires;
                throw $ex;
            }

            // Should we reduce the cooldown?
            if ($now >= $penalty->getCooldownStart()) {
                $this->storage->set(
                    $target,
                    $lookups[$target],
                    $this->getCooledDown($penalty)
                );
            }
        }
        // If you're here, you waited long enough. Good job.
    }

    /**
     * Reduce the cooldown until zero or the cooldown window is in the future:
     *
     * @throws DateMalformedIntervalStringException
     */
    #[Override]
    public function getCooledDown(RateLimitData $data): RateLimitData
    {
        $now = (new DateTimeImmutable('NOW'));
        $start = $data->getCooldownStart();
        $failures = $data->failures;
        do {
            // Decrease and get step size:
            $step = $this->getIntervalFromFailureCount(--$failures);
            $start = $start->add($step)->add($step)->add($step);
            // 3 windows = (1 window (penalty)) + (2 windows (cooldown before reduction))
            if ($start < $now) {
                // We've landed in a window with a reduced cooldown.
                break;
            }

        } while ($data->failures > 0);
        // Either way, return the updated rate-limit info:
        return $data->withFailures($failures)->withCooldownStart($start);
    }

    /**
     * @throws DateMalformedIntervalStringException
     */
    public function getPenaltyTime(?RateLimitData $data, string $target): ?DateTimeImmutable
    {
        $now = (new DateTimeImmutable('NOW'));
        if (is_null($data)) {
            return null;
        }

        $lastFailTime = $data->getLastFailTime();
        $penalty = $this->getIntervalFromFailureCount($data->failures);

        // Maximum penalty support:
        if (array_key_exists($target, $this->maxTimeouts)) {
            $max = $this->maxTimeouts[$target];
            if ($penalty > $max) {
                $penalty = $max;
            }
        }

        $expires = $lastFailTime->add($penalty);
        if ($expires >= $now) {
            return $expires;
        }
        return null;
    }

    /**
     * @throws DateMalformedIntervalStringException
     */
    public function getIntervalFromFailureCount(int $failures): DateInterval
    {
        if ($failures < 1) {
            return new DateInterval('PT0S');
        }
        $milliseconds = $this->baseDelay << ($failures - 1);
        $seconds = floor($milliseconds / 1000);
        $us = ($milliseconds % 1000) * 1000;
        return DateInterval::createFromDateString($seconds. ' seconds + ' . $us . ' microseconds');
    }

    /**
     * @throws DateMalformedIntervalStringException
     */
    #[Override]
    public function recordPenalty(string $type, string $lookup): void
    {
        $newExpires = $this->increaseFailures($this->storage->get($type, $lookup));

        $this->storage->set(
            $type,
            $lookup,
            $newExpires,
        );
    }

    /**
     * @throws DateMalformedIntervalStringException
     */
    public function increaseFailures(?RateLimitData $existingLimit = null): RateLimitData
    {
        if (is_null($existingLimit)) {
            $existingLimit = new RateLimitData(0);
        }
        $now = new DateTimeImmutable('NOW');
        $increasedFailures = $existingLimit->failures + 1;
        $penalty = $this->getIntervalFromFailureCount($increasedFailures);
        // 3 windows = (1 window (penalty)) + (2 windows (cooldown before reduction))
        $cooldownStart = $now->add($penalty)->add($penalty)->add($penalty);
        return $existingLimit
            ->withFailures($increasedFailures)
            ->withLastFailTime($now)
            ->withCooldownStart($cooldownStart);
    }
}
