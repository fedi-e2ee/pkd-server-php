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

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### `time(): string`

#### `canonicalizeActor(string $actor): string`

**Parameters:**

- `$actor`: `string`

**Throws:**

- `DependencyException`
- `GuzzleException`
- `NetworkException`
- `SodiumException`
- `CertaintyException`

#### `error(string $message, int $code = 400): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

#### `signResponse(Psr\Http\Message\ResponseInterface $response): Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `SodiumException`

#### `json(object|array $data, int $status = 200, array $headers = []): Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int`
- `$headers`: `array`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

#### `twig(string $template, array $vars = [], array $headers = [], int $status = 200): Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array`
- `$headers`: `array`
- `$status`: `int`

**Throws:**

- `DependencyException`
- `LoaderError`
- `RuntimeError`
- `SyntaxError`

#### `appCache(string $namespace): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`

#### `table(string $tableName): FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

#### `injectConfig(FediE2EE\PKDServer\ServerConfig $config): void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### `config(): FediE2EE\PKDServer\ServerConfig`

**Throws:**

- `DependencyException`

#### `setWebFinger(FediE2EE\PKDServer\ActivityPub\WebFinger $wf): self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### `webfinger(?GuzzleHttp\Client $http = null): FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

---

