# Traits

Namespace: `FediE2EE\PKDServer\Traits`

## Classes

- [ActivityStreamsTrait](#activitystreamstrait) - trait
- [ConfigTrait](#configtrait) - trait
- [ProtocolMethodTrait](#protocolmethodtrait) - trait
- [ReqTrait](#reqtrait) - trait
- [TOTPTrait](#totptrait) - trait
- [TableRecordTrait](#tablerecordtrait) - trait

---

## ActivityStreamsTrait

**trait** `FediE2EE\PKDServer\Traits\ActivityStreamsTrait`

**File:** [`src/Traits/ActivityStreamsTrait.php`](../../../src/Traits/ActivityStreamsTrait.php)

**Uses:** `FediE2EE\PKDServer\Traits\ConfigTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

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

## ConfigTrait

**trait** `FediE2EE\PKDServer\Traits\ConfigTrait`

**File:** [`src/Traits/ConfigTrait.php`](../../../src/Traits/ConfigTrait.php)

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

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

## ProtocolMethodTrait

**trait** `FediE2EE\PKDServer\Traits\ProtocolMethodTrait`

**File:** [`src/Traits/ProtocolMethodTrait.php`](../../../src/Traits/ProtocolMethodTrait.php)

---

## ReqTrait

**trait** `FediE2EE\PKDServer\Traits\ReqTrait`

**File:** [`src/Traits/ReqTrait.php`](../../../src/Traits/ReqTrait.php)

Request Handler trait

**Uses:** `FediE2EE\PKDServer\Traits\ConfigTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

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

## TOTPTrait

**trait** `FediE2EE\PKDServer\Traits\TOTPTrait`

**File:** [`src/Traits/TOTPTrait.php`](../../../src/Traits/TOTPTrait.php)

**Uses:** `FediE2EE\PKD\Crypto\UtilTrait`

### Methods

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

## TableRecordTrait

**trait** `FediE2EE\PKDServer\Traits\TableRecordTrait`

**File:** [`src/Traits/TableRecordTrait.php`](../../../src/Traits/TableRecordTrait.php)

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

### Methods

#### `hasPrimaryKey(): bool`

#### `getPrimaryKey(): int`

**Throws:**

- `TableException`

#### `attachSymmetricKey(string $property, FediE2EE\PKD\Crypto\SymmetricKey $key): self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:**

- `TableException`

#### `getSymmetricKeyForProperty(string $property): FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:**

- `TableException`

#### `getSymmetricKeys(): array`

#### `getRfc9421PublicKeys(string $actorId): FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

**Parameters:**

- `$actorId`: `string`

**Throws:**

- `CryptoException`
- `FetchException`

---

