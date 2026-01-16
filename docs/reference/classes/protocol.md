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

**Uses:** `FediE2EE\PKDServer\Traits\ConfigTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### `__construct(?FediE2EE\PKDServer\ServerConfig $config = null): void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` (nullable)

#### `localKeyWrap(string $merkleRoot, FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap $keyMap): void`

Wrap the local symmetric keys in 'wrappedkeys'

**Parameters:**

- `$merkleRoot`: `string`
- `$keyMap`: `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

**Throws:**

- `DependencyException`

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

#### `serializeKeyMap(FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap $keyMap): string`

**Parameters:**

- `$keyMap`: `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

#### `deserializeKeyMap(string $plaintextJsonString): FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`

**Parameters:**

- `$plaintextJsonString`: `string`

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

