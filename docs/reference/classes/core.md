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

**Uses:** `FediE2EE\PKDServer\Traits\JsonTrait`

### Methods

#### [`__construct`](../../../src/AppCache.php#L26-L35)

Returns `void`

**Parameters:**

- `$serverConfig`: `FediE2EE\PKDServer\ServerConfig`
- `$namespace`: `string` = ''
- `$defaultTTL`: `int` = 60

#### [`cacheJson`](../../../src/AppCache.php#L46-L56)

Returns `mixed`

Cache as a JSON-serialized string, deserialize from cache.

Used for caching entire HTTP response data (arrays, etc.).)

**Parameters:**

- `$lookup`: `string`
- `$fallback`: `callable`
- `$ttl`: `DateInterval|int|null` = null

**Throws:** `InvalidArgumentException`, `SodiumException`, `JsonException`

#### [`cache`](../../../src/AppCache.php#L66-L75)

Returns `mixed`

If there is a cache-hit, it returns the value.

Otherwise, it invokes the fallback to determine the value.

**Parameters:**

- `$lookup`: `string`
- `$fallback`: `callable`
- `$ttl`: `DateInterval|int|null` = null

**Throws:** `InvalidArgumentException`, `SodiumException`

#### [`deriveKey`](../../../src/AppCache.php#L80-L83)

Returns `string`

**Parameters:**

- `$input`: `string`

**Throws:** `SodiumException`

#### [`get`](../../../src/AppCache.php#L86-L94)

Returns `mixed`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`
- `$default`: `mixed` = null

#### [`set`](../../../src/AppCache.php#L97-L105)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`
- `$value`: `mixed`
- `$ttl`: `DateInterval|int|null` = null

#### [`delete`](../../../src/AppCache.php#L108-L116)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`

#### [`clear`](../../../src/AppCache.php#L119-L127)

Returns `bool`

**Attributes:** `#[Override]`

#### [`getMultiple`](../../../src/AppCache.php#L130-L142)

Returns `array`

**Attributes:** `#[Override]`

**Parameters:**

- `$keys`: `iterable`
- `$default`: `mixed` = null

#### [`setMultiple`](../../../src/AppCache.php#L145-L155)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$values`: `iterable`
- `$ttl`: `DateInterval|int|null` = null

#### [`deleteMultiple`](../../../src/AppCache.php#L158-L171)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$keys`: `iterable`

#### [`has`](../../../src/AppCache.php#L174-L180)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`

#### [`processTTL`](../../../src/AppCache.php#L188-L201)

Returns `int`

Collapse multiple types into a number of seconds for Redis.

**Parameters:**

- `$ttl`: `DateInterval|int|null`

#### [`jsonDecode`](../../../src/AppCache.php#L12-L15)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/AppCache.php#L20-L23)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/AppCache.php#L28-L34)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## Math

**abstract class** `FediE2EE\PKDServer\Math`

**File:** [`src/Math.php`](../../../src/Math.php)

### Methods

#### [`getHighVolumeCutoff`](../../../src/Math.php#L7-L10)

static · Returns `int`

**Parameters:**

- `$numLeaves`: `int`

#### [`getLowVolumeCutoff`](../../../src/Math.php#L12-L19)

static · Returns `int`

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

#### [`__construct`](../../../src/Protocol.php#L53-L60)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`process`](../../../src/Protocol.php#L73-L201)

Returns `array`

**Parameters:**

- `$enqueued`: `FediE2EE\PKDServer\ActivityPub\ActivityStream`
- `$isActivityPub`: `bool` = true

**Throws:** `CryptoException`, `DependencyException`, `Exceptions\CacheException`, `HPKEException`, `NotImplementedException`, `ParserException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`webfinger`](../../../src/Protocol.php#L233-L239)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `DependencyException`, `SodiumException`, `CertaintyException`

#### [`setWebFinger`](../../../src/Protocol.php#L247-L251)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`addKey`](../../../src/Protocol.php#L280-L289)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `CryptoException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`revokeKey`](../../../src/Protocol.php#L303-L312)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `CryptoException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`revokeKeyThirdParty`](../../../src/Protocol.php#L326-L341)

Returns `bool`

**Parameters:**

- `$body`: `string`

**Throws:** `BundleException`, `CacheException`, `CryptoException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`moveIdentity`](../../../src/Protocol.php#L355-L364)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `CryptoException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`burnDown`](../../../src/Protocol.php#L378-L391)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `CryptoException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`fireproof`](../../../src/Protocol.php#L405-L414)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `CryptoException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`undoFireproof`](../../../src/Protocol.php#L428-L437)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `CryptoException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`addAuxData`](../../../src/Protocol.php#L451-L460)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `CryptoException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`revokeAuxData`](../../../src/Protocol.php#L475-L484)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `CryptoException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`checkpoint`](../../../src/Protocol.php#L498-L512)

Returns `bool`

**Parameters:**

- `$body`: `string`

**Throws:** `BundleException`, `CacheException`, `CryptoException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`appCache`](../../../src/Protocol.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Protocol.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Protocol.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Protocol.php#L87-L97)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`jsonDecode`](../../../src/Protocol.php#L12-L15)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Protocol.php#L20-L23)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Protocol.php#L28-L34)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## Redirect

**class** `FediE2EE\PKDServer\Redirect`

**File:** [`src/Redirect.php`](../../../src/Redirect.php)

Abstracts an HTTP Redirect

### Methods

#### [`__construct`](../../../src/Redirect.php#L17-L21)

Returns `void`

**Parameters:**

- `$url`: `Psr\Http\Message\UriInterface|string`
- `$message`: `string` = ''
- `$status`: `int` = 301

#### [`respond`](../../../src/Redirect.php#L23-L34)

Returns `Psr\Http\Message\ResponseInterface`

---

## ServerConfig

**class** `FediE2EE\PKDServer\ServerConfig`

**File:** [`src/ServerConfig.php`](../../../src/ServerConfig.php)

### Methods

#### [`__construct`](../../../src/ServerConfig.php#L38)

Returns `void`

**Parameters:**

- `$params`: `FediE2EE\PKDServer\Meta\Params`

#### [`getCaCertFetch`](../../../src/ServerConfig.php#L43-L49)

Returns `ParagonIE\Certainty\Fetch`

**Throws:** `DependencyException`

#### [`getAuxDataTypeAllowList`](../../../src/ServerConfig.php#L54-L57)

**API** · Returns `array`

#### [`getAuxDataRegistry`](../../../src/ServerConfig.php#L59-L65)

Returns `FediE2EE\PKD\Extensions\Registry`

#### [`getGuzzle`](../../../src/ServerConfig.php#L67-L72)

Returns `GuzzleHttp\Client`

#### [`getCipherSweet`](../../../src/ServerConfig.php#L77-L83)

Returns `ParagonIE\CipherSweet\CipherSweet`

**Throws:** `DependencyException`

#### [`getDb`](../../../src/ServerConfig.php#L89-L95)

**API** · Returns `ParagonIE\EasyDB\EasyDB`

**Throws:** `DependencyException`

#### [`getHPKE`](../../../src/ServerConfig.php#L101-L107)

**API** · Returns `FediE2EE\PKDServer\Dependency\HPKE`

**Throws:** `DependencyException`

#### [`getLogger`](../../../src/ServerConfig.php#L109-L115)

Returns `Monolog\Logger`

#### [`getParams`](../../../src/ServerConfig.php#L117-L120)

Returns `FediE2EE\PKDServer\Meta\Params`

#### [`getSigningKeys`](../../../src/ServerConfig.php#L126-L132)

**API** · Returns `FediE2EE\PKDServer\Dependency\SigningKeys`

**Throws:** `DependencyException`

#### [`getRateLimit`](../../../src/ServerConfig.php#L138-L144)

**API** · Returns `FediE2EE\PKDServer\Interfaces\RateLimitInterface`

**Throws:** `DependencyException`

#### [`getRouter`](../../../src/ServerConfig.php#L150-L156)

**API** · Returns `League\Route\Router`

**Throws:** `DependencyException`

#### [`getTwig`](../../../src/ServerConfig.php#L162-L168)

**API** · Returns `Twig\Environment`

**Throws:** `DependencyException`

#### [`getRedis`](../../../src/ServerConfig.php#L170-L173)

Returns `?Predis\Client`

#### [`hasRedis`](../../../src/ServerConfig.php#L175-L178)

Returns `bool`

#### [`withAuxDataTypeAllowList`](../../../src/ServerConfig.php#L184-L188)

Returns `static`

**Parameters:**

- `$allowList`: `array` = []

#### [`withAuxDataRegistry`](../../../src/ServerConfig.php#L190-L194)

Returns `static`

**Parameters:**

- `$registry`: `FediE2EE\PKD\Extensions\Registry`

#### [`withCACertFetch`](../../../src/ServerConfig.php#L196-L200)

Returns `static`

**Parameters:**

- `$fetch`: `ParagonIE\Certainty\Fetch`

#### [`withCipherSweet`](../../../src/ServerConfig.php#L202-L206)

Returns `static`

**Parameters:**

- `$ciphersweet`: `ParagonIE\CipherSweet\CipherSweet`

#### [`withDatabase`](../../../src/ServerConfig.php#L208-L212)

Returns `static`

**Parameters:**

- `$db`: `ParagonIE\EasyDB\EasyDB`

#### [`withHPKE`](../../../src/ServerConfig.php#L214-L218)

Returns `static`

**Parameters:**

- `$hpke`: `FediE2EE\PKDServer\Dependency\HPKE`

#### [`withLogger`](../../../src/ServerConfig.php#L220-L224)

Returns `static`

**Parameters:**

- `$logger`: `Monolog\Logger`

#### [`withOptionalRedisClient`](../../../src/ServerConfig.php#L226-L238)

Returns `static`

**Parameters:**

- `$redis`: `?Predis\Client` = null

#### [`withRateLimit`](../../../src/ServerConfig.php#L240-L244)

Returns `static`

**Parameters:**

- `$rateLimit`: `FediE2EE\PKDServer\Interfaces\RateLimitInterface`

#### [`withRouter`](../../../src/ServerConfig.php#L246-L250)

Returns `static`

**Parameters:**

- `$router`: `League\Route\Router`

#### [`withSigningKeys`](../../../src/ServerConfig.php#L252-L256)

Returns `static`

**Parameters:**

- `$signingKeys`: `FediE2EE\PKDServer\Dependency\SigningKeys`

#### [`withTwig`](../../../src/ServerConfig.php#L258-L262)

Returns `static`

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

#### [`__construct`](../../../src/Table.php#L29-L34)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`getCipher`](../../../src/Table.php#L36)

abstract · Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

#### [`clearCache`](../../../src/Table.php#L40-L43)

Returns `void`

#### [`convertKey`](../../../src/Table.php#L45-L48)

Returns `ParagonIE\CipherSweet\Backend\Key\SymmetricKey`

**Parameters:**

- `$inputKey`: `FediE2EE\PKD\Crypto\SymmetricKey`

#### [`assertRecentMerkleRoot`](../../../src/Table.php#L53-L66)

Returns `void`

**Parameters:**

- `$recentMerkle`: `string`

**Throws:** `ProtocolException`

#### [`isMerkleRootRecent`](../../../src/Table.php#L71-L127)

**API** · Returns `bool`

**Parameters:**

- `$merkleRoot`: `string`
- `$isHighVolume`: `bool` = false

#### [`appCache`](../../../src/Table.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Table.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Table.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Table.php#L87-L97)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Table.php#L102-L106)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Table.php#L113-L120)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`jsonDecode`](../../../src/Table.php#L12-L15)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Table.php#L20-L23)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Table.php#L28-L34)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## TableCache

**class** `FediE2EE\PKDServer\TableCache`

**File:** [`src/TableCache.php`](../../../src/TableCache.php)

### Methods

#### [`instance`](../../../src/TableCache.php#L15-L21)

static · Returns `self`

#### [`clearCache`](../../../src/TableCache.php#L23-L26)

Returns `void`

#### [`fetchTable`](../../../src/TableCache.php#L31-L37)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`

#### [`hasTable`](../../../src/TableCache.php#L39-L42)

Returns `bool`

**Parameters:**

- `$tableName`: `string`

#### [`storeTable`](../../../src/TableCache.php#L44-L48)

Returns `static`

**Parameters:**

- `$tableName`: `string`
- `$table`: `FediE2EE\PKDServer\Table`

---

