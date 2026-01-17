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

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### `__construct(): void`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**API Method**

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `__construct(): void`

**Throws:**

- `DependencyException`

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `CacheException`
- `CertaintyException`
- `CryptoException`
- `DependencyException`
- `HPKEException`
- `JsonException`
- `NotImplementedException`
- `ParserException`
- `SodiumException`
- `TableException`

#### `getVerifiedStream(Psr\Http\Message\ServerRequestInterface $message): FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ActivityPubException`
- `DependencyException`
- `FetchException`
- `CryptoException`
- `HttpSignatureException`
- `NotImplementedException`
- `CertaintyException`
- `SodiumException`

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `__construct(): void`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**API Method**

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `__construct(): void`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**API Method**

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `__construct(): void`

**Throws:**

- `DependencyException`
- `TableException`
- `CacheException`

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `__construct(): void`

**Throws:**

- `DependencyException`
- `TableException`
- `CacheException`

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Override]`, `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

## HistorySince

**class** `FediE2EE\PKDServer\RequestHandlers\Api\HistorySince`

**File:** [`src/RequestHandlers/Api/HistorySince.php`](../../../src/RequestHandlers/Api/HistorySince.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### `__construct(): void`

**Throws:**

- `DependencyException`
- `TableException`
- `CacheException`

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

## HistoryView

**class** `FediE2EE\PKDServer\RequestHandlers\Api\HistoryView`

**File:** [`src/RequestHandlers/Api/HistoryView.php`](../../../src/RequestHandlers/Api/HistoryView.php)

**Implements:** `Psr\Http\Server\RequestHandlerInterface`

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### `__construct(): void`

**Throws:**

- `DependencyException`
- `TableException`
- `CacheException`

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `__construct(): void`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**API Method**

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `__construct(): void`

**Throws:**

- `CacheException`
- `DependencyException`
- `TableException`

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**API Method**

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `CacheException`
- `CryptoException`
- `DateMalformedStringException`
- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

#### `actor(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CacheException`
- `CipherSweetException`
- `CryptoException`
- `CryptoOperationException`
- `DateMalformedStringException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

#### `actorKeys(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BaseJsonException`
- `BlindIndexNotFoundException`
- `CacheException`
- `CipherSweetException`
- `CryptoException`
- `CryptoOperationException`
- `DateMalformedStringException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

#### `actorKey(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BaseJsonException`
- `BlindIndexNotFoundException`
- `CacheException`
- `CipherSweetException`
- `CryptoException`
- `CryptoOperationException`
- `DateMalformedStringException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

#### `actorAuxiliary(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CacheException`
- `CipherSweetException`
- `CryptoException`
- `CryptoOperationException`
- `DateMalformedStringException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

#### `actorAuxiliaryItem(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BaseJsonException`
- `BlindIndexNotFoundException`
- `CacheException`
- `CipherSweetException`
- `CryptoException`
- `CryptoOperationException`
- `DateMalformedStringException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

#### `history(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `BaseJsonException`
- `CacheException`
- `CryptoException`
- `DateMalformedStringException`
- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

#### `historySince(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `BaseJsonException`
- `CacheException`
- `CryptoException`
- `DateMalformedStringException`
- `DependencyException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`
- `TableException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `__construct(?FediE2EE\PKDServer\ServerConfig $config = null): void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` (nullable)

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `__construct(): void`

**Throws:**

- `DependencyException`

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`
- `NotImplementedException`
- `TableException`
- `CryptoException`
- `ParserException`
- `HPKEException`
- `SodiumException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `DependencyException`
- `HPKEException`
- `InsecureCurveException`
- `JsonException`
- `NotImplementedException`
- `SodiumException`

#### `cipherSuiteString(ParagonIE\HPKE\HPKE $hpke): string`

**Parameters:**

- `$hpke`: `ParagonIE\HPKE\HPKE`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `__construct(): void`

**Throws:**

- `DependencyException`
- `TableException`
- `CacheException`

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CacheException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `static verifyTOTP(string $secret, string $otp, int $windows = 2): bool`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int`

#### `static generateTOTP(string $secret, ?int $time = null): string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` (nullable)

#### `static ord(string $chr): int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### `throwIfTimeOutsideWindow(int $currentTime): void`

**Parameters:**

- `$currentTime`: `int`

**Throws:**

- `ProtocolException`

#### `static assertAllArrayKeysExist(array $target, string $arrayKeys): void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:**

- `InputException`

#### `static allArrayKeysExist(array $target, string $arrayKeys): bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### `constantTimeSelect(int $select, string $left, string $right): string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:**

- `CryptoException`

#### `static dos2unix(string $in): string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### `static preAuthEncode(array $pieces): string`

**Parameters:**

- `$pieces`: `array`

#### `static sortByKey(array $arr): void`

**Parameters:**

- `$arr`: `array`

#### `static LE64(int $n): string`

**Parameters:**

- `$n`: `int`

#### `stringToByteArray(string $str): array`

**Parameters:**

- `$str`: `string`

#### `static stripNewlines(string $input): string`

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

#### `__construct(): void`

**Throws:**

- `DependencyException`
- `TableException`
- `CacheException`

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CacheException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`
- `HPKEException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `static verifyTOTP(string $secret, string $otp, int $windows = 2): bool`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int`

#### `static generateTOTP(string $secret, ?int $time = null): string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` (nullable)

#### `static ord(string $chr): int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### `throwIfTimeOutsideWindow(int $currentTime): void`

**Parameters:**

- `$currentTime`: `int`

**Throws:**

- `ProtocolException`

#### `static assertAllArrayKeysExist(array $target, string $arrayKeys): void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:**

- `InputException`

#### `static allArrayKeysExist(array $target, string $arrayKeys): bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### `constantTimeSelect(int $select, string $left, string $right): string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:**

- `CryptoException`

#### `static dos2unix(string $in): string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### `static preAuthEncode(array $pieces): string`

**Parameters:**

- `$pieces`: `array`

#### `static sortByKey(array $arr): void`

**Parameters:**

- `$arr`: `array`

#### `static LE64(int $n): string`

**Parameters:**

- `$n`: `int`

#### `stringToByteArray(string $str): array`

**Parameters:**

- `$str`: `string`

#### `static stripNewlines(string $input): string`

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

#### `__construct(): void`

**Throws:**

- `DependencyException`
- `TableException`
- `CacheException`

#### `handle(Psr\Http\Message\ServerRequestInterface $request): Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Route]`, `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CacheException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `HPKEException`
- `InvalidCiphertextException`
- `JsonException`
- `NotImplementedException`
- `ProtocolException`
- `RandomException`
- `SodiumException`
- `TableException`

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

#### `appCache(string $namespace, int $defaultTTL = 60): FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int`

**Throws:**

- `DependencyException`

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

#### `static verifyTOTP(string $secret, string $otp, int $windows = 2): bool`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int`

#### `static generateTOTP(string $secret, ?int $time = null): string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` (nullable)

#### `static ord(string $chr): int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### `throwIfTimeOutsideWindow(int $currentTime): void`

**Parameters:**

- `$currentTime`: `int`

**Throws:**

- `ProtocolException`

#### `static assertAllArrayKeysExist(array $target, string $arrayKeys): void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:**

- `InputException`

#### `static allArrayKeysExist(array $target, string $arrayKeys): bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### `constantTimeSelect(int $select, string $left, string $right): string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:**

- `CryptoException`

#### `static dos2unix(string $in): string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### `static preAuthEncode(array $pieces): string`

**Parameters:**

- `$pieces`: `array`

#### `static sortByKey(array $arr): void`

**Parameters:**

- `$arr`: `array`

#### `static LE64(int $n): string`

**Parameters:**

- `$n`: `int`

#### `stringToByteArray(string $str): array`

**Parameters:**

- `$str`: `string`

#### `static stripNewlines(string $input): string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

