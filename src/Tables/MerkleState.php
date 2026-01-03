<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use FediE2EE\PKD\Crypto\Exceptions\{CryptoException, JsonException, NotImplementedException};
use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Protocol\Cosignature;
use FediE2EE\PKD\Crypto\PublicKey;
use FediE2EE\PKDServer\Exceptions\ProtocolException;
use FediE2EE\PKDServer\Exceptions\TableException;
use FediE2EE\PKD\Crypto\Merkle\{
    InclusionProof,
    IncrementalTree,
    Tree
};
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\Table;
use FediE2EE\PKDServer\Tables\Records\MerkleLeaf;
use Override;
use ParagonIE\ConstantTime\Base64UrlSafe;
use PDO;
use PDOException;
use SodiumException;

/**
 * Merkle State management
 *
 * Insert new leaves
 */
class MerkleState extends Table
{
    #[Override]
    public function getCipher(): WrappedEncryptedRow
    {
        return new WrappedEncryptedRow(
            $this->engine,
            'pkd_merkle_leaves',
            false,
            'merkleleafid'
        );
    }

    /**
     * MerkleState has no encrypted fields.
     *
     * @param AttributeKeyMap $inputMap
     * @return array
     */
    #[Override]
    protected function convertKeyMap(AttributeKeyMap $inputMap): array
    {
        return [];
    }

    /**
     * Return the witness data (including public key) for a given origin
     *
     * @param string $origin
     * @return array<string, mixed>
     * @throws TableException
     */
    public function getWitnessByOrigin(string $origin): array
    {
        /** @var array<string, mixed> $witness */
        $witness = $this->db->row(
            "SELECT * FROM pkd_merkle_witnesses WHERE origin = ?",
            $origin
        );
        if (!$witness) {
            throw new TableException('Witness not found for ' . $origin);
        }
        return $witness;
    }

    /**
     * @api
     *
     * @param string $origin
     * @param string $merkleRoot
     * @param string $cosignature
     * @return bool
     *
     * @throws CryptoException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function addWitnessCosignature(string $origin, string $merkleRoot, string $cosignature): bool
    {
        // Throw if witness is not in allow-list:
        $witness = $this->getWitnessByOrigin($origin);

        // Ensure we're dealing with a valid cosignature
        $pk = PublicKey::fromString($witness['publickey']);

        // This will throw if the signature is invalid:
        $tmp = Cosignature::verifyCosignature($pk, $cosignature);
        if (!hash_equals($tmp['merkle-root'], $merkleRoot)) {
            throw new ProtocolException('Cosignature Merkle root mismatch');
        }

        // Validate hostname:
        $hostname = $this->config->getParams()->hostname;
        if ($tmp['hostname'] !== $hostname) {
            // If hostname is formatted as a URL, just grab the hostname:
            $parsedUrl = parse_url($tmp['hostname']);
            if ($parsedUrl['host'] !== $hostname) {
                // Both mismatched? Bail out.
                throw new ProtocolException('Hostname mismatch');
            }
        }

        // Grab the leaf that they're cosigning:
        $leaf = $this->getLeafByRoot($merkleRoot);

        // If we get here without throwing, we're good:
        $this->db->beginTransaction();
        $this->db->insert(
            'pkd_merkle_witness_cosignatures',
            [
                'leaf' => $leaf->primaryKey,
                'witness' => $witness['witnessid'],
                'cosignature' => $cosignature,
            ]
        );
        return $this->db->commit();
    }

    public function getCosignatures(int $leafId): array
    {
        // TODO: cache
        $cosigs = $this->db->run(
            "SELECT
                    w.origin AS witness,
                    w.publickey,
                    c.cosignature,
                    c.created
                FROM pkd_merkle_witness_cosignatures c 
                JOIN pkd_merkle_witnesses w ON c.witness = w.witnessid
                WHERE c.leaf = ?",
            $leafId
        );
        if (empty($cosigs)) {
            return [];
        }
        return $cosigs;
    }

    public function countCosignatures(int $leafId): int
    {
        $count = $this->db->cell(
            "SELECT count(*) FROM pkd_merkle_witness_cosignatures WHERE leaf = ?",
            $leafId
        );
        if (!$count) {
            return 0;
        }
        return (int) $count;
    }

    /**
     * @api
     */
    public function getLatestRoot(): string
    {
        $result = $this->db->cell(
            "SELECT root FROM pkd_merkle_leaves ORDER BY merkleleafid DESC LIMIT 1"
        );
        if (empty($result)) {
            $result = new Tree([], $this->config()->getParams()->hashAlgo)->getEncodedRoot();
        }
        return $result;
    }

    /**
     * Insert leaf with retry logic for deadlocks
     *
     * @param MerkleLeaf $leaf        Record to be added
     * @param callable $inTransaction Function to call if Merkle Tree successfully updated
     * @param int $maxRetries         Maximum number of retries if Merkle Tree is locked
     * @return bool
     *
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws SodiumException
     * @api
     */
    public function insertLeaf(MerkleLeaf $leaf, callable $inTransaction, int $maxRetries = 3): bool
    {
        $attempt = 0;
        while ($attempt < $maxRetries) {
            if ($this->db->inTransaction()) {
                // Make sure whatever is happening before insertLeaf() is committed
                $this->db->rollBack();
            }
            try {
                return $this->insertLeafInternal($leaf, $inTransaction);
            } catch (PDOException $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $shouldRetry = false;

                // Check for deadlock/lock timeout errors by driver
                switch ($this->db->getDriver()) {
                    case 'mysql':
                        // Deadlock or lock wait timeout
                        $shouldRetry = in_array($e->getCode(), ['40001', 'HY000'], true);
                        break;
                    case 'pgsql':
                        // Deadlock detected or serialization failure
                        $shouldRetry = in_array($e->getCode(), ['40001', '40P01'], true);
                        break;
                    case 'sqlite':
                        // Database is locked
                        $shouldRetry = ($e->getCode() === 'HY000' &&
                            str_contains($e->getMessage(), 'locked'));
                        break;
                }
                $shouldRetry = $shouldRetry || str_contains(
                    haystack:  $e->getMessage(),
                    needle: 'already an active transaction',
                );

                if ($shouldRetry && $attempt < $maxRetries - 1) {
                    $attempt++;
                    usleep(random_int(10000, 100000)); // Random backoff 10-100ms
                    continue;
                }
                throw $e;
            }
        }
        // If we end up here, it was not successful.
        return false;
    }

    /**
     * @api
     */
    public function getLeafByRoot(string $root): ?MerkleLeaf
    {
        if (array_key_exists($root, $this->recordCache)) {
            return $this->recordCache[$root];
        }
        $data = $this->db->row(
            "SELECT 
                    merkleleafid, root, contents, contenthash, signature, publickeyhash, inclusionproof, created
                FROM pkd_merkle_leaves
                WHERE root = ?",
            $root
        );
        if (empty($data)) {
            return null;
        }
        return $this->cacheLeaf($data);
    }

    /**
     * @api
     */
    public function getLeafByID(int $primaryKey): ?MerkleLeaf
    {
        if (array_key_exists($primaryKey, $this->recordCache)) {
            return $this->recordCache[$primaryKey];
        }
        $data = $this->db->row(
            "SELECT 
                    merkleleafid, root, contents, contenthash, signature, publickeyhash, inclusionproof, created
                FROM pkd_merkle_leaves
                WHERE merkleleafid = ?",
            $primaryKey
        );
        if (empty($data)) {
            return null;
        }
        return $this->cacheLeaf($data);
    }

    protected function cacheLeaf(array $data): MerkleLeaf
    {
        $ip = json_decode($data['inclusionproof'], true);
        $leaf = new MerkleLeaf(
            $data['contents'],
            $data['contenthash'],
            $data['signature'],
            $data['publickeyhash'],
            new InclusionProof($ip['index'] ?? 0, $ip['proof'] ?? []),
            (string) $data['created'],
            $data['merkleleafid']
        );
        // Cache for future queries
        $this->recordCache[$data['root']] = $leaf;
        $this->recordCache[$data['merkleleafid']] = $leaf;
        return $leaf;
    }

    /**
     * @api
     */
    public function getHashesSince(string $oldRoot, int $limit, int $offset = 0): array
    {
        $oldRootID = 0;
        // Special case: The first entry has a previous hash of all zeroes
        //
        // No, Semgrep, this is NOT a Telegram key:
        // nosemgrep: generic.secrets.security.detected-telegram-bot-api-key.detected-telegram-bot-api-key
        if ($oldRoot !== 'pkd-mr-v1:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA') {
            $oldRootID = $this->db->cell(
                "SELECT 
                    merkleleafid
                FROM pkd_merkle_leaves
                WHERE root = ?",
                $oldRoot
            );
            if (empty($oldRootID)) {
                return []; // nothing found
            }
        }
        $oldRecords = $this->db->run(
            "SELECT publickeyhash, contents, contenthash, root, created, signature
            FROM pkd_merkle_leaves
            WHERE merkleleafid > ?
            LIMIT {$limit} OFFSET {$offset}",
            $oldRootID
        );
        $return = [];
        foreach ($oldRecords as $row) {
            $return[] = [
                'created' => $row['created'],
                'encrypted-message' => $row['contents'],
                'contenthash' => $row['contenthash'],
                'publickeyhash' => $row['publickeyhash'],
                'signature' => $row['signature'],
                'message' => null,
                'merkle-root' => $row['root'],
                'rewrapped-keys' => null,
            ];
        }
        return $return;
    }

    /**
     * Internal processing for insertLeaf() above.
     *
     * @throws CryptoException
     * @throws NotImplementedException
     * @throws PDOException
     * @throws SodiumException
     * @throws DependencyException
     */
    protected function insertLeafInternal(MerkleLeaf $leaf, callable $inTransaction): bool
    {
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        switch ($this->db->getDriver()) {
            case 'pgsql':
            case 'mysql':
                $this->db->beginTransaction();
                $state = $this->db->cell("SELECT merkle_state FROM pkd_merkle_state WHERE TRUE FOR UPDATE");
                break;
            case "sqlite":
                $this->db->beginTransaction();
                $this->db->exec("PRAGMA busy_timeout=5000");
                $state = $this->db->cell("SELECT merkle_state FROM pkd_merkle_state WHERE 1");
                break;
            default:
                throw new NotImplementedException('Database driver support not implemented');
        }
        $insert = empty($state);
        if ($insert) {
            $incremental = new IncrementalTree([], $this->config()->getParams()->hashAlgo);
        } else {
            // Deserialize state:
            $incremental = IncrementalTree::fromJson(Base64UrlSafe::decodeNoPadding($state));
        }

        // Append this leaf to the tree:
        $rawLeaf = $leaf->serializeForMerkle();
        $incremental->addLeaf($rawLeaf);
        $incremental->updateRoot();
        $inclusion = $incremental->getInclusionProof($rawLeaf);
        $root = $incremental->getEncodedRoot();

        // Sequence ID is needed for some DB drivers:
        $sequenceId = match($this->db->getDriver()) {
            'pgsql' => 'pkd_merkle_leaves_merkleleafid_seq',
            default => '',
        };

        // Insert the new leaf:
        $inserted = $this->db->insertReturnId(
            'pkd_merkle_leaves',
            [
                'publickeyhash' => $leaf->publicKeyHash,
                'contenthash' => $leaf->contentHash,
                'signature' => $leaf->getSignature(),
                'contents' => $leaf->contents,
                'inclusionproof' => json_encode(
                    $inclusion,
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                ),
                'root' => $root,
            ],
            $sequenceId
        );
        // If we didn't get an exception thrown, we can proceed!

        // Set the primary key on the MerkleLeaf record object:
        $leaf->setPrimaryKey((int) $inserted);

        if (!$this->db->inTransaction()) {
            throw new PDOException('we are not in a transaction before the callback!');
        }

        // Insert whatever data was important to the Merkle state, as defined by the callback.
        // We don't check the return value. If it throws, the transaction is never committed.
        $inTransaction();

        // Update the Merkle state:
        if ($insert) {
            // This will only trigger on the first leaf
            $this->db->insert(
                'pkd_merkle_state',
                [
                    'merkle_state' => Base64UrlSafe::encodeUnpadded($incremental->toJson())
                ]
            );
        } else {
            $this->db->update(
                'pkd_merkle_state',
                [
                    'merkle_state' => Base64UrlSafe::encodeUnpadded($incremental->toJson())
                ],
                ['merkle_state' => $state]
            );
        }
        // @phpstan-ignore-next-line
        if (!$this->db->inTransaction()) {
            throw new PDOException('we are not in a transaction after updating merkle_state');
        }

        // We only commit this transaction if all was successful:
        return $this->db->commit();
    }
}
