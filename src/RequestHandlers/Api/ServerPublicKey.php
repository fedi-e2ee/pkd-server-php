<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKDServer\Traits\ReqTrait;
use FediE2EE\PKD\Crypto\Exceptions\{
    JsonException,
    NotImplementedException
};
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\Meta\Route;
use Mdanter\Ecc\Exception\InsecureCurveException;
use Override;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\HPKE\AEAD\{
    AES128GCM,
    AES256GCM,
    ChaCha20Poly1305
};
use ParagonIE\HPKE\{HPKE, HPKEException, KDF\HKDF, KEM\DHKEM\Curve, KEM\DHKEM\EncapsKey, KEM\DiffieHellmanKEM};
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};
use SodiumException;
use TypeError;

class ServerPublicKey implements RequestHandlerInterface
{
    use ReqTrait;

    /**
     * @throws DependencyException
     * @throws HPKEException
     * @throws InsecureCurveException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    #[Route("/api/server-public-key")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $hpke = $this->config()->getHPKE();
        $cs = $this->cipherSuiteString($hpke->cs);
        $encapsKey = $hpke->encapsKey;
        if (!($encapsKey instanceof EncapsKey)) {
            throw new TypeError('Only DHKEM encaps keys are expected');
        }
        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/server-public-key',
            'current-time' => $this->time(),
            'hpke-ciphersuite' => $cs,
            'hpke-public-key' => Base64UrlSafe::encodeUnpadded($encapsKey->bytes),
        ]);
    }

    public function cipherSuiteString(HPKE $hpke): string
    {
        if (!$hpke->kem instanceof DiffieHellmanKEM) {
            throw new TypeError('Too new for this library');
        }
        if (!$hpke->kdf instanceof HKDF) {
            throw new TypeError('Unknown KDF algorithm');
        }

        $curve = match($hpke->kem->curve) {
            Curve::X25519 => 'Curve25519',
            default => throw new TypeError('Forbidden elliptic curve chosen for HPKE'),
        };
        $hash = $hpke->kdf->hash->value;
        $aead = match($hpke->aead::class) {
            AES128GCM::class => 'Aes128Gcm',
            AES256GCM::class => 'Aes256Gcm',
            ChaCha20Poly1305::class => 'ChaChaPoly',
            default => throw new TypeError('Unknown AEAD mode configured for HPKE'),
        };
        return implode('_', [$curve, $hash, $aead]);
    }
}
