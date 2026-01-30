<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Protocol;

use FediE2EE\PKD\Crypto\Exceptions\JsonException;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use JsonSerializable;
use Override;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\HPKE\Factory;
use ParagonIE\HPKE\HPKE as Ciphersuite;
use ParagonIE\HPKE\HPKEException;
use ParagonIE\HPKE\Interfaces\EncapsKeyInterface;
use ParagonIE\HPKE\KEM\DHKEM\EncapsKey;
use ParagonIE\HPKE\KEM\DiffieHellmanKEM;
use function is_object, json_decode, json_last_error_msg, property_exists;

readonly class RewrapConfig implements JsonSerializable
{
    public function __construct(
        public string $cs,
        public string $encapsKey,
    ) {}

    public static function from(
        Ciphersuite $cs,
        EncapsKeyInterface $encapsKey
    ): self {
        if (!($encapsKey instanceof EncapsKey)) {
            throw new DependencyException('EncapsKey is not DHKEM');
        }
        return new RewrapConfig(
            $cs->getSuiteName(),
            Base64UrlSafe::encodeUnpadded($encapsKey->bytes),
        );
    }

    public static function fromJson(string $json): self
    {
        $pieces = json_decode($json);
        if (!is_object($pieces)) {
            throw new JsonException('JSON error: ' . json_last_error_msg());
        }
        if (!property_exists($pieces, 'cs')) {
            throw new JsonException('Ciphersuite (cs) is not defined');
        }
        if (!property_exists($pieces, 'ek')) {
            throw new JsonException('EncapsKey (ek) is not defined');
        }
        return new self($pieces->cs, $pieces->ek);
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'cs' => $this->cs,
            'ek' => $this->encapsKey,
        ];
    }

    /**
     * @throws HPKEException
     */
    public function getCipherSuite(): Ciphersuite
    {
        return Factory::init($this->cs);
    }

    /**
     * @return EncapsKeyInterface
     * @throws DependencyException
     * @throws HPKEException
     */
    public function getEncapsKey(): EncapsKeyInterface
    {
        $cs = $this->getCipherSuite();
        if (!($cs->kem instanceof DiffieHellmanKEM)) {
            throw new DependencyException('Ciphersuite KEM is not DiffieeHemanKEM');
        }
        return new EncapsKey($cs->kem->curve, Base64UrlSafe::decodeNoPadding($this->encapsKey));
    }
}
