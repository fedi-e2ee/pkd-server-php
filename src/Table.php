<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Merkle\Tree;
use FediE2EE\PKD\Crypto\SymmetricKey;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Exceptions\{
    DependencyException,
    ProtocolException
};
use FediE2EE\PKDServer\Traits\ConfigTrait;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\Backend\Key\SymmetricKey as CipherSweetKey;
use ParagonIE\EasyDB\EasyDB;

abstract class Table
{
    use ConfigTrait;

    public readonly CipherSweet $engine;
    public readonly EasyDB $db;
    protected array $recordCache = [];

    /**
     * @throws DependencyException
     */
    public function __construct(ServerConfig $config)
    {
        $this->injectConfig($config);
        $this->db = $config->getDb();
        $this->engine = $config->getCipherSweet();
    }

    abstract public function getCipher(): WrappedEncryptedRow;

    abstract protected function convertKeyMap(AttributeKeyMap $inputMap): array;

    public function clearCache(): void
    {
        $this->recordCache = [];
    }

    public function convertKey(SymmetricKey $inputKey): CipherSweetKey
    {
        return new CipherSweetKey($inputKey->getBytes());
    }

    /**
     * @throws ProtocolException
     */
    public function assertRecentMerkleRoot(string $recentMerkle): void
    {
        if (empty($recentMerkle)) {
            throw new ProtocolException('Empty Merkle Root provided');
        }
        if (!$this->isMerkleRootRecent($recentMerkle)) {
            // Does high-traffic mode make it succeed?
            if (!$this->isMerkleRootRecent($recentMerkle, true)) {
                // No? OK, then let's bail out.
                throw new ProtocolException('Stale or invalid Merkle Root provided');
            }
            // We should log these incidents, even though the Merkle Root is strictly speaking OK.
        }
    }

    /**
     * @api
     */
    public function isMerkleRootRecent(string $merkleRoot, bool $isHighVolume = false): bool
    {
        $numLeaves = $this->db->cell("SELECT count(merkleleafid) FROM pkd_merkle_leaves");
        if (!$numLeaves) {
            // An empty Merkle tree is expected to have an empty root:
            return hash_equals(
                (new Tree([], $this->config->getParams()->hashAlgo))->getEncodedRoot(),
                $merkleRoot
            );
        }
        if ($numLeaves < 2) {
            return true;
        }

        if ($isHighVolume) {
            /**
             * @link https://github.com/fedi-e2ee/public-key-directory-specification/blob/main/Specification.md#recent-merkle-root-included-in-plaintext-commitments
             *
             * """
             * To tolerate large transaction volumes in a short window of time, the chosen Merkle root MUST be at least
             * in the most recent N/2 messages (for N currently accepted Protocol Messages). Public Key Directories MAY
             * reject these messages due to staleness, especially if the Directory isn't experiencing significant
             * throughput, or if an Actor submits multiple Protocol Messages tied to a Merkle root too old to satisfy
             * rule 2.
             * """
             *
             * NOTE: The spec wasn't specific, so we round up. I'll update the spec later.
             */
            $cutoff = Math::getHighVolumeCutoff($numLeaves);
        } else {
            /**
             * @link https://github.com/fedi-e2ee/public-key-directory-specification/blob/main/Specification.md#recent-merkle-root-included-in-plaintext-commitments
             *
             * """
             * If there are N accepted messages in the ledger, the selected Merkle root SHOULD be no more than
             * log_2(N)^2 messages old (rounded up to the nearest whole number). Public Key Directories MUST NOT
             * reject messages newer than this threshold on basis of staleness.
             * """
             */
            $cutoff = Math::getLowVolumeCutoff($numLeaves);
        }
        if ($cutoff < 16) {
            $zero = (new Tree([], $this->config()->getParams()->hashAlgo))->getEncodedRoot();
            if (hash_equals($zero, $merkleRoot)) {
                return true;
            }
            $cutoff = 1;
        }

        return $this->db->exists(
            "SELECT count(merkleleafid)
            FROM pkd_merkle_leaves
            WHERE root = ? AND merkleleafid >= ?",
            $merkleRoot,
            $cutoff
        );
    }
}
