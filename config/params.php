<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Config;

use FediE2EE\PKDServer\Meta\Params;

/* Defer to local config (if applicable) */
if (file_exists(__DIR__ . '/local/params.php')) {
    return require_once __DIR__ . '/local/params.php';
}
if (!file_exists(__DIR__ . '/cache.key')) {
    file_put_contents(__DIR__ . '/cache.key', sodium_bin2hex(random_bytes(32)));
}
$key = file_get_contents(__DIR__ . '/cache.key');

// We will define local configuration here:
return new Params(
    hashAlgo: 'sha256',
    otpMaxLife: 120,
    actorUsername: 'pubkeydir',
    hostname: 'localhost',
    cacheKey: $key,
);
