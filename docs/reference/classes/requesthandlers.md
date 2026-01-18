# RequestHandlers

Namespace: `FediE2EE\PKDServer\RequestHandlers`

## Classes

- [IndexPage](#indexpage) - class

---

## IndexPage

**class** `FediE2EE\PKDServer\RequestHandlers\IndexPage`

**File:** [`src/RequestHandlers/IndexPage.php`](../../../src/RequestHandlers/IndexPage.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`handle`](../../../src/RequestHandlers/IndexPage.php#L23-L29)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### [`time`](../../../src/RequestHandlers/IndexPage.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/IndexPage.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/RequestHandlers/IndexPage.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/IndexPage.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/IndexPage.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/IndexPage.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/IndexPage.php#L42-L45)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/IndexPage.php#L52-L75)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/IndexPage.php#L77-L80)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/IndexPage.php#L85-L95)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/IndexPage.php#L100-L104)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/IndexPage.php#L111-L118)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

---

