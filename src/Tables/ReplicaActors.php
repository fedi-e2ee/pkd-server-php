<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Exceptions\CryptoException;
use FediE2EE\PKD\Crypto\PublicKey;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Exceptions\TableException;
use FediE2EE\PKDServer\Protocol\Payload;
use FediE2EE\PKDServer\Table;
use FediE2EE\PKDServer\Tables\Records\{
    Peer,
    ReplicaActor
};
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use Override;
use ParagonIE\CipherSweet\Backend\Key\SymmetricKey as CipherSweetKey;
use ParagonIE\CipherSweet\BlindIndex;
use SodiumException;

use function hash_equals;
use function is_null;

class ReplicaActors extends Table
{
    #[Override]
    public function getCipher(): WrappedEncryptedRow
    {
        return new WrappedEncryptedRow(
            $this->engine,
            'pkd_replica_actors',
            false,
            'replicaactorid'
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
     *
     * @throws TableException
     */
    #[Override]
    protected function convertKeyMap(AttributeKeyMap $inputMap, string $action = 'AddKey'): array
    {
        $actorField = 'actor';
        if ($action === 'MoveIdentity') {
            $actorField = 'new-actor';
        }
        $key = $inputMap->getKey($actorField);
        if (is_null($key)) {
            throw new TableException("Missing required key: $actorField");
        }
        return [
            'activitypubid' => $this->convertKey($key)
        ];
    }

    public function getNextPrimaryKey(): int
    {
        $maxActorId = $this->db->cell("SELECT MAX(replicaactorid) FROM pkd_replica_actors");
        if (!$maxActorId) {
            return 1;
        }
        return (int) ($maxActorId) + 1;
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     */
    public function searchForActor(int $peerID, string $activityPubID): ?ReplicaActor
    {
        $cipher = $this->getCipher();
        $bi = $cipher->getBlindIndex('activitypubid_idx', ['activitypubid' => $activityPubID]);
        $row = $this->db->row(
            "SELECT * FROM pkd_replica_actors WHERE peer = ? AND activitypubid_idx = ?",
            $peerID,
            self::blindIndexValue($bi)
        );
        if (empty($row)) {
            return null;
        }
        $rowArray = self::rowToStringArray($row);
        $decrypted = $cipher->decryptRow($rowArray);
        // Verify it matches because of potential blind index collisions
        $actorId = self::decryptedString($decrypted, 'activitypubid');
        if (!hash_equals($actorId, $activityPubID)) {
            return null;
        }

        $rfc9421 = self::decryptedString($decrypted, 'rfc9421pubkey');
        $pk = empty($rfc9421) ? null : PublicKey::fromString($rfc9421);

        return new ReplicaActor(
            actorID: $actorId,
            rfc9421pk: $pk,
            fireProof: !empty($rowArray['fireproof']),
            primaryKey: self::assertInt($rowArray['replicaactorid'] ?? 0)
        );
    }

    /**
     * @return array{count-aux: int, count-keys: int}
     */
    public function getCounts(int $peerID, int $actorID): array
    {
        $keyCount = $this->db->cell(
            "SELECT COUNT(replicaactorpublickeyid)
                FROM pkd_replica_actors_publickeys
                WHERE peer = ? AND actor = ? AND trusted",
            $peerID,
            $actorID
        );
        $auxDataCount = $this->db->cell(
            "SELECT COUNT(replicaactorauxdataid)
                FROM pkd_replica_actors_auxdata
                WHERE peer = ? AND actor = ? AND trusted",
            $peerID,
            $actorID
        );
        return [
            'count-aux' => (int) ($auxDataCount ?? 0),
            'count-keys' => (int) ($keyCount ?? 0),
        ];
    }

    /**
     * @throws ArrayKeyException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws SodiumException
     * @throws TableException
     */
    public function createForPeer(
        Peer $peer,
        string $activityPubID,
        Payload $payload,
        ?PublicKey $key = null
    ): int {
        $newActorId = $this->getNextPrimaryKey();
        $encryptor = $this->getCipher();
        $plaintext = $encryptor->wrapBeforeEncrypt(
            [
                'replicaactorid' => $newActorId,
                'peer' => $peer->getPrimaryKey(),
                'activitypubid' => $activityPubID,
                'rfc9421pubkey' => is_null($key) ? null : $key->toString(),
            ],
            $this->convertKeyMap($payload->keyMap, $payload->message->getAction())
        );
        [$fields, $blindIndexes] = $encryptor->prepareRowForStorage($plaintext);
        $fields['activitypubid_idx'] = self::blindIndexValue($blindIndexes['activitypubid_idx']);
        $fields['peer'] = $peer->getPrimaryKey();

        $this->db->insert('pkd_replica_actors', $fields);
        return $newActorId;
    }

    /**
     * Create a replica actor without requiring a Payload.
     *
     * Used when replicating from source server where we have decrypted data.
     *
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws SodiumException
     * @throws TableException
     */
    public function createSimpleForPeer(
        Peer $peer,
        string $activityPubID,
        ?PublicKey $key = null
    ): int {
        $newActorId = $this->getNextPrimaryKey();
        $cipher = $this->getCipher();
        $plaintext = [
            'replicaactorid' => $newActorId,
            'peer' => $peer->getPrimaryKey(),
            'activitypubid' => $activityPubID,
            'rfc9421pubkey' => is_null($key) ? null : $key->toString(),
        ];
        [$fields, $blindIndexes] = $cipher->prepareRowForStorage($plaintext);
        $fields['activitypubid_idx'] = self::blindIndexValue($blindIndexes['activitypubid_idx']);
        $fields['peer'] = $peer->getPrimaryKey();

        $this->db->insert('pkd_replica_actors', $fields);
        return $newActorId;
    }
}
