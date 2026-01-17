<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Interfaces;

interface HttpCacheInterface
{
    public function getPrimaryCacheKey(): string;
}
