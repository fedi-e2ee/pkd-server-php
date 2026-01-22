<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Exceptions;

use DateTimeImmutable;

class RateLimitException extends BaseException
{
    public ?DateTimeImmutable $rateLimitedUntil = null;
}
