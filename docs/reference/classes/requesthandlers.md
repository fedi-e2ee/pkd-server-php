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

#### [`time`](../../../src/RequestHandlers/IndexPage.php#L37-L40)

Returns `string`

#### [`canonicalizeActor`](../../../src/RequestHandlers/IndexPage.php#L51-L59)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/RequestHandlers/IndexPage.php#L67-L70)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/RequestHandlers/IndexPage.php#L81-L92)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/RequestHandlers/IndexPage.php#L104-L123)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/RequestHandlers/IndexPage.php#L133-L150)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/RequestHandlers/IndexPage.php#L46-L49)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/RequestHandlers/IndexPage.php#L56-L79)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/RequestHandlers/IndexPage.php#L81-L84)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/RequestHandlers/IndexPage.php#L89-L99)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/RequestHandlers/IndexPage.php#L104-L108)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/RequestHandlers/IndexPage.php#L115-L122)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/RequestHandlers/IndexPage.php#L128-L135)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/RequestHandlers/IndexPage.php#L143-L149)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/RequestHandlers/IndexPage.php#L154-L160)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/RequestHandlers/IndexPage.php#L162-L171)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/RequestHandlers/IndexPage.php#L176-L185)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/RequestHandlers/IndexPage.php#L192-L202)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/RequestHandlers/IndexPage.php#L208-L218)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/RequestHandlers/IndexPage.php#L225-L235)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/RequestHandlers/IndexPage.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RequestHandlers/IndexPage.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RequestHandlers/IndexPage.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

