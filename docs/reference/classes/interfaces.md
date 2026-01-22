# Interfaces

Namespace: `FediE2EE\PKDServer\Interfaces`

## Classes

- [HttpCacheInterface](#httpcacheinterface) - interface
- [LimitingHandlerInterface](#limitinghandlerinterface) - interface
- [RateLimitInterface](#ratelimitinterface) - interface
- [RateLimitStorageInterface](#ratelimitstorageinterface) - interface

---

## HttpCacheInterface

**abstract interface** `FediE2EE\PKDServer\Interfaces\HttpCacheInterface`

**File:** [`src/Interfaces/HttpCacheInterface.php`](../../../src/Interfaces/HttpCacheInterface.php)

### Methods

#### [`getPrimaryCacheKey`](../../../src/Interfaces/HttpCacheInterface.php#L7)

abstract · Returns `string`

---

## LimitingHandlerInterface

**abstract interface** `FediE2EE\PKDServer\Interfaces\LimitingHandlerInterface`

**File:** [`src/Interfaces/LimitingHandlerInterface.php`](../../../src/Interfaces/LimitingHandlerInterface.php)

### Methods

#### [`getEnabledRateLimits`](../../../src/Interfaces/LimitingHandlerInterface.php#L9)

abstract · Returns `array`

---

## RateLimitInterface

**abstract interface** `FediE2EE\PKDServer\Interfaces\RateLimitInterface`

**File:** [`src/Interfaces/RateLimitInterface.php`](../../../src/Interfaces/RateLimitInterface.php)

### Methods

#### [`getStorage`](../../../src/Interfaces/RateLimitInterface.php#L12)

abstract · Returns `FediE2EE\PKDServer\Interfaces\RateLimitStorageInterface`

#### [`isEnabled`](../../../src/Interfaces/RateLimitInterface.php#L13)

abstract · Returns `bool`

#### [`getBaseDelay`](../../../src/Interfaces/RateLimitInterface.php#L14)

abstract · Returns `int`

#### [`enforceRateLimit`](../../../src/Interfaces/RateLimitInterface.php#L19-L22)

abstract · Returns `void`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$handler`: `Psr\Http\Server\RequestHandlerInterface&FediE2EE\PKDServer\Interfaces\LimitingHandlerInterface`

**Throws:** `RateLimitException`

#### [`shouldEnforce`](../../../src/Interfaces/RateLimitInterface.php#L23)

abstract · Returns `bool`

**Parameters:**

- `$type`: `string`

#### [`recordPenalty`](../../../src/Interfaces/RateLimitInterface.php#L24)

abstract · Returns `void`

**Parameters:**

- `$type`: `string`
- `$lookup`: `string`

#### [`getCooledDown`](../../../src/Interfaces/RateLimitInterface.php#L25)

abstract · Returns `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Parameters:**

- `$data`: `FediE2EE\PKDServer\RateLimit\RateLimitData`

#### [`getRequestActor`](../../../src/Interfaces/RateLimitInterface.php#L27)

abstract · Returns `?string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### [`getRequestDomain`](../../../src/Interfaces/RateLimitInterface.php#L28)

abstract · Returns `?string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### [`getRequestSubnet`](../../../src/Interfaces/RateLimitInterface.php#L29)

abstract · Returns `string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

---

## RateLimitStorageInterface

**abstract interface** `FediE2EE\PKDServer\Interfaces\RateLimitStorageInterface`

**File:** [`src/Interfaces/RateLimitStorageInterface.php`](../../../src/Interfaces/RateLimitStorageInterface.php)

### Methods

#### [`get`](../../../src/Interfaces/RateLimitStorageInterface.php#L9)

abstract · Returns `?FediE2EE\PKDServer\RateLimit\RateLimitData`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

#### [`set`](../../../src/Interfaces/RateLimitStorageInterface.php#L10)

abstract · Returns `bool`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`
- `$data`: `FediE2EE\PKDServer\RateLimit\RateLimitData`

#### [`delete`](../../../src/Interfaces/RateLimitStorageInterface.php#L11)

abstract · Returns `bool`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

---

