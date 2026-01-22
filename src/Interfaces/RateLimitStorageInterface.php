<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Interfaces;

use FediE2EE\PKDServer\RateLimit\RateLimitData;

interface RateLimitStorageInterface
{
    public function get(string $type, string $identifier): ?RateLimitData;
    public function set(string $type, string $identifier, RateLimitData $data): bool;
    public function delete(string $type, string $identifier): bool;
}
