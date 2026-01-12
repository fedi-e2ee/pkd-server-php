<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use FediE2EE\PKD\Crypto\Exceptions\{
    BundleException,
    CryptoException,
    JsonException,
    NetworkException,
    NotImplementedException
};
use FediE2EE\PKD\Crypto\Protocol\{
    Actions\AddKey,
    Actions\BurnDown,
    Actions\Checkpoint,
    Actions\Fireproof,
    Actions\MoveIdentity,
    Actions\RevokeKey,
    Actions\RevokeKeyThirdParty,
    Actions\UndoFireproof,
    Bundle,
};
use DateMalformedStringException;
use DateTimeImmutable;
use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\PublicKey;
use FediE2EE\PKD\Crypto\Revocation;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\Protocol\Payload;
use FediE2EE\PKDServer\Table;
use FediE2EE\PKDServer\Tables\Records\{
    ActorKey,
    MerkleLeaf
};
use GuzzleHttp\Exception\GuzzleException;
use FediE2EE\PKDServer\Traits\{
    ProtocolMethodTrait,
    TOTPTrait
};
use JsonException as BaseJsonException;
use Override;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\EasyDB\EasyStatement;
use Random\RandomException;
use SodiumException;
use TypeError;

class PublicKeys extends Table
{
    use ProtocolMethodTrait;
    use TOTPTrait;

    #[Override]
    public function getCipher(): WrappedEncryptedRow
    {
        return new WrappedEncryptedRow(
            $this->engine,
            'pkd_actors_publickeys',
            false,
            'actorpublickeyid'
        )
            ->addTextField('publickey')
            ->addBlindIndex('publickey', new BlindIndex('publickey_idx', [], 16, true))
        ;
    }

    /**
     * @throws RandomException
     */
    public function generateKeyID(): string
    {
        return Base64UrlSafe::encodeUnpadded(random_bytes(32));
    }

    #[Override]
    protected function convertKeyMap(AttributeKeyMap $inputMap): array
    {
        return [
            'publickey' =>
                $this->convertKey($inputMap->getKey('public-key')),
        ];
    }

    /**
     * @param int $actorPrimaryKey
     * @param string $keyID
     * @return array
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     * @throws DateMalformedStringException
     * @throws BaseJsonException
     */
    public function lookup(int $actorPrimaryKey, string $keyID): array
    {
        $row = $this->db->row(
            "SELECT 
                pk.actorpublickeyid,
                pk.publickey,
                pk.wrap_publickey,
                mli.root AS insertmerkleroot,
                mli.inclusionproof,
                mlr.root AS revokemerkleroot,
                mli.created AS inserttime,
                mlr.created AS revoketime
            FROM pkd_actors_publickeys pk
            LEFT JOIN pkd_merkle_leaves mli ON mli.merkleleafid = pk.insertleaf
            LEFT JOIN pkd_merkle_leaves mlr ON mlr.merkleleafid = pk.revokeleaf
            WHERE
                pk.actorid = ? AND pk.key_id = ?",
            $actorPrimaryKey,
            $keyID
        );
        if (empty($row)) {
            return [];
        }
        $decrypted = $this->getCipher()->decryptRow($row);
        $insertTime = (string) new DateTimeImmutable((string) $decrypted['inserttime'])->getTimestamp();
        $revokeTime = is_string($decrypted['revoketime'])
            ? (string) new DateTimeImmutable($decrypted['revoketime'])->getTimestamp()
            : null;
        $inclusionProof = json_decode(
            (string) $decrypted['inclusionproof'],
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        if (!is_array($inclusionProof)) {
            $inclusionProof = [];
        }

        return [
            'merkle-root' => $decrypted['insertmerkleroot'],
            'public-key' => $decrypted['publickey'],
            'revoked-root' => $decrypted['revokemerkleroot'],
            'created' => $insertTime,
            'revoked' => $revokeTime,
            'inclusion-proof' => $inclusionProof,
            'key-id' => $keyID,
        ];
    }

    /**
     * @param int $primaryKey
     * @return ActorKey
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     * @throws TableException
     */
    public function getRecord(int $primaryKey): ActorKey
    {
        $row = $this->db->row(
            "SELECT * FROM pkd_actors_publickeys WHERE actorpublickeyid = ?",
            $primaryKey
        );
        if (empty($row)) {
            throw new TableException('Actor not found: ' . $primaryKey);
        }
        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        /** @var MerkleState $merkleTable */
        $merkleTable = $this->table('MerkleState');

        $actor = $actorTable->getActorByID($row['actorid']);
        $insertLeaf = $merkleTable->getLeafByID($row['insertleaf']);
        $revokeLeaf = is_null($row['revokeleaf']) ? null : $merkleTable->getLeafByID($row['revokeleaf']);
        $decrypted = $this->getCipher()->decryptRow($row);

        return new ActorKey(
            actor: $actor,
            publicKey: PublicKey::fromString((string) $decrypted['publickey']),
            trusted: !empty($row['trusted']),
            insertLeaf: $insertLeaf,
            revokeLeaf: $revokeLeaf,
            keyID: $row['key_id'],
        );
    }

    /**
     * @throws BaseJsonException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     * @throws TableException
     */
    public function getPublicKeysFor(string $actorName, string $keyId = ''): array
    {
        $results = [];
        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        if (!($actorTable instanceof Actors)) {
            throw new TableException('Actor table not found');
        }
        $actor = $actorTable->searchForActor($actorName);
        if (!$actor) {
            return [];
        }

        /**
         * KeyID is an optional parameter. Because I'm feeling lazy, I'm using EasyStatement to build the query.
         * This saves us from having to write redundant SQL queries in different conditional branches.
         *
         * @link https://github.com/paragonie/easydb?tab=readme-ov-file#generate-dynamic-query-conditions
         */
        $stmt = EasyStatement::open()
            ->with("pk.trusted")
            ->andWith("pk.actorid = ?", $actor->getPrimaryKey());
        if ($keyId) {
            $stmt->andWith("pk.key_id = ?", $keyId);
        }

        /** @var array<string, string> $row */
        foreach ($this->db->run(
            "SELECT
                    pk.actorpublickeyid,
                    pk.publickey,
                    pk.wrap_publickey,
                    pk.key_id,
                    mli.root AS insertmerkleroot,
                    mli.inclusionproof,
                    mli.created AS inserttime
                FROM pkd_actors_publickeys pk
                LEFT JOIN pkd_merkle_leaves mli ON mli.merkleleafid = pk.insertleaf
                WHERE {$stmt}",
            ...$stmt->values()
        ) as $row) {
            $decrypt = $this->getCipher()->decryptRow($row);
            if (empty($keyId) || hash_equals((string) $row['key_id'], $keyId)) {
                $insertTime = new DateTimeImmutable((string) $decrypt['inserttime'])->getTimestamp();
                $inclusionProof = json_decode(
                    (string) $decrypt['inclusionproof'],
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
                if (!is_array($inclusionProof)) {
                    $inclusionProof = [];
                }

                $results[] = [
                    'public-key' => PublicKey::fromString((string) $decrypt['publickey']),
                    'actorpublickeyid' => (int) $row['actorpublickeyid'],
                    'key-id' => (string) $row['key_id'],
                    'created' => (string) $insertTime,
                    'merkle-root' => $decrypt['insertmerkleroot'],
                    'inclusion-proof' => $inclusionProof,
                ];
            }
        }
        return $results;
    }

    public function getNextPrimaryKey(): int
    {
        $maxActorPKId = $this->db->cell("SELECT MAX(actorpublickeyid) FROM pkd_actors_publickeys");
        if (!$maxActorPKId) {
            return 1;
        }
        return (int) ($maxActorPKId) + 1;
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function addKey(Payload $payload, string $outerActor): ActorKey
    {
        return $this->protocolMethod(
            $payload,
            'AddKey',
            fn (MerkleLeaf $leaf, Payload $payload) =>
                $this->addKeyCallback($leaf, $payload, $outerActor)
        );
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function revokeKey(Payload $payload, string $outerActor): ActorKey
    {
        return $this->protocolMethod(
            $payload,
            'RevokeKey',
            fn (MerkleLeaf $leaf, Payload $payload) =>
                $this->revokeKeyCallback($leaf, $payload, $outerActor)
        );
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function revokeKeyThirdParty(Payload $payload): bool
    {
        return $this->protocolMethod(
            $payload,
            'RevokeKeyThirdParty',
            fn (MerkleLeaf $leaf, Payload $payload) => $this->revokeKeyThirdPartyCallback($leaf, $payload),
            self::ENCRYPTION_DISALLOWED
        );
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function moveIdentity(Payload $payload, string $outerActor): bool
    {
        return $this->protocolMethod(
            $payload,
            'MoveIdentity',
            fn (MerkleLeaf $leaf, Payload $payload) =>
                $this->moveIdentityCallback($leaf, $payload, $outerActor)
        );
    }

    /**
     * This is called by MerkleState::insertLeaf()
     *
     * @throws ArrayKeyException
     * @throws BaseJsonException
     * @throws BlindIndexNotFoundException
     * @throws BundleException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws InvalidCiphertextException
     * @throws NetworkException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    protected function addKeyCallback(MerkleLeaf $leaf, Payload $payload, string $outerActor): ActorKey
    {
        $decoded = $payload->jsonDecode();

        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        if (!($actorTable instanceof Actors)) {
            throw new TypeError('Actors table class not returned');
        }

        // Check the recency of the recent-merkle-root record:
        $recentMerkle = $decoded['recent-merkle-root'];
        $this->assertRecentMerkleRoot($recentMerkle);

        // AddKey is special: It can be self-signed!
        $decrypted = $payload->decrypt();
        if (!($decrypted instanceof AddKey)) {
            throw new TypeError('Decrypted message must be an AddKey');
        }
        $actionData = $decrypted->toArray();

        // Explicit check that the outer actor (from ActivityPub) matches the protocol message
        $this->explicitOuterActorCheck($outerActor, $actionData['actor']);

        // Does this actor need to e created? (Only AddKey can create an actor!)
        $actor = $actorTable->searchForActor($actionData['actor']);
        if (is_null($actor)) {
            $actorId = $actorTable->createActor($actionData['actor'], $payload);
        } else {
            $actorId = $actor->getPrimaryKey();
        }

        $sm = Bundle::fromJson($payload->rawJson)->toSignedMessage();
        $signatureIsValid = false;
        $candidatePublicKeys = $this->getPublicKeysFor($actionData['actor']);
        if (empty($candidatePublicKeys)) {
            // We just need a valid signature for the public key on the message
            $newPublicKey = PublicKey::fromString($actionData['public-key']);
            $signatureIsValid = $sm->verify($newPublicKey);
        } else {
            // We need to make sure it was signed by a currently-trusted public key
            // Notice that $newPublicKey is omitted from this check
            foreach ($candidatePublicKeys as $row) {
                if ($sm->verify($row['public-key'])) {
                    $signatureIsValid = true;
                    break;
                }
            }
        }

        if (!$signatureIsValid) {
            throw new ProtocolException('Invalid signature');
        }
        $encryptor = $this->getCipher();

        // Write the new public key to the table
        $nextActorPKId = $this->getNextPrimaryKey();
        $keyID = $this->generateKeyID();
        $plaintextRow = $encryptor->wrapBeforeEncrypt(
            [
                'actorpublickeyid' => $nextActorPKId,
                'actorid' => (string) $actorId,
                'publickey' => (string) $actionData['public-key'],
                'trusted' => true,
                'key_id' => $keyID,
                'insertleaf' => $leaf->getPrimaryKey(),
            ],
            $this->convertKeyMap($payload->keyMap)
        );
        [$rowToInsert, $blindIndexes] = $encryptor->prepareRowForStorage($plaintextRow);
        $rowToInsert['publickey_idx'] = (string) $blindIndexes['publickey_idx'];
        $this->db->insert(
            'pkd_actors_publickeys',
            $rowToInsert
        );
        return $this->getRecord($nextActorPKId);
    }

    /**
     * This is called by MerkleState::insertLeaf()
     *
     * @throws BaseJsonException
     * @throws BundleException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws InvalidCiphertextException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    protected function revokeKeyCallback(MerkleLeaf $leaf, Payload $payload, string $outerActor): ActorKey
    {
        $rawJson = $payload->rawJson;
        $decoded = json_decode($rawJson, true);

        // Check the recency of the recent-merkle-root record:
        $recentMerkle = $decoded['recent-merkle-root'];
        $this->assertRecentMerkleRoot($recentMerkle);

        // Get the public key used to sign the protocol message
        $decrypted = $payload->decrypt();
        if (!($decrypted instanceof RevokeKey)) {
            throw new ProtocolException('Invalid message type');
        }
        $actionData = $decrypted->toArray();

        // Explicit check that the outer actor (from ActivityPub) matches the protocol message
        $this->explicitOuterActorCheck($outerActor, $actionData['actor']);
        $sm = Bundle::fromJson($rawJson)->toSignedMessage();
        $candidatePublicKeys = $this->getPublicKeysFor(
            actorName: $actionData['actor'],
            keyId: $actionData['key-id'] ?? ''
        );
        foreach ($candidatePublicKeys as $row) {
            if ($sm->verify($row['public-key'])) {
                // Valid key found!
                $this->db->update(
                    'pkd_actors_publickeys',
                    [
                        'trusted' => false,
                        'revokeleaf' => $leaf->getPrimaryKey(),
                    ],
                    ['actorpublickeyid' => $row['actorpublickeyid']]
                );
                return $this->getRecord($row['actorpublickeyid']);
            }
        }
        throw new ProtocolException('Signature is not valid');
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    protected function revokeKeyThirdPartyCallback(MerkleLeaf $leaf, Payload $payload): bool
    {
        $message = $payload->message;
        if (!($message instanceof RevokeKeyThirdParty)) {
            throw new ProtocolException('Invalid message type');
        }
        $token = $message->getRevocationToken();

        $revocation = new Revocation();
        [$subject, $signed, $signature] = $revocation->decode($token);

        // First, let's make sure the signature is valid.
        if (!$subject->verify($signature, $signed)) {
            throw new ProtocolException('Invalid signature on revocation token');
        }

        // Now, let's look up all keys that match this public key.
        $statement = EasyStatement::open()
            ->with('trusted = ?', true)
            ->andWith('publickey_idx = ?', $this->getCipher()->getBlindIndex(
                'publickey_idx',
                ['publickey' => $subject->toString()]
            ));
        $toRevoke = $this->db->run(
            "SELECT actorpublickeyid, publickey, wrap_publickey FROM pkd_actors_publickeys WHERE {$statement}",
            ...$statement->values()
        );
        // The above query is using EasyStatement. If your SAST is complaining about string concatenation, that is a
        // false positive. User input does not influence `$statement->__toString()`, only `$statement->values()`.
        // This is, in turn, used with prepared statements to avoid SQL injection. It's kind of clever, but cleverness
        // doesn't always lend towards easy auditability. Hence, my comment about it here. -- Soatok, 2025-11-28 <3

        if (empty($toRevoke)) {
            // Nothing to revoke.
            return false;
        }

        $revoked = false;
        foreach ($toRevoke as $row) {
            $decrypted = $this->getCipher()->decryptRow($row);
            if (hash_equals($subject->toString(), (string) $decrypted['publickey'])) {
                $this->db->update(
                    'pkd_actors_publickeys',
                    [
                        'trusted' => false,
                        'revokeleaf' => $leaf->getPrimaryKey(),
                    ],
                    ['actorpublickeyid' => $row['actorpublickeyid']]
                );
                $revoked = true;
            }
        }
        return $revoked;
    }

    /**
     * @throws ArrayKeyException
     * @throws BaseJsonException
     * @throws BlindIndexNotFoundException
     * @throws BundleException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws InvalidCiphertextException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    protected function moveIdentityCallback(MerkleLeaf $leaf, Payload $payload, string $outerActor): bool
    {
        $rawJson = $payload->rawJson;
        $decoded = json_decode($rawJson, true);

        $this->assertRecentMerkleRoot($decoded['recent-merkle-root']);
        $actorTable = $this->table('Actors');
        if (!($actorTable instanceof Actors)) {
            throw new DependencyException('Actor Table is of wrong type');
        }

        $decrypted = $payload->decrypt();
        if (!($decrypted instanceof MoveIdentity)) {
            throw new ProtocolException('Invalid message type');
        }
        $actionData = $decrypted->toArray();
        // Explicit check that the outer actor (from ActivityPub) matches the protocol message
        $this->explicitOuterActorCheck($outerActor, $actionData['new-actor']);
        $sm = Bundle::fromJson($rawJson)->toSignedMessage();

        $oldActorId = $actorTable
            ->searchForActor($actionData['old-actor'])->getPrimaryKey();
        $candidatePublicKeys = $this->getPublicKeysFor(
            actorName: $actionData['old-actor'],
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

        if (!empty($this->getPublicKeysFor($actionData['new-actor']))) {
            throw new ProtocolException('New actor already has public keys');
        }
        $newActorId = $actorTable->createActor($actionData['new-actor'], $payload);

        $this->db->update(
            'pkd_actors_publickeys',
            ['actorid' => $newActorId],
            ['actorid' => $oldActorId]
        );
        $this->db->update(
            'pkd_actors',
            ['movedleaf' => $leaf->getPrimaryKey()],
            ['actorid' => $oldActorId]
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
    public function burnDown(Payload $payload, string $outerActor): bool
    {
        return $this->protocolMethod(
            $payload,
            'BurnDown',
            fn (MerkleLeaf $leaf, Payload $payload) =>
                $this->burnDownCallback($leaf, $payload, $outerActor),
            self::ENCRYPTION_DISALLOWED
        );
    }

    /**
     * @throws ArrayKeyException
     * @throws BaseJsonException
     * @throws BlindIndexNotFoundException
     * @throws BundleException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws InvalidCiphertextException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    protected function burnDownCallback(MerkleLeaf $leaf, Payload $payload, string $outerActor): bool
    {
        $rawJson = $payload->rawJson;
        $decoded = json_decode($rawJson, true);

        $this->assertRecentMerkleRoot($decoded['recent-merkle-root']);

        $decrypted = $payload->decrypt();
        if (!($decrypted instanceof BurnDown)) {
            throw new ProtocolException('Invalid message type');
        }
        $actionData = $decrypted->toArray();
        $sm = Bundle::fromJson($rawJson)->toSignedMessage();

        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        $actor = $actorTable->searchForActor($actionData['actor']);
        if (is_null($actor)) {
            throw new ProtocolException('Actor not found');
        }
        if ($actor->fireProof) {
            throw new ProtocolException('Actor is fireproof');
        }

        // Explicit check that the outer actor (from ActivityPub) matches the protocol message
        $this->explicitOuterActorCheck($outerActor, $actionData['operator']);
        $operator = $actorTable->searchForActor($actionData['operator']);
        if (is_null($operator)) {
            throw new ProtocolException('Operator not found');
        }
        $candidatePublicKeys = $this->getPublicKeysFor(
            actorName: $operator->actorID,
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

        /** @var TOTP $totpTable */
        $totpTable = $this->table('TOTP');
        $domain = parse_url($actor->actorID)['host'];
        $secret = $totpTable->getSecretByDomain($domain);
        if ($secret) {
            if (!$this->verifyTOTP($secret, $decrypted->getOtp() ?? '')) {
                throw new ProtocolException('Invalid TOTP code');
            }
        }

        $affected = $this->db->update(
            'pkd_actors_publickeys',
            [
                'trusted' => false,
                'revokeleaf' => $leaf->getPrimaryKey()
            ],
            ['actorid' => $actor->getPrimaryKey()]
        );
        return $affected > 0;
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function fireproof(Payload $payload, string $outerActor): bool
    {
        return $this->protocolMethod(
            $payload,
            'Fireproof',
            fn (MerkleLeaf $leaf, Payload $payload) =>
                $this->fireproofCallback($leaf, $payload, $outerActor)
        );
    }

    /**
     * @throws ArrayKeyException
     * @throws BaseJsonException
     * @throws BlindIndexNotFoundException
     * @throws BundleException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws InvalidCiphertextException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     *
     */
    protected function fireproofCallback(MerkleLeaf $leaf, Payload $payload, string $outerActor): bool
    {
        $rawJson = $payload->rawJson;
        $decoded = json_decode($rawJson, true);

        $this->assertRecentMerkleRoot($decoded['recent-merkle-root']);

        $decrypted = $payload->decrypt();
        if (!($decrypted instanceof Fireproof)) {
            throw new ProtocolException('Invalid message type');
        }
        $actionData = $decrypted->toArray();

        // Explicit check that the outer actor (from ActivityPub) matches the protocol message
        $this->explicitOuterActorCheck($outerActor, $actionData['actor']);

        $sm = Bundle::fromJson($rawJson)->toSignedMessage();

        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        $actor = $actorTable->searchForActor($actionData['actor']);
        if ($actor->fireProof) {
            throw new ProtocolException('Actor is already fireproof');
        }

        $candidatePublicKeys = $this->getPublicKeysFor(
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

        $this->db->update(
            'pkd_actors',
            [
                'fireproof' => 1,
                'fireproofleaf' => $leaf->getPrimaryKey()
            ],
            ['actorid' => $actor->getPrimaryKey()]
        );
        $actorTable->clearCacheForActor($actor);
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
    public function undoFireproof(Payload $payload, string $outerActor): bool
    {
        return $this->protocolMethod(
            $payload,
            'UndoFireproof',
            fn (MerkleLeaf $leaf, Payload $payload) =>
                $this->undoFireproofCallback($leaf, $payload, $outerActor)
        );
    }

    /**
     * @throws ArrayKeyException
     * @throws BaseJsonException
     * @throws BlindIndexNotFoundException
     * @throws BundleException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws InvalidCiphertextException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    protected function undoFireproofCallback(MerkleLeaf $leaf, Payload $payload, string $outerActor): bool
    {
        $rawJson = $payload->rawJson;
        $decoded = json_decode($rawJson, true);

        $this->assertRecentMerkleRoot($decoded['recent-merkle-root']);

        $decrypted = $payload->decrypt();
        if (!($decrypted instanceof UndoFireproof)) {
            throw new ProtocolException('Invalid message type');
        }
        $actionData = $decrypted->toArray();

        // Explicit check that the outer actor (from ActivityPub) matches the protocol message
        $this->explicitOuterActorCheck($outerActor, $actionData['actor']);

        $sm = Bundle::fromJson($rawJson)->toSignedMessage();

        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        $actor = $actorTable->searchForActor($actionData['actor']);
        if (!$actor->fireProof) {
            throw new ProtocolException('Actor is not fireproof');
        }

        $candidatePublicKeys = $this->getPublicKeysFor(
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

        $this->db->update(
            'pkd_actors',
            [
                'fireproof' => 0,
                'undofireproofleaf' => $leaf->getPrimaryKey()
            ],
            ['actorid' => $actor->getPrimaryKey()]
        );
        $actorTable->clearCacheForActor($actor);
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
    public function checkpoint(Payload $payload): bool
    {
        return $this->protocolMethod(
            $payload,
            'Checkpoint',
            fn (MerkleLeaf $leaf, Payload $payload) => $this->checkpointCallback($leaf, $payload),
            self::ENCRYPTION_DISALLOWED
        );
    }

    /**
     * @throws ProtocolException
     */
    protected function checkpointCallback(MerkleLeaf $leaf, Payload $payload): bool
    {
        $rawJson = $payload->rawJson;
        $decoded = json_decode($rawJson, true);

        $this->assertRecentMerkleRoot($decoded['recent-merkle-root']);

        if (!($payload->message instanceof Checkpoint)) {
            throw new ProtocolException('Invalid message type');
        }
        // TODO: In the future, verify the signature against a known directory public key
        return true;
    }
}
