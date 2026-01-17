<?php

namespace FediE2EE\PKDServer\Traits;

use FediE2EE\PKDServer\AppCache;
use FediE2EE\PKDServer\Exceptions\DependencyException;

/**
 * @property AppCache $cache
 * @method string getPrimaryCacheKey()
 */
trait HttpCacheTrait
{
    use ReqTrait;
    protected ?AppCache $cache = null;

    /**
     * @throws DependencyException
     */
    protected function getCache(): AppCache
    {
        if ($this->cache === null) {
            $this->cache = $this->appCache(
                $this->getPrimaryCacheKey(),
                90
            );
        }
        return $this->cache;
    }

    /**
     * @throws DependencyException
     */
    public function clearCache(): bool
    {
        return $this->getCache()->clear();
    }
}
