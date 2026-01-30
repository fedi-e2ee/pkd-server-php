<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RateLimit;

use DateMalformedStringException;
use DateTimeImmutable;
use FediE2EE\PKD\Crypto\Exceptions\InputException;
use FediE2EE\PKD\Crypto\UtilTrait;
use FediE2EE\PKDServer\Traits\JsonTrait;
use JsonException as BaseJsonException;
use Override;
use JsonSerializable;
use function is_array, is_null;

class RateLimitData implements JsonSerializable
{
    use JsonTrait;
    use UtilTrait;

    protected DateTimeImmutable $lastFailTime;
    protected DateTimeImmutable $cooldownStart;

    public function __construct(
        public int $failures,
        ?DateTimeImmutable $lastFailTime = null,
        ?DateTimeImmutable $cooldownStart = null,
    ) {
        if (is_null($lastFailTime)) {
            $lastFailTime = new DateTimeImmutable('NOW');
        }
        if (is_null($cooldownStart)) {
            $cooldownStart = new DateTimeImmutable('NOW');
        }
        $this->lastFailTime = $lastFailTime;
        $this->cooldownStart = $cooldownStart;
    }

    /**
     * @throws BaseJsonException
     * @throws DateMalformedStringException
     * @throws InputException
     */
    public static function fromJson(string $json): self
    {
        $decoded = self::jsonDecode($json);
        self::assertAllArrayKeysExist(
            $decoded,
            'failures',
            'last-fail-time',
            'cooldown-start',
        );
        $lastFail = null;
        if (!empty($decoded['last-fail-time'])) {
            if (is_array($decoded['last-fail-time'])) {
                $lastFail = new DateTimeImmutable($decoded['last-fail-time']['date']);
            } else {
                $lastFail = new DateTimeImmutable($decoded['last-fail-time']);
            }
        }
        $cooldownStart = null;
        if (!empty($decoded['cooldown-start'])) {
            if (is_array($decoded['cooldown-start'])) {
                $cooldownStart = new DateTimeImmutable($decoded['cooldown-start']['date']);
            } else {
                $cooldownStart = new DateTimeImmutable($decoded['cooldown-start']);
            }
        }
        return new self(
            $decoded['failures'] ?? 0,
            $lastFail,
            $cooldownStart,
        );
    }

    public function getLastFailTime(): DateTimeImmutable
    {
        return $this->lastFailTime;
    }

    public function getCooldownStart(): DateTimeImmutable
    {
        return $this->cooldownStart;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'failures' => $this->failures,
            'last-fail-time' => $this->lastFailTime,
            'cooldown-start' => $this->cooldownStart,
        ];
    }

    public function failure(?DateTimeImmutable $cooldownStart = null): self
    {
        return new self(
            $this->failures + 1,
            null,
            $cooldownStart ?? $this->cooldownStart,
        );
    }

    public function withCooldownStart(DateTimeImmutable $cooldownStart): self
    {
        return new self(
            $this->failures,
            $this->lastFailTime,
            $cooldownStart,
        );
    }

    public function withFailures(int $failures): self
    {
        return new self(
            $failures,
            $this->lastFailTime,
            $this->cooldownStart,
        );
    }

    public function withLastFailTime(DateTimeImmutable $lastFailTime): self
    {
        return new self(
            $this->failures,
            $lastFailTime,
            $this->cooldownStart,
        );
    }
}
