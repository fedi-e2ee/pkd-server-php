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

#### [`__construct`](../../../src/RateLimit/DefaultRateLimiting.php#L42-L52)

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

#### [`getStorage`](../../../src/RateLimit/DefaultRateLimiting.php#L55-L58)

Returns `FediE2EE\PKDServer\Interfaces\RateLimitStorageInterface`

**Attributes:** `#[Override]`

#### [`isEnabled`](../../../src/RateLimit/DefaultRateLimiting.php#L61-L64)

Returns `bool`

**Attributes:** `#[Override]`

#### [`getBaseDelay`](../../../src/RateLimit/DefaultRateLimiting.php#L67-L70)

Returns `int`

**Attributes:** `#[Override]`

#### [`withBaseDelay`](../../../src/RateLimit/DefaultRateLimiting.php#L72-L77)

Returns `static`

**Parameters:**

- `$baseDelay`: `int`

#### [`withMaxTimeout`](../../../src/RateLimit/DefaultRateLimiting.php#L79-L90)

Returns `static`

**Parameters:**

- `$key`: `string`
- `$interval`: `?DateInterval` = null

#### [`getRequestSubnet`](../../../src/RateLimit/DefaultRateLimiting.php#L93-L102)

Returns `string`

**Attributes:** `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### [`shouldEnforce`](../../../src/RateLimit/DefaultRateLimiting.php#L108-L116)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`

**Throws:** `DependencyException`

#### [`enforceRateLimit`](../../../src/RateLimit/DefaultRateLimiting.php#L123-L170)

Returns `void`

**Attributes:** `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$handler`: `Psr\Http\Server\RequestHandlerInterface&FediE2EE\PKDServer\Interfaces\LimitingHandlerInterface`

**Throws:** `RateLimitException`, `DateMalformedIntervalStringException`

#### [`getCooledDown`](../../../src/RateLimit/DefaultRateLimiting.php#L178-L196)

Returns `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Attributes:** `#[Override]`

Reduce the cooldown until zero or the cooldown window is in the future:

**Parameters:**

- `$data`: `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Throws:** `DateMalformedIntervalStringException`

#### [`processTTL`](../../../src/RateLimit/DefaultRateLimiting.php#L204-L217)

Returns `int`

Collapse multiple types into a number of seconds.

**Parameters:**

- `$ttl`: `DateInterval|int|null`

#### [`getPenaltyTime`](../../../src/RateLimit/DefaultRateLimiting.php#L222-L247)

Returns `?DateTimeImmutable`

**Parameters:**

- `$data`: `?FediE2EE\PKDServer\RateLimit\RateLimitData`
- `$target`: `string`

**Throws:** `DateMalformedIntervalStringException`

#### [`getIntervalFromFailureCount`](../../../src/RateLimit/DefaultRateLimiting.php#L252-L265)

Returns `DateInterval`

**Parameters:**

- `$failures`: `int`

**Throws:** `DateMalformedIntervalStringException`

#### [`recordPenalty`](../../../src/RateLimit/DefaultRateLimiting.php#L271-L280)

Returns `void`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$lookup`: `string`

**Throws:** `DateMalformedIntervalStringException`

#### [`increaseFailures`](../../../src/RateLimit/DefaultRateLimiting.php#L285-L299)

Returns `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Parameters:**

- `$existingLimit`: `?FediE2EE\PKDServer\RateLimit\RateLimitData` = null

**Throws:** `DateMalformedIntervalStringException`

#### [`getRequestIPSubnet`](../../../src/RateLimit/DefaultRateLimiting.php#L28-L42)

Returns `string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$trustedProxies`: `array` = []
- `$ipv4MaskBits`: `int` = 32
- `$ipv6MaskBits`: `int` = 128

**Throws:** `NetTraitException`

#### [`extractIPFromRequest`](../../../src/RateLimit/DefaultRateLimiting.php#L47-L71)

Returns `string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$trustedProxies`: `array` = []

#### [`ipv4Mask`](../../../src/RateLimit/DefaultRateLimiting.php#L76-L102)

Returns `string`

**Parameters:**

- `$ip`: `string`
- `$maskBits`: `int` = 32

**Throws:** `NetTraitException`

#### [`ipv6Mask`](../../../src/RateLimit/DefaultRateLimiting.php#L107-L133)

Returns `string`

**Parameters:**

- `$ip`: `string`
- `$maskBits`: `int` = 128

**Throws:** `NetTraitException`

#### [`stringToByteArray`](../../../src/RateLimit/DefaultRateLimiting.php#L138-L145)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`byteArrayToString`](../../../src/RateLimit/DefaultRateLimiting.php#L150-L153)

Returns `string`

**Parameters:**

- `$array`: `array`

#### [`getRequestActor`](../../../src/RateLimit/DefaultRateLimiting.php#L155-L175)

Returns `?string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### [`getRequestDomain`](../../../src/RateLimit/DefaultRateLimiting.php#L177-L185)

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

#### [`__construct`](../../../src/RateLimit/RateLimitData.php#L24-L37)

Returns `void`

**Parameters:**

- `$failures`: `int`
- `$lastFailTime`: `?DateTimeImmutable` = null
- `$cooldownStart`: `?DateTimeImmutable` = null

#### [`fromJson`](../../../src/RateLimit/RateLimitData.php#L44-L74)

static · Returns `self`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`, `DateMalformedStringException`, `InputException`

#### [`getLastFailTime`](../../../src/RateLimit/RateLimitData.php#L76-L79)

Returns `DateTimeImmutable`

#### [`getCooldownStart`](../../../src/RateLimit/RateLimitData.php#L81-L84)

Returns `DateTimeImmutable`

#### [`jsonSerialize`](../../../src/RateLimit/RateLimitData.php#L90-L97)

Returns `array`

**Attributes:** `#[Override]`

#### [`failure`](../../../src/RateLimit/RateLimitData.php#L99-L106)

Returns `self`

**Parameters:**

- `$cooldownStart`: `?DateTimeImmutable` = null

#### [`withCooldownStart`](../../../src/RateLimit/RateLimitData.php#L108-L115)

Returns `self`

**Parameters:**

- `$cooldownStart`: `DateTimeImmutable`

#### [`withFailures`](../../../src/RateLimit/RateLimitData.php#L117-L124)

Returns `self`

**Parameters:**

- `$failures`: `int`

#### [`withLastFailTime`](../../../src/RateLimit/RateLimitData.php#L126-L133)

Returns `self`

**Parameters:**

- `$lastFailTime`: `DateTimeImmutable`

#### [`jsonDecode`](../../../src/RateLimit/RateLimitData.php#L16-L19)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RateLimit/RateLimitData.php#L24-L27)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RateLimit/RateLimitData.php#L33-L39)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/RateLimit/RateLimitData.php#L27-L32)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RateLimit/RateLimitData.php#L34-L41)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RateLimit/RateLimitData.php#L48-L65)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RateLimit/RateLimitData.php#L73-L76)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RateLimit/RateLimitData.php#L84-L97)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RateLimit/RateLimitData.php#L99-L107)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RateLimit/RateLimitData.php#L111-L114)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RateLimit/RateLimitData.php#L116-L123)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RateLimit/RateLimitData.php#L131-L165)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

