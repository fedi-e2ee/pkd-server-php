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

#### [`time`](../../../src/RequestHandlers/IndexPage.php#L39-L42)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/IndexPage.php#L53-L61)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/IndexPage.php#L69-L72)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/IndexPage.php#L83-L95)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/IndexPage.php#L107-L126)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/IndexPage.php#L136-L153)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/IndexPage.php#L55-L58)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/IndexPage.php#L65-L88)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/IndexPage.php#L90-L93)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/IndexPage.php#L98-L108)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/IndexPage.php#L113-L117)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/IndexPage.php#L124-L131)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/IndexPage.php#L137-L144)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/IndexPage.php#L152-L158)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/IndexPage.php#L163-L169)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/IndexPage.php#L171-L180)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/IndexPage.php#L185-L194)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/IndexPage.php#L201-L211)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/IndexPage.php#L217-L227)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/IndexPage.php#L234-L244)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/IndexPage.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/IndexPage.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/IndexPage.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

