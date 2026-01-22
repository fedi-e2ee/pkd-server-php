<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Config;

use FediE2EE\PKDServer\RateLimit\DefaultRateLimiting;
use FediE2EE\PKDServer\RateLimit\Storage\Filesystem;
use FediE2EE\PKDServer\RateLimit\Storage\Redis;
use FediE2EE\PKDServer\ServerConfig;

/* Defer to local config (if applicable) */
if (file_exists(__DIR__ . '/local/rate-limiting.php')) {
    return require_once __DIR__ . '/local/rate-limiting.php';
}

/** @var ServerConfig $GLOBALS['pkdConfig'] */
$redisClient = $GLOBALS['pkdConfig']->getRedis();

// Cache key
if (!file_exists(__DIR__ . '/rate-limiting-cache.key')) {
    file_put_contents(__DIR__ . '/rate-limiting-cache.key', sodium_bin2hex(random_bytes(32)));
}
$cacheKey = sodium_hex2bin(file_get_contents(__DIR__ . '/rate-limiting-cache.key'));

// Use Redis if we can, fallback to the filesystem otherwise
$storage = !is_null($GLOBALS['pkdConfig']->getRedis())
    ? new Redis($redisClient, $cacheKey)
    : new Filesystem(dirname(__DIR__) . '/tmp/rate-limiting');

return new DefaultRateLimiting(
    storage: $storage,
    enabled: true,
    baseDelay: 100,    // milliseconds
    trustedProxies: [], // IP addresses that can set X-Forwarded-For
);
