<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables\Records;

use DateTimeImmutable;
use FediE2EE\PKD\Crypto\Merkle\IncrementalTree;
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
        public IncrementalTree $tree,
        public string $latestRoot,
        public DateTimeImmutable $created,
        public DateTimeImmutable $modified,
        ?int $primaryKey = null,
    ) {}

    public function toArray(): array
    {
        return [
            'hostname' =>
                $this->hostname,
            'incrementaltreestate' =>
                Base64UrlSafe::encodeUnpadded($this->tree->toJson()),
            'latestroot' =>
                $this->latestRoot,
            'created' =>
                $this->created->format(DateTimeImmutable::ATOM),
            'modified' =>
                $this->modified->format(DateTimeImmutable::ATOM),
        ];
    }
}
