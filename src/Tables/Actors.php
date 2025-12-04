<?php
namespace FediE2EE\PKDServer\Tables;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\PublicKey;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Exceptions\{
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\Protocol\Payload;
use FediE2EE\PKDServer\Table;
use FediE2EE\PKDServer\Tables\Records\Actor;
use Override;
use ParagonIE\CipherSweet\Backend\Key\SymmetricKey as CipherSweetKey;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use SodiumException;

class Actors extends Table
{
    /** @var array<int, Actor>  */
    private array $idCache = [];

    #[Override]
    public function getCipher(): WrappedEncryptedRow
    {
        return new WrappedEncryptedRow(
            $this->engine,
            'pkd_actors',
            false,
            'actorid'
        )
            ->addTextField('activitypubid', '', 'wrap_activitypubid')
            ->addBlindIndex(
                'activitypubid',
                new BlindIndex('activitypubid_idx', [], 16, true)
            );
    }

    /**
     * @param AttributeKeyMap $inputMap
     * @param string $action
     * @return array<string, CipherSweetKey>
     */
    #[Override]
    protected function convertKeyMap(AttributeKeyMap $inputMap, string $action = 'AddKey'): array
    {
        $actorField = 'actor';
        if ($action === 'MoveIdentity') {
            $actorField = 'new-actor';
        }
        return [
            'activitypubid' =>
                $this->convertKey($inputMap->getKey($actorField))
        ];
    }

    public function getNextPrimaryKey(): int
    {
        $maxActorId = $this->db->cell("SELECT MAX(actorid) FROM pkd_actors");
        if (!$maxActorId) {
            return 1;
        }
        return (int) ($maxActorId) + 1;
    }

    /**
     * When you already have a database ID, just fetch the object.
     *
     * @api
     *
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     * @throws TableException
     */
    public function getActorByID(int $actorID): Actor
    {
        if (array_key_exists($actorID, $this->idCache)) {
            if ($this->idCache[$actorID]->getPrimaryKey() === $actorID) {
                return $this->idCache[$actorID];
            } else {
                unset($this->idCache[$actorID]);
            }
        }
        $cipher = $this->getCipher();
        $encryptedRow = $this->db->row(
            "SELECT 
                    actorid, activitypubid, wrap_activitypubid, fireproof, rfc9421pubkey
                FROM 
                    pkd_actors
                WHERE 
                    actorid = ?",
            $actorID
        );
        $row = $cipher->decryptRow($encryptedRow);
        $canonicalActorID = (string) $row['activitypubid'];
        $pk = empty($row['rfc9421pubkey'])
            ? null
            : PublicKey::fromString((string) $row['rfc9421pubkey']);
        $actor = new Actor(
            actorID: $canonicalActorID,
            rfc9421pk: $pk,
            fireProof: !empty($row['fireproof']),
            primaryKey: (int) $row['actorid'],
        );
        // Store in cache to save on future queries
        $this->recordCache[$canonicalActorID] = $actor;
        $actorID = (int) $row['actorid'];
        $this->idCache[$actorID] = $actor;
        return $actor;
    }


    public function getCounts(int $actorID): array
    {
        $keyCount = $this->db->cell(
            "SELECT COUNT(actorpublickeyid) FROM pkd_actors_publickeys WHERE actorid = ? AND trusted",
            $actorID
        );
        $auxDataCount = $this->db->cell(
            "SELECT COUNT(actorauxdataid) FROM pkd_actors_auxdata WHERE actorid = ? AND trusted",
            $actorID
        );
        return [
            'count-aux' => $auxDataCount,
            'count-keys' => $keyCount,
        ];
    }

    /**
     * When you only have an ActivityPub Actor ID, first canonicalize it, then fetch the Actor object
     * from the database based on that value. May return NULL, which indicates no records found.
     *
     * @api
     *
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     */
    public function searchForActor(string $canonicalActorID): ?Actor
    {
        if (array_key_exists($canonicalActorID, $this->recordCache)) {
            return $this->recordCache[$canonicalActorID];
        }
        $cipher = $this->getCipher();
        $bi = $cipher->getBlindIndex('activitypubid_idx', ['activitypubid' => $canonicalActorID]);
        $results = $this->db->run(
            "SELECT 
                    actorid, activitypubid, wrap_activitypubid, fireproof, rfc9421pubkey
                FROM 
                    pkd_actors
                WHERE 
                    activitypubid_idx = ?",
            $bi
        );
        /** @var array<string, string> $encryptedRow */
        foreach ($results as $encryptedRow) {
            /** @var array<string, scalar> $row */
            $row = $cipher->decryptRow($encryptedRow);
            if (hash_equals((string) $row['activitypubid'], $canonicalActorID)) {
                $pk = empty($row['rfc9421pubkey'])
                    ? null
                    : PublicKey::fromString($row['rfc9421pubkey']);
                $actor = new Actor(
                    actorID: (string) $row['activitypubid'],
                    rfc9421pk: $pk,
                    fireProof: !empty($row['fireproof']),
                    primaryKey: (int) $row['actorid'],
                );
                // Store in cache to save on future queries
                $this->recordCache[$canonicalActorID] = $actor;
                $actorID = (int) $row['actorid'];
                $this->idCache[$actorID] = $actor;
                return $actor;
            }
        }
        return null;
    }

    /**
     * @throws ArrayKeyException
     * @throws CryptoOperationException
     * @throws CipherSweetException
     * @throws SodiumException
     * @throws ProtocolException
     */
    public function createActor(string $activityPubID, Payload $payload, ?PublicKey $key = null): int
    {
        $newActorId = $this->getNextPrimaryKey();
        $encryptor = $this->getCipher();
        $plaintext = $encryptor->wrapBeforeEncrypt(
            [
                'actorid' => $newActorId,
                'activitypubid' => $activityPubID,
                'rfc9421pubkey' => is_null($key) ? null : $key->toString(),
            ],
            $this->convertKeyMap($payload->keyMap, $payload->message->getAction())
        );
        [$fields, $blindIndexes] = $encryptor->prepareRowForStorage($plaintext);
        $fields['activitypubid_idx'] = $blindIndexes['activitypubid_idx'];
        /** @var array<string, scalar|null> $fields */
        $inserted = $this->db->insert('pkd_actors', $fields);
        if (!$inserted) {
            throw new ProtocolException('Could not insert actor');
        }
        return $newActorId;
    }

    /**
     * @throws TableException
     */
    public function clearCacheForActor(Actor $actor): void
    {
        unset($this->recordCache[$actor->actorID]);
        if ($actor->getPrimaryKey()) {
            unset($this->idCache[$actor->getPrimaryKey()]);
        }
    }
}
