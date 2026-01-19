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

#### [`__construct`](../../../src/RequestHandlers/Api/Actor.php#L49-L56)

Returns `void`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`getPrimaryCacheKey`](../../../src/RequestHandlers/Api/Actor.php#L59-L62)

Returns `string`

**Attributes:** `#[Override]`

#### [`handle`](../../../src/RequestHandlers/Api/Actor.php#L82-L120)

**API** · Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DependencyException`, `InvalidArgumentException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`clearCache`](../../../src/RequestHandlers/Api/Actor.php#L34-L37)

Returns `bool`

**Throws:** `DependencyException`

#### [`time`](../../../src/RequestHandlers/Api/Actor.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/Actor.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/Actor.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/Actor.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/Actor.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/Actor.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/Actor.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/Actor.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/Actor.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/Actor.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/Actor.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/Actor.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/Actor.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/Actor.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`__construct`](../../../src/RequestHandlers/Api/BurnDown.php#L49-L52)

Returns `void`

**Throws:** `DependencyException`

#### [`handle`](../../../src/RequestHandlers/Api/BurnDown.php#L69-L91)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `CacheException`, `CertaintyException`, `CryptoException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `ParserException`, `SodiumException`, `TableException`, `InvalidArgumentException`

#### [`getVerifiedStream`](../../../src/RequestHandlers/Api/BurnDown.php#L37-L60)

Returns `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ActivityPubException`, `CertaintyException`, `CryptoException`, `DependencyException`, `FetchException`, `HttpSignatureException`, `InvalidArgumentException`, `NotImplementedException`, `SodiumException`

#### [`appCache`](../../../src/RequestHandlers/Api/BurnDown.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/BurnDown.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/BurnDown.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/BurnDown.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/BurnDown.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/BurnDown.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/BurnDown.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/BurnDown.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`time`](../../../src/RequestHandlers/Api/BurnDown.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/BurnDown.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/BurnDown.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/BurnDown.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/BurnDown.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/BurnDown.php#L127-L144)

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

#### [`time`](../../../src/RequestHandlers/Api/Checkpoint.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/Checkpoint.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/Checkpoint.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/Checkpoint.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/Checkpoint.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/Checkpoint.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/Checkpoint.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/Checkpoint.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/Checkpoint.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/Checkpoint.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/Checkpoint.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/Checkpoint.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/Checkpoint.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/Checkpoint.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`time`](../../../src/RequestHandlers/Api/Extensions.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/Extensions.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/Extensions.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/Extensions.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/Extensions.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/Extensions.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/Extensions.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/Extensions.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/Extensions.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/Extensions.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/Extensions.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/Extensions.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/Extensions.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/Extensions.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`__construct`](../../../src/RequestHandlers/Api/GetAuxData.php#L49-L62)

Returns `void`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`handle`](../../../src/RequestHandlers/Api/GetAuxData.php#L83-L118)

**API** · Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`time`](../../../src/RequestHandlers/Api/GetAuxData.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/GetAuxData.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/GetAuxData.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/GetAuxData.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/GetAuxData.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/GetAuxData.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/GetAuxData.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/GetAuxData.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/GetAuxData.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/GetAuxData.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/GetAuxData.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/GetAuxData.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/GetAuxData.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/GetAuxData.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`__construct`](../../../src/RequestHandlers/Api/GetKey.php#L49-L62)

Returns `void`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`handle`](../../../src/RequestHandlers/Api/GetKey.php#L83-L119)

**API** · Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`time`](../../../src/RequestHandlers/Api/GetKey.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/GetKey.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/GetKey.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/GetKey.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/GetKey.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/GetKey.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/GetKey.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/GetKey.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/GetKey.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/GetKey.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/GetKey.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/GetKey.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/GetKey.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/GetKey.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`time`](../../../src/RequestHandlers/Api/History.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/History.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/History.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/History.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/History.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/History.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/History.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/History.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/History.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/History.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/History.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/History.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/History.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/History.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`__construct`](../../../src/RequestHandlers/Api/HistoryCosign.php#L38-L45)

Returns `void`

**Throws:** `DependencyException`, `TableException`, `CacheException`

#### [`handle`](../../../src/RequestHandlers/Api/HistoryCosign.php#L55-L102)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Override]`, `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`time`](../../../src/RequestHandlers/Api/HistoryCosign.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/HistoryCosign.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/HistoryCosign.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/HistoryCosign.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/HistoryCosign.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/HistoryCosign.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/HistoryCosign.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/HistoryCosign.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/HistoryCosign.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/HistoryCosign.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/HistoryCosign.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/HistoryCosign.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/HistoryCosign.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/HistoryCosign.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`time`](../../../src/RequestHandlers/Api/HistorySince.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/HistorySince.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/HistorySince.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/HistorySince.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/HistorySince.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/HistorySince.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/HistorySince.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/HistorySince.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/HistorySince.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/HistorySince.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/HistorySince.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/HistorySince.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/HistorySince.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/HistorySince.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`__construct`](../../../src/RequestHandlers/Api/HistoryView.php#L48-L55)

Returns `void`

**Throws:** `DependencyException`, `TableException`, `CacheException`

#### [`getPrimaryCacheKey`](../../../src/RequestHandlers/Api/HistoryView.php#L58-L61)

Returns `string`

**Attributes:** `#[Override]`

#### [`handle`](../../../src/RequestHandlers/Api/HistoryView.php#L76-L108)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `BundleException`, `CryptoException`, `DependencyException`, `HPKEException`, `InputException`, `InvalidArgumentException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`clearCache`](../../../src/RequestHandlers/Api/HistoryView.php#L34-L37)

Returns `bool`

**Throws:** `DependencyException`

#### [`time`](../../../src/RequestHandlers/Api/HistoryView.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/HistoryView.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/HistoryView.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/HistoryView.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/HistoryView.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/HistoryView.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/HistoryView.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/HistoryView.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/HistoryView.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/HistoryView.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/HistoryView.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/HistoryView.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/HistoryView.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/HistoryView.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`handle`](../../../src/RequestHandlers/Api/Info.php#L34-L45)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`time`](../../../src/RequestHandlers/Api/Info.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/Info.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/Info.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/Info.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/Info.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/Info.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/Info.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/Info.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/Info.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/Info.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/Info.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/Info.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/Info.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/Info.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`__construct`](../../../src/RequestHandlers/Api/ListAuxData.php#L52-L65)

Returns `void`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`handle`](../../../src/RequestHandlers/Api/ListAuxData.php#L85-L112)

**API** · Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CipherSweetException`, `CryptoOperationException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`, `DateMalformedStringException`, `CryptoException`

#### [`time`](../../../src/RequestHandlers/Api/ListAuxData.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/ListAuxData.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/ListAuxData.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/ListAuxData.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/ListAuxData.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/ListAuxData.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/ListAuxData.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/ListAuxData.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/ListAuxData.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/ListAuxData.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/ListAuxData.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/ListAuxData.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/ListAuxData.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/ListAuxData.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`__construct`](../../../src/RequestHandlers/Api/ListKeys.php#L54-L67)

Returns `void`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`handle`](../../../src/RequestHandlers/Api/ListKeys.php#L89-L126)

**API** · Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoOperationException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`, `DateMalformedStringException`, `CryptoException`, `BaseJsonException`

#### [`time`](../../../src/RequestHandlers/Api/ListKeys.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/ListKeys.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/ListKeys.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/ListKeys.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/ListKeys.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/ListKeys.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/ListKeys.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/ListKeys.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/ListKeys.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/ListKeys.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/ListKeys.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/ListKeys.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/ListKeys.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/ListKeys.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`handle`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L86-L107)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `CacheException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`actor`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L125-L148)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`actorKeys`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L167-L191)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`actorKey`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L210-L239)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`actorAuxiliary`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L257-L281)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`actorAuxiliaryItem`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L300-L329)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`history`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L343-L359)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `BaseJsonException`, `CacheException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`historySince`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L373-L390)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `BaseJsonException`, `CacheException`, `CryptoException`, `DateMalformedStringException`, `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`time`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/ReplicaInfo.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`__construct`](../../../src/RequestHandlers/Api/Replicas.php#L40-L51)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` = null

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`handle`](../../../src/RequestHandlers/Api/Replicas.php#L63-L77)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `CryptoException`, `DateMalformedStringException`, `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`time`](../../../src/RequestHandlers/Api/Replicas.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/Replicas.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/Replicas.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/Replicas.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/Replicas.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/Replicas.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/Replicas.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/Replicas.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/Replicas.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/Replicas.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/Replicas.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/Replicas.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/Replicas.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/Replicas.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

---

## Revoke

**class** `FediE2EE\PKDServer\RequestHandlers\Api\Revoke`

**File:** [`src/RequestHandlers/Api/Revoke.php`](../../../src/RequestHandlers/Api/Revoke.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/Revoke.php#L41-L44)

Returns `void`

**Throws:** `DependencyException`

#### [`handle`](../../../src/RequestHandlers/Api/Revoke.php#L59-L71)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `BundleException`, `CacheException`, `CryptoException`, `DependencyException`, `HPKEException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`time`](../../../src/RequestHandlers/Api/Revoke.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/Revoke.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/Revoke.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/Revoke.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/Revoke.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/Revoke.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/Revoke.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/Revoke.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/Revoke.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/Revoke.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/Revoke.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/Revoke.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/Revoke.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/Revoke.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`time`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/ServerPublicKey.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

---

## TotpDisenroll

**class** `FediE2EE\PKDServer\RequestHandlers\Api\TotpDisenroll`

**File:** [`src/RequestHandlers/Api/TotpDisenroll.php`](../../../src/RequestHandlers/Api/TotpDisenroll.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`, `FediE2EE\PKDServer\Traits\TOTPTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L55-L62)

Returns `void`

**Throws:** `DependencyException`, `TableException`, `CacheException`

#### [`handle`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L82-L145)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `SodiumException`, `TableException`

#### [`time`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`verifyTOTP`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L39-L53)

static · Returns `bool`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int` = 2

#### [`generateTOTP`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L55-L70)

static · Returns `string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` = null

#### [`ord`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L75-L78)

static · Returns `int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### [`throwIfTimeOutsideWindow`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L135-L144)

Returns `void`

**Parameters:**

- `$currentTime`: `int`

**Throws:** `ProtocolException`

#### [`assertAllArrayKeysExist`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RequestHandlers/Api/TotpDisenroll.php#L107-L141)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## TotpEnroll

**class** `FediE2EE\PKDServer\RequestHandlers\Api\TotpEnroll`

**File:** [`src/RequestHandlers/Api/TotpEnroll.php`](../../../src/RequestHandlers/Api/TotpEnroll.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`, `FediE2EE\PKDServer\Traits\TOTPTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/TotpEnroll.php#L59-L66)

Returns `void`

**Throws:** `DependencyException`, `TableException`, `CacheException`

#### [`handle`](../../../src/RequestHandlers/Api/TotpEnroll.php#L88-L162)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `RandomException`, `SodiumException`, `TableException`

#### [`time`](../../../src/RequestHandlers/Api/TotpEnroll.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/TotpEnroll.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/TotpEnroll.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/TotpEnroll.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/TotpEnroll.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/TotpEnroll.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/TotpEnroll.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/TotpEnroll.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/TotpEnroll.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/TotpEnroll.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/TotpEnroll.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/TotpEnroll.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/TotpEnroll.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/TotpEnroll.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`verifyTOTP`](../../../src/RequestHandlers/Api/TotpEnroll.php#L39-L53)

static · Returns `bool`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int` = 2

#### [`generateTOTP`](../../../src/RequestHandlers/Api/TotpEnroll.php#L55-L70)

static · Returns `string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` = null

#### [`ord`](../../../src/RequestHandlers/Api/TotpEnroll.php#L75-L78)

static · Returns `int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### [`throwIfTimeOutsideWindow`](../../../src/RequestHandlers/Api/TotpEnroll.php#L135-L144)

Returns `void`

**Parameters:**

- `$currentTime`: `int`

**Throws:** `ProtocolException`

#### [`assertAllArrayKeysExist`](../../../src/RequestHandlers/Api/TotpEnroll.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RequestHandlers/Api/TotpEnroll.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RequestHandlers/Api/TotpEnroll.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RequestHandlers/Api/TotpEnroll.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RequestHandlers/Api/TotpEnroll.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RequestHandlers/Api/TotpEnroll.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RequestHandlers/Api/TotpEnroll.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RequestHandlers/Api/TotpEnroll.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RequestHandlers/Api/TotpEnroll.php#L107-L141)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## TotpRotate

**class** `FediE2EE\PKDServer\RequestHandlers\Api\TotpRotate`

**File:** [`src/RequestHandlers/Api/TotpRotate.php`](../../../src/RequestHandlers/Api/TotpRotate.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`, `FediE2EE\PKDServer\Traits\TOTPTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/Api/TotpRotate.php#L59-L66)

Returns `void`

**Throws:** `DependencyException`, `TableException`, `CacheException`

#### [`handle`](../../../src/RequestHandlers/Api/TotpRotate.php#L88-L167)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `HPKEException`, `InvalidCiphertextException`, `JsonException`, `NotImplementedException`, `RandomException`, `SodiumException`, `TableException`

#### [`time`](../../../src/RequestHandlers/Api/TotpRotate.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/Api/TotpRotate.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/Api/TotpRotate.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/Api/TotpRotate.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/Api/TotpRotate.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/Api/TotpRotate.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/Api/TotpRotate.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/Api/TotpRotate.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/Api/TotpRotate.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`jsonDecode`](../../../src/RequestHandlers/Api/TotpRotate.php#L87-L94)

Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/Api/TotpRotate.php#L99-L106)

Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`config`](../../../src/RequestHandlers/Api/TotpRotate.php#L111-L121)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/Api/TotpRotate.php#L126-L130)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/Api/TotpRotate.php#L137-L144)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`verifyTOTP`](../../../src/RequestHandlers/Api/TotpRotate.php#L39-L53)

static · Returns `bool`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int` = 2

#### [`generateTOTP`](../../../src/RequestHandlers/Api/TotpRotate.php#L55-L70)

static · Returns `string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` = null

#### [`ord`](../../../src/RequestHandlers/Api/TotpRotate.php#L75-L78)

static · Returns `int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### [`throwIfTimeOutsideWindow`](../../../src/RequestHandlers/Api/TotpRotate.php#L135-L144)

Returns `void`

**Parameters:**

- `$currentTime`: `int`

**Throws:** `ProtocolException`

#### [`assertAllArrayKeysExist`](../../../src/RequestHandlers/Api/TotpRotate.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RequestHandlers/Api/TotpRotate.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RequestHandlers/Api/TotpRotate.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RequestHandlers/Api/TotpRotate.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RequestHandlers/Api/TotpRotate.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RequestHandlers/Api/TotpRotate.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RequestHandlers/Api/TotpRotate.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RequestHandlers/Api/TotpRotate.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RequestHandlers/Api/TotpRotate.php#L107-L141)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

