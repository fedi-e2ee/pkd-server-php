<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables\Records;

use FediE2EE\PKD\Crypto\{
    PublicKey,
    UtilTrait
};
use FediE2EE\PKDServer\Meta\RecordForTable;
use FediE2EE\PKDServer\Traits\TableRecordTrait;

#[RecordForTable(ReplicaActor::class)]
final class ReplicaActor
{
    use TableRecordTrait;
    use UtilTrait;

    public function __construct(
        public string $actorID,
        public ?PublicKey $rfc9421pk = null,
        public bool $fireProof = false,
        ?int $primaryKey = null,
    ) {
        $this->primaryKey = $primaryKey;
    }

    public function toArray(): array
    {
        return [
            'activitypubid' => $this->actorID,
            'rfc9421pubkey' => $this->rfc9421pk,
            'fireproof' => $this->fireProof,
        ];
    }
}
