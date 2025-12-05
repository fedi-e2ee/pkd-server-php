<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Config;

use Predis\Client as PredisClient;

/* Defer to local config (if applicable) */
if (file_exists(__DIR__ . '/local/redis.php')) {
    return require_once __DIR__ . '/local/redis.php';
}

if (DIRECTORY_SEPARATOR === '/') {
    return new PredisClient();
}
// By default, we do not load on Windows. Overload this in local/redis.php if you want it.
return null;
