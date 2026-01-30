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

#### [`handle`](../../../src/RequestHandlers/ActivityPub/Finger.php#L47-L93)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `BaseJsonException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`getVerifiedStream`](../../../src/RequestHandlers/ActivityPub/Finger.php#L42-L65)

Returns `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ActivityPubException`, `CertaintyException`, `CryptoException`, `DependencyException`, `FetchException`, `HttpSignatureException`, `InvalidArgumentException`, `NotImplementedException`, `SodiumException`

#### [`appCache`](../../../src/RequestHandlers/ActivityPub/Finger.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/ActivityPub/Finger.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/ActivityPub/Finger.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/ActivityPub/Finger.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/ActivityPub/Finger.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/ActivityPub/Finger.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`jsonDecode`](../../../src/RequestHandlers/ActivityPub/Finger.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/ActivityPub/Finger.php#L23-L26)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/ActivityPub/Finger.php#L31-L37)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`time`](../../../src/RequestHandlers/ActivityPub/Finger.php#L38-L41)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/ActivityPub/Finger.php#L50-L54)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/ActivityPub/Finger.php#L62-L65)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/ActivityPub/Finger.php#L76-L88)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/ActivityPub/Finger.php#L98-L117)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/ActivityPub/Finger.php#L125-L142)

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

#### [`__construct`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L49-L56)

Returns `void`

**Throws:** `TableException`, `DependencyException`, `CacheException`

#### [`handle`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L70-L85)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `BaseJsonException`, `CertaintyException`, `CryptoException`, `DependencyException`, `InvalidArgumentException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`getVerifiedStream`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L42-L65)

Returns `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ActivityPubException`, `CertaintyException`, `CryptoException`, `DependencyException`, `FetchException`, `HttpSignatureException`, `InvalidArgumentException`, `NotImplementedException`, `SodiumException`

#### [`appCache`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`jsonDecode`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L23-L26)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L31-L37)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`time`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L38-L41)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L50-L54)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L62-L65)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L76-L88)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L98-L117)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/ActivityPub/Inbox.php#L125-L142)

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

#### [`getVerifiedStream`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L42-L65)

Returns `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ActivityPubException`, `CertaintyException`, `CryptoException`, `DependencyException`, `FetchException`, `HttpSignatureException`, `InvalidArgumentException`, `NotImplementedException`, `SodiumException`

#### [`appCache`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`jsonDecode`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L23-L26)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L31-L37)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`time`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L38-L41)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L50-L54)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L62-L65)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L76-L88)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L98-L117)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/ActivityPub/UserPage.php#L125-L142)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

---

