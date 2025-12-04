<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tables;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use FediE2EE\PKDServer\Exceptions\TableException;
use FediE2EE\PKDServer\Table;
use Override;
use ParagonIE\CipherSweet\Backend\Key\SymmetricKey;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use Random\RandomException;
use SensitiveParameter;
use SodiumException;

class TOTP extends Table
{
    #[Override]
    public function getCipher(): WrappedEncryptedRow
    {
        return new WrappedEncryptedRow(
            $this->engine,
            'pkd_totp_secrets',
            false,
            'totpid'
        )->addTextField('secret');
    }

    protected function getNextTotpId(): int
    {
        $cell = $this->db->cell("SELECT MAX(totpid) FROM pkd_totp_secrets");
        if (empty($cell)) {
            return 1;
        }
        return (int) ($cell) + 1;
    }

    #[Override]
    protected function convertKeyMap(AttributeKeyMap $inputMap): array
    {
        return [
            'secret' => $this->convertKey(
                $inputMap->getKey('totp-secret')
            ),
        ];
    }

    /**
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws SodiumException
     * @throws InvalidCiphertextException
     */
    public function getSecretByDomain(string $domain): ?string
    {
        $row = $this->db->row(
            "SELECT * FROM pkd_totp_secrets WHERE domain = ?",
            $domain
        );
        if (!$row) {
            return null;
        }
        $cipher = $this->getCipher();
        $cipher->decryptRow($row);
        $decrypted = $cipher->decryptRow($row);
        return (string) $decrypted['secret'];
    }

    /**
     * @throws ArrayKeyException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function saveSecret(string $domain, #[SensitiveParameter] string $secret): void
    {
        $cipher = $this->getCipher();
        $plaintext = [
            'totpid' => $this->getNextTotpId(),
            'domain' => $domain,
            'secret' => $secret,
        ];
        $row = $cipher->wrapBeforeEncrypt($plaintext, ['secret' => new SymmetricKey(random_bytes(32))]);
        $toStore = $cipher->encryptRow($row);
        if (!array_key_exists('wrap_secret', $toStore)) {
            throw new TableException('wrapping is not specified!');
        }
        $this->db->insert('pkd_totp_secrets', $toStore);
    }

    public function deleteSecret(string $domain): void
    {
        $this->db->delete('pkd_totp_secrets', ['domain' => $domain]);
    }

    /**
     * @throws ArrayKeyException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws SodiumException
     * @throws TableException
     * @throws RandomException
     */
    public function updateSecret(string $domain, #[SensitiveParameter] string $secret): void
    {
        $rowID = $this->db->cell(
            "SELECT totpid FROM pkd_totp_secrets WHERE domain = ?",
            $domain
        );
        if (empty($rowID)) {
            throw new TableException('TOTP for domain not found');
        }
        $plaintext = [
            'totpid' => $rowID,
            'domain' => $domain,
            'secret' => $secret,
        ];
        $row = $this->getCipher()->wrapBeforeEncrypt(
            $plaintext,
            [
                'secret' => new SymmetricKey(random_bytes(32))
            ]
        );
        $encrypted = $this->getCipher()->encryptRow($row);
        if (!array_key_exists('wrap_secret', $encrypted)) {
            throw new TableException('wrapping is not specified!');
        }
        $this->db->update(
            'pkd_totp_secrets',
            [
                'secret' => $encrypted['secret'],
                'wrap_secret' => $encrypted['wrap_secret']
            ],
            ['totpid' => $rowID],
        );
    }
}
