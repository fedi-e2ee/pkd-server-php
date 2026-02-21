<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Config;

use FediE2EE\PKDServer\Dependency\HPKE;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use ParagonIE\HPKE\KEM\DHKEM\{
    EncapsKey,
    DecapsKey
};
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\HPKE\Factory;
use ParagonIE\HPKE\KEM\DiffieHellmanKEM;

/* Defer to local config (if applicable) */
if (file_exists(__DIR__ . '/local/hpke.php')) {
    return require_once __DIR__ . '/local/hpke.php';
}
/* Default cipher suite: */
$defaultCipherSuite = 'DHKEM(X25519, HKDF-SHA256), HKDF-SHA256, ChaCha20-Poly1305';

if (file_exists(__DIR__ . '/hpke.json')) {
    $file = file_get_contents(__DIR__ . '/hpke.json');
    if (!is_string($file)) {
        throw new DependencyException('Cannot read HPKE keys');
    }
    $json = json_decode($file, true);
    if (!is_array($json)) {
        throw new DependencyException('Cannot read HPKE json');
    }
    $hpke = Factory::init($json['ciphersuite'] ?? $defaultCipherSuite);
    if ($hpke->kem instanceof DiffieHellmanKEM) {
        $decapsKey = new DecapsKey($hpke->kem->curve, Base64UrlSafe::decodeNoPadding($json['decaps-key']));
        $encapsKey = new EncapsKey($hpke->kem->curve, Base64UrlSafe::decodeNoPadding($json['encaps-key']));
    } else {
        throw new DependencyException('Unrecognized HPKE KEM type');
    }
} else {
    // No keys? No problem.
    $hpke = Factory::init($defaultCipherSuite);
    [$decapsKey, $encapsKey] = $hpke->kem->generateKeys();

    // Save to disk:
    file_put_contents(__DIR__ . '/hpke.json', json_encode([
        'ciphersuite' =>
            $defaultCipherSuite,
        'decaps-key' =>
            Base64UrlSafe::encodeUnpadded($decapsKey->bytes),
        'encaps-key' =>
            Base64UrlSafe::encodeUnpadded($encapsKey->bytes),
    ], JSON_PRETTY_PRINT));
    chmod(__DIR__ . '/hpke.json', 0600);
}
return new HPKE($hpke, $decapsKey, $encapsKey);
