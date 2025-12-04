<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables\Records;

use FediE2EE\PKDServer\Meta\RecordForTable;
use FediE2EE\PKDServer\Traits\TableRecordTrait;
use FediE2EE\PKDServer\Tables\AuxData;

/**
 * Abstraction for a row in the AuxData table
 */
#[RecordForTable(AuxData::class)]
class AuxDatum
{
    use TableRecordTrait;

    public function __construct(
        public Actor $actor,
        public string $auxDataType,
        public string $auxData,
        public bool $trusted,
        public MerkleLeaf $insertLeaf,
        public ?MerkleLeaf $revokeLeaf = null,
        ?int $primaryKey = null,
    ) {
        $this->primaryKey = $primaryKey;
    }

    public function getActor(): Actor
    {
        return $this->actor;
    }
}
