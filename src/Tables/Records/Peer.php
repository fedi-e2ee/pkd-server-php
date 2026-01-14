<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables\Records;

use DateTimeImmutable;
use FediE2EE\PKD\Crypto\Merkle\IncrementalTree;
use FediE2EE\PKD\Crypto\PublicKey;
use FediE2EE\PKD\Crypto\UtilTrait;
use FediE2EE\PKDServer\Meta\RecordForTable;
use FediE2EE\PKDServer\Tables\Peers;
use FediE2EE\PKDServer\Traits\TableRecordTrait;
use ParagonIE\ConstantTime\Base64UrlSafe;

#[RecordForTable(Peers::class)]
class Peer
{
    use TableRecordTrait;
    use UtilTrait;

    public function __construct(
        public string $hostname,
        public string $uniqueId,
        public PublicKey $publicKey,
        public IncrementalTree $tree,
        public string $latestRoot,
        public bool $cosign,
        public bool $replicate,
        public DateTimeImmutable $created,
        public DateTimeImmutable $modified,
        ?int $primaryKey = null,
    ) {
        $this->primaryKey = $primaryKey;
    }

    public function toArray(): array
    {
        return [
            'uniqueid' =>
                $this->uniqueId,
            'hostname' =>
                $this->hostname,
            'publicKey' =>
                $this->publicKey->toString(),
            'incrementaltreestate' =>
                Base64UrlSafe::encodeUnpadded($this->tree->toJson()),
            'latestroot' =>
                $this->latestRoot,
            'cosign' =>
                $this->cosign,
            'replicate' =>
                $this->replicate,
            'created' =>
                $this->created->format(DateTimeImmutable::ATOM),
            'modified' =>
                $this->modified->format(DateTimeImmutable::ATOM),
        ];
    }
}
