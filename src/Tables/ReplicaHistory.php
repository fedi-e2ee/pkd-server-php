<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Merkle\InclusionProof;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Table;
use FediE2EE\PKDServer\Tables\Records\Peer;
use FediE2EE\PKDServer\Tables\Records\ReplicaLeaf;
use Override;

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

    public function save(Peer $peer, ReplicaLeaf $leaf): void
    {
        $params = $leaf->toArray();
        $params['peer'] = $peer->getPrimaryKey();
        $this->db->insert(
            'pkd_replica_history',
            $params
        );
    }
}
