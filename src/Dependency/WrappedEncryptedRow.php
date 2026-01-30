<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Dependency;

use ParagonIE\CipherSweet\{
    AAD,
    Backend\Key\SymmetricKey,
    Constants,
    EncryptedRow,
    Exception\CipherSweetException,
    Exception\CryptoOperationException
};
use Override;

use function array_key_exists;
use function is_null;

/**
 * Extends the CipherSweet EncryptedRow class to support key-wrapping
 *
 * @api
 */
class WrappedEncryptedRow extends EncryptedRow
{
    public const string EXTENSION = 'fedi-e2ee-pkd-v1';

    /**
     * @var array<string, string>
     */
    protected array $wrappedColumnName = [];

    protected array $wrapKeys = [];

    public function getWrappedColumnNames(): array
    {
        return $this->wrappedColumnName;
    }

    /**
     * Define a field that will be encrypted.
     *
     * @param string $fieldName
     * @param string $type
     * @param string|AAD $aadSource Field name to source AAD from
     * @param bool $autoBindContext
     * @param ?string $wrappedKeyColumnName Field name that possesses wrapped column
     * @return static
     */
    #[Override]
    public function addField(
        string $fieldName,
        string $type = Constants::TYPE_TEXT,
        string|AAD $aadSource = '',
        bool $autoBindContext = false,
        ?string $wrappedKeyColumnName = null
    ): static {
        if (is_null($wrappedKeyColumnName)) {
            $wrappedKeyColumnName = 'wrap_' . $fieldName;
        }
        $this->wrappedColumnName[$fieldName] = $wrappedKeyColumnName;
        return parent::addField($fieldName, $type, $aadSource, $autoBindContext);
    }

    /**
     * Get the key used to encrypt/decrypt the field symmetric key.
     *
     * @throws CipherSweetException
     * @throws CryptoOperationException
     */
    public function getExtensionKey(): SymmetricKey
    {
        return $this->engine->getExtensionKey(self::EXTENSION);
    }

    /**
     * @throws CipherSweetException
     * @throws CryptoOperationException
     */
    public function wrapKey(SymmetricKey $key, string $fieldName): string
    {
        // Check the cache first:
        if (array_key_exists($fieldName, $this->wrapKeys)) {
            return $this->wrapKeys[$fieldName];
        }
        // Wrap it:
        return $this->engine->getBackend()->encrypt(
            $key->getRawKey(),
            $this->getExtensionKey(),
            AAD::field($fieldName)->canonicalize()
        );
    }

    /**
     * @throws CipherSweetException
     * @throws CryptoOperationException
     */
    public function unwrapKey(string $wrapped, string $fieldName): SymmetricKey
    {
        // Check the cache first:
        if (array_key_exists($fieldName, $this->wrapKeys)) {
            return $this->wrapKeys[$fieldName];
        }
        // Unwrap it:
        $unwrapped = $this->engine->getBackend()->decrypt(
            $wrapped,
            $this->getExtensionKey(),
            AAD::field($fieldName)->canonicalize()
        );
        return new SymmetricKey($unwrapped);
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, SymmetricKey> $symmetricKeyMap
     * @return array<string, mixed>
     *
     * @throws CipherSweetException
     * @throws CryptoOperationException
     *
     * @api
     */
    public function wrapBeforeEncrypt(array $row, array $symmetricKeyMap = []): array
    {
        $outRow = [];
        foreach ($row as $field => $value) {
            // Copy to output
            $outRow[$field] = $value;

            // If we're encrypting this field under key-wrap:
            if (!array_key_exists($field, $this->wrappedColumnName)) {
                continue;
            }
            if (!array_key_exists($field, $symmetricKeyMap)) {
                continue;
            }
            $wrapIdx = $this->wrappedColumnName[$field];
            $wrapKey = $this->wrapKey($symmetricKeyMap[$field], $field);
            // Store wrapped key in row:
            $outRow[$wrapIdx] = $wrapKey;
            // Cache plaintext key here:
            $this->wrapKeys[$field] = $symmetricKeyMap[$field];
        }
        return $outRow;
    }

    /**
     * @return static
     * @api
     */
    public function purgeWrapKeyCache(): static
    {
        $this->wrapKeys = [];
        return $this;
    }

    /**
     * Get the wrapped symmetric key for a given field.
     *
     * @throws CipherSweetException
     * @throws CryptoOperationException
     */
    #[Override]
    protected function getFieldSymmetricKey(array $row, string $field): ?SymmetricKey
    {
        if (array_key_exists($field, $this->wrappedColumnName)) {
            $wrapIdx = $this->wrappedColumnName[$field];
            if (empty($row[$wrapIdx])) {
                // We assume the field was crypto-shredded.
                return null;
            }
            return $this->unwrapKey($row[$wrapIdx], $field);
        }
        return parent::getFieldSymmetricKey($row, $field);
    }

    /**
     * @api
     */
    #[Override]
    public function addBooleanField(
        string $fieldName,
        string|AAD $aadSource = '',
        ?string $wrappedKeyColumnName = null,
        bool $autoBindContext = false
    ): static {
        return $this->addField(
            $fieldName,
            Constants::TYPE_BOOLEAN,
            $aadSource,
            $autoBindContext,
            $wrappedKeyColumnName
        );
    }

    /**
     * @api
     */
    #[Override]
    public function addFloatField(
        string $fieldName,
        string|AAD $aadSource = '',
        ?string $wrappedKeyColumnName = null,
        bool $autoBindContext = false
    ): static {
        return $this->addField(
            $fieldName,
            Constants::TYPE_FLOAT,
            $aadSource,
            $autoBindContext,
            $wrappedKeyColumnName
        );
    }

    /**
     * @api
     */
    #[Override]
    public function addIntegerField(
        string $fieldName,
        string|AAD $aadSource = '',
        ?string $wrappedKeyColumnName = null,
        bool $autoBindContext = false
    ): static {
        return $this->addField(
            $fieldName,
            Constants::TYPE_INT,
            $aadSource,
            $autoBindContext,
            $wrappedKeyColumnName
        );
    }

    /**
     * @api
     */
    #[Override]
    public function addOptionalBooleanField(
        string $fieldName,
        string|AAD $aadSource = '',
        ?string $wrappedKeyColumnName = null,
        bool $autoBindContext = false
    ): static {
        return $this->addField(
            $fieldName,
            Constants::TYPE_OPTIONAL_BOOLEAN,
            $aadSource,
            $autoBindContext,
            $wrappedKeyColumnName
        );
    }

    /**
     * @api
     */
    #[Override]
    public function addOptionalFloatField(
        string $fieldName,
        string|AAD $aadSource = '',
        ?string $wrappedKeyColumnName = null,
        bool $autoBindContext = false
    ): static {
        return $this->addField(
            $fieldName,
            Constants::TYPE_OPTIONAL_FLOAT,
            $aadSource,
            $autoBindContext,
            $wrappedKeyColumnName
        );
    }

    /**
     * @api
     */
    #[Override]
    public function addOptionalIntegerField(
        string $fieldName,
        string|AAD $aadSource = '',
        ?string $wrappedKeyColumnName = null,
        bool $autoBindContext = false
    ): static {
        return $this->addField(
            $fieldName,
            Constants::TYPE_OPTIONAL_INT,
            $aadSource,
            $autoBindContext,
            $wrappedKeyColumnName
        );
    }

    /**
     * @api
     */
    #[Override]
    public function addOptionalTextField(
        string $fieldName,
        string|AAD $aadSource = '',
        ?string $wrappedKeyColumnName = null,
        bool $autoBindContext = false
    ): static {
        return $this->addField(
            $fieldName,
            Constants::TYPE_OPTIONAL_TEXT,
            $aadSource,
            $autoBindContext,
            $wrappedKeyColumnName
        );
    }

    /**
     * @api
     */
    #[Override]
    public function addTextField(
        string $fieldName,
        string|AAD $aadSource = '',
        ?string $wrappedKeyColumnName = null,
        bool $autoBindContext = false
    ): static {
        return $this->addField(
            $fieldName,
            Constants::TYPE_TEXT,
            $aadSource,
            $autoBindContext,
            $wrappedKeyColumnName
        );
    }
}
