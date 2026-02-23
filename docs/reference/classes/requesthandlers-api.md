# RequestHandlers / Api

Namespace: `FediE2EE\PKDServer\RequestHandlers\Api`

## Classes

- [Actor](#actor) - class
- [BurnDown](#burndown) - class
- [Checkpoint](#checkpoint) - class
- [Extensions](#extensions) - class
- [GetAuxData](#getauxdata) - class
- [GetKey](#getkey) - class
- [History](#history) - class
- [HistoryCosign](#historycosign) - class
- [HistorySince](#historysince) - class
- [HistoryView](#historyview) - class
- [Info](#info) - class
- [ListAuxData](#listauxdata) - class
- [ListKeys](#listkeys) - class
- [ReplicaInfo](#replicainfo) - class
- [Replicas](#replicas) - class
- [Revoke](#revoke) - class
- [ServerPublicKey](#serverpublickey) - class
- [TotpDisenroll](#totpdisenroll) - class
- [TotpEnroll](#totpenroll) - class
- [TotpRotate](#totprotate) - class

---

## Actor

**class** `FediE2EE\PKDServer\RequestHandlers\Api\Actor`

**File:** [`src/RequestHandlers/Api/Actor.php`](../../../src/RequestHandlers/Api/Actor.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`, `FediE2EE\PKDServer\Interfaces\HttpCacheInterface`

**Uses:** `FediE2EE\PKDServer\Traits\HttpCacheTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/Actor.php#L50-L57)

Returns `void`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`getPrimaryCacheKey`](../../../src/RequestHandlers/Api/Actor.php#L60-L63)

Returns `string`

**Attributes:** `#[Override]`

#### [`handle`](../../../src/RequestHandlers/Api/Actor.php#L83-L121)

**API** · Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DependencyException`, `InvalidArgumentException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`clearCache`](../../../src/RequestHandlers/Api/Actor.php#L34-L37)

Returns `bool`

**Throws:** `DependencyException`

#### [`time`](../../../src/RequestHandlers/Api/Actor.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/Actor.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/Actor.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/Actor.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/Actor.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/Actor.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/Actor.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/Actor.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/Actor.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/Actor.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/Actor.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/Actor.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/Actor.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/Actor.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/Actor.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/Actor.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/Actor.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/Actor.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/Actor.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/Actor.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/Actor.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/Actor.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/Actor.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## BurnDown

**class** `FediE2EE\PKDServer\RequestHandlers\Api\BurnDown`

**File:** [`src/RequestHandlers/Api/BurnDown.php`](../../../src/RequestHandlers/Api/BurnDown.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ActivityStreamsTrait`, `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/BurnDown.php#L54-L57)

Returns `void`

**Throws:** `DependencyException`

#### [`handle`](../../../src/RequestHandlers/Api/BurnDown.php#L79-L106)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `BaseJsonException`, `BundleException`, `CacheException`, `CertaintyException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `InvalidArgumentException`, `JsonException`, `NotImplementedException`, `ParserException`, `RandomException`, `SodiumException`, `TableException`

#### [`getVerifiedStream`](../../../src/RequestHandlers/Api/BurnDown.php#L39-L62)

Returns `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ActivityPubException`, `CertaintyException`, `CryptoException`, `DependencyException`, `FetchException`, `HttpSignatureException`, `InvalidArgumentException`, `NotImplementedException`, `SodiumException`

#### [`appCache`](../../../src/RequestHandlers/Api/BurnDown.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/BurnDown.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/BurnDown.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/BurnDown.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/BurnDown.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/BurnDown.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/BurnDown.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/BurnDown.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/BurnDown.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/BurnDown.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/BurnDown.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/BurnDown.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/BurnDown.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/BurnDown.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/BurnDown.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/BurnDown.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/BurnDown.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`time`](../../../src/RequestHandlers/Api/BurnDown.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/BurnDown.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/BurnDown.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/BurnDown.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/BurnDown.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/BurnDown.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

---

## Checkpoint

**class** `FediE2EE\PKDServer\RequestHandlers\Api\Checkpoint`

**File:** [`src/RequestHandlers/Api/Checkpoint.php`](../../../src/RequestHandlers/Api/Checkpoint.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`handle`](../../../src/RequestHandlers/Api/Checkpoint.php#L23-L26)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### [`time`](../../../src/RequestHandlers/Api/Checkpoint.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/Checkpoint.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/Checkpoint.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/Checkpoint.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/Checkpoint.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/Checkpoint.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/Checkpoint.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/Checkpoint.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/Checkpoint.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/Checkpoint.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/Checkpoint.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/Checkpoint.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/Checkpoint.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/Checkpoint.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/Checkpoint.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/Checkpoint.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/Checkpoint.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/Checkpoint.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/Checkpoint.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/Checkpoint.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/Checkpoint.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/Checkpoint.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/Checkpoint.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## Extensions

**class** `FediE2EE\PKDServer\RequestHandlers\Api\Extensions`

**File:** [`src/RequestHandlers/Api/Extensions.php`](../../../src/RequestHandlers/Api/Extensions.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`handle`](../../../src/RequestHandlers/Api/Extensions.php#L35-L42)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`time`](../../../src/RequestHandlers/Api/Extensions.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/Extensions.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/Extensions.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/Extensions.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/Extensions.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/Extensions.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/Extensions.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/Extensions.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/Extensions.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/Extensions.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/Extensions.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/Extensions.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/Extensions.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/Extensions.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/Extensions.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/Extensions.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/Extensions.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/Extensions.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/Extensions.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/Extensions.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/Extensions.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/Extensions.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/Extensions.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## GetAuxData

**class** `FediE2EE\PKDServer\RequestHandlers\Api\GetAuxData`

**File:** [`src/RequestHandlers/Api/GetAuxData.php`](../../../src/RequestHandlers/Api/GetAuxData.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/GetAuxData.php#L54-L67)

Returns `void`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`handle`](../../../src/RequestHandlers/Api/GetAuxData.php#L88-L123)

**API** · Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`time`](../../../src/RequestHandlers/Api/GetAuxData.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/GetAuxData.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/GetAuxData.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/GetAuxData.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/GetAuxData.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/GetAuxData.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/GetAuxData.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/GetAuxData.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/GetAuxData.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/GetAuxData.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/GetAuxData.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/GetAuxData.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/GetAuxData.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/GetAuxData.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/GetAuxData.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/GetAuxData.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/GetAuxData.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/GetAuxData.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/GetAuxData.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/GetAuxData.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/GetAuxData.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/GetAuxData.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/GetAuxData.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## GetKey

**class** `FediE2EE\PKDServer\RequestHandlers\Api\GetKey`

**File:** [`src/RequestHandlers/Api/GetKey.php`](../../../src/RequestHandlers/Api/GetKey.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/GetKey.php#L54-L67)

Returns `void`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`handle`](../../../src/RequestHandlers/Api/GetKey.php#L88-L124)

**API** · Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`time`](../../../src/RequestHandlers/Api/GetKey.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/GetKey.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/GetKey.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/GetKey.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/GetKey.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/GetKey.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/GetKey.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/GetKey.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/GetKey.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/GetKey.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/GetKey.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/GetKey.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/GetKey.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/GetKey.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/GetKey.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/GetKey.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/GetKey.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/GetKey.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/GetKey.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/GetKey.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/GetKey.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/GetKey.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/GetKey.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## History

**class** `FediE2EE\PKDServer\RequestHandlers\Api\History`

**File:** [`src/RequestHandlers/Api/History.php`](../../../src/RequestHandlers/Api/History.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/History.php#L38-L45)

Returns `void`

**Throws:** `DependencyException`, `TableException`, `CacheException`

#### [`handle`](../../../src/RequestHandlers/Api/History.php#L55-L65)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`time`](../../../src/RequestHandlers/Api/History.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/History.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/History.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/History.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/History.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/History.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/History.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/History.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/History.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/History.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/History.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/History.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/History.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/History.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/History.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/History.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/History.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/History.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/History.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/History.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/History.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/History.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/History.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## HistoryCosign

**class** `FediE2EE\PKDServer\RequestHandlers\Api\HistoryCosign`

**File:** [`src/RequestHandlers/Api/HistoryCosign.php`](../../../src/RequestHandlers/Api/HistoryCosign.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/HistoryCosign.php#L37-L44)

Returns `void`

**Throws:** `DependencyException`, `TableException`, `CacheException`

#### [`handle`](../../../src/RequestHandlers/Api/HistoryCosign.php#L54-L101)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Override]`, `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`time`](../../../src/RequestHandlers/Api/HistoryCosign.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/HistoryCosign.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/HistoryCosign.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/HistoryCosign.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/HistoryCosign.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/HistoryCosign.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/HistoryCosign.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/HistoryCosign.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/HistoryCosign.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/HistoryCosign.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/HistoryCosign.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/HistoryCosign.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/HistoryCosign.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/HistoryCosign.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/HistoryCosign.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/HistoryCosign.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/HistoryCosign.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/HistoryCosign.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/HistoryCosign.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/HistoryCosign.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/HistoryCosign.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/HistoryCosign.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/HistoryCosign.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## HistorySince

**class** `FediE2EE\PKDServer\RequestHandlers\Api\HistorySince`

**File:** [`src/RequestHandlers/Api/HistorySince.php`](../../../src/RequestHandlers/Api/HistorySince.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`, `FediE2EE\PKDServer\Interfaces\HttpCacheInterface`

**Uses:** `FediE2EE\PKDServer\Traits\HttpCacheTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/HistorySince.php#L45-L52)

Returns `void`

**Throws:** `DependencyException`, `TableException`, `CacheException`

#### [`getPrimaryCacheKey`](../../../src/RequestHandlers/Api/HistorySince.php#L55-L58)

Returns `string`

**Attributes:** `#[Override]`

#### [`handle`](../../../src/RequestHandlers/Api/HistorySince.php#L73-L92)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `BundleException`, `CryptoException`, `DependencyException`, `HPKEException`, `InputException`, `InvalidArgumentException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`clearCache`](../../../src/RequestHandlers/Api/HistorySince.php#L34-L37)

Returns `bool`

**Throws:** `DependencyException`

#### [`time`](../../../src/RequestHandlers/Api/HistorySince.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/HistorySince.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/HistorySince.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/HistorySince.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/HistorySince.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/HistorySince.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/HistorySince.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/HistorySince.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/HistorySince.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/HistorySince.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/HistorySince.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/HistorySince.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/HistorySince.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/HistorySince.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/HistorySince.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/HistorySince.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/HistorySince.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/HistorySince.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/HistorySince.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/HistorySince.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/HistorySince.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/HistorySince.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/HistorySince.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## HistoryView

**class** `FediE2EE\PKDServer\RequestHandlers\Api\HistoryView`

**File:** [`src/RequestHandlers/Api/HistoryView.php`](../../../src/RequestHandlers/Api/HistoryView.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`, `FediE2EE\PKDServer\Interfaces\HttpCacheInterface`

**Uses:** `FediE2EE\PKDServer\Traits\HttpCacheTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/HistoryView.php#L51-L58)

Returns `void`

**Throws:** `DependencyException`, `TableException`, `CacheException`

#### [`getPrimaryCacheKey`](../../../src/RequestHandlers/Api/HistoryView.php#L61-L64)

Returns `string`

**Attributes:** `#[Override]`

#### [`handle`](../../../src/RequestHandlers/Api/HistoryView.php#L80-L115)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `BaseJsonException`, `BundleException`, `CryptoException`, `DependencyException`, `HPKEException`, `InputException`, `InvalidArgumentException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`clearCache`](../../../src/RequestHandlers/Api/HistoryView.php#L34-L37)

Returns `bool`

**Throws:** `DependencyException`

#### [`time`](../../../src/RequestHandlers/Api/HistoryView.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/HistoryView.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/HistoryView.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/HistoryView.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/HistoryView.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/HistoryView.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/HistoryView.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/HistoryView.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/HistoryView.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/HistoryView.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/HistoryView.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/HistoryView.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/HistoryView.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/HistoryView.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/HistoryView.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/HistoryView.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/HistoryView.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/HistoryView.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/HistoryView.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/HistoryView.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/HistoryView.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/HistoryView.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/HistoryView.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## Info

**class** `FediE2EE\PKDServer\RequestHandlers\Api\Info`

**File:** [`src/RequestHandlers/Api/Info.php`](../../../src/RequestHandlers/Api/Info.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`handle`](../../../src/RequestHandlers/Api/Info.php#L34-L46)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`time`](../../../src/RequestHandlers/Api/Info.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/Info.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/Info.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/Info.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/Info.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/Info.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/Info.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/Info.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/Info.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/Info.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/Info.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/Info.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/Info.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/Info.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/Info.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/Info.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/Info.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/Info.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/Info.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/Info.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/Info.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/Info.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/Info.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## ListAuxData

**class** `FediE2EE\PKDServer\RequestHandlers\Api\ListAuxData`

**File:** [`src/RequestHandlers/Api/ListAuxData.php`](../../../src/RequestHandlers/Api/ListAuxData.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/ListAuxData.php#L53-L66)

Returns `void`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`handle`](../../../src/RequestHandlers/Api/ListAuxData.php#L86-L113)

**API** · Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CipherSweetException`, `CryptoOperationException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`, `DateMalformedStringException`, `CryptoException`

#### [`time`](../../../src/RequestHandlers/Api/ListAuxData.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/ListAuxData.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/ListAuxData.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/ListAuxData.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/ListAuxData.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/ListAuxData.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/ListAuxData.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/ListAuxData.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/ListAuxData.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/ListAuxData.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/ListAuxData.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/ListAuxData.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/ListAuxData.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/ListAuxData.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/ListAuxData.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/ListAuxData.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/ListAuxData.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/ListAuxData.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/ListAuxData.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/ListAuxData.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/ListAuxData.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/ListAuxData.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/ListAuxData.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## ListKeys

**class** `FediE2EE\PKDServer\RequestHandlers\Api\ListKeys`

**File:** [`src/RequestHandlers/Api/ListKeys.php`](../../../src/RequestHandlers/Api/ListKeys.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/ListKeys.php#L55-L68)

Returns `void`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`handle`](../../../src/RequestHandlers/Api/ListKeys.php#L90-L127)

**API** · Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoOperationException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`, `DateMalformedStringException`, `CryptoException`, `BaseJsonException`

#### [`time`](../../../src/RequestHandlers/Api/ListKeys.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/ListKeys.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/ListKeys.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/ListKeys.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/ListKeys.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/ListKeys.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/ListKeys.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/ListKeys.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/ListKeys.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/ListKeys.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/ListKeys.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/ListKeys.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/ListKeys.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/ListKeys.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/ListKeys.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/ListKeys.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/ListKeys.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/ListKeys.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/ListKeys.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/ListKeys.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/ListKeys.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/ListKeys.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/ListKeys.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## ReplicaInfo

**class** `FediE2EE\PKDServer\RequestHandlers\Api\ReplicaInfo`

**File:** [`src/RequestHandlers/Api/ReplicaInfo.php`](../../../src/RequestHandlers/Api/ReplicaInfo.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`handle`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L87-L108)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `CacheException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`actor`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L126-L149)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`actorKeys`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L168-L192)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`actorKey`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L211-L240)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`actorAuxiliary`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L258-L282)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`actorAuxiliaryItem`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L301-L330)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`history`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L344-L360)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `BaseJsonException`, `CacheException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`historySince`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L374-L391)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `BaseJsonException`, `CacheException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`time`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## Replicas

**class** `FediE2EE\PKDServer\RequestHandlers\Api\Replicas`

**File:** [`src/RequestHandlers/Api/Replicas.php`](../../../src/RequestHandlers/Api/Replicas.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/Replicas.php#L41-L52)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` = null

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`handle`](../../../src/RequestHandlers/Api/Replicas.php#L64-L78)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `CryptoException`, `DateMalformedStringException`, `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`time`](../../../src/RequestHandlers/Api/Replicas.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/Replicas.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/Replicas.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/Replicas.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/Replicas.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/Replicas.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/Replicas.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/Replicas.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/Replicas.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/Replicas.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/Replicas.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/Replicas.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/Replicas.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/Replicas.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/Replicas.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/Replicas.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/Replicas.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/Replicas.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/Replicas.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/Replicas.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/Replicas.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/Replicas.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/Replicas.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## Revoke

**class** `FediE2EE\PKDServer\RequestHandlers\Api\Revoke`

**File:** [`src/RequestHandlers/Api/Revoke.php`](../../../src/RequestHandlers/Api/Revoke.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`, `FediE2EE\PKDServer\Interfaces\LimitingHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/Revoke.php#L46-L49)

Returns `void`

**Throws:** `DependencyException`

#### [`handle`](../../../src/RequestHandlers/Api/Revoke.php#L69-L80)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `BaseJsonException`, `BundleException`, `CacheException`, `ConcurrentException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`getEnabledRateLimits`](../../../src/RequestHandlers/Api/Revoke.php#L83-L86)

Returns `array`

**Attributes:** `#[Override]`

#### [`time`](../../../src/RequestHandlers/Api/Revoke.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/Revoke.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/Revoke.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/Revoke.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/Revoke.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/Revoke.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/Revoke.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/Revoke.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/Revoke.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/Revoke.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/Revoke.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/Revoke.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/Revoke.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/Revoke.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/Revoke.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/Revoke.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/Revoke.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/Revoke.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/Revoke.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/Revoke.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/Revoke.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/Revoke.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/Revoke.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## ServerPublicKey

**class** `FediE2EE\PKDServer\RequestHandlers\Api\ServerPublicKey`

**File:** [`src/RequestHandlers/Api/ServerPublicKey.php`](../../../src/RequestHandlers/Api/ServerPublicKey.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`handle`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L35-L49)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`time`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## TotpDisenroll

**class** `FediE2EE\PKDServer\RequestHandlers\Api\TotpDisenroll`

**File:** [`src/RequestHandlers/Api/TotpDisenroll.php`](../../../src/RequestHandlers/Api/TotpDisenroll.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`, `FediE2EE\PKDServer\Interfaces\LimitingHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`, `FediE2EE\PKDServer\Traits\TOTPTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L54-L61)

Returns `void`

**Throws:** `DependencyException`, `TableException`, `CacheException`

#### [`handle`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L81-L126)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`getEnabledRateLimits`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L129-L132)

Returns `array`

**Attributes:** `#[Override]`

#### [`time`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`verifyTOTP`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L49-L63)

static · Returns `?int`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int` = 2

#### [`generateTOTP`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L65-L81)

static · Returns `string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` = null

#### [`ord`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L86-L90)

static · Returns `int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### [`throwIfTimeOutsideWindow`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L151-L160)

Returns `void`

**Parameters:**

- `$currentTime`: `int`

**Throws:** `DependencyException`, `ProtocolException`

#### [`assertAllArrayKeysExist`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L29-L34)

static · Returns `void`

This method throws an InputException if any of the expected keys are absent.

It does not return anything.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L42-L49)

static · Returns `bool`

This method returns true if every expected array key is found in the target array.

Otherwise, it returns false. This is useful for input validation.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L64-L81)

Returns `string`

This is a constant-time conditional select. It should be read like a ternary operation.

$result = ClassWithTrait::constantTimeSelect(1, $left, $right); -> $result === $left. $result = ClassWithTrait::constantTimeSelect(0, $left, $right); -> $result === $right.

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L91-L94)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

This is mostly used for PEM-encoded strings.

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L112-L125)

static · Returns `string`

This is an implementation of PAE() from PASETO. It encodes an array of strings into a flat string consisting of:

1. The number of pieces. 2. For each piece: 1. The length of the piece (in bytes). 2. The contents of the piece. This allows multipart messages to have an injective canonical representation before passing ot a hash function (or other cryptographic function).

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L132-L140)

static · Returns `void`

This sorts the target array in-place, by its keys, including child arrays.

Used for ensuring arrays are sorted before JSON encoding.

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L147-L150)

static · Returns `string`

Mostly used by preAuthEncode() above. This packs an integer as 8 bytes.

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L155-L162)

Returns `array`

Get an array of bytes representing the input string.

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L170-L204)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## TotpEnroll

**class** `FediE2EE\PKDServer\RequestHandlers\Api\TotpEnroll`

**File:** [`src/RequestHandlers/Api/TotpEnroll.php`](../../../src/RequestHandlers/Api/TotpEnroll.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`, `FediE2EE\PKDServer\Interfaces\LimitingHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`, `FediE2EE\PKDServer\Traits\TOTPTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/TotpEnroll.php#L58-L65)

Returns `void`

**Throws:** `DependencyException`, `TableException`, `CacheException`

#### [`handle`](../../../src/RequestHandlers/Api/TotpEnroll.php#L87-L140)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `RandomException`, `SodiumException`, `TableException`

#### [`getEnabledRateLimits`](../../../src/RequestHandlers/Api/TotpEnroll.php#L143-L146)

Returns `array`

**Attributes:** `#[Override]`

#### [`time`](../../../src/RequestHandlers/Api/TotpEnroll.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/TotpEnroll.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/TotpEnroll.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/TotpEnroll.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/TotpEnroll.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/TotpEnroll.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/TotpEnroll.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/TotpEnroll.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/TotpEnroll.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/TotpEnroll.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/TotpEnroll.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/TotpEnroll.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/TotpEnroll.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/TotpEnroll.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/TotpEnroll.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/TotpEnroll.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/TotpEnroll.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/TotpEnroll.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/TotpEnroll.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/TotpEnroll.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/TotpEnroll.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/TotpEnroll.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/TotpEnroll.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`verifyTOTP`](../../../src/RequestHandlers/Api/TotpEnroll.php#L49-L63)

static · Returns `?int`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int` = 2

#### [`generateTOTP`](../../../src/RequestHandlers/Api/TotpEnroll.php#L65-L81)

static · Returns `string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` = null

#### [`ord`](../../../src/RequestHandlers/Api/TotpEnroll.php#L86-L90)

static · Returns `int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### [`throwIfTimeOutsideWindow`](../../../src/RequestHandlers/Api/TotpEnroll.php#L151-L160)

Returns `void`

**Parameters:**

- `$currentTime`: `int`

**Throws:** `DependencyException`, `ProtocolException`

#### [`assertAllArrayKeysExist`](../../../src/RequestHandlers/Api/TotpEnroll.php#L29-L34)

static · Returns `void`

This method throws an InputException if any of the expected keys are absent.

It does not return anything.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RequestHandlers/Api/TotpEnroll.php#L42-L49)

static · Returns `bool`

This method returns true if every expected array key is found in the target array.

Otherwise, it returns false. This is useful for input validation.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RequestHandlers/Api/TotpEnroll.php#L64-L81)

Returns `string`

This is a constant-time conditional select. It should be read like a ternary operation.

$result = ClassWithTrait::constantTimeSelect(1, $left, $right); -> $result === $left. $result = ClassWithTrait::constantTimeSelect(0, $left, $right); -> $result === $right.

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RequestHandlers/Api/TotpEnroll.php#L91-L94)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

This is mostly used for PEM-encoded strings.

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RequestHandlers/Api/TotpEnroll.php#L112-L125)

static · Returns `string`

This is an implementation of PAE() from PASETO. It encodes an array of strings into a flat string consisting of:

1. The number of pieces. 2. For each piece: 1. The length of the piece (in bytes). 2. The contents of the piece. This allows multipart messages to have an injective canonical representation before passing ot a hash function (or other cryptographic function).

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RequestHandlers/Api/TotpEnroll.php#L132-L140)

static · Returns `void`

This sorts the target array in-place, by its keys, including child arrays.

Used for ensuring arrays are sorted before JSON encoding.

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RequestHandlers/Api/TotpEnroll.php#L147-L150)

static · Returns `string`

Mostly used by preAuthEncode() above. This packs an integer as 8 bytes.

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RequestHandlers/Api/TotpEnroll.php#L155-L162)

Returns `array`

Get an array of bytes representing the input string.

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RequestHandlers/Api/TotpEnroll.php#L170-L204)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## TotpRotate

**class** `FediE2EE\PKDServer\RequestHandlers\Api\TotpRotate`

**File:** [`src/RequestHandlers/Api/TotpRotate.php`](../../../src/RequestHandlers/Api/TotpRotate.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`, `FediE2EE\PKDServer\Interfaces\LimitingHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`, `FediE2EE\PKDServer\Traits\TOTPTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/TotpRotate.php#L58-L65)

Returns `void`

**Throws:** `DependencyException`, `TableException`, `CacheException`

#### [`handle`](../../../src/RequestHandlers/Api/TotpRotate.php#L87-L147)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `RandomException`, `SodiumException`, `TableException`

#### [`getEnabledRateLimits`](../../../src/RequestHandlers/Api/TotpRotate.php#L150-L153)

Returns `array`

**Attributes:** `#[Override]`

#### [`time`](../../../src/RequestHandlers/Api/TotpRotate.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/TotpRotate.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/Api/TotpRotate.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/TotpRotate.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/TotpRotate.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/TotpRotate.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/TotpRotate.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/TotpRotate.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/TotpRotate.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/Api/TotpRotate.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/TotpRotate.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/TotpRotate.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/Api/TotpRotate.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/Api/TotpRotate.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/Api/TotpRotate.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/Api/TotpRotate.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/Api/TotpRotate.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/Api/TotpRotate.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/Api/TotpRotate.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/Api/TotpRotate.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/Api/TotpRotate.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/TotpRotate.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/Api/TotpRotate.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`verifyTOTP`](../../../src/RequestHandlers/Api/TotpRotate.php#L49-L63)

static · Returns `?int`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int` = 2

#### [`generateTOTP`](../../../src/RequestHandlers/Api/TotpRotate.php#L65-L81)

static · Returns `string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` = null

#### [`ord`](../../../src/RequestHandlers/Api/TotpRotate.php#L86-L90)

static · Returns `int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### [`throwIfTimeOutsideWindow`](../../../src/RequestHandlers/Api/TotpRotate.php#L151-L160)

Returns `void`

**Parameters:**

- `$currentTime`: `int`

**Throws:** `DependencyException`, `ProtocolException`

#### [`assertAllArrayKeysExist`](../../../src/RequestHandlers/Api/TotpRotate.php#L29-L34)

static · Returns `void`

This method throws an InputException if any of the expected keys are absent.

It does not return anything.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RequestHandlers/Api/TotpRotate.php#L42-L49)

static · Returns `bool`

This method returns true if every expected array key is found in the target array.

Otherwise, it returns false. This is useful for input validation.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RequestHandlers/Api/TotpRotate.php#L64-L81)

Returns `string`

This is a constant-time conditional select. It should be read like a ternary operation.

$result = ClassWithTrait::constantTimeSelect(1, $left, $right); -> $result === $left. $result = ClassWithTrait::constantTimeSelect(0, $left, $right); -> $result === $right.

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RequestHandlers/Api/TotpRotate.php#L91-L94)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

This is mostly used for PEM-encoded strings.

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RequestHandlers/Api/TotpRotate.php#L112-L125)

static · Returns `string`

This is an implementation of PAE() from PASETO. It encodes an array of strings into a flat string consisting of:

1. The number of pieces. 2. For each piece: 1. The length of the piece (in bytes). 2. The contents of the piece. This allows multipart messages to have an injective canonical representation before passing ot a hash function (or other cryptographic function).

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RequestHandlers/Api/TotpRotate.php#L132-L140)

static · Returns `void`

This sorts the target array in-place, by its keys, including child arrays.

Used for ensuring arrays are sorted before JSON encoding.

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RequestHandlers/Api/TotpRotate.php#L147-L150)

static · Returns `string`

Mostly used by preAuthEncode() above. This packs an integer as 8 bytes.

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RequestHandlers/Api/TotpRotate.php#L155-L162)

Returns `array`

Get an array of bytes representing the input string.

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RequestHandlers/Api/TotpRotate.php#L170-L204)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

