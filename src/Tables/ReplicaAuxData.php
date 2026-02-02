<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use DateMalformedStringException;
use DateTimeImmutable;
use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Exceptions\TableException;
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

use function is_array;
use function is_null;
use function is_string;
use function json_decode;

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

    /**
     * @throws TableException
     */
    #[Override]
    protected function convertKeyMap(AttributeKeyMap $inputMap): array
    {
        $key = $inputMap->getKey('aux-data');
        if (is_null($key)) {
            throw new TableException('Missing required key: aux-data');
        }
        return [
            'auxdata' => $this->convertKey($key),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
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
     * @return array<string, mixed>
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
        $decrypted = $this->getCipher()->decryptRow(self::rowToStringArray($row));
        $insertTimeVal = $decrypted['inserttime'] ?? 'now';
        $insertTime = (string) new DateTimeImmutable(
            is_string($insertTimeVal) ? $insertTimeVal : 'now'
        )->getTimestamp();
        $revokeTimeVal = $decrypted['revoketime'] ?? null;
        $revokeTime = is_string($revokeTimeVal) && !empty($revokeTimeVal)
            ? (string) new DateTimeImmutable($revokeTimeVal)->getTimestamp()
            : null;
        $inclusionVal = $decrypted['inclusionproof'] ?? '[]';
        $inclusionProof = json_decode(
            is_string($inclusionVal) ? $inclusionVal : '[]',
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
