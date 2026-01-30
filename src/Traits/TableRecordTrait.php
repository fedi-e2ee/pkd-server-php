<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Traits;

use FediE2EE\PKD\Crypto\Exceptions\CryptoException;
use FediE2EE\PKDServer\ServerConfig;
use Psr\SimpleCache\InvalidArgumentException;
use FediE2EE\PKD\Crypto\{
    PublicKey,
    SymmetricKey
};
use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\Exceptions\{
    FetchException,
    TableException,
};
use SodiumException;
use function array_key_exists, is_null, property_exists;

/**
 * @property ServerConfig $config
 * @property ?int $primaryKey
 */
trait TableRecordTrait
{
    use JsonTrait;

    public ?int $primaryKey = null;

    /** @var SymmetricKey[] */
    public array $symmetricKeys = [];

    /**
     * @psalm-suppress UndefinedThisPropertyFetch
     */
    public function hasPrimaryKey(): bool
    {
        return !is_null($this->primaryKey);
    }

    /**
     * @throws TableException
     * @psalm-suppress UndefinedThisPropertyFetch
     */
    public function getPrimaryKey(): int
    {
        if (is_null($this->primaryKey)) {
            throw new TableException('Access violation: This record has no primary key');
        }
        return $this->primaryKey;
    }

    /**
     * @throws TableException
     * @psalm-suppress UndefinedThisPropertyFetch
     */
    public function attachSymmetricKey(string $property, SymmetricKey $key): self
    {
        if (!property_exists($this, $property)) {
            throw new TableException('Property ' . $property . ' does not exist!');
        }
        if (array_key_exists($property, $this->symmetricKeys)) {
            throw new TableException('Property ' . $property .' already has a symmetric key');
        }
        $this->symmetricKeys[$property] = $key;
        return $this;
    }

    /**
     * @throws TableException
     * @psalm-suppress UndefinedThisPropertyFetch
     */
    public function getSymmetricKeyForProperty(string $property): SymmetricKey
    {
        if (!property_exists($this, $property)) {
            throw new TableException('Property ' . $property . ' does not exist!');
        }
        if (!array_key_exists($property, $this->symmetricKeys)) {
            throw new TableException('Property ' . $property .' has no symmetric key');
        }
        return $this->symmetricKeys[$property];
    }

    public function getSymmetricKeys(): array
    {
        return $this->symmetricKeys;
    }

    /**
     * Fetch the RFC 9421 public keys for an actor.
     *
     * If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519
     * public key is found. We do not support JWS, RSA, or ECDSA keys.
     *
     * @param string $actorId
     * @return PublicKey
     *
     * @throws CryptoException
     * @throws FetchException
     * @throws InvalidArgumentException
     * @throws SodiumException
     */
    public function getRfc9421PublicKeys(string $actorId): PublicKey
    {
        return (new WebFinger())->getPublicKey($actorId);
    }
}
