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

#### [`__construct`](../../../src/AppCache.php#L21-L30)

Returns `void`

**Parameters:**

- `$serverConfig`: `FediE2EE\PKDServer\ServerConfig`
- `$namespace`: `string` = ''
- `$defaultTTL`: `int` = 60

#### [`cacheJson`](../../../src/AppCache.php#L40-L50)

Returns `mixed`

Cache as a JSON-serialized string, deserialize from cache.

Used for caching entire HTTP response data (arrays, etc.).)

**Parameters:**

- `$lookup`: `string`
- `$fallback`: `callable`
- `$ttl`: `DateInterval|int|null` = null

**Throws:** `SodiumException`, `InvalidArgumentException`

#### [`cache`](../../../src/AppCache.php#L60-L69)

Returns `mixed`

If there is a cache-hit, it returns the value.

Otherwise, it invokes the fallback to determine the value.

**Parameters:**

- `$lookup`: `string`
- `$fallback`: `callable`
- `$ttl`: `DateInterval|int|null` = null

**Throws:** `InvalidArgumentException`, `SodiumException`

#### [`deriveKey`](../../../src/AppCache.php#L74-L77)

Returns `string`

**Parameters:**

- `$input`: `string`

**Throws:** `SodiumException`

#### [`get`](../../../src/AppCache.php#L80-L88)

Returns `mixed`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`
- `$default`: `mixed` = null

#### [`set`](../../../src/AppCache.php#L91-L99)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`
- `$value`: `mixed`
- `$ttl`: `DateInterval|int|null` = null

#### [`delete`](../../../src/AppCache.php#L102-L110)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`

#### [`clear`](../../../src/AppCache.php#L113-L121)

Returns `bool`

**Attributes:** `#[Override]`

#### [`getMultiple`](../../../src/AppCache.php#L124-L136)

Returns `array`

**Attributes:** `#[Override]`

**Parameters:**

- `$keys`: `iterable`
- `$default`: `mixed` = null

#### [`setMultiple`](../../../src/AppCache.php#L139-L149)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$values`: `iterable`
- `$ttl`: `DateInterval|int|null` = null

#### [`deleteMultiple`](../../../src/AppCache.php#L152-L165)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$keys`: `iterable`

#### [`has`](../../../src/AppCache.php#L168-L174)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$key`: `string`

#### [`processTTL`](../../../src/AppCache.php#L182-L198)

Returns `int`

Collapse multiple types into a number of seconds for Redis.

**Parameters:**

- `$ttl`: `DateInterval|int|null`

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

#### [`__construct`](../../../src/Protocol.php#L46-L53)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig`

#### [`process`](../../../src/Protocol.php#L66-L194)

Returns `array`

**Parameters:**

- `$enqueued`: `FediE2EE\PKDServer\ActivityPub\ActivityStream`
- `$isActivityPub`: `bool` = true

**Throws:** `CryptoException`, `DependencyException`, `Exceptions\CacheException`, `HPKEException`, `NotImplementedException`, `ParserException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`webfinger`](../../../src/Protocol.php#L223-L229)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `DependencyException`, `SodiumException`, `CertaintyException`

#### [`setWebFinger`](../../../src/Protocol.php#L237-L241)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`addKey`](../../../src/Protocol.php#L270-L278)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `ProtocolException`, `TableException`, `HPKEException`, `NotImplementedException`, `ParserException`, `SodiumException`

#### [`revokeKey`](../../../src/Protocol.php#L290-L298)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `ProtocolException`, `TableException`, `HPKEException`, `NotImplementedException`, `ParserException`, `SodiumException`

#### [`revokeKeyThirdParty`](../../../src/Protocol.php#L310-L324)

Returns `bool`

**Parameters:**

- `$body`: `string`

**Throws:** `CryptoException`, `DependencyException`, `HPKEException`, `NotImplementedException`, `ParserException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`moveIdentity`](../../../src/Protocol.php#L336-L344)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `HPKEException`, `NotImplementedException`, `ParserException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`burnDown`](../../../src/Protocol.php#L356-L368)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `HPKEException`, `NotImplementedException`, `ParserException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`fireproof`](../../../src/Protocol.php#L380-L388)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `HPKEException`, `NotImplementedException`, `ParserException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`undoFireproof`](../../../src/Protocol.php#L400-L408)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `HPKEException`, `NotImplementedException`, `ParserException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`addAuxData`](../../../src/Protocol.php#L420-L428)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `HPKEException`, `NotImplementedException`, `ParserException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`revokeAuxData`](../../../src/Protocol.php#L440-L448)

Returns `bool`

**Parameters:**

- `$body`: `string`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `HPKEException`, `NotImplementedException`, `ParserException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`checkpoint`](../../../src/Protocol.php#L459-L472)

Returns `bool`

**Parameters:**

- `$body`: `string`

**Throws:** `CryptoException`, `DependencyException`, `NotImplementedException`, `ParserException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`appCache`](../../../src/Protocol.php#L42-L45)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Protocol.php#L52-L75)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Protocol.php#L77-L80)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Protocol.php#L85-L95)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

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

#### [`__construct`](../../../src/ServerConfig.php#L36)

Returns `void`

**Parameters:**

- `$params`: `FediE2EE\PKDServer\Meta\Params`

#### [`getCaCertFetch`](../../../src/ServerConfig.php#L41-L47)

Returns `ParagonIE\Certainty\Fetch`

**Throws:** `DependencyException`

#### [`getAuxDataTypeAllowList`](../../../src/ServerConfig.php#L52-L55)

**API** · Returns `array`

#### [`getAuxDataRegistry`](../../../src/ServerConfig.php#L57-L63)

Returns `FediE2EE\PKD\Extensions\Registry`

#### [`getGuzzle`](../../../src/ServerConfig.php#L65-L70)

Returns `GuzzleHttp\Client`

#### [`getCipherSweet`](../../../src/ServerConfig.php#L75-L81)

Returns `ParagonIE\CipherSweet\CipherSweet`

**Throws:** `DependencyException`

#### [`getDb`](../../../src/ServerConfig.php#L87-L93)

**API** · Returns `ParagonIE\EasyDB\EasyDB`

**Throws:** `DependencyException`

#### [`getHPKE`](../../../src/ServerConfig.php#L99-L105)

**API** · Returns `FediE2EE\PKDServer\Dependency\HPKE`

**Throws:** `DependencyException`

#### [`getLogger`](../../../src/ServerConfig.php#L107-L113)

Returns `Monolog\Logger`

#### [`getParams`](../../../src/ServerConfig.php#L115-L118)

Returns `FediE2EE\PKDServer\Meta\Params`

#### [`getSigningKeys`](../../../src/ServerConfig.php#L124-L130)

**API** · Returns `FediE2EE\PKDServer\Dependency\SigningKeys`

**Throws:** `DependencyException`

#### [`getRouter`](../../../src/ServerConfig.php#L136-L142)

**API** · Returns `League\Route\Router`

**Throws:** `DependencyException`

#### [`getTwig`](../../../src/ServerConfig.php#L148-L154)

**API** · Returns `Twig\Environment`

**Throws:** `DependencyException`

#### [`getRedis`](../../../src/ServerConfig.php#L156-L159)

Returns `?Predis\Client`

#### [`hasRedis`](../../../src/ServerConfig.php#L161-L164)

Returns `bool`

#### [`withAuxDataTypeAllowList`](../../../src/ServerConfig.php#L170-L174)

Returns `static`

**Parameters:**

- `$allowList`: `array` = []

#### [`withAuxDataRegistry`](../../../src/ServerConfig.php#L176-L180)

Returns `static`

**Parameters:**

- `$registry`: `FediE2EE\PKD\Extensions\Registry`

#### [`withCACertFetch`](../../../src/ServerConfig.php#L182-L186)

Returns `static`

**Parameters:**

- `$fetch`: `ParagonIE\Certainty\Fetch`

#### [`withCipherSweet`](../../../src/ServerConfig.php#L188-L192)

Returns `static`

**Parameters:**

- `$ciphersweet`: `ParagonIE\CipherSweet\CipherSweet`

#### [`withDatabase`](../../../src/ServerConfig.php#L194-L198)

Returns `static`

**Parameters:**

- `$db`: `ParagonIE\EasyDB\EasyDB`

#### [`withHPKE`](../../../src/ServerConfig.php#L200-L204)

Returns `static`

**Parameters:**

- `$hpke`: `FediE2EE\PKDServer\Dependency\HPKE`

#### [`withLogger`](../../../src/ServerConfig.php#L206-L210)

Returns `static`

**Parameters:**

- `$logger`: `Monolog\Logger`

#### [`withOptionalRedisClient`](../../../src/ServerConfig.php#L212-L224)

Returns `static`

**Parameters:**

- `$redis`: `?Predis\Client` = null

#### [`withRouter`](../../../src/ServerConfig.php#L226-L230)

Returns `static`

**Parameters:**

- `$router`: `League\Route\Router`

#### [`withSigningKeys`](../../../src/ServerConfig.php#L232-L236)

Returns `static`

**Parameters:**

- `$signingKeys`: `FediE2EE\PKDServer\Dependency\SigningKeys`

#### [`withTwig`](../../../src/ServerConfig.php#L238-L242)

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

#### [`appCache`](../../../src/Table.php#L42-L45)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Table.php#L52-L75)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Table.php#L77-L80)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Table.php#L85-L95)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Table.php#L100-L104)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Table.php#L111-L118)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

