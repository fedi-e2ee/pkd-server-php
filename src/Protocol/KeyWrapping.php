<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Protocol;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Exceptions\BundleException;
use FediE2EE\PKD\Crypto\Exceptions\CryptoException;
use FediE2EE\PKD\Crypto\Exceptions\InputException;
use FediE2EE\PKD\Crypto\Exceptions\JsonException;
use FediE2EE\PKD\Crypto\Protocol\Bundle;
use FediE2EE\PKD\Crypto\Protocol\HPKEAdapter;
use FediE2EE\PKD\Crypto\SymmetricKey;
use FediE2EE\PKD\Crypto\UtilTrait;
use FediE2EE\PKDServer\Dependency\HPKE;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\Exceptions\TableException;
use FediE2EE\PKDServer\ServerConfig;
use FediE2EE\PKDServer\Tables\Peers;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\EasyDB\EasyDB;
use ParagonIE\HPKE\HPKEException;
use SensitiveParameter;

class KeyWrapping
{
    use ConfigTrait;
    use UtilTrait;

    private EasyDB $db;
    private HPKE $hpke;

    public function __construct(?ServerConfig $config = null)
    {
        $this->config = $config ?? $GLOBALS['pkdConfig'];
        $this->db = $this->config->getDb();
        $this->hpke = $this->config->getHPKE();
    }

    /**
     * Wrap the local symmetric keys in 'wrappedkeys'
     *
     * @throws DependencyException
     */
    /*
    public function localKeyWrap(string $merkleRoot, AttributeKeyMap $keyMap): void
    {
        $ciphertext = $this->hpkeWrapSymmetricKeys($keyMap);
        $this->db->update(
            'pkd_merkle_leaves',
            [
                'wrappedkeys' => $ciphertext,
            ],
            [
                'root' => $merkleRoot,
            ]
        );
    }
    */

    /**
     * Initiate a rewrapping of the symmetric keys associated with a record.
     */
    public function rewrapSymmetricKeys(string $merkleRoot, ?AttributeKeyMap $keyMap = null): void
    {
        $peersTable = $this->table('Peers');
        if (!($peersTable instanceof Peers)) {
            throw new TableException('Could not load table: Peers');
        }

        $merkleLeafId = $this->db->cell(
            "SELECT merkleleafid FROM pkd_merkle_leaves WHERE root = ?",
            $merkleRoot
        );

        // Do we need to fetch it from the pkd_merkle_leaves table?
        if (is_null($keyMap)) {
            $keyMap = $this->retrieveLocalWrappedKeys($merkleRoot);
        }

        // Okay, let's find the peers who are allowed to receive a copy:
        $candidates = $peersTable->getRewrapCandidates();
        if (empty($candidates)) {
            return;
        }

        // Rewrap the symmetric keys for our trusted replica peers (if any):
        foreach ($candidates as $peer) {
            $peersTable->rewrapKeyMap($peer, $keyMap, $merkleLeafId);
        }
    }

    /**
     * @throws HPKEException
     * @throws JsonException
     * @throws TableException
     */
    public function retrieveLocalWrappedKeys(string $merkleRoot): AttributeKeyMap
    {
        $cipher = $this->db->cell(
            "SELECT wrappedkeys FROM pkd_merkle_leaves WHERE root = ?",
            $merkleRoot
        );
        if (!is_string($cipher)) {
            throw new TableException('Wrapped keys not stored on merkle leaf');
        }
        $plaintext = $this->hpkeUnwrap($cipher);
        return $this->deserializeKeyMap($plaintext);
    }

    public function hpkeWrapSymmetricKeys(AttributeKeyMap $keyMap): string
    {
        return (new HPKEAdapter($this->hpke->cs))->seal(
            $this->hpke->encapsKey,
            $this->serializeKeyMap($keyMap)
        );
    }

    /**
     * @throws HPKEException
     */
    public function hpkeUnwrap(string $ciphertext): string
    {
        return (new HPKEAdapter($this->hpke->cs))
            ->open($this->hpke->decapsKey, $this->hpke->encapsKey, $ciphertext);
    }

    public function serializeKeyMap(AttributeKeyMap $keyMap): string
    {
        if ($keyMap->isEmpty()) {
            return '[]';
        }
        $collected = [];
        foreach ($keyMap->getAttributes() as $name) {
            $collected[$name] = Base64UrlSafe::encodeUnpadded(
                $keyMap->getKey($name)->getBytes()
            );
        }
        return json_encode($collected, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function deserializeKeyMap(
        #[SensitiveParameter]
        string $plaintextJsonString
    ): AttributeKeyMap {
        $jsonObject = json_decode($plaintextJsonString);
        if (!is_object($jsonObject)) {
            throw new JsonException('Invalid json:' . json_last_error_msg());
        }
        $keyMap = new AttributeKeyMap();
        foreach ($jsonObject as $name => $value) {
            $key = new SymmetricKey(Base64UrlSafe::decodeNoPadding($value));
            $keyMap->addKey($name, $key);
        }
        return $keyMap;
    }

    /**
     * Usage:
     *
     * [$message, $rewrappedKeys] = $keyWrapping->decryptAndRewrapp
     *
     * @return array|null[]
     * @throws BundleException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InputException
     * @throws JsonException
     */
    public function decryptAndGetRewrapped(string $merkleRoot, ?string $wrappedKeys = null): array
    {
        if (is_null($wrappedKeys)) {
            // Cannot decrypt!
            return [null, null];
        }

        $cache = $this->appCache('key-wrapping-decrypt');
        $lookupKey = $merkleRoot . ':' . $wrappedKeys;

        /** @var string|null $cached */
        $cached = $cache->cache($lookupKey, function() use ($merkleRoot, $wrappedKeys) {
            // We assume the rewrapping occurred on insert.
            $encryptedMessage = $this->db->cell(
                "SELECT contents FROM pkd_merkle_leaves WHERE root = ?",
                $merkleRoot
            );
            $message = $this->unwrapLocalMessage($encryptedMessage, $wrappedKeys);
            $rewrappedKeys = $this->getRewrappedFor($merkleRoot);
            return json_encode([$message, $rewrappedKeys]);
        }, 43200);

        if (is_string($cached)) {
            return (array) json_decode($cached, true);
        }
        return [null, null];
    }

    /**
     * @throws BundleException
     * @throws CryptoException
     * @throws HPKEException
     * @throws InputException
     * @throws JsonException
     */
    public function unwrapLocalMessage(string $encryptedMessage, string $wrappedKeys): array
    {
        $unwrappedKeys = $this->hpkeUnwrap($wrappedKeys);
        $keyMap = $this->deserializeKeyMap($unwrappedKeys);
        return Bundle::fromJson($encryptedMessage, $keyMap)
            ->toSignedMessage()
            ->getDecryptedContents($keyMap);
    }

    /**
     * @throws InputException
     */
    public function getRewrappedFor(string $merkleRoot): array
    {
        $rewrappedFlat = $this->db->run(
            "SELECT
                    p.uniqueid,
                    rw.pkdattrname,
                    rw.rewrapped
                FROM pkd_merkle_leaf_rewrapped_keys rw
                JOIN pkd_merkle_leaves ml ON rw.leaf = ml.merkleleafid
                JOIN pkd_peers p ON rw.peer = p.peerid 
                WHERE ml.root = ?
                ORDER BY p.uniqueid ASC",
            $merkleRoot
        );
        $rewrappedShaped = [];
        foreach ($rewrappedFlat as $row) {
            self::assertAllArrayKeysExist(
                $row,
                'uniqueid',
                'pkdattrname',
                'rewrapped'
            );
            $uniqueid = $row['uniqueid'];
            $attr = $row['pkdattrname'];
            $rewrapped = $row['rewrapped'];
            if (!array_key_exists($uniqueid, $rewrappedShaped)) {
                $rewrappedShaped[$uniqueid] = [];
            }
            $rewrappedShaped[$row['uniqueid']][$attr] = $rewrapped;
        }
        return $rewrappedShaped;
    }
}
