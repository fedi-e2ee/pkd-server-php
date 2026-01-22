# RequestHandlers / ActivityPub

Namespace: `FediE2EE\PKDServer\RequestHandlers\ActivityPub`

## Classes

- [Finger](#finger) - class
- [Inbox](#inbox) - class
- [UserPage](#userpage) - class

---

## Finger

**class** `FediE2EE\PKDServer\RequestHandlers\ActivityPub\Finger`

**File:** [`src/RequestHandlers/ActivityPub/Finger.php`](../../../src/RequestHandlers/ActivityPub/Finger.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ActivityStreamsTrait`, `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`handle`](../../../src/RequestHandlers/ActivityPub/Finger.php#L41-L87)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `CertaintyException`, `DependencyException`, `GuzzleException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`getVerifiedStream`](../../../src/RequestHandlers/ActivityPub/Finger.php#L37-L60)

Returns `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ActivityPubException`, `CertaintyException`, `CryptoException`, `DependencyException`, `FetchException`, `HttpSignatureException`, `InvalidArgumentException`, `NotImplementedException`, `SodiumException`

#### [`appCache`](../../../src/RequestHandlers/ActivityPub/Finger.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/ActivityPub/Finger.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/ActivityPub/Finger.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/ActivityPub/Finger.php#L87-L97)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/ActivityPub/Finger.php#L102-L106)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/ActivityPub/Finger.php#L113-L120)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`jsonDecode`](../../../src/RequestHandlers/ActivityPub/Finger.php#L12-L15)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/ActivityPub/Finger.php#L20-L23)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/ActivityPub/Finger.php#L28-L34)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`time`](../../../src/RequestHandlers/ActivityPub/Finger.php#L35-L38)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/ActivityPub/Finger.php#L47-L51)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/ActivityPub/Finger.php#L59-L62)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/ActivityPub/Finger.php#L73-L85)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/ActivityPub/Finger.php#L95-L114)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/ActivityPub/Finger.php#L122-L139)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

---

## Inbox

**class** `FediE2EE\PKDServer\RequestHandlers\ActivityPub\Inbox`

**File:** [`src/RequestHandlers/ActivityPub/Inbox.php`](../../../src/RequestHandlers/ActivityPub/Inbox.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ActivityStreamsTrait`, `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L46-L53)

Returns `void`

**Throws:** `TableException`, `DependencyException`, `CacheException`

#### [`handle`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L66-L81)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `CertaintyException`, `CryptoException`, `DependencyException`, `InvalidArgumentException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`getVerifiedStream`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L37-L60)

Returns `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ActivityPubException`, `CertaintyException`, `CryptoException`, `DependencyException`, `FetchException`, `HttpSignatureException`, `InvalidArgumentException`, `NotImplementedException`, `SodiumException`

#### [`appCache`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L87-L97)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L102-L106)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L113-L120)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`jsonDecode`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L12-L15)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L20-L23)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L28-L34)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`time`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L35-L38)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L47-L51)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L59-L62)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L73-L85)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L95-L114)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L122-L139)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

---

## UserPage

**class** `FediE2EE\PKDServer\RequestHandlers\ActivityPub\UserPage`

**File:** [`src/RequestHandlers/ActivityPub/UserPage.php`](../../../src/RequestHandlers/ActivityPub/UserPage.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ActivityStreamsTrait`, `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`handle`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L24-L27)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### [`getVerifiedStream`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L37-L60)

Returns `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ActivityPubException`, `CertaintyException`, `CryptoException`, `DependencyException`, `FetchException`, `HttpSignatureException`, `InvalidArgumentException`, `NotImplementedException`, `SodiumException`

#### [`appCache`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L44-L47)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L54-L77)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L79-L82)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L87-L97)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L102-L106)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L113-L120)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`jsonDecode`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L12-L15)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L20-L23)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L28-L34)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`time`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L35-L38)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L47-L51)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L59-L62)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L73-L85)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L95-L114)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L122-L139)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

---

