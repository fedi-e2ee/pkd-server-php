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

#### [`__construct`](../../../src/AppCache.php#L35-L44)

Returns `void`

**Parameters:**

- `$serverConfig`: `FediE2EE\PKDServer\ServerConfig`
- `$namespace`: `string` = ''
- `$defaultTTL`: `int` = 60

#### [`cacheJson`](../../../src/AppCache.php#L55-L65)

Returns `mixed`

Cache as a JSON-serialized string, deserialize from cache.

Used for caching entire HTTP response data (arrays, etc.).)

**Parameters:**

- `$lookup`: `string`
- `$fallback`: `callable`
- `$ttl`: `DateInterval|int|null` = null

**Throws:** `InvalidArgumentException`, `SodiumException`, `JsonException`

#### [`cache`](../../../src/AppCache.php#L75-L84)

Returns `mixed`

If there is a cache-hit, it returns the value.

Otherwise, it invokes the fallback to determine the value.

**Parameters:**

- `$lookup`: `string`
- `$fallback`: `callable`
- `$ttl`: `DateInterval|int|null` = null

**Throws:** `InvalidArgumentException`, `SodiumException`

#### [`deriveKey`](../../../src/AppCache.php#L89-L92)

Returns `string`

**Parameters:**

- `$input`: `string`

**Throws:** `SodiumException`

#### [`get`](../../../src/AppCache.php#L95-L103)

Returns `mixed`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`
- `$default`: `mixed` = null

#### [`set`](../../../src/AppCache.php#L106-L114)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`
- `$value`: `mixed`
- `$ttl`: `DateInterval|int|null` = null

#### [`delete`](../../../src/AppCache.php#L117-L125)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`

#### [`clear`](../../../src/AppCache.php#L128-L136)

Returns `bool`

**Attributes:** `#[Override]`

#### [`getMultiple`](../../../src/AppCache.php#L145-L158)

Returns `array`

**Attributes:** `#[Override]`

**Parameters:**

- `$keys`: `iterable`
- `$default`: `mixed` = null

**Throws:** `InvalidArgumentException`

#### [`setMultiple`](../../../src/AppCache.php#L166-L176)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$values`: `iterable`
- `$ttl`: `DateInterval|int|null` = null

**Throws:** `InvalidArgumentException`

#### [`deleteMultiple`](../../../src/AppCache.php#L179-L192)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$keys`: `iterable`

#### [`has`](../../../src/AppCache.php#L195-L201)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`

#### [`processTTL`](../../../src/AppCache.php#L209-L222)

Returns `int`

Collapse multiple types into a number of seconds for Redis.

**Parameters:**

- `$ttl`: `DateInterval|int|null`

#### [`jsonDecode`](../../../src/AppCache.php#L16-L19)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/AppCache.php#L24-L27)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/AppCache.php#L33-L39)

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

#### [`__construct`](../../../src/Protocol.php#L57-L64)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`process`](../../../src/Protocol.php#L84-L126)

Returns `array`

**Parameters:**

- `$enqueued`: `FediE2EE\PKDServer\ActivityPub\ActivityStream`
- `$isActivityPub`: `bool` = true

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ParserException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`webfinger`](../../../src/Protocol.php#L307-L313)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `DependencyException`, `SodiumException`, `CertaintyException`

#### [`setWebFinger`](../../../src/Protocol.php#L321-L325)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`addKey`](../../../src/Protocol.php#L357-L366)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`revokeKey`](../../../src/Protocol.php#L383-L392)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`revokeKeyThirdParty`](../../../src/Protocol.php#L409-L424)

Returns `bool`

**Parameters:**

- `$body`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`moveIdentity`](../../../src/Protocol.php#L441-L450)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`burnDown`](../../../src/Protocol.php#L477-L491)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`fireproof`](../../../src/Protocol.php#L508-L517)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`undoFireproof`](../../../src/Protocol.php#L534-L543)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`addAuxData`](../../../src/Protocol.php#L560-L569)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`revokeAuxData`](../../../src/Protocol.php#L586-L595)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`checkpoint`](../../../src/Protocol.php#L612-L626)

Returns `bool`

**Parameters:**

- `$body`: `string`

**Throws:** `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`appCache`](../../../src/Protocol.php#L54-L57)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Protocol.php#L64-L87)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Protocol.php#L89-L92)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Protocol.php#L97-L107)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`parseUrlHost`](../../../src/Protocol.php#L136-L143)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Protocol.php#L151-L157)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Protocol.php#L162-L168)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Protocol.php#L170-L179)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Protocol.php#L184-L193)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Protocol.php#L200-L210)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Protocol.php#L216-L226)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Protocol.php#L233-L243)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Protocol.php#L16-L19)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Protocol.php#L24-L27)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Protocol.php#L33-L39)

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

#### [`__construct`](../../../src/ServerConfig.php#L42)

Returns `void`

**Parameters:**

- `$params`: `FediE2EE\PKDServer\Meta\Params`

#### [`getCaCertFetch`](../../../src/ServerConfig.php#L47-L53)

Returns `ParagonIE\Certainty\Fetch`

**Throws:** `DependencyException`

#### [`getAuxDataTypeAllowList`](../../../src/ServerConfig.php#L59-L62)

**API** · Returns `array`

#### [`getAuxDataRegistry`](../../../src/ServerConfig.php#L64-L70)

Returns `FediE2EE\PKD\Extensions\Registry`

#### [`getGuzzle`](../../../src/ServerConfig.php#L77-L82)

Returns `GuzzleHttp\Client`

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`getCipherSweet`](../../../src/ServerConfig.php#L87-L93)

Returns `ParagonIE\CipherSweet\CipherSweet`

**Throws:** `DependencyException`

#### [`getDb`](../../../src/ServerConfig.php#L99-L105)

**API** · Returns `ParagonIE\EasyDB\EasyDB`

**Throws:** `DependencyException`

#### [`getHPKE`](../../../src/ServerConfig.php#L111-L117)

**API** · Returns `FediE2EE\PKDServer\Dependency\HPKE`

**Throws:** `DependencyException`

#### [`getLogger`](../../../src/ServerConfig.php#L119-L125)

Returns `Monolog\Logger`

#### [`getParams`](../../../src/ServerConfig.php#L127-L130)

Returns `FediE2EE\PKDServer\Meta\Params`

#### [`getSigningKeys`](../../../src/ServerConfig.php#L136-L142)

**API** · Returns `FediE2EE\PKDServer\Dependency\SigningKeys`

**Throws:** `DependencyException`

#### [`getRateLimit`](../../../src/ServerConfig.php#L148-L154)

**API** · Returns `FediE2EE\PKDServer\Interfaces\RateLimitInterface`

**Throws:** `DependencyException`

#### [`getRouter`](../../../src/ServerConfig.php#L160-L166)

**API** · Returns `League\Route\Router`

**Throws:** `DependencyException`

#### [`getTwig`](../../../src/ServerConfig.php#L172-L178)

**API** · Returns `Twig\Environment`

**Throws:** `DependencyException`

#### [`getRedis`](../../../src/ServerConfig.php#L180-L183)

Returns `?Predis\Client`

#### [`hasRedis`](../../../src/ServerConfig.php#L185-L188)

Returns `bool`

#### [`withAuxDataTypeAllowList`](../../../src/ServerConfig.php#L194-L198)

Returns `static`

**Parameters:**

- `$allowList`: `array` = []

#### [`withAuxDataRegistry`](../../../src/ServerConfig.php#L200-L204)

Returns `static`

**Parameters:**

- `$registry`: `FediE2EE\PKD\Extensions\Registry`

#### [`withCACertFetch`](../../../src/ServerConfig.php#L206-L210)

Returns `static`

**Parameters:**

- `$fetch`: `ParagonIE\Certainty\Fetch`

#### [`withCipherSweet`](../../../src/ServerConfig.php#L212-L216)

Returns `static`

**Parameters:**

- `$ciphersweet`: `ParagonIE\CipherSweet\CipherSweet`

#### [`withDatabase`](../../../src/ServerConfig.php#L218-L222)

Returns `static`

**Parameters:**

- `$db`: `ParagonIE\EasyDB\EasyDB`

#### [`withHPKE`](../../../src/ServerConfig.php#L224-L228)

Returns `static`

**Parameters:**

- `$hpke`: `FediE2EE\PKDServer\Dependency\HPKE`

#### [`withLogger`](../../../src/ServerConfig.php#L230-L234)

Returns `static`

**Parameters:**

- `$logger`: `Monolog\Logger`

#### [`withOptionalRedisClient`](../../../src/ServerConfig.php#L236-L248)

Returns `static`

**Parameters:**

- `$redis`: `?Predis\Client` = null

#### [`withRateLimit`](../../../src/ServerConfig.php#L250-L254)

Returns `static`

**Parameters:**

- `$rateLimit`: `FediE2EE\PKDServer\Interfaces\RateLimitInterface`

#### [`withRouter`](../../../src/ServerConfig.php#L256-L260)

Returns `static`

**Parameters:**

- `$router`: `League\Route\Router`

#### [`withSigningKeys`](../../../src/ServerConfig.php#L262-L266)

Returns `static`

**Parameters:**

- `$signingKeys`: `FediE2EE\PKDServer\Dependency\SigningKeys`

#### [`withTwig`](../../../src/ServerConfig.php#L268-L272)

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

#### [`__construct`](../../../src/Table.php#L32-L37)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`getCipher`](../../../src/Table.php#L39)

abstract · Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

#### [`clearCache`](../../../src/Table.php#L46-L49)

Returns `void`

#### [`convertKey`](../../../src/Table.php#L51-L54)

Returns `ParagonIE\CipherSweet\Backend\Key\SymmetricKey`

**Parameters:**

- `$inputKey`: `FediE2EE\PKD\Crypto\SymmetricKey`

#### [`assertRecentMerkleRoot`](../../../src/Table.php#L61-L74)

Returns `void`

**Parameters:**

- `$recentMerkle`: `string`

**Throws:** `DependencyException`, `ProtocolException`, `SodiumException`

#### [`isMerkleRootRecent`](../../../src/Table.php#L82-L138)

**API** · Returns `bool`

**Parameters:**

- `$merkleRoot`: `string`
- `$isHighVolume`: `bool` = false

**Throws:** `DependencyException`, `SodiumException`

#### [`appCache`](../../../src/Table.php#L54-L57)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Table.php#L64-L87)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Table.php#L89-L92)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Table.php#L97-L107)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Table.php#L112-L116)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Table.php#L123-L130)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Table.php#L136-L143)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Table.php#L151-L157)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Table.php#L162-L168)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Table.php#L170-L179)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Table.php#L184-L193)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Table.php#L200-L210)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Table.php#L216-L226)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Table.php#L233-L243)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Table.php#L16-L19)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Table.php#L24-L27)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Table.php#L33-L39)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## TableCache

**class** `FediE2EE\PKDServer\TableCache`

**File:** [`src/TableCache.php`](../../../src/TableCache.php)

### Methods

#### [`instance`](../../../src/TableCache.php#L19-L25)

static · Returns `self`

#### [`clearCache`](../../../src/TableCache.php#L27-L30)

Returns `void`

#### [`fetchTable`](../../../src/TableCache.php#L35-L41)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`

#### [`hasTable`](../../../src/TableCache.php#L43-L46)

Returns `bool`

**Parameters:**

- `$tableName`: `string`

#### [`storeTable`](../../../src/TableCache.php#L48-L52)

Returns `static`

**Parameters:**

- `$tableName`: `string`
- `$table`: `FediE2EE\PKDServer\Table`

---

