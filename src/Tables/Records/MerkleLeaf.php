<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables\Records;

use FediE2EE\PKD\Crypto\Merkle\InclusionProof;
use FediE2EE\PKD\Crypto\SecretKey;
use FediE2EE\PKD\Crypto\UtilTrait;
use FediE2EE\PKDServer\Meta\RecordForTable;
use FediE2EE\PKDServer\Protocol\Payload;
use FediE2EE\PKDServer\Traits\TableRecordTrait;
use FediE2EE\PKDServer\Tables\MerkleState;
use ParagonIE\ConstantTime\Base64UrlSafe;

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
        ?int $primaryKey = null
    ) {
        $this->primaryKey = $primaryKey;
    }

    public static function from(
        string $contents,
        SecretKey $sk
    ): self {
        $contentHash = hash('sha256', $contents);
        $signature = $sk->sign(sodium_hex2bin($contentHash));
        $publicKeyHash = hash('sha256', $sk->getPublicKey()->getBytes());
        return new self(
            $contents,
            $contentHash,
            $signature,
            $publicKeyHash,
            null,
            (string) time()
        );
    }

    /**
     * @api
     */
    public static function fromPayload(Payload $payload, SecretKey $sk): self
    {
        return self::from($payload->getMerkleTreePayload(), $sk);
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
        return Base64UrlSafe::encodeUnpadded($this->signature);
    }

    public function serializeForMerkle(): string
    {
        return $this->preAuthEncode([
            $this->contentHash,
            $this->signature,
            $this->publicKeyHash,
        ]);
    }
}
