<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Config;

use FediE2EE\PKD\Crypto\SecretKey;
use FediE2EE\PKDServer\Dependency\SigningKeys;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use ParagonIE\ConstantTime\Base64UrlSafe;

/* Defer to local config (if applicable) */
if (file_exists(__DIR__ . '/local/signing-keys.php')) {
    return require_once __DIR__ . '/local/signing-keys.php';
}

if (file_exists(__DIR__ . '/signing.json')) {
    $file = file_get_contents(__DIR__ . '/signing.json');
    if (!is_string($file)) {
        throw new DependencyException('Cannot read signing keys');
    }
    $json = json_decode($file, true);
    if (!is_array($json)) {
        throw new DependencyException('Cannot read signing json');
    }
    $secretKey = new SecretKey(
        Base64UrlSafe::decodeNoPadding($json['secret-key'] ?? ''),
        $json['alg'] ?? 'ed25519',
    );
    $publicKey = $secretKey->getPublicKey();
    if (!hash_equals($publicKey->toString(), $json['public-key'])) {
        throw new DependencyException('Invalid Ed25519 keypair: public key mismatch');
    }
} else {
    // No keys? No problem.
    $secretKey = SecretKey::generate();
    $publicKey = $secretKey->getPublicKey();
    $alg = $secretKey->getAlgo();

    file_put_contents(__DIR__ . '/signing.json', json_encode([
        'alg' =>
            $alg,
        'secret-key' =>
            Base64UrlSafe::encodeUnpadded($secretKey->getBytes()),
        'public-key' =>
            $publicKey->toString(),
    ], JSON_PRETTY_PRINT));
    chmod(__DIR__ . '/signing.json', 0600);
}

return new SigningKeys($secretKey, $publicKey);
