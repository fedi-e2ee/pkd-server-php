# RateLimit

Namespace: `FediE2EE\PKDServer\RateLimit`

## Classes

- [DefaultRateLimiting](#defaultratelimiting) - class
- [RateLimitData](#ratelimitdata) - class

---

## DefaultRateLimiting

**class** `FediE2EE\PKDServer\RateLimit\DefaultRateLimiting`

**File:** [`src/RateLimit/DefaultRateLimiting.php`](../../../src/RateLimit/DefaultRateLimiting.php)

**Implements:** `FediE2EE\PKDServer\Interfaces\RateLimitInterface`

**Uses:** `FediE2EE\PKDServer\Traits\NetworkTrait`

### Methods

#### [`__construct`](../../../src/RateLimit/DefaultRateLimiting.php#L37-L47)

Returns `void`

**Parameters:**

- `$storage`: `FediE2EE\PKDServer\Interfaces\RateLimitStorageInterface`
- `$enabled`: `bool` = true
- `$baseDelay`: `int` = 100
- `$trustedProxies`: `array` = []
- `$ipv4MaskBits`: `int` = 32
- `$ipv6MaskBits`: `int` = 64
- `$shouldEnforceDomain`: `bool` = true
- `$shouldEnforceActor`: `bool` = true
- `$maxTimeouts`: `array` = []

#### [`getStorage`](../../../src/RateLimit/DefaultRateLimiting.php#L50-L53)

Returns `FediE2EE\PKDServer\Interfaces\RateLimitStorageInterface`

**Attributes:** `#[Override]`

#### [`isEnabled`](../../../src/RateLimit/DefaultRateLimiting.php#L56-L59)

Returns `bool`

**Attributes:** `#[Override]`

#### [`getBaseDelay`](../../../src/RateLimit/DefaultRateLimiting.php#L62-L65)

Returns `int`

**Attributes:** `#[Override]`

#### [`withBaseDelay`](../../../src/RateLimit/DefaultRateLimiting.php#L67-L72)

Returns `static`

**Parameters:**

- `$baseDelay`: `int`

#### [`getRequestSubnet`](../../../src/RateLimit/DefaultRateLimiting.php#L75-L84)

Returns `string`

**Attributes:** `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### [`shouldEnforce`](../../../src/RateLimit/DefaultRateLimiting.php#L90-L98)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`

**Throws:** `DependencyException`

#### [`enforceRateLimit`](../../../src/RateLimit/DefaultRateLimiting.php#L105-L145)

Returns `void`

**Attributes:** `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$handler`: `Psr\Http\Server\RequestHandlerInterface&FediE2EE\PKDServer\Interfaces\LimitingHandlerInterface`

**Throws:** `RateLimitException`, `DateMalformedIntervalStringException`

#### [`getCooledDown`](../../../src/RateLimit/DefaultRateLimiting.php#L153-L171)

Returns `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Attributes:** `#[Override]`

Reduce the cooldown until zero or the cooldown window is in the future:

**Parameters:**

- `$data`: `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Throws:** `DateMalformedIntervalStringException`

#### [`getPenaltyTime`](../../../src/RateLimit/DefaultRateLimiting.php#L176-L199)

Returns `?DateTimeImmutable`

**Parameters:**

- `$data`: `?FediE2EE\PKDServer\RateLimit\RateLimitData`
- `$target`: `string`

**Throws:** `DateMalformedIntervalStringException`

#### [`getIntervalFromFailureCount`](../../../src/RateLimit/DefaultRateLimiting.php#L204-L213)

Returns `DateInterval`

**Parameters:**

- `$failures`: `int`

**Throws:** `DateMalformedIntervalStringException`

#### [`recordPenalty`](../../../src/RateLimit/DefaultRateLimiting.php#L219-L228)

Returns `void`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$lookup`: `string`

**Throws:** `DateMalformedIntervalStringException`

#### [`increaseFailures`](../../../src/RateLimit/DefaultRateLimiting.php#L233-L247)

Returns `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Parameters:**

- `$existingLimit`: `?FediE2EE\PKDServer\RateLimit\RateLimitData` = null

**Throws:** `DateMalformedIntervalStringException`

#### [`getRequestIPSubnet`](../../../src/RateLimit/DefaultRateLimiting.php#L10-L24)

Returns `string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$trustedProxies`: `array` = []
- `$ipv4MaskBits`: `int` = 32
- `$ipv6MaskBits`: `int` = 128

#### [`extractIPFromRequest`](../../../src/RateLimit/DefaultRateLimiting.php#L26-L50)

Returns `string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$trustedProxies`: `array` = []

#### [`ipv4Mask`](../../../src/RateLimit/DefaultRateLimiting.php#L52-L78)

Returns `string`

**Parameters:**

- `$ip`: `string`
- `$maskBits`: `int` = 32

#### [`ipv6Mask`](../../../src/RateLimit/DefaultRateLimiting.php#L80-L106)

Returns `string`

**Parameters:**

- `$ip`: `string`
- `$maskBits`: `int` = 128

#### [`stringToByteArray`](../../../src/RateLimit/DefaultRateLimiting.php#L108-L112)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`byteArrayToString`](../../../src/RateLimit/DefaultRateLimiting.php#L114-L117)

Returns `string`

**Parameters:**

- `$array`: `array`

#### [`getRequestActor`](../../../src/RateLimit/DefaultRateLimiting.php#L119-L139)

Returns `?string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### [`getRequestDomain`](../../../src/RateLimit/DefaultRateLimiting.php#L141-L149)

Returns `?string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

---

## RateLimitData

**class** `FediE2EE\PKDServer\RateLimit\RateLimitData`

**File:** [`src/RateLimit/RateLimitData.php`](../../../src/RateLimit/RateLimitData.php)

**Implements:** `JsonSerializable`

**Uses:** `FediE2EE\PKDServer\Traits\JsonTrait`, `FediE2EE\PKD\Crypto\UtilTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$failures` | `int` |  |

### Methods

#### [`__construct`](../../../src/RateLimit/RateLimitData.php#L20-L33)

Returns `void`

**Parameters:**

- `$failures`: `int`
- `$lastFailTime`: `?DateTimeImmutable` = null
- `$cooldownStart`: `?DateTimeImmutable` = null

#### [`fromJson`](../../../src/RateLimit/RateLimitData.php#L39-L53)

static · Returns `self`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`, `InputException`

#### [`getLastFailTime`](../../../src/RateLimit/RateLimitData.php#L55-L58)

Returns `DateTimeImmutable`

#### [`getCooldownStart`](../../../src/RateLimit/RateLimitData.php#L60-L63)

Returns `DateTimeImmutable`

#### [`jsonSerialize`](../../../src/RateLimit/RateLimitData.php#L66-L73)

Returns `array`

**Attributes:** `#[Override]`

#### [`failure`](../../../src/RateLimit/RateLimitData.php#L75-L82)

Returns `self`

**Parameters:**

- `$cooldownStart`: `?DateTimeImmutable` = null

#### [`withCooldownStart`](../../../src/RateLimit/RateLimitData.php#L84-L91)

Returns `self`

**Parameters:**

- `$cooldownStart`: `DateTimeImmutable`

#### [`withFailures`](../../../src/RateLimit/RateLimitData.php#L93-L100)

Returns `self`

**Parameters:**

- `$failures`: `int`

#### [`withLastFailTime`](../../../src/RateLimit/RateLimitData.php#L102-L109)

Returns `self`

**Parameters:**

- `$lastFailTime`: `DateTimeImmutable`

#### [`jsonDecode`](../../../src/RateLimit/RateLimitData.php#L12-L15)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RateLimit/RateLimitData.php#L20-L23)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RateLimit/RateLimitData.php#L28-L34)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/RateLimit/RateLimitData.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RateLimit/RateLimitData.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RateLimit/RateLimitData.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RateLimit/RateLimitData.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RateLimit/RateLimitData.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RateLimit/RateLimitData.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RateLimit/RateLimitData.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RateLimit/RateLimitData.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RateLimit/RateLimitData.php#L107-L141)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

