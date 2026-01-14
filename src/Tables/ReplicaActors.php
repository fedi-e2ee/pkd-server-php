<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\PublicKey;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Protocol\Payload;
use FediE2EE\PKDServer\Table;
use FediE2EE\PKDServer\Tables\Records\Peer;
use Override;
use ParagonIE\CipherSweet\Backend\Key\SymmetricKey as CipherSweetKey;
use ParagonIE\CipherSweet\BlindIndex;

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
        $maxActorId = $this->db->cell("SELECT MAX(actorid) FROM pkd_replica_actors");
        if (!$maxActorId) {
            return 1;
        }
        return (int) ($maxActorId) + 1;
    }

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
                'activitypubid' => $activityPubID,
                'rfc9421pubkey' => is_null($key) ? null : $key->toString(),
            ],
            $this->convertKeyMap($payload->keyMap, $payload->message->getAction())
        );
        [$fields, $blindIndexes] = $encryptor->prepareRowForStorage($plaintext);
        $fields['activitypubid_idx'] = (string) $blindIndexes['activitypubid_idx'];

        $this->db->insert('pkd_replica_actors', $fields);
        return $newActorId;
    }
}
