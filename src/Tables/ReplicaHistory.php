<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Merkle\InclusionProof;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Exceptions\TableException;
use FediE2EE\PKDServer\Table;
use JsonException;
use FediE2EE\PKDServer\Tables\Records\{
    Peer,
    ReplicaLeaf
};
use Override;

use function date;
use function json_decode;

class ReplicaHistory extends Table
{
    #[Override]
    public function getCipher(): WrappedEncryptedRow
    {
        return new WrappedEncryptedRow($this->engine, 'pkd_replica_history');
    }

    #[Override]
    protected function convertKeyMap(AttributeKeyMap $inputMap): array
    {
        return [];
    }

    /**
     * @param array<string, mixed> $apiResponseRecord
     */
    public function createLeaf(
        array $apiResponseRecord,
        string $cosignature,
        InclusionProof $proof,
    ): ReplicaLeaf {
        return new ReplicaLeaf(
            $apiResponseRecord['merkle-root'],
            $apiResponseRecord['publickeyhash'],
            $apiResponseRecord['contenthash'],
            $apiResponseRecord['signature'],
            $apiResponseRecord['encrypted-message'],
            $cosignature,
            $proof,
            $apiResponseRecord['created'] ?? '',
            $apiResponseRecord['replicated'] ?? date('Y-m-d H:i:s'),
        );
    }

    /**
     * @throws JsonException
     * @throws TableException
     */
    public function save(Peer $peer, ReplicaLeaf $leaf): void
    {
        $params = $leaf->toArray();
        $params['peer'] = $peer->getPrimaryKey();
        $this->db->insert(
            'pkd_replica_history',
            $params
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws JsonException
     */
    public function getHistory(int $peerID, int $limit = 100, int $offset = 0): array
    {
        $results = $this->db->run(
            "SELECT *
                FROM pkd_replica_history
                WHERE peer = ?
                ORDER BY replicahistoryid DESC
                LIMIT {$limit} OFFSET {$offset}",
            $peerID
        );
        return $this->formatHistory($results);
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws JsonException
     */
    public function getHistorySince(int $peerID, string $hash, int $limit = 100, int $offset = 0): array
    {
        $afterId = $this->db->cell(
            "SELECT replicahistoryid FROM pkd_replica_history WHERE peer = ? AND root = ?",
            $peerID,
            $hash
        );
        if (empty($afterId)) {
            return [];
        }
        $results = $this->db->run(
            "SELECT *
                FROM pkd_replica_history
                WHERE peer = ? AND replicahistoryid > ?
                ORDER BY replicahistoryid
                LIMIT {$limit} OFFSET {$offset}",
            $peerID,
            $afterId
        );
        return $this->formatHistory($results);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     * @throws JsonException
     */
    protected function formatHistory(array $rows): array
    {
        $return = [];
        foreach ($rows as $row) {
            $return[] = [
                'created' => $row['created'],
                'replicated' => $row['replicated'],
                'encrypted-message' => $row['contents'],
                'contenthash' => $row['contenthash'],
                'publickeyhash' => $row['publickeyhash'],
                'signature' => $row['signature'],
                'merkle-root' => $row['root'],
                'cosignature' => $row['cosignature'],
                'inclusion-proof' => json_decode(
                    (string) ($row['inclusionproof'] ?? '[]'),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                ),
            ];
        }
        return $return;
    }
}
