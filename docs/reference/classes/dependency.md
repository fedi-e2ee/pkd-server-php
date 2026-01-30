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

**File:** [`src/Dependency/HPKE.php`](../../../src/Dependency/HPKE.php)

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$cs` | `ParagonIE\HPKE\HPKE` | (readonly)  |
| `$decapsKey` | `ParagonIE\HPKE\Interfaces\DecapsKeyInterface` | (readonly)  |
| `$encapsKey` | `ParagonIE\HPKE\Interfaces\EncapsKeyInterface` | (readonly)  |

### Methods

#### [`__construct`](../../../src/Dependency/HPKE.php#L11-L15)

Returns `void`

**Parameters:**

- `$cs`: `ParagonIE\HPKE\HPKE`
- `$decapsKey`: `ParagonIE\HPKE\Interfaces\DecapsKeyInterface`
- `$encapsKey`: `ParagonIE\HPKE\Interfaces\EncapsKeyInterface`

#### [`getCipherSuite`](../../../src/Dependency/HPKE.php#L20-L23)

**API** · Returns `ParagonIE\HPKE\HPKE`

#### [`getDecapsKey`](../../../src/Dependency/HPKE.php#L28-L31)

**API** · Returns `ParagonIE\HPKE\Interfaces\DecapsKeyInterface`

#### [`getEncapsKey`](../../../src/Dependency/HPKE.php#L36-L39)

**API** · Returns `ParagonIE\HPKE\Interfaces\EncapsKeyInterface`

---

## InjectConfigStrategy

**class** `FediE2EE\PKDServer\Dependency\InjectConfigStrategy`

**File:** [`src/Dependency/InjectConfigStrategy.php`](../../../src/Dependency/InjectConfigStrategy.php)

**Extends:** `League\Route\Strategy\ApplicationStrategy`

**Implements:** `League\Route\ContainerAwareInterface`, `League\Route\Strategy\StrategyInterface`

### Methods

#### [`__construct`](../../../src/Dependency/InjectConfigStrategy.php#L25-L32)

Returns `void`

**Throws:** `DependencyException`

#### [`invokeRouteCallable`](../../../src/Dependency/InjectConfigStrategy.php#L38-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Override]`

**Parameters:**

- `$route`: `League\Route\Route`
- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `DependencyException`

---

## SigningKeys

**class** `FediE2EE\PKDServer\Dependency\SigningKeys`

**File:** [`src/Dependency/SigningKeys.php`](../../../src/Dependency/SigningKeys.php)

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$secretKey` | `FediE2EE\PKD\Crypto\SecretKey` | (readonly)  |
| `$publicKey` | `FediE2EE\PKD\Crypto\PublicKey` | (readonly)  |

### Methods

#### [`__construct`](../../../src/Dependency/SigningKeys.php#L10-L13)

Returns `void`

**Parameters:**

- `$secretKey`: `FediE2EE\PKD\Crypto\SecretKey`
- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`

---

## WrappedEncryptedRow

**class** `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**File:** [`src/Dependency/WrappedEncryptedRow.php`](../../../src/Dependency/WrappedEncryptedRow.php)

Extends the CipherSweet EncryptedRow class to support key-wrapping

**Extends:** `ParagonIE\CipherSweet\EncryptedRow`

### Methods

#### [`getWrappedColumnNames`](../../../src/Dependency/WrappedEncryptedRow.php#L34-L37)

Returns `array`

#### [`addField`](../../../src/Dependency/WrappedEncryptedRow.php#L50-L62)

Returns `static`

**Attributes:** `#[Override]`

Define a field that will be encrypted.

**Parameters:**

- `$fieldName`: `string`
- `$type`: `string` = 'string'
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string` = ''
- `$autoBindContext`: `bool` = false
- `$wrappedKeyColumnName`: `?string` = null

#### [`getExtensionKey`](../../../src/Dependency/WrappedEncryptedRow.php#L70-L73)

Returns `ParagonIE\CipherSweet\Backend\Key\SymmetricKey`

Get the key used to encrypt/decrypt the field symmetric key.

**Throws:** `CipherSweetException`, `CryptoOperationException`

#### [`wrapKey`](../../../src/Dependency/WrappedEncryptedRow.php#L79-L91)

Returns `string`

**Parameters:**

- `$key`: `ParagonIE\CipherSweet\Backend\Key\SymmetricKey`
- `$fieldName`: `string`

**Throws:** `CipherSweetException`, `CryptoOperationException`

#### [`unwrapKey`](../../../src/Dependency/WrappedEncryptedRow.php#L97-L110)

Returns `ParagonIE\CipherSweet\Backend\Key\SymmetricKey`

**Parameters:**

- `$wrapped`: `string`
- `$fieldName`: `string`

**Throws:** `CipherSweetException`, `CryptoOperationException`

#### [`wrapBeforeEncrypt`](../../../src/Dependency/WrappedEncryptedRow.php#L122-L144)

**API** · Returns `array`

**Parameters:**

- `$row`: `array`
- `$symmetricKeyMap`: `array` = []

**Throws:** `CipherSweetException`, `CryptoOperationException`

#### [`purgeWrapKeyCache`](../../../src/Dependency/WrappedEncryptedRow.php#L150-L154)

**API** · Returns `static`

#### [`addBooleanField`](../../../src/Dependency/WrappedEncryptedRow.php#L180-L193)

**API** · Returns `static`

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string` = ''
- `$wrappedKeyColumnName`: `?string` = null
- `$autoBindContext`: `bool` = false

#### [`addFloatField`](../../../src/Dependency/WrappedEncryptedRow.php#L199-L212)

**API** · Returns `static`

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string` = ''
- `$wrappedKeyColumnName`: `?string` = null
- `$autoBindContext`: `bool` = false

#### [`addIntegerField`](../../../src/Dependency/WrappedEncryptedRow.php#L218-L231)

**API** · Returns `static`

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string` = ''
- `$wrappedKeyColumnName`: `?string` = null
- `$autoBindContext`: `bool` = false

#### [`addOptionalBooleanField`](../../../src/Dependency/WrappedEncryptedRow.php#L237-L250)

**API** · Returns `static`

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string` = ''
- `$wrappedKeyColumnName`: `?string` = null
- `$autoBindContext`: `bool` = false

#### [`addOptionalFloatField`](../../../src/Dependency/WrappedEncryptedRow.php#L256-L269)

**API** · Returns `static`

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string` = ''
- `$wrappedKeyColumnName`: `?string` = null
- `$autoBindContext`: `bool` = false

#### [`addOptionalIntegerField`](../../../src/Dependency/WrappedEncryptedRow.php#L275-L288)

**API** · Returns `static`

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string` = ''
- `$wrappedKeyColumnName`: `?string` = null
- `$autoBindContext`: `bool` = false

#### [`addOptionalTextField`](../../../src/Dependency/WrappedEncryptedRow.php#L294-L307)

**API** · Returns `static`

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string` = ''
- `$wrappedKeyColumnName`: `?string` = null
- `$autoBindContext`: `bool` = false

#### [`addTextField`](../../../src/Dependency/WrappedEncryptedRow.php#L313-L326)

**API** · Returns `static`

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string` = ''
- `$wrappedKeyColumnName`: `?string` = null
- `$autoBindContext`: `bool` = false

---

