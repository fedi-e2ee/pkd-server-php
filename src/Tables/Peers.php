<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use DateMalformedStringException;
use DateTimeImmutable;
use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Exceptions\CryptoException;
use FediE2EE\PKD\Crypto\Protocol\HPKEAdapter;
use FediE2EE\PKD\Crypto\PublicKey;
use FediE2EE\PKD\Crypto\Merkle\{
    IncrementalTree,
    Tree
};
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Exceptions\{
    DependencyException,
    TableException
};
use FediE2EE\PKDServer\Protocol\RewrapConfig;
use FediE2EE\PKDServer\Table;
use FediE2EE\PKDServer\Tables\Records\Peer;
use Override;
use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\HPKE\HPKEException;
use Random\RandomException;
use SodiumException;
use function is_null, random_bytes;

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
     *
     * @throws TableException
     * @throws RandomException
     */
    public function create(
        PublicKey $publicKey,
        string $hostname,
        bool $cosign = false,
        bool $replicate = false,
        ?RewrapConfig $rewrapConfig = null,
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
            $hostname,
            $newUniqueId,
            $publicKey,
            new IncrementalTree(),
            new Tree()->getEncodedRoot(),
            $cosign,
            $replicate,
            new DateTimeImmutable('NOW'),
            new DateTimeImmutable('NOW'),
            $rewrapConfig,
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
        if (is_null($peer['publickey'])) {
            throw new TableException('Peer has no public key');
        }
        return $this->tableRowToPeer($peer);
    }

    /**
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws SodiumException
     * @throws TableException
     */
    public function getPeer(string $hostname): Peer
    {
        $peer = $this->db->row("SELECT * FROM pkd_peers WHERE hostname = ?", $hostname);
        if (empty($peer)) {
            throw new TableException('Peer not found: ' . $hostname);
        }
        if (is_null($peer['publickey'])) {
            throw new TableException('Peer has no public key');
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
            $peer['hostname'],
            $peer['uniqueid'],
            PublicKey::fromString($peer['publickey']),
            $tree,
            $peer['latestroot'],
            (bool) $peer['cosign'],
            (bool) $peer['replicate'],
            new DateTimeImmutable($peer['created']),
            new DateTimeImmutable($peer['modified']),
            is_null($peer['rewrap']) ? null : RewrapConfig::fromJson($peer['rewrap']),
            (int) $peer['peerid'],
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
            if (!is_null($peer['publickey'])) {
                $peerList[] = $this->tableRowToPeer($peer);
            }
        }
        return $peerList;
    }

    /**
     * Lists which peers we replicate.
     *
     * @return Peer[]
     *
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws SodiumException
     */
    public function listReplicatingPeers(): array
    {
        $peerList = [];
        foreach ($this->db->run("SELECT * FROM pkd_peers WHERE replicate") as $peer) {
            if (!is_null($peer['publickey'])) {
                $peerList[] = $this->tableRowToPeer($peer);
            }
        }
        return $peerList;
    }

    /**
     * @throws TableException
     */
    public function save(Peer $peer): bool
    {
        if ($peer->hasPrimaryKey()) {
            $this->db->update('pkd_peers', $peer->toArray(), ['peerid' => $peer->getPrimaryKey()]);
        } else {
            $peer->primaryKey = $this->getNextPeerId();
            $this->db->insert('pkd_peers', $peer->toArray());
        }
        return true;
    }

    /**
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws SodiumException
     */
    public function getRewrapCandidates(): array
    {
        $rows = $this->db->run(
            "SELECT
                    *
                FROM 
                    pkd_peers 
                WHERE 
                    replicate 
                    AND rewrap IS NOT NULL"
        );
        $peers = [];
        foreach ($rows as $row) {
            if (!is_null($row['publickey'])) {
                $peers[] = $this->tableRowToPeer($row);
            }
        }
        return $peers;
    }

    /**
     * @throws DependencyException
     * @throws HPKEException
     * @throws TableException
     */
    public function rewrapKeyMap(Peer $peer, AttributeKeyMap $keyMap, int $leafId): void
    {
        if (is_null($peer->wrapConfig)) {
            return;
        }
        $cs = $peer->wrapConfig->getCipherSuite();
        $encapsKey = $peer->wrapConfig->getEncapsKey();
        $adapter = (new HPKEAdapter($cs));
        foreach ($keyMap->getAttributes() as $attr) {
            // Are we replacing or inserting?
            $exists = $this->db->exists(
                "SELECT count(rewrappedkeyid) 
                    FROM pkd_merkle_leaf_rewrapped_keys
                    WHERE peer = ? AND leaf = ? AND pkdattrname = ?",
                $peer->getPrimaryKey(),
                $leafId,
                $attr
            );
            $ciphertext = $adapter->seal($encapsKey, $keyMap->getKey($attr)->getBytes());
            if ($exists) {
                $this->db->update(
                    'pkd_merkle_leaf_rewrapped_keys',
                    [
                        'rewrapped' => $ciphertext,
                    ],
                    [
                        'peer' => $peer->getPrimaryKey(),
                        'leaf' => $leafId,
                        'pkdattrname' => $attr,
                    ]
                );
            } else {
                $this->db->insert(
                    'pkd_merkle_leaf_rewrapped_keys',
                    [
                        'peer' => $peer->getPrimaryKey(),
                        'leaf' => $leafId,
                        'pkdattrname' => $attr,
                        'rewrapped' => $ciphertext,
                    ]
                );
            }
        }
    }
}
