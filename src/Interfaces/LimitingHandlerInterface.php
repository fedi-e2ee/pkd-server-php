<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Interfaces;

interface LimitingHandlerInterface
{
    /**
     * @return array<int, string>
     */
    public function getEnabledRateLimits(): array;
}
