# RateLimit / Storage

Namespace: `FediE2EE\PKDServer\RateLimit\Storage`

## Classes

- [Filesystem](#filesystem) - class
- [Redis](#redis) - class

---

## Filesystem

**class** `FediE2EE\PKDServer\RateLimit\Storage\Filesystem`

**File:** [`src/RateLimit/Storage/Filesystem.php`](../../../src/RateLimit/Storage/Filesystem.php)

**Implements:** `FediE2EE\PKDServer\Interfaces\RateLimitStorageInterface`

**Uses:** `FediE2EE\PKDServer\Traits\JsonTrait`, `FediE2EE\PKD\Crypto\UtilTrait`

### Methods

#### [`__construct`](../../../src/RateLimit/Storage/Filesystem.php#L41-L50)

Returns `void`

**Parameters:**

- `$baseDir`: `string`
- `$cacheKey`: `?string` = null
- `$ttl`: `int` = 86400

**Throws:** `DependencyException`

#### [`get`](../../../src/RateLimit/Storage/Filesystem.php#L59-L82)

Returns `?FediE2EE\PKDServer\RateLimit\RateLimitData`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `DateMalformedStringException`, `InputException`, `JsonException`, `SodiumException`

#### [`set`](../../../src/RateLimit/Storage/Filesystem.php#L89-L97)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`
- `$data`: `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Throws:** `SodiumException`, `JsonException`

#### [`delete`](../../../src/RateLimit/Storage/Filesystem.php#L103-L112)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `SodiumException`

#### [`getFilesystemPath`](../../../src/RateLimit/Storage/Filesystem.php#L117-L138)

Returns `string`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `SodiumException`

#### [`jsonDecode`](../../../src/RateLimit/Storage/Filesystem.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RateLimit/Storage/Filesystem.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RateLimit/Storage/Filesystem.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/RateLimit/Storage/Filesystem.php#L29-L34)

static · Returns `void`

This method throws an InputException if any of the expected keys are absent.

It does not return anything.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RateLimit/Storage/Filesystem.php#L42-L49)

static · Returns `bool`

This method returns true if every expected array key is found in the target array.

Otherwise, it returns false. This is useful for input validation.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RateLimit/Storage/Filesystem.php#L64-L81)

Returns `string`

This is a constant-time conditional select. It should be read like a ternary operation.

$result = ClassWithTrait::constantTimeSelect(1, $left, $right); -> $result === $left. $result = ClassWithTrait::constantTimeSelect(0, $left, $right); -> $result === $right.

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RateLimit/Storage/Filesystem.php#L91-L94)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

This is mostly used for PEM-encoded strings.

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RateLimit/Storage/Filesystem.php#L112-L125)

static · Returns `string`

This is an implementation of PAE() from PASETO. It encodes an array of strings into a flat string consisting of:

1. The number of pieces. 2. For each piece: 1. The length of the piece (in bytes). 2. The contents of the piece. This allows multipart messages to have an injective canonical representation before passing ot a hash function (or other cryptographic function).

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RateLimit/Storage/Filesystem.php#L132-L140)

static · Returns `void`

This sorts the target array in-place, by its keys, including child arrays.

Used for ensuring arrays are sorted before JSON encoding.

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RateLimit/Storage/Filesystem.php#L147-L150)

static · Returns `string`

Mostly used by preAuthEncode() above. This packs an integer as 8 bytes.

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RateLimit/Storage/Filesystem.php#L155-L162)

Returns `array`

Get an array of bytes representing the input string.

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RateLimit/Storage/Filesystem.php#L170-L204)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## Redis

**class** `FediE2EE\PKDServer\RateLimit\Storage\Redis`

**File:** [`src/RateLimit/Storage/Redis.php`](../../../src/RateLimit/Storage/Redis.php)

**Implements:** `FediE2EE\PKDServer\Interfaces\RateLimitStorageInterface`

**Uses:** `FediE2EE\PKDServer\Traits\JsonTrait`, `FediE2EE\PKD\Crypto\UtilTrait`

### Methods

#### [`__construct`](../../../src/RateLimit/Storage/Redis.php#L28-L36)

Returns `void`

**Parameters:**

- `$redis`: `Predis\Client`
- `$cacheKey`: `?string` = null

**Throws:** `DependencyException`

#### [`get`](../../../src/RateLimit/Storage/Redis.php#L44-L52)

Returns `?FediE2EE\PKDServer\RateLimit\RateLimitData`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `InputException`, `JsonException`, `SodiumException`

#### [`set`](../../../src/RateLimit/Storage/Redis.php#L59-L64)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`
- `$data`: `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Throws:** `JsonException`, `SodiumException`

#### [`delete`](../../../src/RateLimit/Storage/Redis.php#L70-L75)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `SodiumException`

#### [`jsonDecode`](../../../src/RateLimit/Storage/Redis.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RateLimit/Storage/Redis.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RateLimit/Storage/Redis.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/RateLimit/Storage/Redis.php#L29-L34)

static · Returns `void`

This method throws an InputException if any of the expected keys are absent.

It does not return anything.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RateLimit/Storage/Redis.php#L42-L49)

static · Returns `bool`

This method returns true if every expected array key is found in the target array.

Otherwise, it returns false. This is useful for input validation.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RateLimit/Storage/Redis.php#L64-L81)

Returns `string`

This is a constant-time conditional select. It should be read like a ternary operation.

$result = ClassWithTrait::constantTimeSelect(1, $left, $right); -> $result === $left. $result = ClassWithTrait::constantTimeSelect(0, $left, $right); -> $result === $right.

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RateLimit/Storage/Redis.php#L91-L94)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

This is mostly used for PEM-encoded strings.

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RateLimit/Storage/Redis.php#L112-L125)

static · Returns `string`

This is an implementation of PAE() from PASETO. It encodes an array of strings into a flat string consisting of:

1. The number of pieces. 2. For each piece: 1. The length of the piece (in bytes). 2. The contents of the piece. This allows multipart messages to have an injective canonical representation before passing ot a hash function (or other cryptographic function).

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RateLimit/Storage/Redis.php#L132-L140)

static · Returns `void`

This sorts the target array in-place, by its keys, including child arrays.

Used for ensuring arrays are sorted before JSON encoding.

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RateLimit/Storage/Redis.php#L147-L150)

static · Returns `string`

Mostly used by preAuthEncode() above. This packs an integer as 8 bytes.

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RateLimit/Storage/Redis.php#L155-L162)

Returns `array`

Get an array of bytes representing the input string.

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RateLimit/Storage/Redis.php#L170-L204)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

