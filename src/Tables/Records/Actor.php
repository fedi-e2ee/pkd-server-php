<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables\Records;

use FediE2EE\PKD\Crypto\PublicKey;
use FediE2EE\PKDServer\Meta\RecordForTable;
use FediE2EE\PKDServer\Traits\TableRecordTrait;
use FediE2EE\PKDServer\Tables\Actors;

/**
 * Abstraction for a row in the Actors table
 */
#[RecordForTable(Actors::class)]
final class Actor
{
    use TableRecordTrait;

    /**
     * @param string $actorID           ActivityPub Actor identifier (string)
     * @param PublicKey|null $rfc9421pk RFC 9421 (HTTP Message Signatures) public key
     * @param bool $fireProof           Cannot be burned down
     * @param int|null $primaryKey      Database ID
     */
    public function __construct(
        public string $actorID,
        public ?PublicKey $rfc9421pk = null,
        public bool $fireProof = false,
        ?int $primaryKey = null,
    ) {
        $this->primaryKey = $primaryKey;
    }

    /**
     * Instantiate a new object without a primary key
     */
    public static function create(
        string $actorID,
        string $rfc9421pk = '',
        bool $fireProof = false
    ): self {
        return new self(
            actorID: $actorID,
            rfc9421pk: empty($rfc9421pk) ? null : PublicKey::fromString($rfc9421pk),
            fireProof: $fireProof
        );
    }

    public function toArray(): array
    {
        return [
            'activitypubid' => $this->actorID,
            'rfc9421pubkey' => $this->rfc9421pk->toString(),
            'fireproof' => $this->fireProof,
        ];
    }
}
