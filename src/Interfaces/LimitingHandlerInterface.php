<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface LimitingHandlerInterface
{
    public function getEnabledRateLimits(): array;
}
