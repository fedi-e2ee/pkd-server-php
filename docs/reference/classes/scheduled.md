# Scheduled

Namespace: `FediE2EE\PKDServer\Scheduled`

## Classes

- [ASQueue](#asqueue) - class
- [Witness](#witness) - class

---

## ASQueue

**class** `FediE2EE\PKDServer\Scheduled\ASQueue`

**File:** [`src/Scheduled/ASQueue.php`](../../../src/Scheduled/ASQueue.php)

### Methods

#### `__construct(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

#### `run(): void`

ASQueue::run() is a very dumb method.

---

## Witness

**class** `FediE2EE\PKDServer\Scheduled\Witness`

**File:** [`src/Scheduled/Witness.php`](../../../src/Scheduled/Witness.php)

Perform witness co-signatures for third-porty Public Key Directory instances.

**Uses:** `FediE2EE\PKDServer\Traits\ConfigTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### `__construct(?FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` (nullable)

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

#### `run(): void`

**Throws:**

- `CryptoException`
- `DateMalformedStringException`
- `SodiumException`

#### `getHashesSince(FediE2EE\PKDServer\Tables\Records\Peer $peer): array`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`

**Throws:**

- `CryptoException`
- `GuzzleException`
- `HttpSignatureException`
- `JsonException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`

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

