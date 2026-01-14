<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables\Records;

use FediE2EE\PKD\Crypto\PublicKey;
use FediE2EE\PKD\Crypto\UtilTrait;
use FediE2EE\PKDServer\Meta\RecordForTable;
use FediE2EE\PKDServer\Tables\ReplicaPublicKeys;
use FediE2EE\PKDServer\Traits\TableRecordTrait;

#[RecordForTable(ReplicaPublicKeys::class)]
final class ReplicaPublicKey
{
    use TableRecordTrait;
    use UtilTrait;

    public function __construct(
        public Peer $peer,
        public ReplicaActor $actor,
        public PublicKey $publicKey,
        public bool $trusted,
        public ReplicaLeaf $insertLeaf,
        public ?ReplicaLeaf $revokeLeaf = null,
        public ?string $keyID = null,
        ?int $primaryKey = null,
    ) {
        $this->primaryKey = $primaryKey;
    }
}
