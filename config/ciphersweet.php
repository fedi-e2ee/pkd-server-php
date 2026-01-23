<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Config;

use ParagonIE\CipherSweet\{
    Backend\BoringCrypto,
    CipherSweet,
    KeyProvider\FileProvider,
    KeyProvider\StringProvider
};

if (file_exists(__DIR__ . '/local/ciphersweet.php')) {
    return require_once __DIR__ . '/local/ciphersweet.php';
}

// Primary encryption key:
$filePath = __DIR__ . '/ciphersweet-primary.key';
if (!file_exists($filePath)) {
    $primary = random_bytes(32);
    file_put_contents($filePath, sodium_bin2hex($primary));
    chmod($filePath, 0600);
    $keyProvider = new StringProvider($primary);
} else {
    $keyProvider = new FileProvider($filePath);
}

return new CipherSweet(
    $keyProvider,
    new BoringCrypto()
);
