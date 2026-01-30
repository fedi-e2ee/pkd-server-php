<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables\Records;

use FediE2EE\PKD\Crypto\Merkle\InclusionProof;
use FediE2EE\PKD\Crypto\UtilTrait;
use FediE2EE\PKDServer\Meta\RecordForTable;
use FediE2EE\PKDServer\Tables\ReplicaHistory;
use FediE2EE\PKDServer\Traits\TableRecordTrait;
use JsonException;
use SodiumException;

use function is_null;
use function sodium_hex2bin;

#[RecordForTable(ReplicaHistory::class)]
final class ReplicaLeaf
{
    use TableRecordTrait;
    use UtilTrait;

    public function __construct(
        public string $root,
        public string $publicKeyHash,
        public string $contentHash,
        public string $signature,
        public string $contents,
        public string $cosignature,
        public ?InclusionProof $inclusionProof = null,
        public readonly string $created = '',
        public readonly string $replicated = '',
        ?int $primaryKey = null
    ) {
        $this->primaryKey = $primaryKey;
    }

    /**
     * @throws JsonException
     */
    public function toArray(): array
    {
        return [
            'root' =>
                $this->root,
            'publickeyhash' =>
                $this->publicKeyHash,
            'contenthash' =>
                $this->contentHash,
            'signature' =>
                $this->signature,
            'contents' =>
                $this->contents,
            'cosignature' =>
                $this->cosignature,
            'inclusionproof' =>
                is_null($this->inclusionProof)
                    ? null
                    : self::jsonEncode($this->inclusionProof),
            'created' =>
                $this->created,
            'replicated' =>
                $this->replicated,
        ];
    }

    /**
     * @api
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
