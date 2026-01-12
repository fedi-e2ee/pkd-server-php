<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Dependency;

use FediE2EE\PKDServer\Dependency\WrappedEncryptedRow;
use ParagonIE\CipherSweet\Backend\{
    BoringCrypto,
    FIPSCrypto,
    Key\SymmetricKey
};
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use ParagonIE\CipherSweet\KeyProvider\StringProvider;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    DataProvider
};
use PHPUnit\Framework\TestCase;
use Random\RandomException;
use SodiumException;

#[CoversClass(WrappedEncryptedRow::class)]
class WrappedEncryptedRowTest extends TestCase
{
    /**
     * @throws CryptoOperationException
     * @throws RandomException
     */
    public static function cipherSweetProvider(): array
    {
        return [
            [new CipherSweet(new StringProvider(random_bytes(32)), new FIPSCrypto())],
            [new CipherSweet(new StringProvider(random_bytes(32)), new BoringCrypto())]
        ];
    }

    /**
     * @throws ArrayKeyException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws RandomException
     * @throws SodiumException
     */
    #[DataProvider("cipherSweetProvider")]
    public function testWER(CipherSweet $cs): void
    {
        $wer = new WrappedEncryptedRow($cs, 'phpunit');
        $wer->addTextField('foo');
        $map = ['foo' => new SymmetricKey(random_bytes(32))];
        $row = $wer->wrapBeforeEncrypt(['foo' => 'bar'], $map);
        $encrypted = $wer->encryptRow($row);

        $this->assertArrayHasKey('foo', $encrypted);
        $this->assertArrayHasKey('wrap_foo', $encrypted);
        $this->assertNotSame('bar', $encrypted['foo']);

        $decrypted = $wer->decryptRow($encrypted);
        $this->assertSame('bar', $decrypted['foo']);
    }

    /**
     * Test getExtensionKey returns a valid symmetric key
     *
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws RandomException
     */
    #[DataProvider("cipherSweetProvider")]
    public function testGetExtensionKey(CipherSweet $cs): void
    {
        $wer = new WrappedEncryptedRow($cs, 'phpunit');
        $key = $wer->getExtensionKey();

        $this->assertInstanceOf(SymmetricKey::class, $key);
        $this->assertSame(32, strlen($key->getRawKey()));

        // Should return the same key on subsequent calls
        $key2 = $wer->getExtensionKey();
        $this->assertSame($key->getRawKey(), $key2->getRawKey());
    }

    /**
     * Test wrapKey directly wraps a symmetric key
     *
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws RandomException
     */
    #[DataProvider("cipherSweetProvider")]
    public function testWrapKey(CipherSweet $cs): void
    {
        $wer = new WrappedEncryptedRow($cs, 'phpunit');
        $wer->addTextField('test_field');

        $originalKey = new SymmetricKey(random_bytes(32));
        $wrapped = $wer->wrapKey($originalKey, 'test_field');

        // Wrapped key should be a non-empty string (ciphertext)
        $this->assertIsString($wrapped);
        $this->assertNotEmpty($wrapped);

        // Wrapped key should be different from the raw key
        $this->assertNotSame($originalKey->getRawKey(), $wrapped);
    }

    /**
     * Test unwrapKey recovers the original key material
     *
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws RandomException
     */
    #[DataProvider("cipherSweetProvider")]
    public function testUnwrapKey(CipherSweet $cs): void
    {
        $wer = new WrappedEncryptedRow($cs, 'phpunit');
        $wer->addTextField('test_field');

        $originalKey = new SymmetricKey(random_bytes(32));
        $wrapped = $wer->wrapKey($originalKey, 'test_field');

        // Clear any caches to ensure unwrapKey actually decrypts
        $wer->purgeWrapKeyCache();

        $unwrapped = $wer->unwrapKey($wrapped, 'test_field');

        $this->assertInstanceOf(SymmetricKey::class, $unwrapped);
        $this->assertSame($originalKey->getRawKey(), $unwrapped->getRawKey());
    }

    /**
     * Test unwrapKey returns cached key when cache was populated by wrapBeforeEncrypt
     *
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws RandomException
     * @throws SodiumException
     */
    #[DataProvider("cipherSweetProvider")]
    public function testUnwrapKeyCacheHit(CipherSweet $cs): void
    {
        $wer = new WrappedEncryptedRow($cs, 'phpunit');
        $wer->addTextField('test_field');

        $originalKey = new SymmetricKey(random_bytes(32));

        // wrapBeforeEncrypt populates the cache with the SymmetricKey
        $row = $wer->wrapBeforeEncrypt(['test_field' => 'data'], ['test_field' => $originalKey]);

        // unwrapKey should return the SAME cached object (not just equal value)
        $unwrapped = $wer->unwrapKey($row['wrap_test_field'], 'test_field');

        // Must be the exact same object from cache
        $this->assertSame($originalKey, $unwrapped);
    }

    /**
     * Test wrapKey with different field names produces different ciphertexts
     * (AAD includes field name)
     *
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws RandomException
     */
    #[DataProvider("cipherSweetProvider")]
    public function testWrapKeyFieldNameAAD(CipherSweet $cs): void
    {
        $wer = new WrappedEncryptedRow($cs, 'phpunit');
        $wer->addTextField('field_a');
        $wer->addTextField('field_b');

        $key = new SymmetricKey(random_bytes(32));
        $wrapped_a = $wer->wrapKey($key, 'field_a');

        // Clear cache and wrap again with different field name
        $wer->purgeWrapKeyCache();
        $wrapped_b = $wer->wrapKey($key, 'field_b');

        // Same key, different field names should produce different ciphertexts
        $this->assertNotSame($wrapped_a, $wrapped_b);
    }

    /**
     * Test purgeWrapKeyCache clears the internal cache
     *
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws RandomException
     * @throws SodiumException
     */
    #[DataProvider("cipherSweetProvider")]
    public function testPurgeWrapKeyCache(CipherSweet $cs): void
    {
        $wer = new WrappedEncryptedRow($cs, 'phpunit');
        $wer->addTextField('foo');

        $key = new SymmetricKey(random_bytes(32));
        $map = ['foo' => $key];

        // wrapBeforeEncrypt populates the cache
        $row = $wer->wrapBeforeEncrypt(['foo' => 'bar'], $map);
        $encrypted = $wer->encryptRow($row);

        // Purge the cache
        $result = $wer->purgeWrapKeyCache();

        // Should return self for fluent interface
        $this->assertSame($wer, $result);

        // Now decryption should still work (uses wrapped key from row)
        $decrypted = $wer->decryptRow($encrypted);
        $this->assertSame('bar', $decrypted['foo']);
    }

    /**
     * Test getWrappedColumnNames returns the correct mapping
     *
     * @throws RandomException
     */
    #[DataProvider("cipherSweetProvider")]
    public function testGetWrappedColumnNames(CipherSweet $cs): void
    {
        $wer = new WrappedEncryptedRow($cs, 'phpunit');
        $wer->addTextField('foo');
        $wer->addTextField('bar', '', 'custom_wrap_bar');

        $names = $wer->getWrappedColumnNames();

        $this->assertSame('wrap_foo', $names['foo']);
        $this->assertSame('custom_wrap_bar', $names['bar']);
    }

    /**
     * Test that getFieldSymmetricKey returns null for crypto-shredded field
     * (empty wrapped key). This is tested indirectly via the protected method
     * by verifying the return type contract.
     *
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws RandomException
     * @throws SodiumException
     */
    #[DataProvider("cipherSweetProvider")]
    public function testCryptoShreddedFieldReturnsNull(CipherSweet $cs): void
    {
        $wer = new WrappedEncryptedRow($cs, 'phpunit');
        $wer->addTextField('foo');

        $key = new SymmetricKey(random_bytes(32));
        $map = ['foo' => $key];
        $row = $wer->wrapBeforeEncrypt(['foo' => 'bar'], $map);
        $encrypted = $wer->encryptRow($row);

        // Simulate crypto-shredding by emptying the wrapped key
        $encrypted['wrap_foo'] = '';

        // Clear cache to force re-read from row
        $wer->purgeWrapKeyCache();

        // Use reflection to test getFieldSymmetricKey directly
        $method = new \ReflectionMethod(WrappedEncryptedRow::class, 'getFieldSymmetricKey');
        $result = $method->invoke($wer, $encrypted, 'foo');

        // Should return null for crypto-shredded field
        $this->assertNull($result);
    }

    /**
     * Test different field types work correctly
     *
     * @throws ArrayKeyException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws RandomException
     * @throws SodiumException
     */
    #[DataProvider("cipherSweetProvider")]
    public function testDifferentFieldTypes(CipherSweet $cs): void
    {
        $wer = new WrappedEncryptedRow($cs, 'phpunit');
        $wer->addTextField('text_field');
        $wer->addIntegerField('int_field');
        $wer->addBooleanField('bool_field');
        $wer->addFloatField('float_field');

        $map = [
            'text_field' => new SymmetricKey(random_bytes(32)),
            'int_field' => new SymmetricKey(random_bytes(32)),
            'bool_field' => new SymmetricKey(random_bytes(32)),
            'float_field' => new SymmetricKey(random_bytes(32)),
        ];

        $row = $wer->wrapBeforeEncrypt([
            'text_field' => 'hello',
            'int_field' => 42,
            'bool_field' => true,
            'float_field' => 3.14,
        ], $map);

        $encrypted = $wer->encryptRow($row);
        $wer->purgeWrapKeyCache();
        $decrypted = $wer->decryptRow($encrypted);

        $this->assertSame('hello', $decrypted['text_field']);
        $this->assertSame(42, $decrypted['int_field']);
        $this->assertTrue($decrypted['bool_field']);
        $this->assertEqualsWithDelta(3.14, $decrypted['float_field'], 0.001);
    }

    /**
     * Test optional field types
     *
     * @throws ArrayKeyException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws RandomException
     * @throws SodiumException
     */
    #[DataProvider("cipherSweetProvider")]
    public function testOptionalFieldTypes(CipherSweet $cs): void
    {
        $wer = new WrappedEncryptedRow($cs, 'phpunit');
        $wer->addOptionalTextField('opt_text');
        $wer->addOptionalIntegerField('opt_int');
        $wer->addOptionalBooleanField('opt_bool');
        $wer->addOptionalFloatField('opt_float');

        $map = [
            'opt_text' => new SymmetricKey(random_bytes(32)),
            'opt_int' => new SymmetricKey(random_bytes(32)),
            'opt_bool' => new SymmetricKey(random_bytes(32)),
            'opt_float' => new SymmetricKey(random_bytes(32)),
        ];

        // Test with null values
        $row = $wer->wrapBeforeEncrypt([
            'opt_text' => null,
            'opt_int' => null,
            'opt_bool' => null,
            'opt_float' => null,
        ], $map);

        $encrypted = $wer->encryptRow($row);
        $wer->purgeWrapKeyCache();
        $decrypted = $wer->decryptRow($encrypted);

        $this->assertNull($decrypted['opt_text']);
        $this->assertNull($decrypted['opt_int']);
        $this->assertNull($decrypted['opt_bool']);
        $this->assertNull($decrypted['opt_float']);
    }
}
