<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use DateMalformedStringException;
use DateTimeImmutable;
use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Table;
use JsonException;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\Exception\{
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use Override;
use SodiumException;

class ReplicaAuxData extends Table
{
    #[Override]
    public function getCipher(): WrappedEncryptedRow
    {
        return new WrappedEncryptedRow(
            $this->engine,
            'pkd_replica_actors_auxdata',
            false,
            'replicaactorauxdataid'
        )
            ->addTextField('auxdata')
            ->addBlindIndex('auxdata', new BlindIndex('auxdata_idx', [], 16, true))
        ;
    }

    #[Override]
    protected function convertKeyMap(AttributeKeyMap $inputMap): array
    {
        return [
            'auxdata' => $this->convertKey(
                $inputMap->getKey('aux-data')
            ),
        ];
    }

    /**
     * @throws DateMalformedStringException
     */
    public function getAuxDataForActor(int $peerID, int $actorID): array
    {
        $results = [];
        $query = $this->db->run(
            "SELECT
                ad.replicaactorauxdataid,
                ad.auxdatatype,
                ad.auxdata,
                ad.wrap_auxdata,
                mli.created AS inserttime
            FROM pkd_replica_actors_auxdata ad
            LEFT JOIN pkd_replica_history mli ON mli.replicahistoryid = ad.insertleaf
            WHERE
                ad.peer = ? AND ad.actor = ? AND ad.trusted",
            $peerID,
            $actorID
        );
        foreach ($query as $row) {
            $insertTime = (string) new DateTimeImmutable($row['inserttime'] ?? 'now')->getTimestamp();
            $results[] = [
                'aux-id' => $row['replicaactorauxdataid'],
                'aux-type' => $row['auxdatatype'],
                'created' => $insertTime,
            ];
        }
        return $results;
    }

    /**
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws SodiumException
     */
    public function getAuxDataById(int $peerID, int $actorID, string $auxId): array
    {
        $row = $this->db->row(
            "SELECT
                ad.replicaactorauxdataid,
                ad.auxdata,
                ad.wrap_auxdata,
                ad.auxdatatype,
                ad.trusted,
                mli.root AS insertmerkleroot,
                mli.inclusionproof,
                mlr.root AS revokemerkleroot,
                mli.created AS inserttime,
                mlr.created AS revoketime
            FROM pkd_replica_actors_auxdata ad
            LEFT JOIN pkd_replica_history mli ON mli.replicahistoryid = ad.insertleaf
            LEFT JOIN pkd_replica_history mlr ON mlr.replicahistoryid = ad.revokeleaf
            WHERE
                ad.peer = ? AND ad.actor = ? AND ad.replicaactorauxdataid = ? AND ad.trusted",
            $peerID,
            $actorID,
            $auxId
        );
        if (!$row) {
            return [];
        }
        $decrypted = $this->getCipher()->decryptRow($row);
        $insertTime = (string) new DateTimeImmutable((string) $decrypted['inserttime'] ?? 'now')->getTimestamp();
        $revokeTime = is_string($decrypted['revoketime'] ?? null)
            ? (string) new DateTimeImmutable((string) $decrypted['revoketime'])->getTimestamp()
            : null;
        $inclusionProof = json_decode(
            (string) ($decrypted['inclusionproof'] ?? '[]'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        if (!is_array($inclusionProof)) {
            $inclusionProof = [];
        }

        return [
            'aux-data' => $decrypted['auxdata'],
            'aux-id' => $auxId,
            'aux-type' => $decrypted['auxdatatype'],
            'created' => $insertTime,
            'inclusion-proof' => $inclusionProof,
            'merkle-root' => $decrypted['insertmerkleroot'] ?? '',
            'revoked' => $revokeTime,
            'revoke-root' => $decrypted['revokemerkleroot'] ?? null,
        ];
    }
}
