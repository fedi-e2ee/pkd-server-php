<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Table;
use ParagonIE\CipherSweet\BlindIndex;
use Override;

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
}
