<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Table;
use Override;

class ReplicaPublicKeys extends Table
{
    #[Override]
    public function getCipher(): WrappedEncryptedRow
    {
        return new WrappedEncryptedRow($this->engine, 'pkd_replica_actors_publickeys');
    }

    #[Override]
    protected function convertKeyMap(AttributeKeyMap $inputMap): array
    {
        return [];
    }
}
