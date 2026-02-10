<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use FediE2EE\PKD\Crypto\Exceptions\{
    BundleException,
    CryptoException,
    InputException,
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
use FediE2EE\PKD\Crypto\{
    PublicKey,
    Revocation
};
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    ConcurrentException,
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
use ParagonIE\Certainty\Exception\CertaintyException;
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
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
use SodiumException;
use TypeError;

use function array_any;
use function hash_equals;
use function is_array;
use function is_null;
use function is_string;
use function json_decode;
use function random_bytes;

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
            ->addBlindIndex(
                'publickey',
                new BlindIndex('publickey_idx', [], 16, true)
            )
        ;
    }

    /**
     * @throws RandomException
     */
    public function generateKeyID(): string
    {
        return Base64UrlSafe::encodeUnpadded(random_bytes(32));
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
     * @param int $actorPrimaryKey
     * @param string $keyID
     * @return array<string, mixed>
     *
     * @throws BaseJsonException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws InvalidCiphertextException
     * @throws SodiumException
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
        $decrypted = $this->getCipher()->decryptRow(self::rowToStringArray($row));
        $insertTime = (string) new DateTimeImmutable(self::decryptedString($decrypted, 'inserttime'))->getTimestamp();
        $revokeTimeValue = $decrypted['revoketime'] ?? null;
        $revokeTime = is_string($revokeTimeValue) && !empty($revokeTimeValue)
            ? (string) new DateTimeImmutable($revokeTimeValue)->getTimestamp()
            : null;
        $inclusionProof = json_decode(
            self::decryptedString($decrypted, 'inclusionproof'),
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
     *
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
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
        $rowArray = self::assertArray($row);
        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        /** @var MerkleState $merkleTable */
        $merkleTable = $this->table('MerkleState');

        $actor = $actorTable->getActorByID(self::assertInt($rowArray['actorid'] ?? 0));
        $insertLeaf = $merkleTable->getLeafByID(self::assertInt($rowArray['insertleaf'] ?? 0));
        if (is_null($insertLeaf)) {
            throw new TableException('Insert leaf not found for key: ' . $primaryKey);
        }
        $revokeLeaf = is_null($rowArray['revokeleaf'] ?? null)
            ? null
            : $merkleTable->getLeafByID(self::assertInt($rowArray['revokeleaf']));
        $decrypted = $this->getCipher()->decryptRow(self::rowToStringArray($rowArray));

        return new ActorKey(
            actor: $actor,
            publicKey: PublicKey::fromString(self::decryptedString($decrypted, 'publickey')),
            trusted: !empty($rowArray['trusted']),
            insertLeaf: $insertLeaf,
            revokeLeaf: $revokeLeaf,
            keyID: self::assertString($rowArray['key_id'] ?? ''),
        );
    }

    /**
     * @throws ArrayKeyException
     * @throws BaseJsonException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @return array<int, array<string, mixed>>
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
            $rowArray = self::rowToStringArray($row);
            $decrypt = $this->getCipher()->decryptRow($rowArray);
            if (empty($keyId) || hash_equals($rowArray['key_id'] ?? '', $keyId)) {
                $insertTime = new DateTimeImmutable(self::decryptedString($decrypt, 'inserttime'))->getTimestamp();
                $inclusionProof = json_decode(
                    self::decryptedString($decrypt, 'inclusionproof'),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
                if (!is_array($inclusionProof)) {
                    $inclusionProof = [];
                }

                $results[] = [
                    'public-key' => PublicKey::fromString(self::decryptedString($decrypt, 'publickey')),
                    'actorpublickeyid' => (int) ($rowArray['actorpublickeyid'] ?? 0),
                    'key-id' => $rowArray['key_id'] ?? '',
                    'created' => (string) $insertTime,
                    'merkle-root' => $decrypt['insertmerkleroot'] ?? null,
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
     * Verify a protocol message signature against an actor's enrolled keys.
     *
     * @throws ArrayKeyException
     * @throws BaseJsonException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    protected function verifyProtocolSignature(
        string $rawJson,
        string $actorName,
        string $keyId = '',
    ): void {
        $sm = Bundle::fromJson($rawJson)->toSignedMessage();
        $anySignatureIsValid = array_any(
            $this->getPublicKeysFor($actorName, $keyId),
            fn (array $row) => $sm->verify($row['public-key'])
        );
        if (!$anySignatureIsValid) {
            throw new ProtocolException('Invalid signature');
        }
    }

    /**
     * @throws ProtocolException
     */
    protected function verifyOperatorDomain(
        string $actorId,
        string $operatorId,
    ): void {
        $actorDomain = self::parseUrlHost($actorId);
        $operatorDomain = self::parseUrlHost($operatorId);
        if (
            is_null($actorDomain)
            || is_null($operatorDomain)
            || !hash_equals($actorDomain, $operatorDomain)
        ) {
            throw new ProtocolException(
                'Operator must be on the same instance as the target actor'
            );
        }
    }

    /**
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    protected function verifyBurnDownTotp(
        string $actorId,
        BurnDown $decrypted,
    ): void {
        /** @var TOTP $totpTable */
        $totpTable = $this->table('TOTP');
        $domain = self::parseUrlHost($actorId);
        if (is_null($domain)) {
            throw new ProtocolException('Invalid actor URL');
        }
        $totp = $totpTable->getTotpByDomain($domain);
        if (!$totp) {
            return;
        }
        $ts = $this->verifyTOTP($totp['secret'], $decrypted->getOtp() ?? '');
        if (is_null($ts)) {
            throw new ProtocolException('Invalid TOTP code');
        }
        if ($ts <= $totp['last_time_step']) {
            throw new ProtocolException('TOTP code already used');
        }
        $totpTable->updateLastTimeStep($domain, $ts);
    }

    /**
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
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
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
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
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
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
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
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
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws InputException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#addkey
    //# The first `AddKey` for any given Actor **MUST** be self-signed by the same public key being added.
    protected function addKeyCallback(MerkleLeaf $leaf, Payload $payload, string $outerActor): ActorKey
    {
        $decoded = $payload->decode();

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
        $newPublicKey = PublicKey::fromString($actionData['public-key']);
        $candidatePublicKeys = $this->getPublicKeysFor($actionData['actor']);
        if (empty($candidatePublicKeys)) {
            //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#addkey
            //# The first `AddKey` for any given Actor **MUST** be self-signed by the same public key being added.
            $signatureIsValid = $sm->verify($newPublicKey);
        } else {
            //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#addkey
            //# Every subsequent `AddKey` must be signed by an existing, non-revoked public key.
            foreach ($candidatePublicKeys as $row) {
                if ($sm->verify($row['public-key'])) {
                    // Check if this is a self-signed AddKey (new key == signing key)
                    if (hash_equals($newPublicKey->toString(), $row['public-key']->toString())) {
                        throw new ProtocolException(
                            'Self-signed AddKey not allowed when keys exist'
                        );
                    }
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
        $rowToInsert['publickey_idx'] = self::blindIndexValue($blindIndexes['publickey_idx']);
        $this->db->insert(
            'pkd_actors_publickeys',
            $rowToInsert
        );
        return $this->getRecord($nextActorPKId);
    }

    //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#revokekey
    //# Attempting to issue a `RevokeKey` **MUST** fail unless there is another public key associated with this Actor.
    /**
     * This is called by MerkleState::insertLeaf()
     *
     * @throws ArrayKeyException
     * @throws BaseJsonException
     * @throws BlindIndexNotFoundException
     * @throws BundleException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws InputException
     * @throws InvalidArgumentException
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

    //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#revokekeythirdparty
    //# This is a special message type in two ways:
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

        //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#revokekeythirdparty-validation-steps
        //# Validate signature for  `version || REVOCATION_CONSTANT || public_key`, using `public_key`.
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
            if (hash_equals($subject->toString(), self::decryptedString($decrypted, 'publickey'))) {
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
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws InputException
     * @throws InvalidCiphertextException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#moveidentity
    //# This moves all the mappings from the old Actor ID to the new Actor ID.
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
        $this->explicitOuterActorCheck($outerActor, $actionData['old-actor']);
        $oldActor = $actorTable->searchForActor($actionData['old-actor']);
        if (is_null($oldActor)) {
            throw new ProtocolException('Old actor not found');
        }
        $oldActorId = $oldActor->getPrimaryKey();

        //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#moveidentity
        //# The message **MUST** be signed by a valid secret key for the `old-actor`
        $this->verifyProtocolSignature($rawJson, $actionData['old-actor'], $decoded['key-id'] ?? '');

        //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#moveidentity
        //# This message **MUST** be rejected if there are existing public keys for the target `new-actor`.
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
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
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
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws InputException
     * @throws InvalidCiphertextException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#burndown
    //# A `BurnDown` message acts as a soft delete for all public keys and auxiliary data for a given Actor
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

        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        $actor = $actorTable->searchForActor($actionData['actor']);
        if (is_null($actor)) {
            throw new ProtocolException('Actor not found');
        }
        //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#burndown-validation-steps
        //# If this actor is [fireproof](#fireproof), abort.
        if ($actor->fireProof) {
            throw new ProtocolException('Actor is fireproof');
        }

        // Explicit check that the outer actor (from ActivityPub) matches the protocol message
        //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#burndown
        //# a `BurnDown` is issued by an operator account on the Fediverse instance that hosts the
        $this->explicitOuterActorCheck($outerActor, $actionData['operator']);
        $operator = $actorTable->searchForActor($actionData['operator']);
        if (is_null($operator)) {
            throw new ProtocolException('Operator not found');
        }

        $this->verifyOperatorDomain($actor->actorID, $operator->actorID);

        //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#burndown-validation-steps
        //# Validate the message signature for the given public key.
        $this->verifyProtocolSignature($rawJson, $operator->actorID, $decoded['key-id'] ?? '');

        //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#burndown-validation-steps
        //# If the instance has previously enrolled a TOTP secret to this Fediverse server
        $this->verifyBurnDownTotp($actor->actorID, $decrypted);

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
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
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
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws NetworkException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#fireproof
    //# `Fireproof` opts out of this recovery
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

        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        $actor = $actorTable->searchForActor($actionData['actor']);
        if (is_null($actor)) {
            throw new ProtocolException('Actor has no enrolled keys');
        }
        //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#fireproof
        //# If an Actor is already in Fireproof status, a subsequent `Fireproof` message **MUST** be rejected.
        if ($actor->fireProof) {
            throw new ProtocolException('Actor is already fireproof');
        }

        $this->verifyProtocolSignature($rawJson, $actor->actorID, $decoded['key-id'] ?? '');

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
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
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
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws NetworkException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#undofireproof
    //# This reverts the Fireproof status for a given Actor, re-enabling account recovery by instance administrators.
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

        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        $actor = $actorTable->searchForActor($actionData['actor']);
        if (is_null($actor)) {
            throw new ProtocolException('Actor not found');
        }
        //= https://raw.githubusercontent.com/fedi-e2ee/public-key-directory-specification/refs/heads/main/Specification.md#undofireproof
        //# If the user is not in `Fireproof` status, this message is rejected.
        if (!$actor->fireProof) {
            throw new ProtocolException('Actor is not fireproof');
        }

        $this->verifyProtocolSignature($rawJson, $actor->actorID, $decoded['key-id'] ?? '');

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
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
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
     * @throws DependencyException
     * @throws ProtocolException
     * @throws SodiumException
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
