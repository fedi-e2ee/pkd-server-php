<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKDServer\ActivityPub\ActivityStream;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Exceptions\ActivityPubException;
use FediE2EE\PKDServer\Table;
use Override;

class ActivityStreamQueue extends Table
{
    #[Override]
    public function getCipher(): WrappedEncryptedRow
    {
        return new WrappedEncryptedRow(
            $this->engine,
            'pkd_activitystream_queue',
            false,
            'queueid'
        );
    }

    #[Override]
    protected function convertKeyMap(AttributeKeyMap $inputMap): array
    {
        return [];
    }

    public function getNextPrimaryKey(): int
    {
        $maxId = $this->db->cell("SELECT MAX(queueid) FROM pkd_activitystream_queue");
        if (!$maxId) {
            return 1;
        }
        return (int) ($maxId) + 1;
    }

    /**
     * @throws ActivityPubException
     */
    public function insert(ActivityStream $as): int
    {
        $this->db->beginTransaction();
        $nextPrimaryKey = $this->getNextPrimaryKey();
        $this->db->insert(
            'pkd_activitystream_queue',
            [
                'queueid' => $nextPrimaryKey,
                'message' => (string) $as,
                'processed' => false,
                'successful' => false,
            ]
        );
        if (!$this->db->commit()) {
            $this->db->rollBack();
            throw new ActivityPubException('A database error occurred.');
        }
        return $nextPrimaryKey;
    }
}
