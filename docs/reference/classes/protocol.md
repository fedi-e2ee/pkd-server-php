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

#### [`__construct`](../../../src/Protocol/KeyWrapping.php#L57-L63)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` = null

**Throws:** `DependencyException`

#### [`rewrapSymmetricKeys`](../../../src/Protocol/KeyWrapping.php#L77-L107)

Returns `void`

Initiate a rewrapping of the symmetric keys associated with a record.

**Parameters:**

- `$merkleRoot`: `string`
- `$keyMap`: `?FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap` = null

**Throws:** `CacheException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `SodiumException`, `TableException`

#### [`retrieveLocalWrappedKeys`](../../../src/Protocol/KeyWrapping.php#L114-L125)

Returns `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

**Parameters:**

- `$merkleRoot`: `string`

**Throws:** `HPKEException`, `JsonException`, `TableException`

#### [`hpkeWrapSymmetricKeys`](../../../src/Protocol/KeyWrapping.php#L127-L133)

Returns `string`

**Parameters:**

- `$keyMap`: `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

#### [`hpkeUnwrap`](../../../src/Protocol/KeyWrapping.php#L138-L142)

Returns `string`

**Parameters:**

- `$ciphertext`: `string`

**Throws:** `HPKEException`

#### [`serializeKeyMap`](../../../src/Protocol/KeyWrapping.php#L147-L161)

Returns `string`

**Parameters:**

- `$keyMap`: `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

**Throws:** `BaseJsonException`

#### [`deserializeKeyMap`](../../../src/Protocol/KeyWrapping.php#L166-L180)

Returns `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

**Parameters:**

- `$plaintextJsonString`: `string`

**Throws:** `JsonException`

#### [`decryptAndGetRewrapped`](../../../src/Protocol/KeyWrapping.php#L197-L226)

Returns `array`

Usage:

[$message, $rewrappedKeys] = $keyWrapping->decryptAndRewrapp

**Parameters:**

- `$merkleRoot`: `string`
- `$wrappedKeys`: `?string` = null

**Throws:** `BundleException`, `CryptoException`, `DependencyException`, `HPKEException`, `InputException`, `InvalidArgumentException`, `JsonException`, `SodiumException`

#### [`unwrapLocalMessage`](../../../src/Protocol/KeyWrapping.php#L236-L243)

Returns `array`

**Parameters:**

- `$encryptedMessage`: `string`
- `$wrappedKeys`: `string`

**Throws:** `BundleException`, `CryptoException`, `HPKEException`, `InputException`, `JsonException`

#### [`getRewrappedFor`](../../../src/Protocol/KeyWrapping.php#L249-L280)

Returns `array`

**Parameters:**

- `$merkleRoot`: `string`

**Throws:** `InputException`

#### [`appCache`](../../../src/Protocol/KeyWrapping.php#L55-L58)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Protocol/KeyWrapping.php#L65-L88)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Protocol/KeyWrapping.php#L90-L93)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Protocol/KeyWrapping.php#L98-L108)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Protocol/KeyWrapping.php#L113-L117)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Protocol/KeyWrapping.php#L124-L131)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Protocol/KeyWrapping.php#L137-L144)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Protocol/KeyWrapping.php#L152-L158)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Protocol/KeyWrapping.php#L163-L169)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Protocol/KeyWrapping.php#L171-L180)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Protocol/KeyWrapping.php#L185-L194)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Protocol/KeyWrapping.php#L201-L211)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Protocol/KeyWrapping.php#L217-L227)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Protocol/KeyWrapping.php#L234-L244)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Protocol/KeyWrapping.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Protocol/KeyWrapping.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Protocol/KeyWrapping.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Protocol/KeyWrapping.php#L27-L32)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Protocol/KeyWrapping.php#L34-L41)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Protocol/KeyWrapping.php#L48-L65)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Protocol/KeyWrapping.php#L73-L76)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Protocol/KeyWrapping.php#L84-L97)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Protocol/KeyWrapping.php#L99-L107)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Protocol/KeyWrapping.php#L111-L114)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Protocol/KeyWrapping.php#L116-L123)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Protocol/KeyWrapping.php#L131-L165)

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

#### [`decode`](../../../src/Protocol/Payload.php#L37-L40)

Returns `array`

**Throws:** `JsonException`

#### [`getMerkleTreePayload`](../../../src/Protocol/Payload.php#L45-L55)

Returns `string`

**Throws:** `JsonException`

#### [`jsonDecode`](../../../src/Protocol/Payload.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Protocol/Payload.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Protocol/Payload.php#L34-L40)

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

#### [`from`](../../../src/Protocol/RewrapConfig.php#L32-L43)

static · Returns `self`

**Parameters:**

- `$cs`: `ParagonIE\HPKE\HPKE`
- `$encapsKey`: `ParagonIE\HPKE\Interfaces\EncapsKeyInterface`

**Throws:** `DependencyException`

#### [`fromJson`](../../../src/Protocol/RewrapConfig.php#L45-L58)

static · Returns `self`

**Parameters:**

- `$json`: `string`

#### [`jsonSerialize`](../../../src/Protocol/RewrapConfig.php#L64-L70)

Returns `array`

**Attributes:** `#[Override]`

#### [`getCipherSuite`](../../../src/Protocol/RewrapConfig.php#L75-L78)

Returns `ParagonIE\HPKE\HPKE`

**Throws:** `HPKEException`

#### [`getEncapsKey`](../../../src/Protocol/RewrapConfig.php#L85-L92)

Returns `ParagonIE\HPKE\Interfaces\EncapsKeyInterface`

**Throws:** `DependencyException`, `HPKEException`

---

