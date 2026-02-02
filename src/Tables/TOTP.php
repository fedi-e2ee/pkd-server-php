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

use function array_key_exists;
use function is_null;
use function random_bytes;

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

    /**
     * @throws TableException
     */
    #[Override]
    protected function convertKeyMap(AttributeKeyMap $inputMap): array
    {
        $key = $inputMap->getKey('totp-secret');
        if (is_null($key)) {
            throw new TableException('Missing required key: totp-secret');
        }
        return [
            'secret' => $this->convertKey($key),
        ];
    }

    /**
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     */
    public function getSecretByDomain(string $domain): ?string
    {
        $totp = $this->getTotpByDomain($domain);
        if (is_null($totp)) {
            return null;
        }
        return $totp['secret'];
    }

    /**
     * @return array<string, mixed>|null
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     */
    public function getTotpByDomain(string $domain): ?array
    {
        $row = $this->db->row(
            "SELECT * FROM pkd_totp_secrets WHERE domain = ?",
            $domain
        );
        if (!$row) {
            return null;
        }
        $rowArray = self::rowToStringArray($row);
        $cipher = $this->getCipher();
        $decrypted = $cipher->decryptRow($rowArray);
        return [
            'secret' => self::decryptedString($decrypted, 'secret'),
            'last_time_step' => (int) ($rowArray['last_time_step'] ?? 0),
        ];
    }

    /**
     * @throws ArrayKeyException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function saveSecret(
        string $domain,
        #[SensitiveParameter] string $secret,
        int $lastTimeStep = 0
    ): void {
        $cipher = $this->getCipher();
        $plaintext = [
            'totpid' => $this->getNextTotpId(),
            'domain' => $domain,
            'secret' => $secret,
            'last_time_step' => $lastTimeStep,
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
    public function updateSecret(
        string $domain,
        #[SensitiveParameter] string $secret,
        int $lastTimeStep = 0
    ): void {
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
            'last_time_step' => $lastTimeStep,
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
                'wrap_secret' => $encrypted['wrap_secret'],
                'last_time_step' => $lastTimeStep,
            ],
            ['totpid' => $rowID],
        );
    }

    public function updateLastTimeStep(string $domain, int $lastTimeStep): void
    {
        $this->db->update(
            'pkd_totp_secrets',
            ['last_time_step' => $lastTimeStep],
            ['domain' => $domain]
        );
    }
}
