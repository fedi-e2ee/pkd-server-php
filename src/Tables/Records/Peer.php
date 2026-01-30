<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables\Records;

use DateTimeImmutable;
use JsonException;
use FediE2EE\PKD\Crypto\{
    Merkle\IncrementalTree,
    PublicKey,
    UtilTrait
};
use FediE2EE\PKDServer\Meta\RecordForTable;
use FediE2EE\PKDServer\Protocol\RewrapConfig;
use FediE2EE\PKDServer\Tables\Peers;
use FediE2EE\PKDServer\Traits\TableRecordTrait;
use ParagonIE\ConstantTime\Base64UrlSafe;

use function is_null;

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
        public ?RewrapConfig $wrapConfig = null,
        ?int $primaryKey = null,
    ) {
        $this->primaryKey = $primaryKey;
    }

    /**
     * @throws JsonException
     */
    public function toArray(): array
    {
        return [
            'uniqueid' =>
                $this->uniqueId,
            'hostname' =>
                $this->hostname,
            'publickey' =>
                $this->publicKey->toString(),
            'incrementaltreestate' =>
                Base64UrlSafe::encodeUnpadded($this->tree->toJson()),
            'latestroot' =>
                $this->latestRoot,
            'rewrap' =>
                is_null($this->wrapConfig)
                    ? null
                    : self::jsonEncode($this->wrapConfig),
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
