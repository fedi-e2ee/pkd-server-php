<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables\Records;

use FediE2EE\PKD\Crypto\UtilTrait;
use FediE2EE\PKDServer\Meta\RecordForTable;
use FediE2EE\PKDServer\Tables\ReplicaAuxData;
use FediE2EE\PKDServer\Traits\TableRecordTrait;

#[RecordForTable(ReplicaAuxData::class)]
final class ReplicaAuxDatum
{
    use TableRecordTrait;
    use UtilTrait;

    public function __construct(
        public Peer $peer,
        public ReplicaActor $actor,
        public string $auxDataType,
        public string $auxData,
        public bool $trusted,
        public ReplicaLeaf $insertLeaf,
        public ?ReplicaLeaf $revokeLeaf = null,
        ?int $primaryKey = null,
    ) {
        $this->primaryKey = $primaryKey;
    }
}
