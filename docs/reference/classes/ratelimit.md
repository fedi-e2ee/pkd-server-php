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

#### [`__construct`](../../../src/RateLimit/DefaultRateLimiting.php#L43-L53)

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

#### [`getStorage`](../../../src/RateLimit/DefaultRateLimiting.php#L56-L59)

Returns `FediE2EE\PKDServer\Interfaces\RateLimitStorageInterface`

**Attributes:** `#[Override]`

#### [`isEnabled`](../../../src/RateLimit/DefaultRateLimiting.php#L62-L65)

Returns `bool`

**Attributes:** `#[Override]`

#### [`getBaseDelay`](../../../src/RateLimit/DefaultRateLimiting.php#L68-L71)

Returns `int`

**Attributes:** `#[Override]`

#### [`withBaseDelay`](../../../src/RateLimit/DefaultRateLimiting.php#L73-L78)

Returns `static`

**Parameters:**

- `$baseDelay`: `int`

#### [`withMaxTimeout`](../../../src/RateLimit/DefaultRateLimiting.php#L80-L91)

Returns `static`

**Parameters:**

- `$key`: `string`
- `$interval`: `?DateInterval` = null

#### [`getRequestSubnet`](../../../src/RateLimit/DefaultRateLimiting.php#L94-L103)

Returns `string`

**Attributes:** `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### [`shouldEnforce`](../../../src/RateLimit/DefaultRateLimiting.php#L109-L117)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`

**Throws:** `DependencyException`

#### [`enforceRateLimit`](../../../src/RateLimit/DefaultRateLimiting.php#L124-L171)

Returns `void`

**Attributes:** `#[Override]`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$handler`: `Psr\Http\Server\RequestHandlerInterface&FediE2EE\PKDServer\Interfaces\LimitingHandlerInterface`

**Throws:** `RateLimitException`, `DateMalformedIntervalStringException`

#### [`getCooledDown`](../../../src/RateLimit/DefaultRateLimiting.php#L179-L197)

Returns `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Attributes:** `#[Override]`

Reduce the cooldown until zero or the cooldown window is in the future:

**Parameters:**

- `$data`: `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Throws:** `DateMalformedIntervalStringException`

#### [`processTTL`](../../../src/RateLimit/DefaultRateLimiting.php#L205-L218)

Returns `int`

Collapse multiple types into a number of seconds.

**Parameters:**

- `$ttl`: `DateInterval|int|null`

#### [`getPenaltyTime`](../../../src/RateLimit/DefaultRateLimiting.php#L223-L248)

Returns `?DateTimeImmutable`

**Parameters:**

- `$data`: `?FediE2EE\PKDServer\RateLimit\RateLimitData`
- `$target`: `string`

**Throws:** `DateMalformedIntervalStringException`

#### [`getIntervalFromFailureCount`](../../../src/RateLimit/DefaultRateLimiting.php#L253-L266)

Returns `DateInterval`

**Parameters:**

- `$failures`: `int`

**Throws:** `DateMalformedIntervalStringException`

#### [`recordPenalty`](../../../src/RateLimit/DefaultRateLimiting.php#L272-L281)

Returns `void`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$lookup`: `string`

**Throws:** `DateMalformedIntervalStringException`

#### [`increaseFailures`](../../../src/RateLimit/DefaultRateLimiting.php#L286-L300)

Returns `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Parameters:**

- `$existingLimit`: `?FediE2EE\PKDServer\RateLimit\RateLimitData` = null

**Throws:** `DateMalformedIntervalStringException`

#### [`getRequestIPSubnet`](../../../src/RateLimit/DefaultRateLimiting.php#L29-L43)

Returns `string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$trustedProxies`: `array` = []
- `$ipv4MaskBits`: `int` = 32
- `$ipv6MaskBits`: `int` = 128

**Throws:** `NetTraitException`

#### [`extractIPFromRequest`](../../../src/RateLimit/DefaultRateLimiting.php#L48-L72)

Returns `string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$trustedProxies`: `array` = []

#### [`ipv4Mask`](../../../src/RateLimit/DefaultRateLimiting.php#L77-L103)

Returns `string`

**Parameters:**

- `$ip`: `string`
- `$maskBits`: `int` = 32

**Throws:** `NetTraitException`

#### [`ipv6Mask`](../../../src/RateLimit/DefaultRateLimiting.php#L108-L134)

Returns `string`

**Parameters:**

- `$ip`: `string`
- `$maskBits`: `int` = 128

**Throws:** `NetTraitException`

#### [`stringToByteArray`](../../../src/RateLimit/DefaultRateLimiting.php#L139-L146)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`byteArrayToString`](../../../src/RateLimit/DefaultRateLimiting.php#L151-L154)

Returns `string`

**Parameters:**

- `$array`: `array`

#### [`getRequestActor`](../../../src/RateLimit/DefaultRateLimiting.php#L156-L176)

Returns `?string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### [`getRequestDomain`](../../../src/RateLimit/DefaultRateLimiting.php#L178-L186)

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

#### [`__construct`](../../../src/RateLimit/RateLimitData.php#L25-L38)

Returns `void`

**Parameters:**

- `$failures`: `int`
- `$lastFailTime`: `?DateTimeImmutable` = null
- `$cooldownStart`: `?DateTimeImmutable` = null

#### [`fromJson`](../../../src/RateLimit/RateLimitData.php#L45-L75)

static · Returns `self`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`, `DateMalformedStringException`, `InputException`

#### [`getLastFailTime`](../../../src/RateLimit/RateLimitData.php#L77-L80)

Returns `DateTimeImmutable`

#### [`getCooldownStart`](../../../src/RateLimit/RateLimitData.php#L82-L85)

Returns `DateTimeImmutable`

#### [`jsonSerialize`](../../../src/RateLimit/RateLimitData.php#L91-L98)

Returns `array`

**Attributes:** `#[Override]`

#### [`failure`](../../../src/RateLimit/RateLimitData.php#L100-L107)

Returns `self`

**Parameters:**

- `$cooldownStart`: `?DateTimeImmutable` = null

#### [`withCooldownStart`](../../../src/RateLimit/RateLimitData.php#L109-L116)

Returns `self`

**Parameters:**

- `$cooldownStart`: `DateTimeImmutable`

#### [`withFailures`](../../../src/RateLimit/RateLimitData.php#L118-L125)

Returns `self`

**Parameters:**

- `$failures`: `int`

#### [`withLastFailTime`](../../../src/RateLimit/RateLimitData.php#L127-L134)

Returns `self`

**Parameters:**

- `$lastFailTime`: `DateTimeImmutable`

#### [`jsonDecode`](../../../src/RateLimit/RateLimitData.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RateLimit/RateLimitData.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RateLimit/RateLimitData.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/RateLimit/RateLimitData.php#L30-L35)

static · Returns `void`

This method throws an InputException if any of the expected keys are absent.

It does not return anything.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RateLimit/RateLimitData.php#L43-L50)

static · Returns `bool`

This method returns true if every expected array key is found in the target array.

Otherwise, it returns false. This is useful for input validation.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RateLimit/RateLimitData.php#L65-L82)

Returns `string`

This is a constant-time conditional select. It should be read like a ternary operation.

$result = ClassWithTrait::constantTimeSelect(1, $left, $right); -> $result === $left. $result = ClassWithTrait::constantTimeSelect(0, $left, $right); -> $result === $right.

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RateLimit/RateLimitData.php#L92-L95)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

This is mostly used for PEM-encoded strings.

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RateLimit/RateLimitData.php#L113-L126)

static · Returns `string`

This is an implementation of PAE() from PASETO. It encodes an array of strings into a flat string consisting of:

1. The number of pieces. 2. For each piece: 1. The length of the piece (in bytes). 2. The contents of the piece. This allows multipart messages to have an injective canonical representation before passing ot a hash function (or other cryptographic function).

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RateLimit/RateLimitData.php#L133-L141)

static · Returns `void`

This sorts the target array in-place, by its keys, including child arrays.

Used for ensuring arrays are sorted before JSON encoding.

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RateLimit/RateLimitData.php#L148-L151)

static · Returns `string`

Mostly used by preAuthEncode() above. This packs an integer as 8 bytes.

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RateLimit/RateLimitData.php#L156-L163)

Returns `array`

Get an array of bytes representing the input string.

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RateLimit/RateLimitData.php#L171-L205)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

