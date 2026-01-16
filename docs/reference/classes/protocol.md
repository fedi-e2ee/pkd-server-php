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

#### `__construct(?FediE2EE\PKDServer\ServerConfig $config = null): void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` (nullable)

#### `rewrapSymmetricKeys(string $merkleRoot, ?FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap $keyMap = null): void`

Initiate a rewrapping of the symmetric keys associated with a record.

**Parameters:**

- `$merkleRoot`: `string`
- `$keyMap`: `?FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap` (nullable)

#### `retrieveLocalWrappedKeys(string $merkleRoot): FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

**Parameters:**

- `$merkleRoot`: `string`

**Throws:**

- `HPKEException`
- `JsonException`
- `TableException`

#### `hpkeWrapSymmetricKeys(FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap $keyMap): string`

**Parameters:**

- `$keyMap`: `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

#### `hpkeUnwrap(string $ciphertext): string`

**Parameters:**

- `$ciphertext`: `string`

**Throws:**

- `HPKEException`

#### `serializeKeyMap(FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap $keyMap): string`

**Parameters:**

- `$keyMap`: `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

#### `deserializeKeyMap(string $plaintextJsonString): FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

**Parameters:**

- `$plaintextJsonString`: `string`

#### `decryptAndGetRewrapped(string $merkleRoot, ?string $wrappedKeys = null): array`

Usage:

**Parameters:**

- `$merkleRoot`: `string`
- `$wrappedKeys`: `?string` (nullable)

#### `unwrapLocalMessage(string $encryptedMessage, string $wrappedKeys): array`

**Parameters:**

- `$encryptedMessage`: `string`
- `$wrappedKeys`: `string`

**Throws:**

- `BundleException`
- `CryptoException`
- `HPKEException`
- `InputException`
- `JsonException`

#### `getRewrappedFor(string $merkleRoot): array`

**Parameters:**

- `$merkleRoot`: `string`

**Throws:**

- `InputException`

#### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

#### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

#### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

#### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

#### `static assertAllArrayKeysExist(array $target, string $arrayKeys): void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:**

- `InputException`

#### `static allArrayKeysExist(array $target, string $arrayKeys): bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### `constantTimeSelect(int $select, string $left, string $right): string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:**

- `CryptoException`

#### `static dos2unix(string $in): string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### `static preAuthEncode(array $pieces): string`

**Parameters:**

- `$pieces`: `array`

#### `static sortByKey(array $arr): void`

**Parameters:**

- `$arr`: `array`

#### `static LE64(int $n): string`

**Parameters:**

- `$n`: `int`

#### `stringToByteArray(string $str): array`

**Parameters:**

- `$str`: `string`

#### `static stripNewlines(string $input): string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## Payload

**class** `FediE2EE\PKDServer\Protocol\Payload`

**File:** [`src/Protocol/Payload.php`](../../../src/Protocol/Payload.php)

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$message` | `FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface` | (readonly)  |
| `$keyMap` | `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap` | (readonly)  |
| `$rawJson` | `string` | (readonly)  |

### Methods

#### `__construct(FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface $message, FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap $keyMap, string $rawJson): void`

**Parameters:**

- `$message`: `FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface`
- `$keyMap`: `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`
- `$rawJson`: `string`

#### `decrypt(): FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface`

#### `jsonDecode(): array`

**Throws:**

- `JsonException`

#### `getMerkleTreePayload(): string`

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

#### `__construct(string $cs, string $encapsKey): void`

**Parameters:**

- `$cs`: `string`
- `$encapsKey`: `string`

#### `static from(ParagonIE\HPKE\HPKE $cs, ParagonIE\HPKE\Interfaces\EncapsKeyInterface $encapsKey): self`

**Parameters:**

- `$cs`: `ParagonIE\HPKE\HPKE`
- `$encapsKey`: `ParagonIE\HPKE\Interfaces\EncapsKeyInterface`

#### `static fromJson(string $json): self`

**Parameters:**

- `$json`: `string`

#### `jsonSerialize(): array`

**Attributes:** `#[Override]`

#### `getCipherSuite(): ParagonIE\HPKE\HPKE`

**Throws:**

- `HPKEException`

#### `getEncapsKey(): ParagonIE\HPKE\Interfaces\EncapsKeyInterface`

**Throws:**

- `DependencyException`
- `HPKEException`

---

