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

#### [`__construct`](../../../src/RateLimit/Storage/Filesystem.php#L42-L51)

Returns `void`

**Parameters:**

- `$baseDir`: `string`
- `$cacheKey`: `?string` = null
- `$ttl`: `int` = 86400

**Throws:** `DependencyException`

#### [`get`](../../../src/RateLimit/Storage/Filesystem.php#L60-L83)

Returns `?FediE2EE\PKDServer\RateLimit\RateLimitData`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `DateMalformedStringException`, `InputException`, `JsonException`, `SodiumException`

#### [`set`](../../../src/RateLimit/Storage/Filesystem.php#L90-L98)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`
- `$data`: `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Throws:** `SodiumException`, `JsonException`

#### [`delete`](../../../src/RateLimit/Storage/Filesystem.php#L104-L113)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `SodiumException`

#### [`getFilesystemPath`](../../../src/RateLimit/Storage/Filesystem.php#L118-L139)

Returns `string`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `SodiumException`

#### [`jsonDecode`](../../../src/RateLimit/Storage/Filesystem.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RateLimit/Storage/Filesystem.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RateLimit/Storage/Filesystem.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/RateLimit/Storage/Filesystem.php#L30-L35)

static · Returns `void`

This method throws an InputException if any of the expected keys are absent.

It does not return anything.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RateLimit/Storage/Filesystem.php#L43-L50)

static · Returns `bool`

This method returns true if every expected array key is found in the target array.

Otherwise, it returns false. This is useful for input validation.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RateLimit/Storage/Filesystem.php#L65-L82)

Returns `string`

This is a constant-time conditional select. It should be read like a ternary operation.

$result = ClassWithTrait::constantTimeSelect(1, $left, $right); -> $result === $left. $result = ClassWithTrait::constantTimeSelect(0, $left, $right); -> $result === $right.

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RateLimit/Storage/Filesystem.php#L92-L95)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

This is mostly used for PEM-encoded strings.

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RateLimit/Storage/Filesystem.php#L113-L126)

static · Returns `string`

This is an implementation of PAE() from PASETO. It encodes an array of strings into a flat string consisting of:

1. The number of pieces. 2. For each piece: 1. The length of the piece (in bytes). 2. The contents of the piece. This allows multipart messages to have an injective canonical representation before passing ot a hash function (or other cryptographic function).

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RateLimit/Storage/Filesystem.php#L133-L141)

static · Returns `void`

This sorts the target array in-place, by its keys, including child arrays.

Used for ensuring arrays are sorted before JSON encoding.

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RateLimit/Storage/Filesystem.php#L148-L151)

static · Returns `string`

Mostly used by preAuthEncode() above. This packs an integer as 8 bytes.

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RateLimit/Storage/Filesystem.php#L156-L163)

Returns `array`

Get an array of bytes representing the input string.

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RateLimit/Storage/Filesystem.php#L171-L205)

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

#### [`__construct`](../../../src/RateLimit/Storage/Redis.php#L32-L40)

Returns `void`

**Parameters:**

- `$redis`: `Predis\Client`
- `$cacheKey`: `?string` = null

**Throws:** `DependencyException`

#### [`get`](../../../src/RateLimit/Storage/Redis.php#L48-L56)

Returns `?FediE2EE\PKDServer\RateLimit\RateLimitData`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `InputException`, `JsonException`, `SodiumException`

#### [`set`](../../../src/RateLimit/Storage/Redis.php#L63-L68)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`
- `$data`: `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Throws:** `JsonException`, `SodiumException`

#### [`delete`](../../../src/RateLimit/Storage/Redis.php#L74-L79)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `SodiumException`

#### [`jsonDecode`](../../../src/RateLimit/Storage/Redis.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RateLimit/Storage/Redis.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RateLimit/Storage/Redis.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/RateLimit/Storage/Redis.php#L30-L35)

static · Returns `void`

This method throws an InputException if any of the expected keys are absent.

It does not return anything.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RateLimit/Storage/Redis.php#L43-L50)

static · Returns `bool`

This method returns true if every expected array key is found in the target array.

Otherwise, it returns false. This is useful for input validation.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RateLimit/Storage/Redis.php#L65-L82)

Returns `string`

This is a constant-time conditional select. It should be read like a ternary operation.

$result = ClassWithTrait::constantTimeSelect(1, $left, $right); -> $result === $left. $result = ClassWithTrait::constantTimeSelect(0, $left, $right); -> $result === $right.

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RateLimit/Storage/Redis.php#L92-L95)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

This is mostly used for PEM-encoded strings.

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RateLimit/Storage/Redis.php#L113-L126)

static · Returns `string`

This is an implementation of PAE() from PASETO. It encodes an array of strings into a flat string consisting of:

1. The number of pieces. 2. For each piece: 1. The length of the piece (in bytes). 2. The contents of the piece. This allows multipart messages to have an injective canonical representation before passing ot a hash function (or other cryptographic function).

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RateLimit/Storage/Redis.php#L133-L141)

static · Returns `void`

This sorts the target array in-place, by its keys, including child arrays.

Used for ensuring arrays are sorted before JSON encoding.

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RateLimit/Storage/Redis.php#L148-L151)

static · Returns `string`

Mostly used by preAuthEncode() above. This packs an integer as 8 bytes.

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RateLimit/Storage/Redis.php#L156-L163)

Returns `array`

Get an array of bytes representing the input string.

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RateLimit/Storage/Redis.php#L171-L205)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

