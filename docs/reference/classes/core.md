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

#### [`__construct`](../../../src/AppCache.php#L36-L45)

Returns `void`

**Parameters:**

- `$serverConfig`: `FediE2EE\PKDServer\ServerConfig`
- `$namespace`: `string` = ''
- `$defaultTTL`: `int` = 60

#### [`cacheJson`](../../../src/AppCache.php#L56-L66)

Returns `mixed`

Cache as a JSON-serialized string, deserialize from cache.

Used for caching entire HTTP response data (arrays, etc.).)

**Parameters:**

- `$lookup`: `string`
- `$fallback`: `callable`
- `$ttl`: `DateInterval|int|null` = null

**Throws:** `InvalidArgumentException`, `SodiumException`, `JsonException`

#### [`cache`](../../../src/AppCache.php#L76-L85)

Returns `mixed`

If there is a cache-hit, it returns the value.

Otherwise, it invokes the fallback to determine the value.

**Parameters:**

- `$lookup`: `string`
- `$fallback`: `callable`
- `$ttl`: `DateInterval|int|null` = null

**Throws:** `InvalidArgumentException`, `SodiumException`

#### [`deriveKey`](../../../src/AppCache.php#L90-L93)

Returns `string`

**Parameters:**

- `$input`: `string`

**Throws:** `SodiumException`

#### [`get`](../../../src/AppCache.php#L96-L104)

Returns `mixed`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`
- `$default`: `mixed` = null

#### [`set`](../../../src/AppCache.php#L107-L115)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`
- `$value`: `mixed`
- `$ttl`: `DateInterval|int|null` = null

#### [`delete`](../../../src/AppCache.php#L118-L126)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`

#### [`clear`](../../../src/AppCache.php#L129-L137)

Returns `bool`

**Attributes:** `#[Override]`

#### [`getMultiple`](../../../src/AppCache.php#L146-L159)

Returns `array`

**Attributes:** `#[Override]`

**Parameters:**

- `$keys`: `iterable`
- `$default`: `mixed` = null

**Throws:** `InvalidArgumentException`

#### [`setMultiple`](../../../src/AppCache.php#L167-L177)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$values`: `iterable`
- `$ttl`: `DateInterval|int|null` = null

**Throws:** `InvalidArgumentException`

#### [`deleteMultiple`](../../../src/AppCache.php#L180-L193)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$keys`: `iterable`

#### [`has`](../../../src/AppCache.php#L196-L202)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`

#### [`processTTL`](../../../src/AppCache.php#L210-L223)

Returns `int`

Collapse multiple types into a number of seconds for Redis.

**Parameters:**

- `$ttl`: `DateInterval|int|null`

#### [`jsonDecode`](../../../src/AppCache.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/AppCache.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/AppCache.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## Math

**abstract class** `FediE2EE\PKDServer\Math`

**File:** [`src/Math.php`](../../../src/Math.php)

### Methods

#### [`getHighVolumeCutoff`](../../../src/Math.php#L10-L13)

static · Returns `int`

**Parameters:**

- `$numLeaves`: `int`

#### [`getLowVolumeCutoff`](../../../src/Math.php#L15-L22)

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

#### [`__construct`](../../../src/Protocol.php#L58-L65)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`process`](../../../src/Protocol.php#L85-L223)

Returns `array`

**Parameters:**

- `$enqueued`: `FediE2EE\PKDServer\ActivityPub\ActivityStream`
- `$isActivityPub`: `bool` = true

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ParserException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`webfinger`](../../../src/Protocol.php#L258-L264)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `DependencyException`, `SodiumException`, `CertaintyException`

#### [`setWebFinger`](../../../src/Protocol.php#L272-L276)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`addKey`](../../../src/Protocol.php#L308-L317)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`revokeKey`](../../../src/Protocol.php#L334-L343)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`revokeKeyThirdParty`](../../../src/Protocol.php#L360-L375)

Returns `bool`

**Parameters:**

- `$body`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`moveIdentity`](../../../src/Protocol.php#L392-L401)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`burnDown`](../../../src/Protocol.php#L428-L442)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`fireproof`](../../../src/Protocol.php#L459-L468)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`undoFireproof`](../../../src/Protocol.php#L485-L494)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`addAuxData`](../../../src/Protocol.php#L511-L520)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`revokeAuxData`](../../../src/Protocol.php#L537-L546)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`checkpoint`](../../../src/Protocol.php#L563-L577)

Returns `bool`

**Parameters:**

- `$body`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`appCache`](../../../src/Protocol.php#L55-L58)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Protocol.php#L65-L88)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Protocol.php#L90-L93)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Protocol.php#L98-L108)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`parseUrlHost`](../../../src/Protocol.php#L137-L144)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Protocol.php#L152-L158)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Protocol.php#L163-L169)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Protocol.php#L171-L180)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Protocol.php#L185-L194)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Protocol.php#L201-L211)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Protocol.php#L217-L227)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Protocol.php#L234-L244)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Protocol.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Protocol.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Protocol.php#L34-L40)

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

#### [`__construct`](../../../src/ServerConfig.php#L43)

Returns `void`

**Parameters:**

- `$params`: `FediE2EE\PKDServer\Meta\Params`

#### [`getCaCertFetch`](../../../src/ServerConfig.php#L48-L54)

Returns `ParagonIE\Certainty\Fetch`

**Throws:** `DependencyException`

#### [`getAuxDataTypeAllowList`](../../../src/ServerConfig.php#L60-L63)

**API** · Returns `array`

#### [`getAuxDataRegistry`](../../../src/ServerConfig.php#L65-L71)

Returns `FediE2EE\PKD\Extensions\Registry`

#### [`getGuzzle`](../../../src/ServerConfig.php#L78-L83)

Returns `GuzzleHttp\Client`

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`getCipherSweet`](../../../src/ServerConfig.php#L88-L94)

Returns `ParagonIE\CipherSweet\CipherSweet`

**Throws:** `DependencyException`

#### [`getDb`](../../../src/ServerConfig.php#L100-L106)

**API** · Returns `ParagonIE\EasyDB\EasyDB`

**Throws:** `DependencyException`

#### [`getHPKE`](../../../src/ServerConfig.php#L112-L118)

**API** · Returns `FediE2EE\PKDServer\Dependency\HPKE`

**Throws:** `DependencyException`

#### [`getLogger`](../../../src/ServerConfig.php#L120-L126)

Returns `Monolog\Logger`

#### [`getParams`](../../../src/ServerConfig.php#L128-L131)

Returns `FediE2EE\PKDServer\Meta\Params`

#### [`getSigningKeys`](../../../src/ServerConfig.php#L137-L143)

**API** · Returns `FediE2EE\PKDServer\Dependency\SigningKeys`

**Throws:** `DependencyException`

#### [`getRateLimit`](../../../src/ServerConfig.php#L149-L155)

**API** · Returns `FediE2EE\PKDServer\Interfaces\RateLimitInterface`

**Throws:** `DependencyException`

#### [`getRouter`](../../../src/ServerConfig.php#L161-L167)

**API** · Returns `League\Route\Router`

**Throws:** `DependencyException`

#### [`getTwig`](../../../src/ServerConfig.php#L173-L179)

**API** · Returns `Twig\Environment`

**Throws:** `DependencyException`

#### [`getRedis`](../../../src/ServerConfig.php#L181-L184)

Returns `?Predis\Client`

#### [`hasRedis`](../../../src/ServerConfig.php#L186-L189)

Returns `bool`

#### [`withAuxDataTypeAllowList`](../../../src/ServerConfig.php#L195-L199)

Returns `static`

**Parameters:**

- `$allowList`: `array` = []

#### [`withAuxDataRegistry`](../../../src/ServerConfig.php#L201-L205)

Returns `static`

**Parameters:**

- `$registry`: `FediE2EE\PKD\Extensions\Registry`

#### [`withCACertFetch`](../../../src/ServerConfig.php#L207-L211)

Returns `static`

**Parameters:**

- `$fetch`: `ParagonIE\Certainty\Fetch`

#### [`withCipherSweet`](../../../src/ServerConfig.php#L213-L217)

Returns `static`

**Parameters:**

- `$ciphersweet`: `ParagonIE\CipherSweet\CipherSweet`

#### [`withDatabase`](../../../src/ServerConfig.php#L219-L223)

Returns `static`

**Parameters:**

- `$db`: `ParagonIE\EasyDB\EasyDB`

#### [`withHPKE`](../../../src/ServerConfig.php#L225-L229)

Returns `static`

**Parameters:**

- `$hpke`: `FediE2EE\PKDServer\Dependency\HPKE`

#### [`withLogger`](../../../src/ServerConfig.php#L231-L235)

Returns `static`

**Parameters:**

- `$logger`: `Monolog\Logger`

#### [`withOptionalRedisClient`](../../../src/ServerConfig.php#L237-L249)

Returns `static`

**Parameters:**

- `$redis`: `?Predis\Client` = null

#### [`withRateLimit`](../../../src/ServerConfig.php#L251-L255)

Returns `static`

**Parameters:**

- `$rateLimit`: `FediE2EE\PKDServer\Interfaces\RateLimitInterface`

#### [`withRouter`](../../../src/ServerConfig.php#L257-L261)

Returns `static`

**Parameters:**

- `$router`: `League\Route\Router`

#### [`withSigningKeys`](../../../src/ServerConfig.php#L263-L267)

Returns `static`

**Parameters:**

- `$signingKeys`: `FediE2EE\PKDServer\Dependency\SigningKeys`

#### [`withTwig`](../../../src/ServerConfig.php#L269-L273)

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

#### [`__construct`](../../../src/Table.php#L33-L38)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`getCipher`](../../../src/Table.php#L40)

abstract · Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

#### [`clearCache`](../../../src/Table.php#L47-L50)

Returns `void`

#### [`convertKey`](../../../src/Table.php#L52-L55)

Returns `ParagonIE\CipherSweet\Backend\Key\SymmetricKey`

**Parameters:**

- `$inputKey`: `FediE2EE\PKD\Crypto\SymmetricKey`

#### [`assertRecentMerkleRoot`](../../../src/Table.php#L62-L75)

Returns `void`

**Parameters:**

- `$recentMerkle`: `string`

**Throws:** `DependencyException`, `ProtocolException`, `SodiumException`

#### [`isMerkleRootRecent`](../../../src/Table.php#L83-L139)

**API** · Returns `bool`

**Parameters:**

- `$merkleRoot`: `string`
- `$isHighVolume`: `bool` = false

**Throws:** `DependencyException`, `SodiumException`

#### [`appCache`](../../../src/Table.php#L55-L58)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Table.php#L65-L88)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Table.php#L90-L93)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Table.php#L98-L108)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Table.php#L113-L117)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Table.php#L124-L131)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Table.php#L137-L144)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Table.php#L152-L158)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Table.php#L163-L169)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Table.php#L171-L180)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Table.php#L185-L194)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Table.php#L201-L211)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Table.php#L217-L227)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Table.php#L234-L244)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Table.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Table.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Table.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## TableCache

**class** `FediE2EE\PKDServer\TableCache`

**File:** [`src/TableCache.php`](../../../src/TableCache.php)

### Methods

#### [`instance`](../../../src/TableCache.php#L20-L26)

static · Returns `self`

#### [`clearCache`](../../../src/TableCache.php#L28-L31)

Returns `void`

#### [`fetchTable`](../../../src/TableCache.php#L36-L42)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`

#### [`hasTable`](../../../src/TableCache.php#L44-L47)

Returns `bool`

**Parameters:**

- `$tableName`: `string`

#### [`storeTable`](../../../src/TableCache.php#L49-L53)

Returns `static`

**Parameters:**

- `$tableName`: `string`
- `$table`: `FediE2EE\PKDServer\Table`

---

