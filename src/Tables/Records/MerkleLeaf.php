<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables\Records;

use FediE2EE\PKD\Crypto\Exceptions\NotImplementedException;
use FediE2EE\PKD\Crypto\{
    Merkle\InclusionProof,
    SecretKey,
    UtilTrait
};
use FediE2EE\PKDServer\Meta\RecordForTable;
use FediE2EE\PKDServer\Protocol\Payload;
use FediE2EE\PKDServer\Traits\TableRecordTrait;
use FediE2EE\PKDServer\Tables\MerkleState;
use ParagonIE\ConstantTime\Base64UrlSafe;
use SodiumException;

use function hash;
use function json_decode;
use function sodium_bin2hex;
use function sodium_hex2bin;
use function time;

/**
 * Abstraction for a row in the MerkleState table
 */
#[RecordForTable(MerkleState::class)]
class MerkleLeaf
{
    use TableRecordTrait;
    use UtilTrait;

    public function __construct(
        public readonly string $contents,
        public readonly string $contentHash,
        public readonly string $signature,
        public readonly string $publicKeyHash,
        public ?InclusionProof $inclusionProof = null,
        public readonly string $created = '',
        public ?string $wrappedKeys = null,
        ?int $primaryKey = null
    ) {
        $this->primaryKey = $primaryKey;
    }

    /**
     * @throws NotImplementedException
     * @throws SodiumException
     */
    public static function from(
        string $contents,
        SecretKey $sk,
        ?string $rewrappedKeys = null,
    ): self {
        $contentHash = hash('sha256', $contents);
        $signature = sodium_bin2hex($sk->sign(sodium_hex2bin($contentHash)));
        $publicKeyHash = hash('sha256', $sk->getPublicKey()->getBytes());
        return new self(
            $contents,
            $contentHash,
            $signature,
            $publicKeyHash,
            null,
            (string) time(),
            $rewrappedKeys,
        );
    }

    /**
     * @api
     *
     * @throws NotImplementedException
     * @throws SodiumException
     */
    public static function fromPayload(
        Payload $payload,
        SecretKey $sk,
        ?string $rewrappedKeys = null
    ): self {
        return self::from(
            $payload->getMerkleTreePayload(),
            $sk,
            $rewrappedKeys
        );
    }

    public function setPrimaryKey(?int $primary): static
    {
        $this->primaryKey = $primary;
        return $this;
    }

    public function getContents(): array
    {
        return json_decode($this->contents, true);
    }

    /**
     * @api
     */
    public function getInclusionProof(): ?InclusionProof
    {
        return $this->inclusionProof;
    }

    public function getSignature(): string
    {
        return Base64UrlSafe::encodeUnpadded(sodium_hex2bin($this->signature));
    }

    /**
     * @throws SodiumException
     */
    public function serializeForMerkle(): string
    {
        return $this->preAuthEncode([
            sodium_hex2bin($this->contentHash),
            sodium_hex2bin($this->signature),
            sodium_hex2bin($this->publicKeyHash),
        ]);
    }
}
