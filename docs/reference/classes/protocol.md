# Protocol

Namespace: `FediE2EE\PKDServer\Protocol`

## Classes

- [KeyWrapping](#keywrapping) - class
- [Payload](#payload) - class
- [RewrapConfig](#rewrapconfig) - class

---

## KeyWrapping

**class** `FediE2EE\PKDServer\Protocol\KeyWrapping`

**File:** [`src/Protocol/KeyWrapping.php`](../../../src/Protocol/KeyWrapping.php)

**Uses:** `FediE2EE\PKDServer\Traits\ConfigTrait`, `FediE2EE\PKD\Crypto\UtilTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/Protocol/KeyWrapping.php#L54-L59)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` = null

#### [`rewrapSymmetricKeys`](../../../src/Protocol/KeyWrapping.php#L73-L100)

Returns `void`

Initiate a rewrapping of the symmetric keys associated with a record.

**Parameters:**

- `$merkleRoot`: `string`
- `$keyMap`: `?FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap` = null

**Throws:** `CacheException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `SodiumException`, `TableException`

#### [`retrieveLocalWrappedKeys`](../../../src/Protocol/KeyWrapping.php#L107-L118)

Returns `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

**Parameters:**

- `$merkleRoot`: `string`

**Throws:** `HPKEException`, `JsonException`, `TableException`

#### [`hpkeWrapSymmetricKeys`](../../../src/Protocol/KeyWrapping.php#L120-L126)

Returns `string`

**Parameters:**

- `$keyMap`: `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

#### [`hpkeUnwrap`](../../../src/Protocol/KeyWrapping.php#L131-L135)

Returns `string`

**Parameters:**

- `$ciphertext`: `string`

**Throws:** `HPKEException`

#### [`serializeKeyMap`](../../../src/Protocol/KeyWrapping.php#L140-L152)

Returns `string`

**Parameters:**

- `$keyMap`: `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

**Throws:** `BaseJsonException`

#### [`deserializeKeyMap`](../../../src/Protocol/KeyWrapping.php#L157-L171)

Returns `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

**Parameters:**

- `$plaintextJsonString`: `string`

**Throws:** `JsonException`

#### [`decryptAndGetRewrapped`](../../../src/Protocol/KeyWrapping.php#L188-L214)

Returns `array`

Usage:

[$message, $rewrappedKeys] = $keyWrapping->decryptAndRewrapp

**Parameters:**

- `$merkleRoot`: `string`
- `$wrappedKeys`: `?string` = null

**Throws:** `BundleException`, `CryptoException`, `DependencyException`, `HPKEException`, `InputException`, `InvalidArgumentException`, `JsonException`, `SodiumException`

#### [`unwrapLocalMessage`](../../../src/Protocol/KeyWrapping.php#L223-L230)

Returns `array`

**Parameters:**

- `$encryptedMessage`: `string`
- `$wrappedKeys`: `string`

**Throws:** `BundleException`, `CryptoException`, `HPKEException`, `InputException`, `JsonException`

#### [`getRewrappedFor`](../../../src/Protocol/KeyWrapping.php#L235-L266)

Returns `array`

**Parameters:**

- `$merkleRoot`: `string`

**Throws:** `InputException`

#### [`appCache`](../../../src/Protocol/KeyWrapping.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Protocol/KeyWrapping.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Protocol/KeyWrapping.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Protocol/KeyWrapping.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Protocol/KeyWrapping.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Protocol/KeyWrapping.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`jsonDecode`](../../../src/Protocol/KeyWrapping.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Protocol/KeyWrapping.php#L23-L26)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Protocol/KeyWrapping.php#L31-L37)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Protocol/KeyWrapping.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Protocol/KeyWrapping.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Protocol/KeyWrapping.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Protocol/KeyWrapping.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Protocol/KeyWrapping.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Protocol/KeyWrapping.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Protocol/KeyWrapping.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Protocol/KeyWrapping.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Protocol/KeyWrapping.php#L107-L141)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## Payload

**class** `FediE2EE\PKDServer\Protocol\Payload`

**File:** [`src/Protocol/Payload.php`](../../../src/Protocol/Payload.php)

**Uses:** `FediE2EE\PKDServer\Traits\JsonTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$message` | `FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface` | (readonly)  |
| `$keyMap` | `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap` | (readonly)  |
| `$rawJson` | `string` | (readonly)  |

### Methods

#### [`__construct`](../../../src/Protocol/Payload.php#L19-L23)

Returns `void`

**Parameters:**

- `$message`: `FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface`
- `$keyMap`: `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`
- `$rawJson`: `string`

#### [`decrypt`](../../../src/Protocol/Payload.php#L25-L31)

Returns `FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface`

#### [`decode`](../../../src/Protocol/Payload.php#L36-L39)

Returns `array`

**Throws:** `JsonException`

#### [`getMerkleTreePayload`](../../../src/Protocol/Payload.php#L41-L51)

Returns `string`

#### [`jsonDecode`](../../../src/Protocol/Payload.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Protocol/Payload.php#L23-L26)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Protocol/Payload.php#L31-L37)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## RewrapConfig

**class** `FediE2EE\PKDServer\Protocol\RewrapConfig`

**File:** [`src/Protocol/RewrapConfig.php`](../../../src/Protocol/RewrapConfig.php)

**Implements:** `JsonSerializable`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$cs` | `string` | (readonly)  |
| `$encapsKey` | `string` | (readonly)  |

### Methods

#### [`__construct`](../../../src/Protocol/RewrapConfig.php#L24-L27)

Returns `void`

**Parameters:**

- `$cs`: `string`
- `$encapsKey`: `string`

#### [`from`](../../../src/Protocol/RewrapConfig.php#L29-L40)

static · Returns `self`

**Parameters:**

- `$cs`: `ParagonIE\HPKE\HPKE`
- `$encapsKey`: `ParagonIE\HPKE\Interfaces\EncapsKeyInterface`

#### [`fromJson`](../../../src/Protocol/RewrapConfig.php#L42-L55)

static · Returns `self`

**Parameters:**

- `$json`: `string`

#### [`jsonSerialize`](../../../src/Protocol/RewrapConfig.php#L58-L64)

Returns `array`

**Attributes:** `#[Override]`

#### [`getCipherSuite`](../../../src/Protocol/RewrapConfig.php#L69-L72)

Returns `ParagonIE\HPKE\HPKE`

**Throws:** `HPKEException`

#### [`getEncapsKey`](../../../src/Protocol/RewrapConfig.php#L79-L86)

Returns `ParagonIE\HPKE\Interfaces\EncapsKeyInterface`

**Throws:** `DependencyException`, `HPKEException`

---

