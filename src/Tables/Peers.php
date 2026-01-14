<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use DateMalformedStringException;
use DateTimeImmutable;
use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Exceptions\CryptoException;
use FediE2EE\PKD\Crypto\PublicKey;
use FediE2EE\PKD\Crypto\Merkle\{
    IncrementalTree,
    Tree
};
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Exceptions\TableException;
use FediE2EE\PKDServer\Table;
use FediE2EE\PKDServer\Tables\Records\Peer;
use Override;
use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Base64UrlSafe;
use SodiumException;

class Peers extends Table
{
    #[Override]
    public function getCipher(): WrappedEncryptedRow
    {
        return new WrappedEncryptedRow($this->engine, 'pkd_peers');
    }

    #[Override]
    protected function convertKeyMap(AttributeKeyMap $inputMap): array
    {
        return [];
    }

    public function getNextPeerId(): int
    {
        $maxId = $this->db->cell('SELECT MAX(peerid) FROM pkd_peers');
        if (empty($maxId)) {
            return 1;
        }
        return (int) $maxId + 1;
    }

    /**
     * @api
     */
    public function create(
        PublicKey $publicKey,
        string $hostname,
        bool $cosign = false,
        bool $replicate = false
    ): Peer {
        // Get an unused unique id
        do {
            $newUniqueId = Base32::encodeUnpadded(random_bytes(20));
        } while (
            $this->db->exists(
                "SELECT count(peerid) FROM pkd_peers WHERE uniqueid = ?",
                $newUniqueId
            )
        );

        $peer = new Peer(
            $newUniqueId,
            $hostname,
            $publicKey,
            new IncrementalTree(),
            new Tree()->getEncodedRoot(),
            $cosign,
            $replicate,
            new DateTimeImmutable('NOW'),
            new DateTimeImmutable('NOW'),
        );
        if (!$this->save($peer)) {
            throw new TableException('Failed to save peer');
        }
        return $peer;
    }

    /**
     * @api
     *
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws SodiumException
     * @throws TableException
     */
    public function getPeerByUniqueId(string $uniqueId): Peer
    {
        $peer = $this->db->row("SELECT * FROM pkd_peers WHERE uniqueid = ?", $uniqueId);
        if (empty($peer)) {
            throw new TableException('Peer not found: ' . $uniqueId);
        }
        return $this->tableRowToPeer($peer);
    }

    public function getPeer(string $hostname): Peer
    {
        $peer = $this->db->row("SELECT * FROM pkd_peers WHERE hostname = ?", $hostname);
        if (empty($peer)) {
            throw new TableException('Peer not found: ' . $hostname);
        }

        return $this->tableRowToPeer($peer);
    }

    /**
     * @throws DateMalformedStringException
     * @throws CryptoException
     * @throws SodiumException
     */
    protected function tableRowToPeer(array $peer): Peer
    {
        // When we first add a peer, we start with an incremental tree:
        if (empty($peer['incrementaltreestate'])) {
            $tree = new IncrementalTree();
        } else {
            $tree = IncrementalTree::fromJson(
                Base64UrlSafe::decodeNoPadding($peer['incrementaltreestate'])
            );
        }

        return new Peer(
            $peer['uniqueid'],
            $peer['hostname'],
            PublicKey::fromString($peer['publicKey']),
            $tree,
            $peer['latestroot'],
            $peer['cosign'],
            $peer['replicate'],
            new DateTimeImmutable($peer['created']),
            new DateTimeImmutable($peer['modified']),
            $peer['peerid'],
        );
    }

    /**
     * @api
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws SodiumException
     */
    public function listAll(): array
    {
        $peerList = [];
        foreach ($this->db->run("SELECT * FROM pkd_peers") as $peer) {
            $peerList[] = $this->tableRowToPeer($peer);
        }
        return $peerList;
    }

    public function save(Peer $peer): bool
    {
        $this->db->beginTransaction();
        if ($peer->hasPrimaryKey()) {
            $this->db->update('pkd_peers', $peer->toArray(), ['peerid' => $peer->getPrimaryKey()]);
        } else {
            $peer->primaryKey = $this->getNextPeerId();
            $this->db->insert('pkd_peers', $peer->toArray());
        }
        return $this->db->commit();
    }
}
