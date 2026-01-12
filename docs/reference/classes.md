# Classes Reference

This document provides technical reference for all classes in the PKD Server.

## Table of Contents

- [FediE2EE\PKDServer](#fedie2ee-pkdserver)
- [ActivityPub](#activitypub)
- [Dependency](#dependency)
- [Exceptions](#exceptions)
- [Meta](#meta)
- [Protocol](#protocol)
- [RequestHandlers](#requesthandlers)
- [RequestHandlers\ActivityPub](#requesthandlers-activitypub)
- [RequestHandlers\Api](#requesthandlers-api)
- [Scheduled](#scheduled)
- [Tables](#tables)
- [Tables\Records](#tables-records)
- [Traits](#traits)

---

## FediE2EE\PKDServer

### AppCache

**class** `FediE2EE\PKDServer\AppCache`

**File:** `src\AppCache.php`

**Implements:** `Psr\SimpleCache\CacheInterface`

#### Methods

##### `__construct(FediE2EE\PKDServer\ServerConfig $serverConfig, string $namespace = '', int $defaultTTL = 60): void`

**Parameters:**

- `$serverConfig`: `FediE2EE\PKDServer\ServerConfig`
- `$namespace`: `string`
- `$defaultTTL`: `int`

##### `cache(string $lookup, callable $fallback): mixed`

If there is a cache-hit, it returns the value.

**Parameters:**

- `$lookup`: `string`
- `$fallback`: `callable`

##### `deriveKey(string $input): string`

**Parameters:**

- `$input`: `string`

**Throws:**

- `SodiumException`

##### `get(string $key, mixed $default = null): mixed`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`
- `$default`: `mixed` (nullable)

##### `set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`
- `$value`: `mixed` (nullable)
- `$ttl`: `DateInterval|int|null` (nullable)

##### `delete(string $key): bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`

##### `clear(): bool`

**Attributes:** `#[Override]`

##### `getMultiple(iterable $keys, mixed $default = null): array`

**Attributes:** `#[Override]`

**Parameters:**

- `$keys`: `iterable`
- `$default`: `mixed` (nullable)

##### `setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$values`: `iterable`
- `$ttl`: `DateInterval|int|null` (nullable)

##### `deleteMultiple(iterable $keys): bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$keys`: `iterable`

##### `has(string $key): bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`

##### `processTTL(DateInterval|int|null $ttl): int`

Collapse multiple types into a number of seconds for Redis.

**Parameters:**

- `$ttl`: `DateInterval|int|null` (nullable)

---

### Math

**abstract class** `FediE2EE\PKDServer\Math`

**File:** `src\Math.php`

#### Methods

##### `static getHighVolumeCutoff(int $numLeaves): int`

**Parameters:**

- `$numLeaves`: `int`

##### `static getLowVolumeCutoff(int $numLeaves): int`

**Parameters:**

- `$numLeaves`: `int`

---

### Protocol

**class** `FediE2EE\PKDServer\Protocol`

**File:** `src\Protocol.php`

This class defines the process for which records are updated in the Public Key Directory.

**Uses:** `FediE2EE\PKDServer\Traits\ConfigTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(?FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` (nullable)

##### `process(FediE2EE\PKDServer\ActivityPub\ActivityStream $enqueued, bool $isActivityPub = true): array`

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

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `DependencyException`
- `SodiumException`
- `CertaintyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `addKey(string $body, string $outerActor): FediE2EE\PKDServer\Tables\Records\ActorKey`

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

##### `revokeKey(string $body, string $outerActor): FediE2EE\PKDServer\Tables\Records\ActorKey`

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

##### `revokeKeyThirdParty(string $body): bool`

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

##### `moveIdentity(string $body, string $outerActor): bool`

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

##### `burnDown(string $body, string $outerActor): bool`

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

##### `fireproof(string $body, string $outerActor): bool`

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

##### `undoFireproof(string $body, string $outerActor): bool`

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

##### `addAuxData(string $body, string $outerActor): bool`

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

##### `revokeAuxData(string $body, string $outerActor): bool`

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

##### `checkpoint(string $body): bool`

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

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

---

### Redirect

**class** `FediE2EE\PKDServer\Redirect`

**File:** `src\Redirect.php`

Abstracts an HTTP Redirect

#### Methods

##### `__construct(Psr\Http\Message\UriInterface|string $url, string $message = '', int $status = 301): void`

**Parameters:**

- `$url`: `Psr\Http\Message\UriInterface|string`
- `$message`: `string`
- `$status`: `int`

##### `respond(): Psr\Http\Message\ResponseInterface`

---

### ServerConfig

**class** `FediE2EE\PKDServer\ServerConfig`

**File:** `src\ServerConfig.php`

#### Methods

##### `__construct(FediE2EE\PKDServer\Meta\Params $params): void`

**Parameters:**

- `$params`: `FediE2EE\PKDServer\Meta\Params`

##### `getCaCertFetch(): ParagonIE\Certainty\Fetch`

**Throws:**

- `DependencyException`

##### `getAuxDataTypeAllowList(): array`

**API Method**

##### `getAuxDataRegistry(): FediE2EE\PKD\Extensions\Registry`

##### `getGuzzle(): GuzzleHttp\Client`

##### `getCipherSweet(): ParagonIE\CipherSweet\CipherSweet`

**Throws:**

- `DependencyException`

##### `getDb(): ParagonIE\EasyDB\EasyDB`

**API Method**

**Throws:**

- `DependencyException`

##### `getHPKE(): FediE2EE\PKDServer\Dependency\HPKE`

**API Method**

**Throws:**

- `DependencyException`

##### `getLogger(): Monolog\Logger`

##### `getParams(): FediE2EE\PKDServer\Meta\Params`

##### `getSigningKeys(): FediE2EE\PKDServer\Dependency\SigningKeys`

**API Method**

**Throws:**

- `DependencyException`

##### `getRouter(): League\Route\Router`

**API Method**

**Throws:**

- `DependencyException`

##### `getTwig(): Twig\Environment`

**API Method**

**Throws:**

- `DependencyException`

##### `getRedis(): ?Predis\Client`

##### `hasRedis(): bool`

##### `withAuxDataTypeAllowList(array $allowList = []): static`

**Parameters:**

- `$allowList`: `array`

##### `withAuxDataRegistry(FediE2EE\PKD\Extensions\Registry $registry): static`

**Parameters:**

- `$registry`: `FediE2EE\PKD\Extensions\Registry`

##### `withCACertFetch(ParagonIE\Certainty\Fetch $fetch): static`

**Parameters:**

- `$fetch`: `ParagonIE\Certainty\Fetch`

##### `withCipherSweet(ParagonIE\CipherSweet\CipherSweet $ciphersweet): static`

**Parameters:**

- `$ciphersweet`: `ParagonIE\CipherSweet\CipherSweet`

##### `withDatabase(ParagonIE\EasyDB\EasyDB $db): static`

**Parameters:**

- `$db`: `ParagonIE\EasyDB\EasyDB`

##### `withHPKE(FediE2EE\PKDServer\Dependency\HPKE $hpke): static`

**Parameters:**

- `$hpke`: `FediE2EE\PKDServer\Dependency\HPKE`

##### `withLogger(Monolog\Logger $logger): static`

**Parameters:**

- `$logger`: `Monolog\Logger`

##### `withOptionalRedisClient(?Predis\Client $redis = null): static`

**Parameters:**

- `$redis`: `?Predis\Client` (nullable)

##### `withRouter(League\Route\Router $router): static`

**Parameters:**

- `$router`: `League\Route\Router`

##### `withSigningKeys(FediE2EE\PKDServer\Dependency\SigningKeys $signingKeys): static`

**Parameters:**

- `$signingKeys`: `FediE2EE\PKDServer\Dependency\SigningKeys`

##### `withTwig(Twig\Environment $twig): static`

**Parameters:**

- `$twig`: `Twig\Environment`

---

### Table

**abstract class** `FediE2EE\PKDServer\Table`

**File:** `src\Table.php`

**Uses:** `FediE2EE\PKDServer\Traits\ConfigTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$engine` | `ParagonIE\CipherSweet\CipherSweet` | (readonly)  |
| `$db` | `ParagonIE\EasyDB\EasyDB` | (readonly)  |
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `abstract getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

##### `clearCache(): void`

##### `convertKey(FediE2EE\PKD\Crypto\SymmetricKey $inputKey): ParagonIE\CipherSweet\Backend\Key\SymmetricKey`

**Parameters:**

- `$inputKey`: `FediE2EE\PKD\Crypto\SymmetricKey`

##### `assertRecentMerkleRoot(string $recentMerkle): void`

**Parameters:**

- `$recentMerkle`: `string`

**Throws:**

- `ProtocolException`

##### `isMerkleRootRecent(string $merkleRoot, bool $isHighVolume = false): bool`

**API Method**

**Parameters:**

- `$merkleRoot`: `string`
- `$isHighVolume`: `bool`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### TableCache

**class** `FediE2EE\PKDServer\TableCache`

**File:** `src\TableCache.php`

#### Methods

##### `static instance(): self`

##### `clearCache(): void`

##### `fetchTable(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`

##### `hasTable(string $tableName): bool`

**Parameters:**

- `$tableName`: `string`

##### `storeTable(string $tableName, FediE2EE\PKDServer\Table $table): static`

**Parameters:**

- `$tableName`: `string`
- `$table`: `FediE2EE\PKDServer\Table`

---

## ActivityPub

### ActivityStream

**class** `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**File:** `src\ActivityPub\ActivityStream.php`

**Implements:** `JsonSerializable`, `Stringable`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$id` | `string` |  |
| `$type` | `string` |  |
| `$actor` | `string` |  |
| `$object` | `object` |  |

#### Methods

##### `static fromDecoded(stdClass $decoded): self`

**Parameters:**

- `$decoded`: `stdClass`

**Throws:**

- `ActivityPubException`

##### `static fromString(string $input): self`

**Parameters:**

- `$input`: `string`

**Throws:**

- `ActivityPubException`

##### `jsonSerialize(): stdClass`

**Attributes:** `#[Override]`

##### `__toString(): string`

**Throws:**

- `JsonException`

##### `isDirectMessage(): bool`

---

### WebFinger

**class** `FediE2EE\PKDServer\ActivityPub\WebFinger`

**File:** `src\ActivityPub\WebFinger.php`

#### Methods

##### `__construct(?FediE2EE\PKDServer\ServerConfig $config = null, ?GuzzleHttp\Client $client = null, ?ParagonIE\Certainty\Fetch $fetch = null): void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` (nullable)
- `$client`: `?GuzzleHttp\Client` (nullable)
- `$fetch`: `?ParagonIE\Certainty\Fetch` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

##### `clearCaches(): void`

**API Method**

##### `canonicalize(string $actorUsernameOrUrl): string`

**Parameters:**

- `$actorUsernameOrUrl`: `string`

**Throws:**

- `NetworkException`
- `GuzzleException`

##### `fetch(string $identifier): array`

Fetch an entire remote WebFinger response.

**Parameters:**

- `$identifier`: `string`

**Throws:**

- `GuzzleException`
- `NetworkException`

##### `getInboxUrl(string $actorUrl): string`

**Parameters:**

- `$actorUrl`: `string`

**Throws:**

- `CacheException`
- `GuzzleException`
- `NetworkException`

##### `getPublicKey(string $actorUrl): FediE2EE\PKD\Crypto\PublicKey`

**Parameters:**

- `$actorUrl`: `string`

**Throws:**

- `CryptoException`
- `FetchException`

##### `setCanonicalForTesting(string $index, string $value): void`

Used for unit tests. Sets a canonical value to bypass the live webfinger query.

**Parameters:**

- `$index`: `string`
- `$value`: `string`

**Throws:**

- `SodiumException`
- `InvalidArgumentException`

---

## Dependency

### HPKE

**class** `FediE2EE\PKDServer\Dependency\HPKE`

**File:** `src\Dependency\HPKE.php`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$cs` | `ParagonIE\HPKE\HPKE` | (readonly)  |
| `$decapsKey` | `ParagonIE\HPKE\Interfaces\DecapsKeyInterface` | (readonly)  |
| `$encapsKey` | `ParagonIE\HPKE\Interfaces\EncapsKeyInterface` | (readonly)  |

#### Methods

##### `__construct(ParagonIE\HPKE\HPKE $cs, ParagonIE\HPKE\Interfaces\DecapsKeyInterface $decapsKey, ParagonIE\HPKE\Interfaces\EncapsKeyInterface $encapsKey): void`

**Parameters:**

- `$cs`: `ParagonIE\HPKE\HPKE`
- `$decapsKey`: `ParagonIE\HPKE\Interfaces\DecapsKeyInterface`
- `$encapsKey`: `ParagonIE\HPKE\Interfaces\EncapsKeyInterface`

##### `getCipherSuite(): ParagonIE\HPKE\HPKE`

**API Method**

##### `getDecapsKey(): ParagonIE\HPKE\Interfaces\DecapsKeyInterface`

**API Method**

##### `getEncapsKey(): ParagonIE\HPKE\Interfaces\EncapsKeyInterface`

**API Method**

---

### InjectConfigStrategy

**class** `FediE2EE\PKDServer\Dependency\InjectConfigStrategy`

**File:** `src\Dependency\InjectConfigStrategy.php`

**Extends:** `League\Route\Strategy\ApplicationStrategy`

**Implements:** `League\Route\ContainerAwareInterface`, `League\Route\Strategy\StrategyInterface`

#### Methods

##### `__construct(): void`

**Throws:**

- `DependencyException`

##### `invokeRouteCallable(League\Route\Route $route, Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Override]`

**Parameters:**

- `$route`: `League\Route\Route`
- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`

---

### SigningKeys

**class** `FediE2EE\PKDServer\Dependency\SigningKeys`

**File:** `src\Dependency\SigningKeys.php`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$secretKey` | `FediE2EE\PKD\Crypto\SecretKey` | (readonly)  |
| `$publicKey` | `FediE2EE\PKD\Crypto\PublicKey` | (readonly)  |

#### Methods

##### `__construct(FediE2EE\PKD\Crypto\SecretKey $secretKey, FediE2EE\PKD\Crypto\PublicKey $publicKey): void`

**Parameters:**

- `$secretKey`: `FediE2EE\PKD\Crypto\SecretKey`
- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`

---

### WrappedEncryptedRow

**class** `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**File:** `src\Dependency\WrappedEncryptedRow.php`

Extends the CipherSweet EncryptedRow class to support key-wrapping

**Extends:** `ParagonIE\CipherSweet\EncryptedRow`

#### Methods

##### `getWrappedColumnNames(): array`

##### `addField(string $fieldName, string $type = 'string', ParagonIE\CipherSweet\AAD|string $aadSource = '', bool $autoBindContext = false, ?string $wrappedKeyColumnName = null): static`

**Attributes:** `#[Override]`

Define a field that will be encrypted.

**Parameters:**

- `$fieldName`: `string`
- `$type`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$autoBindContext`: `bool`
- `$wrappedKeyColumnName`: `?string` (nullable)

##### `getExtensionKey(): ParagonIE\CipherSweet\Backend\Key\SymmetricKey`

Get the key used to encrypt/decrypt the field symmetric key.

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`

##### `wrapKey(ParagonIE\CipherSweet\Backend\Key\SymmetricKey $key, string $fieldName): string`

**Parameters:**

- `$key`: `ParagonIE\CipherSweet\Backend\Key\SymmetricKey`
- `$fieldName`: `string`

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`

##### `unwrapKey(string $wrapped, string $fieldName): ParagonIE\CipherSweet\Backend\Key\SymmetricKey`

**Parameters:**

- `$wrapped`: `string`
- `$fieldName`: `string`

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`

##### `wrapBeforeEncrypt(array $row, array $symmetricKeyMap = []): array`

**API Method**

**Parameters:**

- `$row`: `array`
- `$symmetricKeyMap`: `array`

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`

##### `purgeWrapKeyCache(): static`

**API Method**

##### `addBooleanField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

##### `addFloatField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

##### `addIntegerField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

##### `addOptionalBooleanField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

##### `addOptionalFloatField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

##### `addOptionalIntegerField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

##### `addOptionalTextField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

##### `addTextField(string $fieldName, ParagonIE\CipherSweet\AAD|string $aadSource = '', ?string $wrappedKeyColumnName = null, bool $autoBindContext = false): static`

**API Method**

**Attributes:** `#[Override]`

**Parameters:**

- `$fieldName`: `string`
- `$aadSource`: `ParagonIE\CipherSweet\AAD|string`
- `$wrappedKeyColumnName`: `?string` (nullable)
- `$autoBindContext`: `bool`

---

## Exceptions

### ActivityPubException

**class** `FediE2EE\PKDServer\Exceptions\ActivityPubException`

**File:** `src\Exceptions\ActivityPubException.php`

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

---

### BaseException

**class** `FediE2EE\PKDServer\Exceptions\BaseException`

**File:** `src\Exceptions\BaseException.php`

**Extends:** `Exception`

**Implements:** `Throwable`, `Stringable`

---

### CacheException

**class** `FediE2EE\PKDServer\Exceptions\CacheException`

**File:** `src\Exceptions\CacheException.php`

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

---

### ConcurrentException

**class** `FediE2EE\PKDServer\Exceptions\ConcurrentException`

**File:** `src\Exceptions\ConcurrentException.php`

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

---

### DependencyException

**class** `FediE2EE\PKDServer\Exceptions\DependencyException`

**File:** `src\Exceptions\DependencyException.php`

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

---

### FetchException

**class** `FediE2EE\PKDServer\Exceptions\FetchException`

**File:** `src\Exceptions\FetchException.php`

**Extends:** `Exception`

**Implements:** `Throwable`, `Stringable`

---

### ProtocolException

**class** `FediE2EE\PKDServer\Exceptions\ProtocolException`

**File:** `src\Exceptions\ProtocolException.php`

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

---

### TableException

**class** `FediE2EE\PKDServer\Exceptions\TableException`

**File:** `src\Exceptions\TableException.php`

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

---

## Meta

### Params

**class** `FediE2EE\PKDServer\Meta\Params`

**File:** `src\Meta\Params.php`

Server configuration parameters

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$hashAlgo` | `string` | (readonly)  |
| `$otpMaxLife` | `int` | (readonly)  |
| `$actorUsername` | `string` | (readonly)  |
| `$hostname` | `string` | (readonly)  |
| `$cacheKey` | `string` | (readonly)  |

#### Methods

##### `__construct(string $hashAlgo = 'sha256', int $otpMaxLife = 120, string $actorUsername = 'pubkeydir', string $hostname = 'localhost', string $cacheKey = ''): void`

These parameters MUST be public and MUST have a default value

**Parameters:**

- `$hashAlgo`: `string`
- `$otpMaxLife`: `int`
- `$actorUsername`: `string`
- `$hostname`: `string`
- `$cacheKey`: `string`

---

### RecordForTable

**class** `FediE2EE\PKDServer\Meta\RecordForTable`

**File:** `src\Meta\RecordForTable.php`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$tableName` | `string` | (readonly)  |

#### Methods

##### `__construct(string $tableName = ''): void`

**Parameters:**

- `$tableName`: `string`

---

### Route

**class** `FediE2EE\PKDServer\Meta\Route`

**File:** `src\Meta\Route.php`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$uriPattern` | `string` | (readonly)  |

#### Methods

##### `__construct(string $uriPattern = ''): void`

**Parameters:**

- `$uriPattern`: `string`

---

## Protocol

### Payload

**class** `FediE2EE\PKDServer\Protocol\Payload`

**File:** `src\Protocol\Payload.php`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$message` | `FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface` | (readonly)  |
| `$keyMap` | `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap` | (readonly)  |
| `$rawJson` | `string` | (readonly)  |

#### Methods

##### `__construct(FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface $message, FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap $keyMap, string $rawJson): void`

**Parameters:**

- `$message`: `FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface`
- `$keyMap`: `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`
- `$rawJson`: `string`

##### `decrypt(): FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface`

##### `jsonDecode(): array`

**Throws:**

- `JsonException`

##### `getMerkleTreePayload(): string`

---

## RequestHandlers

### IndexPage

**class** `FediE2EE\PKDServer\RequestHandlers\IndexPage`

**File:** `src\RequestHandlers\IndexPage.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

## RequestHandlers\ActivityPub

### Finger

**class** `FediE2EE\PKDServer\RequestHandlers\ActivityPub\Finger`

**File:** `src\RequestHandlers\ActivityPub\Finger.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ActivityStreamsTrait`, `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `CertaintyException`
- `DependencyException`
- `GuzzleException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `getVerifiedStream(Psr\Http\Message\ServerRequestInterface $message): FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ActivityPubException`
- `DependencyException`
- `FetchException`
- `CryptoException`
- `HttpSignatureException`
- `NotImplementedException`
- `CertaintyException`
- `SodiumException`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

---

### Inbox

**class** `FediE2EE\PKDServer\RequestHandlers\ActivityPub\Inbox`

**File:** `src\RequestHandlers\ActivityPub\Inbox.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ActivityStreamsTrait`, `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `TableException`
- `DependencyException`
- `CacheException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `CertaintyException`
- `CryptoException`
- `DependencyException`
- `HttpSignatureException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `getVerifiedStream(Psr\Http\Message\ServerRequestInterface $message): FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ActivityPubException`
- `DependencyException`
- `FetchException`
- `CryptoException`
- `HttpSignatureException`
- `NotImplementedException`
- `CertaintyException`
- `SodiumException`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

---

### UserPage

**class** `FediE2EE\PKDServer\RequestHandlers\ActivityPub\UserPage`

**File:** `src\RequestHandlers\ActivityPub\UserPage.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ActivityStreamsTrait`, `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

##### `getVerifiedStream(Psr\Http\Message\ServerRequestInterface $message): FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ActivityPubException`
- `DependencyException`
- `FetchException`
- `CryptoException`
- `HttpSignatureException`
- `NotImplementedException`
- `CertaintyException`
- `SodiumException`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

---

## RequestHandlers\Api

### Actor

**class** `FediE2EE\PKDServer\RequestHandlers\Api\Actor`

**File:** `src\RequestHandlers\Api\Actor.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**API Method**

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### BurnDown

**class** `FediE2EE\PKDServer\RequestHandlers\Api\BurnDown`

**File:** `src\RequestHandlers\Api\BurnDown.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ActivityStreamsTrait`, `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `DependencyException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `CacheException`
- `CertaintyException`
- `CryptoException`
- `DependencyException`
- `HPKEException`
- `JsonException`
- `NotImplementedException`
- `ParserException`
- `SodiumException`
- `TableException`

##### `getVerifiedStream(Psr\Http\Message\ServerRequestInterface $message): FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ActivityPubException`
- `DependencyException`
- `FetchException`
- `CryptoException`
- `HttpSignatureException`
- `NotImplementedException`
- `CertaintyException`
- `SodiumException`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

---

### Checkpoint

**class** `FediE2EE\PKDServer\RequestHandlers\Api\Checkpoint`

**File:** `src\RequestHandlers\Api\Checkpoint.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### Extensions

**class** `FediE2EE\PKDServer\RequestHandlers\Api\Extensions`

**File:** `src\RequestHandlers\Api\Extensions.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### GetAuxData

**class** `FediE2EE\PKDServer\RequestHandlers\Api\GetAuxData`

**File:** `src\RequestHandlers\Api\GetAuxData.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**API Method**

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### GetKey

**class** `FediE2EE\PKDServer\RequestHandlers\Api\GetKey`

**File:** `src\RequestHandlers\Api\GetKey.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**API Method**

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### History

**class** `FediE2EE\PKDServer\RequestHandlers\Api\History`

**File:** `src\RequestHandlers\Api\History.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `DependencyException`
- `TableException`
- `CacheException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### HistoryCosign

**class** `FediE2EE\PKDServer\RequestHandlers\Api\HistoryCosign`

**File:** `src\RequestHandlers\Api\HistoryCosign.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `DependencyException`
- `TableException`
- `CacheException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Override]`, `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### HistorySince

**class** `FediE2EE\PKDServer\RequestHandlers\Api\HistorySince`

**File:** `src\RequestHandlers\Api\HistorySince.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `DependencyException`
- `TableException`
- `CacheException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### HistoryView

**class** `FediE2EE\PKDServer\RequestHandlers\Api\HistoryView`

**File:** `src\RequestHandlers\Api\HistoryView.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `DependencyException`
- `TableException`
- `CacheException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### Info

**class** `FediE2EE\PKDServer\RequestHandlers\Api\Info`

**File:** `src\RequestHandlers\Api\Info.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### ListAuxData

**class** `FediE2EE\PKDServer\RequestHandlers\Api\ListAuxData`

**File:** `src\RequestHandlers\Api\ListAuxData.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**API Method**

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### ListKeys

**class** `FediE2EE\PKDServer\RequestHandlers\Api\ListKeys`

**File:** `src\RequestHandlers\Api\ListKeys.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**API Method**

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### ReplicaInfo

**class** `FediE2EE\PKDServer\RequestHandlers\Api\ReplicaInfo`

**File:** `src\RequestHandlers\Api\ReplicaInfo.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### Replicas

**class** `FediE2EE\PKDServer\RequestHandlers\Api\Replicas`

**File:** `src\RequestHandlers\Api\Replicas.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### Revoke

**class** `FediE2EE\PKDServer\RequestHandlers\Api\Revoke`

**File:** `src\RequestHandlers\Api\Revoke.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `DependencyException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `TableException`
- `CryptoException`
- `ParserException`
- `HPKEException`
- `SodiumException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### ServerPublicKey

**class** `FediE2EE\PKDServer\RequestHandlers\Api\ServerPublicKey`

**File:** `src\RequestHandlers\Api\ServerPublicKey.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`
- `HPKEException`
- `InsecureCurveException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `cipherSuiteString(ParagonIE\HPKE\HPKE $hpke): string`

**Parameters:**

- `$hpke`: `ParagonIE\HPKE\HPKE`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### TotpDisenroll

**class** `FediE2EE\PKDServer\RequestHandlers\Api\TotpDisenroll`

**File:** `src\RequestHandlers\Api\TotpDisenroll.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`, `FediE2EE\PKDServer\Traits\TOTPTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `DependencyException`
- `TableException`
- `CacheException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CacheException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

##### `static verifyTOTP(string $secret, string $otp, int $windows = 2): bool`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int`

##### `static generateTOTP(string $secret, ?int $time = null): string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` (nullable)

##### `static ord(string $chr): int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

##### `throwIfTimeOutsideWindow(int $currentTime): void`

**Parameters:**

- `$currentTime`: `int`

**Throws:**

- `ProtocolException`

##### `constantTimeSelect(int $select, string $left, string $right): string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:**

- `CryptoException`

##### `static dos2unix(string $in): string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

##### `static preAuthEncode(array $pieces): string`

**Parameters:**

- `$pieces`: `array`

##### `static sortByKey(array $arr): void`

**Parameters:**

- `$arr`: `array`

##### `static LE64(int $n): string`

**Parameters:**

- `$n`: `int`

##### `stringToByteArray(string $str): array`

**Parameters:**

- `$str`: `string`

##### `static stripNewlines(string $input): string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

### TotpEnroll

**class** `FediE2EE\PKDServer\RequestHandlers\Api\TotpEnroll`

**File:** `src\RequestHandlers\Api\TotpEnroll.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`, `FediE2EE\PKDServer\Traits\TOTPTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `DependencyException`
- `TableException`
- `CacheException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CacheException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`
- `HPKEException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

##### `static verifyTOTP(string $secret, string $otp, int $windows = 2): bool`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int`

##### `static generateTOTP(string $secret, ?int $time = null): string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` (nullable)

##### `static ord(string $chr): int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

##### `throwIfTimeOutsideWindow(int $currentTime): void`

**Parameters:**

- `$currentTime`: `int`

**Throws:**

- `ProtocolException`

##### `constantTimeSelect(int $select, string $left, string $right): string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:**

- `CryptoException`

##### `static dos2unix(string $in): string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

##### `static preAuthEncode(array $pieces): string`

**Parameters:**

- `$pieces`: `array`

##### `static sortByKey(array $arr): void`

**Parameters:**

- `$arr`: `array`

##### `static LE64(int $n): string`

**Parameters:**

- `$n`: `int`

##### `stringToByteArray(string $str): array`

**Parameters:**

- `$str`: `string`

##### `static stripNewlines(string $input): string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

### TotpRotate

**class** `FediE2EE\PKDServer\RequestHandlers\Api\TotpRotate`

**File:** `src\RequestHandlers\Api\TotpRotate.php`

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`, `FediE2EE\PKDServer\Traits\TOTPTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(): void`

**Throws:**

- `DependencyException`
- `TableException`
- `CacheException`

##### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CacheException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `HPKEException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `ProtocolException`
- `RandomException`
- `SodiumException`
- `TableException`

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

##### `static verifyTOTP(string $secret, string $otp, int $windows = 2): bool`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int`

##### `static generateTOTP(string $secret, ?int $time = null): string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` (nullable)

##### `static ord(string $chr): int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

##### `throwIfTimeOutsideWindow(int $currentTime): void`

**Parameters:**

- `$currentTime`: `int`

**Throws:**

- `ProtocolException`

##### `constantTimeSelect(int $select, string $left, string $right): string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:**

- `CryptoException`

##### `static dos2unix(string $in): string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

##### `static preAuthEncode(array $pieces): string`

**Parameters:**

- `$pieces`: `array`

##### `static sortByKey(array $arr): void`

**Parameters:**

- `$arr`: `array`

##### `static LE64(int $n): string`

**Parameters:**

- `$n`: `int`

##### `stringToByteArray(string $str): array`

**Parameters:**

- `$str`: `string`

##### `static stripNewlines(string $input): string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## Scheduled

### ASQueue

**class** `FediE2EE\PKDServer\Scheduled\ASQueue`

**File:** `src\Scheduled\ASQueue.php`

#### Methods

##### `__construct(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

##### `run(): void`

ASQueue::run() is a very dumb method.

---

### Witness

**class** `FediE2EE\PKDServer\Scheduled\Witness`

**File:** `src\Scheduled\Witness.php`

Perform witness co-signatures for third-porty Public Key Directory instances.

**Uses:** `FediE2EE\PKDServer\Traits\ConfigTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `__construct(?FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` (nullable)

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `run(): void`

**Throws:**

- `DateMalformedStringException`

##### `getHashesSince(FediE2EE\PKDServer\Tables\Records\Peer $peer): array`

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

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

## Tables

### ActivityStreamQueue

**class** `FediE2EE\PKDServer\Tables\ActivityStreamQueue`

**File:** `src\Tables\ActivityStreamQueue.php`

**Extends:** `FediE2EE\PKDServer\Table`

#### Methods

##### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

##### `getNextPrimaryKey(): int`

##### `insert(FediE2EE\PKDServer\ActivityPub\ActivityStream $as): int`

**Parameters:**

- `$as`: `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Throws:**

- `ActivityPubException`

---

### Actors

**class** `FediE2EE\PKDServer\Tables\Actors`

**File:** `src\Tables\Actors.php`

**Extends:** `FediE2EE\PKDServer\Table`

#### Methods

##### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

##### `getNextPrimaryKey(): int`

##### `getActorByID(int $actorID): FediE2EE\PKDServer\Tables\Records\Actor`

**API Method**

When you already have a database ID, just fetch the object.

**Parameters:**

- `$actorID`: `int`

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`
- `InvalidCiphertextException`
- `SodiumException`
- `TableException`

##### `getCounts(int $actorID): array`

**Parameters:**

- `$actorID`: `int`

##### `searchForActor(string $canonicalActorID): ?FediE2EE\PKDServer\Tables\Records\Actor`

**API Method**

When you only have an ActivityPub Actor ID, first canonicalize it, then fetch the Actor object

**Parameters:**

- `$canonicalActorID`: `string`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CipherSweetException`
- `CryptoOperationException`
- `InvalidCiphertextException`
- `SodiumException`

##### `createActor(string $activityPubID, FediE2EE\PKDServer\Protocol\Payload $payload, ?FediE2EE\PKD\Crypto\PublicKey $key = null): int`

**Parameters:**

- `$activityPubID`: `string`
- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$key`: `?FediE2EE\PKD\Crypto\PublicKey` (nullable)

**Throws:**

- `ArrayKeyException`
- `CryptoOperationException`
- `CipherSweetException`
- `SodiumException`
- `ProtocolException`

##### `clearCacheForActor(FediE2EE\PKDServer\Tables\Records\Actor $actor): void`

**Parameters:**

- `$actor`: `FediE2EE\PKDServer\Tables\Records\Actor`

**Throws:**

- `TableException`

---

### AuxData

**class** `FediE2EE\PKDServer\Tables\AuxData`

**File:** `src\Tables\AuxData.php`

**Extends:** `FediE2EE\PKDServer\Table`

**Uses:** `FediE2EE\PKDServer\Traits\ProtocolMethodTrait`

#### Methods

##### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

##### `getAuxDataForActor(int $actorId): array`

**Parameters:**

- `$actorId`: `int`

**Throws:**

- `DateMalformedStringException`

##### `getAuxDataById(int $actorId, string $auxId): array`

**API Method**

**Parameters:**

- `$actorId`: `int`
- `$auxId`: `string`

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`
- `DateMalformedStringException`
- `InvalidCiphertextException`
- `JsonException`
- `SodiumException`

##### `addAuxData(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

##### `revokeAuxData(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

---

### MerkleState

**class** `FediE2EE\PKDServer\Tables\MerkleState`

**File:** `src\Tables\MerkleState.php`

Merkle State management

Insert new leaves

**Extends:** `FediE2EE\PKDServer\Table`

#### Methods

##### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

##### `getWitnessByOrigin(string $origin): array`

Return the witness data (including public key) for a given origin

**Parameters:**

- `$origin`: `string`

**Throws:**

- `TableException`

##### `addWitnessCosignature(string $origin, string $merkleRoot, string $cosignature): bool`

**API Method**

**Parameters:**

- `$origin`: `string`
- `$merkleRoot`: `string`
- `$cosignature`: `string`

**Throws:**

- `CryptoException`
- `JsonException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

##### `getCosignatures(int $leafId): array`

**Parameters:**

- `$leafId`: `int`

##### `countCosignatures(int $leafId): int`

**Parameters:**

- `$leafId`: `int`

##### `getLatestRoot(): string`

**API Method**

##### `insertLeaf(FediE2EE\PKDServer\Tables\Records\MerkleLeaf $leaf, callable $inTransaction, int $maxRetries = 5): bool`

**API Method**

Insert leaf with retry logic for deadlocks

**Parameters:**

- `$leaf`: `FediE2EE\PKDServer\Tables\Records\MerkleLeaf`
- `$inTransaction`: `callable`
- `$maxRetries`: `int`

**Throws:**

- `ConcurrentException`
- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `RandomException`
- `SodiumException`

##### `getLeafByRoot(string $root): ?FediE2EE\PKDServer\Tables\Records\MerkleLeaf`

**API Method**

**Parameters:**

- `$root`: `string`

##### `getLeafByID(int $primaryKey): ?FediE2EE\PKDServer\Tables\Records\MerkleLeaf`

**API Method**

**Parameters:**

- `$primaryKey`: `int`

##### `getHashesSince(string $oldRoot, int $limit, int $offset = 0): array`

**API Method**

**Parameters:**

- `$oldRoot`: `string`
- `$limit`: `int`
- `$offset`: `int`

---

### Peers

**class** `FediE2EE\PKDServer\Tables\Peers`

**File:** `src\Tables\Peers.php`

**Extends:** `FediE2EE\PKDServer\Table`

#### Methods

##### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

##### `getNextPeerId(): int`

##### `create(FediE2EE\PKD\Crypto\PublicKey $publicKey, string $hostname): FediE2EE\PKDServer\Tables\Records\Peer`

**API Method**

**Parameters:**

- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`
- `$hostname`: `string`

##### `getPeer(string $hostname): FediE2EE\PKDServer\Tables\Records\Peer`

**Parameters:**

- `$hostname`: `string`

##### `listAll(): array`

**API Method**

**Throws:**

- `DateMalformedStringException`

##### `save(FediE2EE\PKDServer\Tables\Records\Peer $peer): bool`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`

---

### PublicKeys

**class** `FediE2EE\PKDServer\Tables\PublicKeys`

**File:** `src\Tables\PublicKeys.php`

**Extends:** `FediE2EE\PKDServer\Table`

**Uses:** `FediE2EE\PKDServer\Traits\ProtocolMethodTrait`, `FediE2EE\PKDServer\Traits\TOTPTrait`

#### Methods

##### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

##### `generateKeyID(): string`

**Throws:**

- `RandomException`

##### `lookup(int $actorPrimaryKey, string $keyID): array`

**Parameters:**

- `$actorPrimaryKey`: `int`
- `$keyID`: `string`

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`
- `InvalidCiphertextException`
- `SodiumException`
- `DateMalformedStringException`
- `BaseJsonException`

##### `getRecord(int $primaryKey): FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$primaryKey`: `int`

**Throws:**

- `CacheException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `SodiumException`
- `TableException`

##### `getPublicKeysFor(string $actorName, string $keyId = ''): array`

**Parameters:**

- `$actorName`: `string`
- `$keyId`: `string`

**Throws:**

- `BaseJsonException`
- `CacheException`
- `CipherSweetException`
- `CryptoOperationException`
- `DateMalformedStringException`
- `DependencyException`
- `InvalidCiphertextException`
- `SodiumException`
- `TableException`

##### `getNextPrimaryKey(): int`

##### `addKey(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

##### `revokeKey(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

##### `revokeKeyThirdParty(FediE2EE\PKDServer\Protocol\Payload $payload): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

##### `moveIdentity(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

##### `burnDown(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

##### `fireproof(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

##### `undoFireproof(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

##### `checkpoint(FediE2EE\PKDServer\Protocol\Payload $payload): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

##### `static verifyTOTP(string $secret, string $otp, int $windows = 2): bool`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int`

##### `static generateTOTP(string $secret, ?int $time = null): string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` (nullable)

##### `static ord(string $chr): int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

##### `throwIfTimeOutsideWindow(int $currentTime): void`

**Parameters:**

- `$currentTime`: `int`

**Throws:**

- `ProtocolException`

##### `constantTimeSelect(int $select, string $left, string $right): string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:**

- `CryptoException`

##### `static dos2unix(string $in): string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

##### `static preAuthEncode(array $pieces): string`

**Parameters:**

- `$pieces`: `array`

##### `static sortByKey(array $arr): void`

**Parameters:**

- `$arr`: `array`

##### `static LE64(int $n): string`

**Parameters:**

- `$n`: `int`

##### `stringToByteArray(string $str): array`

**Parameters:**

- `$str`: `string`

##### `static stripNewlines(string $input): string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

### TOTP

**class** `FediE2EE\PKDServer\Tables\TOTP`

**File:** `src\Tables\TOTP.php`

**Extends:** `FediE2EE\PKDServer\Table`

#### Methods

##### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

##### `getSecretByDomain(string $domain): ?string`

**Parameters:**

- `$domain`: `string`

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`
- `SodiumException`
- `InvalidCiphertextException`

##### `saveSecret(string $domain, string $secret): void`

**Parameters:**

- `$domain`: `string`
- `$secret`: `string`

**Throws:**

- `ArrayKeyException`
- `CipherSweetException`
- `CryptoOperationException`
- `RandomException`
- `SodiumException`
- `TableException`

##### `deleteSecret(string $domain): void`

**Parameters:**

- `$domain`: `string`

##### `updateSecret(string $domain, string $secret): void`

**Parameters:**

- `$domain`: `string`
- `$secret`: `string`

**Throws:**

- `ArrayKeyException`
- `CipherSweetException`
- `CryptoOperationException`
- `SodiumException`
- `TableException`
- `RandomException`

---

## Tables\Records

### Actor

**final class** `FediE2EE\PKDServer\Tables\Records\Actor`

**File:** `src\Tables\Records\Actor.php`

Abstraction for a row in the Actors table

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$actorID` | `string` |  |
| `$rfc9421pk` | `?FediE2EE\PKD\Crypto\PublicKey` |  |
| `$fireProof` | `bool` |  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

#### Methods

##### `__construct(string $actorID, ?FediE2EE\PKD\Crypto\PublicKey $rfc9421pk = null, bool $fireProof = false, ?int $primaryKey = null): void`

**Parameters:**

- `$actorID`: `string`
- `$rfc9421pk`: `?FediE2EE\PKD\Crypto\PublicKey` (nullable)
- `$fireProof`: `bool`
- `$primaryKey`: `?int` (nullable)

##### `static create(string $actorID, string $rfc9421pk = '', bool $fireProof = false): self`

Instantiate a new object without a primary key

**Parameters:**

- `$actorID`: `string`
- `$rfc9421pk`: `string`
- `$fireProof`: `bool`

##### `toArray(): array`

##### `hasPrimaryKey(): bool`

##### `getPrimaryKey(): int`

**Throws:**

- `TableException`

##### `attachSymmetricKey(string $property, FediE2EE\PKD\Crypto\SymmetricKey $key): self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:**

- `TableException`

##### `getSymmetricKeyForProperty(string $property): FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:**

- `TableException`

##### `getSymmetricKeys(): array`

##### `getRfc9421PublicKeys(string $actorId): FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

**Parameters:**

- `$actorId`: `string`

**Throws:**

- `CryptoException`
- `FetchException`

---

### ActorKey

**class** `FediE2EE\PKDServer\Tables\Records\ActorKey`

**File:** `src\Tables\Records\ActorKey.php`

Abstraction for a row in the PublicKeys table

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$actor` | `FediE2EE\PKDServer\Tables\Records\Actor` |  |
| `$publicKey` | `FediE2EE\PKD\Crypto\PublicKey` |  |
| `$trusted` | `bool` |  |
| `$insertLeaf` | `FediE2EE\PKDServer\Tables\Records\MerkleLeaf` |  |
| `$revokeLeaf` | `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf` |  |
| `$keyID` | `?string` |  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

#### Methods

##### `__construct(FediE2EE\PKDServer\Tables\Records\Actor $actor, FediE2EE\PKD\Crypto\PublicKey $publicKey, bool $trusted, FediE2EE\PKDServer\Tables\Records\MerkleLeaf $insertLeaf, ?FediE2EE\PKDServer\Tables\Records\MerkleLeaf $revokeLeaf = null, ?string $keyID = null, ?int $primaryKey = null): void`

**Parameters:**

- `$actor`: `FediE2EE\PKDServer\Tables\Records\Actor`
- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`
- `$trusted`: `bool`
- `$insertLeaf`: `FediE2EE\PKDServer\Tables\Records\MerkleLeaf`
- `$revokeLeaf`: `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf` (nullable)
- `$keyID`: `?string` (nullable)
- `$primaryKey`: `?int` (nullable)

##### `hasPrimaryKey(): bool`

##### `getPrimaryKey(): int`

**Throws:**

- `TableException`

##### `attachSymmetricKey(string $property, FediE2EE\PKD\Crypto\SymmetricKey $key): self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:**

- `TableException`

##### `getSymmetricKeyForProperty(string $property): FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:**

- `TableException`

##### `getSymmetricKeys(): array`

##### `getRfc9421PublicKeys(string $actorId): FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

**Parameters:**

- `$actorId`: `string`

**Throws:**

- `CryptoException`
- `FetchException`

---

### AuxDatum

**class** `FediE2EE\PKDServer\Tables\Records\AuxDatum`

**File:** `src\Tables\Records\AuxDatum.php`

Abstraction for a row in the AuxData table

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$actor` | `FediE2EE\PKDServer\Tables\Records\Actor` |  |
| `$auxDataType` | `string` |  |
| `$auxData` | `string` |  |
| `$trusted` | `bool` |  |
| `$insertLeaf` | `FediE2EE\PKDServer\Tables\Records\MerkleLeaf` |  |
| `$revokeLeaf` | `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf` |  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

#### Methods

##### `__construct(FediE2EE\PKDServer\Tables\Records\Actor $actor, string $auxDataType, string $auxData, bool $trusted, FediE2EE\PKDServer\Tables\Records\MerkleLeaf $insertLeaf, ?FediE2EE\PKDServer\Tables\Records\MerkleLeaf $revokeLeaf = null, ?int $primaryKey = null): void`

**Parameters:**

- `$actor`: `FediE2EE\PKDServer\Tables\Records\Actor`
- `$auxDataType`: `string`
- `$auxData`: `string`
- `$trusted`: `bool`
- `$insertLeaf`: `FediE2EE\PKDServer\Tables\Records\MerkleLeaf`
- `$revokeLeaf`: `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf` (nullable)
- `$primaryKey`: `?int` (nullable)

##### `getActor(): FediE2EE\PKDServer\Tables\Records\Actor`

##### `hasPrimaryKey(): bool`

##### `getPrimaryKey(): int`

**Throws:**

- `TableException`

##### `attachSymmetricKey(string $property, FediE2EE\PKD\Crypto\SymmetricKey $key): self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:**

- `TableException`

##### `getSymmetricKeyForProperty(string $property): FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:**

- `TableException`

##### `getSymmetricKeys(): array`

##### `getRfc9421PublicKeys(string $actorId): FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

**Parameters:**

- `$actorId`: `string`

**Throws:**

- `CryptoException`
- `FetchException`

---

### MerkleLeaf

**class** `FediE2EE\PKDServer\Tables\Records\MerkleLeaf`

**File:** `src\Tables\Records\MerkleLeaf.php`

Abstraction for a row in the MerkleState table

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`, `FediE2EE\PKD\Crypto\UtilTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$contents` | `string` | (readonly)  |
| `$contentHash` | `string` | (readonly)  |
| `$signature` | `string` | (readonly)  |
| `$publicKeyHash` | `string` | (readonly)  |
| `$inclusionProof` | `?FediE2EE\PKD\Crypto\Merkle\InclusionProof` |  |
| `$created` | `string` | (readonly)  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

#### Methods

##### `__construct(string $contents, string $contentHash, string $signature, string $publicKeyHash, ?FediE2EE\PKD\Crypto\Merkle\InclusionProof $inclusionProof = null, string $created = '', ?int $primaryKey = null): void`

**Parameters:**

- `$contents`: `string`
- `$contentHash`: `string`
- `$signature`: `string`
- `$publicKeyHash`: `string`
- `$inclusionProof`: `?FediE2EE\PKD\Crypto\Merkle\InclusionProof` (nullable)
- `$created`: `string`
- `$primaryKey`: `?int` (nullable)

##### `static from(string $contents, FediE2EE\PKD\Crypto\SecretKey $sk): self`

**Parameters:**

- `$contents`: `string`
- `$sk`: `FediE2EE\PKD\Crypto\SecretKey`

**Throws:**

- `NotImplementedException`
- `SodiumException`

##### `static fromPayload(FediE2EE\PKDServer\Protocol\Payload $payload, FediE2EE\PKD\Crypto\SecretKey $sk): self`

**API Method**

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$sk`: `FediE2EE\PKD\Crypto\SecretKey`

##### `setPrimaryKey(?int $primary): static`

**Parameters:**

- `$primary`: `?int` (nullable)

##### `getContents(): array`

##### `getInclusionProof(): ?FediE2EE\PKD\Crypto\Merkle\InclusionProof`

**API Method**

##### `getSignature(): string`

##### `serializeForMerkle(): string`

##### `hasPrimaryKey(): bool`

##### `getPrimaryKey(): int`

**Throws:**

- `TableException`

##### `attachSymmetricKey(string $property, FediE2EE\PKD\Crypto\SymmetricKey $key): self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:**

- `TableException`

##### `getSymmetricKeyForProperty(string $property): FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:**

- `TableException`

##### `getSymmetricKeys(): array`

##### `getRfc9421PublicKeys(string $actorId): FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

**Parameters:**

- `$actorId`: `string`

**Throws:**

- `CryptoException`
- `FetchException`

##### `constantTimeSelect(int $select, string $left, string $right): string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:**

- `CryptoException`

##### `static dos2unix(string $in): string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

##### `static preAuthEncode(array $pieces): string`

**Parameters:**

- `$pieces`: `array`

##### `static sortByKey(array $arr): void`

**Parameters:**

- `$arr`: `array`

##### `static LE64(int $n): string`

**Parameters:**

- `$n`: `int`

##### `stringToByteArray(string $str): array`

**Parameters:**

- `$str`: `string`

##### `static stripNewlines(string $input): string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

### Peer

**class** `FediE2EE\PKDServer\Tables\Records\Peer`

**File:** `src\Tables\Records\Peer.php`

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`, `FediE2EE\PKD\Crypto\UtilTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$hostname` | `string` |  |
| `$publicKey` | `FediE2EE\PKD\Crypto\PublicKey` |  |
| `$tree` | `FediE2EE\PKD\Crypto\Merkle\IncrementalTree` |  |
| `$latestRoot` | `string` |  |
| `$created` | `DateTimeImmutable` |  |
| `$modified` | `DateTimeImmutable` |  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

#### Methods

##### `__construct(string $hostname, FediE2EE\PKD\Crypto\PublicKey $publicKey, FediE2EE\PKD\Crypto\Merkle\IncrementalTree $tree, string $latestRoot, DateTimeImmutable $created, DateTimeImmutable $modified, ?int $primaryKey = null): void`

**Parameters:**

- `$hostname`: `string`
- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`
- `$tree`: `FediE2EE\PKD\Crypto\Merkle\IncrementalTree`
- `$latestRoot`: `string`
- `$created`: `DateTimeImmutable`
- `$modified`: `DateTimeImmutable`
- `$primaryKey`: `?int` (nullable)

##### `toArray(): array`

##### `hasPrimaryKey(): bool`

##### `getPrimaryKey(): int`

**Throws:**

- `TableException`

##### `attachSymmetricKey(string $property, FediE2EE\PKD\Crypto\SymmetricKey $key): self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:**

- `TableException`

##### `getSymmetricKeyForProperty(string $property): FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:**

- `TableException`

##### `getSymmetricKeys(): array`

##### `getRfc9421PublicKeys(string $actorId): FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

**Parameters:**

- `$actorId`: `string`

**Throws:**

- `CryptoException`
- `FetchException`

##### `constantTimeSelect(int $select, string $left, string $right): string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:**

- `CryptoException`

##### `static dos2unix(string $in): string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

##### `static preAuthEncode(array $pieces): string`

**Parameters:**

- `$pieces`: `array`

##### `static sortByKey(array $arr): void`

**Parameters:**

- `$arr`: `array`

##### `static LE64(int $n): string`

**Parameters:**

- `$n`: `int`

##### `stringToByteArray(string $str): array`

**Parameters:**

- `$str`: `string`

##### `static stripNewlines(string $input): string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## Traits

### ActivityStreamsTrait

**trait** `FediE2EE\PKDServer\Traits\ActivityStreamsTrait`

**File:** `src\Traits\ActivityStreamsTrait.php`

**Uses:** `FediE2EE\PKDServer\Traits\ConfigTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `getVerifiedStream(Psr\Http\Message\ServerRequestInterface $message): FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ActivityPubException`
- `DependencyException`
- `FetchException`
- `CryptoException`
- `HttpSignatureException`
- `NotImplementedException`
- `CertaintyException`
- `SodiumException`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### ConfigTrait

**trait** `FediE2EE\PKDServer\Traits\ConfigTrait`

**File:** `src\Traits\ConfigTrait.php`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### ProtocolMethodTrait

**trait** `FediE2EE\PKDServer\Traits\ProtocolMethodTrait`

**File:** `src\Traits\ProtocolMethodTrait.php`

---

### ReqTrait

**trait** `FediE2EE\PKDServer\Traits\ReqTrait`

**File:** `src\Traits\ReqTrait.php`

Request Handler trait

**Uses:** `FediE2EE\PKDServer\Traits\ConfigTrait`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

#### Methods

##### `time(): string`

##### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

##### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

##### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

##### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

##### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

##### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

##### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

##### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

##### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

##### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

### TOTPTrait

**trait** `FediE2EE\PKDServer\Traits\TOTPTrait`

**File:** `src\Traits\TOTPTrait.php`

**Uses:** `FediE2EE\PKD\Crypto\UtilTrait`

#### Methods

##### `static verifyTOTP(string $secret, string $otp, int $windows = 2): bool`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int`

##### `static generateTOTP(string $secret, ?int $time = null): string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` (nullable)

##### `static ord(string $chr): int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

##### `throwIfTimeOutsideWindow(int $currentTime): void`

**Parameters:**

- `$currentTime`: `int`

**Throws:**

- `ProtocolException`

##### `constantTimeSelect(int $select, string $left, string $right): string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:**

- `CryptoException`

##### `static dos2unix(string $in): string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

##### `static preAuthEncode(array $pieces): string`

**Parameters:**

- `$pieces`: `array`

##### `static sortByKey(array $arr): void`

**Parameters:**

- `$arr`: `array`

##### `static LE64(int $n): string`

**Parameters:**

- `$n`: `int`

##### `stringToByteArray(string $str): array`

**Parameters:**

- `$str`: `string`

##### `static stripNewlines(string $input): string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

### TableRecordTrait

**trait** `FediE2EE\PKDServer\Traits\TableRecordTrait`

**File:** `src\Traits\TableRecordTrait.php`

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

#### Methods

##### `hasPrimaryKey(): bool`

##### `getPrimaryKey(): int`

**Throws:**

- `TableException`

##### `attachSymmetricKey(string $property, FediE2EE\PKD\Crypto\SymmetricKey $key): self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:**

- `TableException`

##### `getSymmetricKeyForProperty(string $property): FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:**

- `TableException`

##### `getSymmetricKeys(): array`

##### `getRfc9421PublicKeys(string $actorId): FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

**Parameters:**

- `$actorId`: `string`

**Throws:**

- `CryptoException`
- `FetchException`

---

