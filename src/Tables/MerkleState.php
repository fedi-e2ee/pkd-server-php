<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    NotImplementedException
};
use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
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
            try {
                return $this->insertLeafInternal($leaf, $inTransaction);
            } catch (PDOException $e) {
                $shouldRetry = false;

                // Check for deadlock/lock timeout errors by driver
                switch ($this->db->getDriver()) {
                    case 'mysql':
                        // Deadlock or lock wait timeout
                        $shouldRetry = in_array($e->getCode(), ['40001', 'HY000']);
                        break;
                    case 'pgsql':
                        // Deadlock detected or serialization failure
                        $shouldRetry = in_array($e->getCode(), ['40001', '40P01']);
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
            "SELECT contents, root, created
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
        $needsTransaction = !$this->db->inTransaction();
        if ($needsTransaction) {
            switch ($this->db->getDriver()) {
                case 'pgsql':
                case 'mysql':
                    $this->db->beginTransaction();
                    $state = $this->db->cell("SELECT merkle_state FROM pkd_merkle_state WHERE TRUE FOR UPDATE");
                    break;
                case "sqlite":
                    $this->db->exec("PRAGMA busy_timeout=5000");
                    $this->db->beginTransaction();
                    $state = $this->db->cell("SELECT merkle_state FROM pkd_merkle_state WHERE 1");
                    break;
                default:
                    throw new NotImplementedException('Database driver support not implemented');
            }
        } else {
            $state = $this->db->cell("SELECT merkle_state FROM pkd_merkle_state");
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

        // Finally, insert whatever data was important to the Merkle state, as defined by the callback.
        // We don't check the return value. If it throws, the transaction is never committed.
        $inTransaction();

        // We only commit this transaction if all was successful:
        return $this->db->commit();
    }
}
