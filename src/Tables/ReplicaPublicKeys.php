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
use Override;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\Exception\{
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use ParagonIE\EasyDB\EasyStatement;
use SodiumException;

use function is_array;
use function is_null;
use function is_string;
use function json_decode;

class ReplicaPublicKeys extends Table
{
    #[Override]
    public function getCipher(): WrappedEncryptedRow
    {
        return new WrappedEncryptedRow(
            $this->engine,
            'pkd_replica_actors_publickeys',
            false,
            'replicaactorpublickeyid'
        )
            ->addTextField('publickey')
            ->addBlindIndex('publickey', new BlindIndex('publickey_idx', [], 16, true))
        ;
    }

    /**
     * @throws TableException
     */
    #[Override]
    protected function convertKeyMap(AttributeKeyMap $inputMap): array
    {
        $key = $inputMap->getKey('public-key');
        if (is_null($key)) {
            throw new TableException('Missing required key: public-key');
        }
        return [
            'publickey' => $this->convertKey($key),
        ];
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
    public function lookup(int $peerID, int $actorID, string $keyID): array
    {
        $row = $this->db->row(
            "SELECT
                pk.replicaactorpublickeyid,
                pk.publickey,
                pk.wrap_publickey,
                pk.key_id,
                pk.trusted,
                mli.root AS insertmerkleroot,
                mli.inclusionproof,
                mlr.root AS revokemerkleroot,
                mli.created AS inserttime,
                mlr.created AS revoketime
            FROM pkd_replica_actors_publickeys pk
            LEFT JOIN pkd_replica_history mli ON mli.replicahistoryid = pk.insertleaf
            LEFT JOIN pkd_replica_history mlr ON mlr.replicahistoryid = pk.revokeleaf
            WHERE
                pk.peer = ? AND pk.actor = ? AND pk.key_id = ?",
            $peerID,
            $actorID,
            $keyID
        );
        if (empty($row)) {
            return [];
        }
        $decrypted = $this->getCipher()->decryptRow(self::rowToStringArray($row));
        $insertTimeVal = $decrypted['inserttime'] ?? 'now';
        $insertTime = (string) (
            new DateTimeImmutable(is_string($insertTimeVal) ? $insertTimeVal : 'now')->getTimestamp()
        );
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
            'merkle-root' => $decrypted['insertmerkleroot'] ?? '',
            'public-key' => $decrypted['publickey'],
            'revoke-root' => $decrypted['revokemerkleroot'] ?? null,
            'created' => $insertTime,
            'revoked' => $revokeTime,
            'inclusion-proof' => $inclusionProof,
            'key-id' => $keyID,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws SodiumException
     */
    public function getPublicKeysFor(int $peerID, int $actorID, string $keyId = ''): array
    {
        $stmt = EasyStatement::open()
            ->with("pk.peer = ?", $peerID)
            ->andWith("pk.actor = ?", $actorID);
        if ($keyId) {
            $stmt->andWith("pk.key_id = ?", $keyId);
        }

        $results = [];
        foreach ($this->db->run(
            "SELECT
                    pk.replicaactorpublickeyid,
                    pk.publickey,
                    pk.wrap_publickey,
                    pk.key_id,
                    pk.trusted,
                    mli.root AS insertmerkleroot,
                    mli.inclusionproof,
                    mli.created AS inserttime
                FROM pkd_replica_actors_publickeys pk
                LEFT JOIN pkd_replica_history mli ON mli.replicahistoryid = pk.insertleaf
                WHERE {$stmt}",
            ...$stmt->values()
        ) as $row) {
            $rowArray = self::rowToStringArray($row);
            $decrypt = $this->getCipher()->decryptRow($rowArray);
            $insertTimeVal = $decrypt['inserttime'] ?? 'now';
            $insertTime = (string) new DateTimeImmutable(is_string($insertTimeVal) ? $insertTimeVal : 'now')->getTimestamp();
            $inclusionVal = $decrypt['inclusionproof'] ?? '[]';
            $inclusionProof = json_decode(
                is_string($inclusionVal) ? $inclusionVal : '[]',
                true,
                512,
                JSON_THROW_ON_ERROR
            );
            if (!is_array($inclusionProof)) {
                $inclusionProof = [];
            }

            $results[] = [
                'public-key' => self::decryptedString($decrypt, 'publickey'),
                'key-id' => $rowArray['key_id'] ?? '',
                'created' => $insertTime,
                'merkle-root' => $decrypt['insertmerkleroot'] ?? '',
                'inclusion-proof' => $inclusionProof,
                'trusted' => !empty($rowArray['trusted'])
            ];
        }
        return $results;
    }
}
