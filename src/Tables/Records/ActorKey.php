<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables\Records;

use FediE2EE\PKD\Crypto\PublicKey;
use FediE2EE\PKDServer\Meta\RecordForTable;
use FediE2EE\PKDServer\Traits\TableRecordTrait;
use FediE2EE\PKDServer\Tables\PublicKeys;

/**
 * Abstraction for a row in the PublicKeys table
 */
#[RecordForTable(PublicKeys::class)]
class ActorKey
{
    use TableRecordTrait;

    public function __construct(
        public Actor $actor,
        public PublicKey $publicKey,
        public bool $trusted,
        public MerkleLeaf $insertLeaf,
        public ?MerkleLeaf $revokeLeaf = null,
        public ?string $keyID = null,
        ?int $primaryKey = null,
    ) {
        $this->primaryKey = $primaryKey;
    }
}
