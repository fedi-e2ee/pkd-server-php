# Dependency

Namespace: `FediE2EE\PKDServer\Dependency`

## Classes

- [HPKE](#hpke) - class
- [InjectConfigStrategy](#injectconfigstrategy) - class
- [SigningKeys](#signingkeys) - class
- [WrappedEncryptedRow](#wrappedencryptedrow) - class

---

## HPKE

**class** `FediE2EE\PKDServer\Dependency\HPKE`

**File:** [`src/Dependency/HPKE.php`](../../src/Dependency/HPKE.php)

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$cs` | `ParagonIE\HPKE\HPKE` | (readonly)  |
| `$decapsKey` | `ParagonIE\HPKE\Interfaces\DecapsKeyInterface` | (readonly)  |
| `$encapsKey` | `ParagonIE\HPKE\Interfaces\EncapsKeyInterface` | (readonly)  |

### Methods

#### `__construct(ParagonIE\HPKE\HPKE $cs, ParagonIE\HPKE\Interfaces\DecapsKeyInterface $decapsKey, ParagonIE\HPKE\Interfaces\EncapsKeyInterface $encapsKey): void`

**Parameters:**

- `$cs`: `ParagonIE\HPKE\HPKE`
- `$decapsKey`: `ParagonIE\HPKE\Interfaces\DecapsKeyInterface`
- `$encapsKey`: `ParagonIE\HPKE\Interfaces\EncapsKeyInterface`

#### `getCipherSuite(): ParagonIE\HPKE\HPKE`

**API Method**

#### `getDecapsKey(): ParagonIE\HPKE\Interfaces\DecapsKeyInterface`

**API Method**

#### `getEncapsKey(): ParagonIE\HPKE\Interfaces\EncapsKeyInterface`

**API Method**

---

## InjectConfigStrategy

**class** `FediE2EE\PKDServer\Dependency\InjectConfigStrategy`

**File:** [`src/Dependency/InjectConfigStrategy.php`](../../src/Dependency/InjectConfigStrategy.php)

**Extends:** `League\Route\Strategy\ApplicationStrategy`

**Implements:** `League\Route\ContainerAwareInterface`, `League\Route\Strategy\StrategyInterface`

### Methods

#### `__construct(): void`

**Throws:**

- `DependencyException`

#### `invokeRouteCallable(League\Route\Route $route, Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Override]`

**Parameters:**

- `$route`: `League\Route\Route`
- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`

---

## SigningKeys

**class** `FediE2EE\PKDServer\Dependency\SigningKeys`

**File:** [`src/Dependency/SigningKeys.php`](../../src/Dependency/SigningKeys.php)

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$secretKey` | `FediE2EE\PKD\Crypto\SecretKey` | (readonly)  |
| `$publicKey` | `FediE2EE\PKD\Crypto\PublicKey` | (readonly)  |

### Methods

#### `__construct(FediE2EE\PKD\Crypto\SecretKey $secretKey, FediE2EE\PKD\Crypto\PublicKey $publicKey): void`

**Parameters:**

- `$secretKey`: `FediE2EE\PKD\Crypto\SecretKey`
- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`

---

## WrappedEncryptedRow

**class** `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**File:** [`src/Dependency/WrappedEncryptedRow.php`](../../src/Dependency/WrappedEncryptedRow.php)

Extends the CipherSweet EncryptedRow class to support key-wrapping

**Extends:** `ParagonIE\CipherSweet\EncryptedRow`

### Methods

#### `getWrappedColumnNames(): array`

#### `addField(string $fieldName, string $type = 'string', ParagonIE\CipherSweet\AAD|string $aadSource = '', bool $autoBindContext = false, ?string $wrappedKeyColumnName = null): static`

**Attributes:** `#[Override]`

Define a field that will be encrypted.

**Parameters:**

- `$fieldName`: `string`
- `$type`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$autoBindContext`: `bool`
- `$wrappedKeyColumnName`: `?string` (nullable)

#### `getExtensionKey(): ParagonIE\CipherSweet\Backend\Key\SymmetricKey`

Get the key used to encrypt/decrypt the field symmetric key.

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`

#### `wrapKey(ParagonIE\CipherSweet\Backend\Key\SymmetricKey $key, string $fieldName): string`

**Parameters:**

- `$key`: `ParagonIE\CipherSweet\Backend\Key\SymmetricKey`
- `$fieldName`: `string`

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`

#### `unwrapKey(string $wrapped, string $fieldName): ParagonIE\CipherSweet\Backend\Key\SymmetricKey`

**Parameters:**

- `$wrapped`: `string`
- `$fieldName`: `string`

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`

#### `wrapBeforeEncrypt(array $row, array $symmetricKeyMap = []): array`

**API Method**

**Parameters:**

- `$row`: `array`
- `$symmetricKeyMap`: `array`

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`

#### `purgeWrapKeyCache(): static`

**API Method**

#### `addBooleanField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

#### `addFloatField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

#### `addIntegerField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

#### `addOptionalBooleanField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

#### `addOptionalFloatField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

#### `addOptionalIntegerField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

#### `addOptionalTextField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

#### `addTextField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

---

