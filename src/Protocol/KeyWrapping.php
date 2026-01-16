<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Protocol;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Exceptions\JsonException;
use FediE2EE\PKD\Crypto\Protocol\HPKEAdapter;
use FediE2EE\PKD\Crypto\SymmetricKey;
use FediE2EE\PKDServer\Dependency\HPKE;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\Exceptions\TableException;
use FediE2EE\PKDServer\ServerConfig;
use FediE2EE\PKDServer\Tables\Peers;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\EasyDB\EasyDB;
use ParagonIE\HPKE\HPKEException;
use PDOException;
use SensitiveParameter;

class KeyWrapping
{
    use ConfigTrait;
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
    public function localKeyWrap(string $merkleRoot, AttributeKeyMap $keyMap): void
    {
        $ciphertext = (new HPKEAdapter($this->hpke->cs))->seal(
            $this->hpke->encapsKey,
            $this->serializeKeyMap($keyMap)
        );
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
        $plaintext = (new HPKEAdapter($this->hpke->cs))
            ->open($this->hpke->decapsKey, $this->hpke->encapsKey, $cipher);
        return $this->deserializeKeyMap($plaintext);
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
}
