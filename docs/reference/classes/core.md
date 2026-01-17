# Core

Namespace: `FediE2EE\PKDServer`

## Classes

- [AppCache](#appcache) - class
- [Math](#math) - class
- [Protocol](#protocol) - class
- [Redirect](#redirect) - class
- [ServerConfig](#serverconfig) - class
- [Table](#table) - class
- [TableCache](#tablecache) - class

---

## AppCache

**class** `FediE2EE\PKDServer\AppCache`

**File:** [`src/AppCache.php`](../../../src/AppCache.php)

**Implements:** `Psr\SimpleCache\CacheInterface`

### Methods

#### `__construct(FediE2EE\PKDServer\ServerConfig $serverConfig, string $namespace = '', int $defaultTTL = 60): void`

**Parameters:**

- `$serverConfig`: `FediE2EE\PKDServer\ServerConfig`
- `$namespace`: `string`
- `$defaultTTL`: `int`

#### `cache(string $lookup, callable $fallback, DateInterval|int|null $ttl = null): mixed`

If there is a cache-hit, it returns the value.

**Parameters:**

- `$lookup`: `string`
- `$fallback`: `callable`
- `$ttl`: `DateInterval|int|null` (nullable)

#### `deriveKey(string $input): string`

**Parameters:**

- `$input`: `string`

**Throws:**

- `SodiumException`

#### `get(string $key, mixed $default = null): mixed`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`
- `$default`: `mixed` (nullable)

#### `set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`
- `$value`: `mixed` (nullable)
- `$ttl`: `DateInterval|int|null` (nullable)

#### `delete(string $key): bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`

#### `clear(): bool`

**Attributes:** `#[Override]`

#### `getMultiple(iterable $keys, mixed $default = null): array`

**Attributes:** `#[Override]`

**Parameters:**

- `$keys`: `iterable`
- `$default`: `mixed` (nullable)

#### `setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$values`: `iterable`
- `$ttl`: `DateInterval|int|null` (nullable)

#### `deleteMultiple(iterable $keys): bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$keys`: `iterable`

#### `has(string $key): bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`

#### `processTTL(DateInterval|int|null $ttl): int`

Collapse multiple types into a number of seconds for Redis.

**Parameters:**

- `$ttl`: `DateInterval|int|null` (nullable)

---

## Math

**abstract class** `FediE2EE\PKDServer\Math`

**File:** [`src/Math.php`](../../../src/Math.php)

### Methods

#### `static getHighVolumeCutoff(int $numLeaves): int`

**Parameters:**

- `$numLeaves`: `int`

#### `static getLowVolumeCutoff(int $numLeaves): int`

**Parameters:**

- `$numLeaves`: `int`

---

## Protocol

**class** `FediE2EE\PKDServer\Protocol`

**File:** [`src/Protocol.php`](../../../src/Protocol.php)

This class defines the process for which records are updated in the Public Key Directory.

**Uses:** `FediE2EE\PKDServer\Traits\ConfigTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### `__construct(?FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` (nullable)

#### `process(FediE2EE\PKDServer\ActivityPub\ActivityStream $enqueued, bool $isActivityPub = true): array`

**Parameters:**

- `$enqueued`: `FediE2EE\PKDServer\ActivityPub\ActivityStream`
- `$isActivityPub`: `bool`

**Throws:**

- `CryptoException`
- `DependencyException`
- `Exceptions\CacheException`
- `HPKEException`
- `NotImplementedException`
- `ParserException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `DependencyException`
- `SodiumException`
- `CertaintyException`

#### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### `addKey(string $body, string $outerActor): FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `ProtocolException`
- `TableException`
- `HPKEException`
- `NotImplementedException`
- `ParserException`
- `SodiumException`

#### `revokeKey(string $body, string $outerActor): FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `ProtocolException`
- `TableException`
- `HPKEException`
- `NotImplementedException`
- `ParserException`
- `SodiumException`

#### `revokeKeyThirdParty(string $body): bool`

**Parameters:**

- `$body`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `HPKEException`
- `NotImplementedException`
- `ParserException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `moveIdentity(string $body, string $outerActor): bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `HPKEException`
- `NotImplementedException`
- `ParserException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `burnDown(string $body, string $outerActor): bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `HPKEException`
- `NotImplementedException`
- `ParserException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `fireproof(string $body, string $outerActor): bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `HPKEException`
- `NotImplementedException`
- `ParserException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `undoFireproof(string $body, string $outerActor): bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `HPKEException`
- `NotImplementedException`
- `ParserException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `addAuxData(string $body, string $outerActor): bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `HPKEException`
- `NotImplementedException`
- `ParserException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `revokeAuxData(string $body, string $outerActor): bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `HPKEException`
- `NotImplementedException`
- `ParserException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `checkpoint(string $body): bool`

**Parameters:**

- `$body`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ParserException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

---

## Redirect

**class** `FediE2EE\PKDServer\Redirect`

**File:** [`src/Redirect.php`](../../../src/Redirect.php)

Abstracts an HTTP Redirect

### Methods

#### `__construct(Psr\Http\Message\UriInterface|string $url, string $message = '', int $status = 301): void`

**Parameters:**

- `$url`: `Psr\Http\Message\UriInterface|string`
- `$message`: `string`
- `$status`: `int`

#### `respond(): Psr\Http\Message\ResponseInterface`

---

## ServerConfig

**class** `FediE2EE\PKDServer\ServerConfig`

**File:** [`src/ServerConfig.php`](../../../src/ServerConfig.php)

### Methods

#### `__construct(FediE2EE\PKDServer\Meta\Params $params): void`

**Parameters:**

- `$params`: `FediE2EE\PKDServer\Meta\Params`

#### `getCaCertFetch(): ParagonIE\Certainty\Fetch`

**Throws:**

- `DependencyException`

#### `getAuxDataTypeAllowList(): array`

**API Method**

#### `getAuxDataRegistry(): FediE2EE\PKD\Extensions\Registry`

#### `getGuzzle(): GuzzleHttp\Client`

#### `getCipherSweet(): ParagonIE\CipherSweet\CipherSweet`

**Throws:**

- `DependencyException`

#### `getDb(): ParagonIE\EasyDB\EasyDB`

**API Method**

**Throws:**

- `DependencyException`

#### `getHPKE(): FediE2EE\PKDServer\Dependency\HPKE`

**API Method**

**Throws:**

- `DependencyException`

#### `getLogger(): Monolog\Logger`

#### `getParams(): FediE2EE\PKDServer\Meta\Params`

#### `getSigningKeys(): FediE2EE\PKDServer\Dependency\SigningKeys`

**API Method**

**Throws:**

- `DependencyException`

#### `getRouter(): League\Route\Router`

**API Method**

**Throws:**

- `DependencyException`

#### `getTwig(): Twig\Environment`

**API Method**

**Throws:**

- `DependencyException`

#### `getRedis(): ?Predis\Client`

#### `hasRedis(): bool`

#### `withAuxDataTypeAllowList(array $allowList = []): static`

**Parameters:**

- `$allowList`: `array`

#### `withAuxDataRegistry(FediE2EE\PKD\Extensions\Registry $registry): static`

**Parameters:**

- `$registry`: `FediE2EE\PKD\Extensions\Registry`

#### `withCACertFetch(ParagonIE\Certainty\Fetch $fetch): static`

**Parameters:**

- `$fetch`: `ParagonIE\Certainty\Fetch`

#### `withCipherSweet(ParagonIE\CipherSweet\CipherSweet $ciphersweet): static`

**Parameters:**

- `$ciphersweet`: `ParagonIE\CipherSweet\CipherSweet`

#### `withDatabase(ParagonIE\EasyDB\EasyDB $db): static`

**Parameters:**

- `$db`: `ParagonIE\EasyDB\EasyDB`

#### `withHPKE(FediE2EE\PKDServer\Dependency\HPKE $hpke): static`

**Parameters:**

- `$hpke`: `FediE2EE\PKDServer\Dependency\HPKE`

#### `withLogger(Monolog\Logger $logger): static`

**Parameters:**

- `$logger`: `Monolog\Logger`

#### `withOptionalRedisClient(?Predis\Client $redis = null): static`

**Parameters:**

- `$redis`: `?Predis\Client` (nullable)

#### `withRouter(League\Route\Router $router): static`

**Parameters:**

- `$router`: `League\Route\Router`

#### `withSigningKeys(FediE2EE\PKDServer\Dependency\SigningKeys $signingKeys): static`

**Parameters:**

- `$signingKeys`: `FediE2EE\PKDServer\Dependency\SigningKeys`

#### `withTwig(Twig\Environment $twig): static`

**Parameters:**

- `$twig`: `Twig\Environment`

---

## Table

**abstract class** `FediE2EE\PKDServer\Table`

**File:** [`src/Table.php`](../../../src/Table.php)

**Uses:** `FediE2EE\PKDServer\Traits\ConfigTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$engine` | `ParagonIE\CipherSweet\CipherSweet` | (readonly)  |
| `$db` | `ParagonIE\EasyDB\EasyDB` | (readonly)  |
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### `__construct(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

#### `abstract getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

#### `clearCache(): void`

#### `convertKey(FediE2EE\PKD\Crypto\SymmetricKey $inputKey): ParagonIE\CipherSweet\Backend\Key\SymmetricKey`

**Parameters:**

- `$inputKey`: `FediE2EE\PKD\Crypto\SymmetricKey`

#### `assertRecentMerkleRoot(string $recentMerkle): void`

**Parameters:**

- `$recentMerkle`: `string`

**Throws:**

- `ProtocolException`

#### `isMerkleRootRecent(string $merkleRoot, bool $isHighVolume = false): bool`

**API Method**

**Parameters:**

- `$merkleRoot`: `string`
- `$isHighVolume`: `bool`

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

## TableCache

**class** `FediE2EE\PKDServer\TableCache`

**File:** [`src/TableCache.php`](../../../src/TableCache.php)

### Methods

#### `static instance(): self`

#### `clearCache(): void`

#### `fetchTable(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`

#### `hasTable(string $tableName): bool`

**Parameters:**

- `$tableName`: `string`

#### `storeTable(string $tableName, FediE2EE\PKDServer\Table $table): static`

**Parameters:**

- `$tableName`: `string`
- `$table`: `FediE2EE\PKDServer\Table`

---

