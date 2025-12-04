<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use DateMalformedStringException;
use DateTimeImmutable;
use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Exceptions\{
    BundleException,
    CryptoException,
    NotImplementedException
};
use FediE2EE\PKD\Crypto\Protocol\Actions\{
    AddAuxData,
    RevokeAuxData
};
use FediE2EE\PKD\Crypto\Protocol\Bundle;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\Protocol\Payload;
use FediE2EE\PKDServer\Table;
use FediE2EE\PKDServer\Tables\Records\MerkleLeaf;
use FediE2EE\PKDServer\Traits\ProtocolMethodTrait;
use JsonException;
use Override;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use SodiumException;

class AuxData extends Table
{
    use ProtocolMethodTrait;

    #[Override]
    public function getCipher(): WrappedEncryptedRow
    {
        return new WrappedEncryptedRow(
            $this->engine,
            'pkd_actors_auxdata',
            false,
            'actorauxdataid'
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
    public function getAuxDataForActor(int $actorId): array
    {
        $results = [];
        $query = $this->db->run(
            "SELECT
                ad.actorauxdataid,
                ad.auxdatatype,
                ad.auxdata,
                ad.wrap_auxdata,
                mli.created AS inserttime
            FROM pkd_actors_auxdata ad
            LEFT JOIN pkd_merkle_leaves mli ON mli.merkleleafid = ad.insertleaf
            WHERE
                ad.actorid = ? AND ad.trusted",
            $actorId
        );
        foreach ($query as $row) {
            $insertTime = new DateTimeImmutable((string) $row['inserttime'])->getTimestamp();
            $results[] = [
                'aux-id' => $row['actorauxdataid'],
                'aux-type' => $row['auxdatatype'],
                'created' => (string) $insertTime,
            ];
        }
        return $results;
    }

    /**
     * @api
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws SodiumException
     */
    public function getAuxDataById(int $actorId, string $auxId): array
    {
        $row = $this->db->row(
            "SELECT
                ad.actorauxdataid,
                ad.auxdata,
                ad.wrap_auxdata,
                ad.auxdatatype,
                ad.trusted,
                mli.root AS insertmerkleroot,
                mli.inclusionproof,
                mlr.root AS revokemerkleroot,
                mli.created AS inserttime,
                mlr.created AS revoketime
            FROM pkd_actors_auxdata ad
            LEFT JOIN pkd_merkle_leaves mli ON mli.merkleleafid = ad.insertleaf
            LEFT JOIN pkd_merkle_leaves mlr ON mlr.merkleleafid = ad.revokeleaf
            WHERE
                ad.actorid = ? AND ad.actorauxdataid = ? AND ad.trusted",
            $actorId,
            $auxId
        );
        if (!$row) {
            return [];
        }
        $decrypted = $this->getCipher()->decryptRow($row);
        $insertTime = (string) new DateTimeImmutable((string) $decrypted['inserttime'])->getTimestamp();
        $revokeTime = is_string($decrypted['revoketime'])
            ? (string) new DateTimeImmutable($decrypted['revoketime'])->getTimestamp()
            : null;
        $inclusionProof = json_decode((string) $decrypted['inclusionproof'], true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($inclusionProof)) {
            $inclusionProof = [];
        }

        return [
            'aux-data' => $decrypted['auxdata'],
            'aux-id' => $auxId,
            'aux-type' => $decrypted['auxdatatype'],
            'created' => $insertTime,
            'inclusion-proof' => $inclusionProof,
            'merkle-root' => $decrypted['insertmerkleroot'],
            'revoked' => $revokeTime,
            'revoke-root' => $decrypted['revokemerkleroot'],
        ];
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function addAuxData(Payload $payload): bool
    {
        return $this->protocolMethod(
            $payload,
            'AddAuxData',
            fn (MerkleLeaf $leaf, Payload $payload) => $this->addAuxDataCallback($leaf, $payload)
        );
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws BundleException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    protected function addAuxDataCallback(MerkleLeaf $leaf, Payload $payload): bool
    {
        $publicKeyTable = $this->table('PublicKeys');
        if (!($publicKeyTable instanceof PublicKeys)) {
            throw new TableException('Public Keys table could not be loaded');
        }

        $rawJson = $payload->rawJson;
        $decoded = json_decode($rawJson, true);

        $this->assertRecentMerkleRoot($decoded['recent-merkle-root']);

        $decrypted = $payload->decrypt();
        if (!($decrypted instanceof AddAuxData)) {
            throw new ProtocolException('Invalid message type');
        }
        $array = $decrypted->toArray();
        $sm = Bundle::fromJson($rawJson)->toSignedMessage();

        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        $actor = $actorTable->searchForActor($array['actor']);
        if (is_null($actor)) {
            throw new ProtocolException('Actor not found');
        }

        $candidatePublicKeys = $publicKeyTable->getPublicKeysFor(
            actorName: $actor->actorID,
            keyId: $decoded['key-id'] ?? ''
        );

        $signatureIsValid = false;
        foreach ($candidatePublicKeys as $row) {
            if ($sm->verify($row['public-key'])) {
                $signatureIsValid = true;
                break;
            }
        }
        if (!$signatureIsValid) {
            throw new ProtocolException('Invalid signature');
        }

        // TODO: Allow-list aux-type
        $encryptor = $this->getCipher();
        $maxId = (int) $this->db->cell("SELECT MAX(actorauxdataid) FROM pkd_actors_auxdata");
        $nextId = $maxId + 1;
        $plaintextRow = $encryptor->wrapBeforeEncrypt(
            [
                'actorauxdataid' => $nextId,
                'actorid' => $actor->getPrimaryKey(),
                'auxdatatype' => $array['aux-type'],
                'auxdata' => $array['aux-data'],
                'insertleaf' => $leaf->getPrimaryKey(),
                'trusted' => 1
            ],
            $this->convertKeyMap($payload->keyMap)
        );
        [$rowToInsert, $blindIndexes] = $encryptor->prepareRowForStorage($plaintextRow);
        $rowToInsert['auxdata_idx'] = (string) $blindIndexes['auxdata_idx'];
        $this->db->insert(
            'pkd_actors_auxdata',
            $rowToInsert
        );
        return true;
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function revokeAuxData(Payload $payload): bool
    {
        return $this->protocolMethod(
            $payload,
            'RevokeAuxData',
            fn (MerkleLeaf $leaf, Payload $payload) => $this->revokeAuxDataCallback($leaf, $payload)
        );
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws BundleException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    protected function revokeAuxDataCallback(MerkleLeaf $leaf, Payload $payload): bool
    {
        $publicKeyTable = $this->table('PublicKeys');
        if (!($publicKeyTable instanceof PublicKeys)) {
            throw new TableException('Public Keys table could not be loaded');
        }

        $rawJson = $payload->rawJson;
        $decoded = json_decode($rawJson, true);

        $this->assertRecentMerkleRoot($decoded['recent-merkle-root']);

        $decrypted = $payload->decrypt();
        if (!($decrypted instanceof RevokeAuxData)) {
            throw new ProtocolException('Invalid message type');
        }
        $array = $decrypted->toArray();
        $sm = Bundle::fromJson($rawJson)->toSignedMessage();

        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        $actor = $actorTable->searchForActor($array['actor']);
        if (is_null($actor)) {
            throw new ProtocolException('Actor not found');
        }

        $candidatePublicKeys = $publicKeyTable->getPublicKeysFor(
            actorName: $actor->actorID,
            keyId: $decoded['key-id'] ?? ''
        );

        $signatureIsValid = false;
        foreach ($candidatePublicKeys as $row) {
            if ($sm->verify($row['public-key'])) {
                $signatureIsValid = true;
                break;
            }
        }
        if (!$signatureIsValid) {
            throw new ProtocolException('Invalid signature');
        }

        $bidx = $this->getCipher()->getBlindIndex(
            'auxdata_idx',
            ['auxdata' => $array['aux-data']]
        );

        $toRevoke = $this->db->run(
            "SELECT actorauxdataid, auxdata, wrap_auxdata
                FROM pkd_actors_auxdata
                WHERE actorid = ? AND auxdatatype = ? AND auxdata_idx = ? AND trusted",
            $actor->getPrimaryKey(),
            $array['aux-type'],
            $bidx
        );

        if (empty($toRevoke)) {
            // Nothing to revoke.
            return false;
        }

        $revoked = false;
        foreach ($toRevoke as $row) {
            $decrypted = $this->getCipher()->decryptRow($row);
            if (hash_equals($array['aux-data'], (string) $decrypted['auxdata'])) {
                $this->db->update(
                    'pkd_actors_auxdata',
                    [
                        'trusted' => false,
                        'revokeleaf' => $leaf->getPrimaryKey(),
                    ],
                    ['actorauxdataid' => $row['actorauxdataid']]
                );
                $revoked = true;
            }
        }
        return $revoked;
    }
}
