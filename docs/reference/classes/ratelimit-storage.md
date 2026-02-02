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

#### [`get`](../../../src/RateLimit/Storage/Filesystem.php#L58-L81)

Returns `?FediE2EE\PKDServer\RateLimit\RateLimitData`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `InputException`, `JsonException`, `SodiumException`

#### [`set`](../../../src/RateLimit/Storage/Filesystem.php#L88-L96)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`
- `$data`: `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Throws:** `SodiumException`, `JsonException`

#### [`delete`](../../../src/RateLimit/Storage/Filesystem.php#L102-L109)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `SodiumException`

#### [`getFilesystemPath`](../../../src/RateLimit/Storage/Filesystem.php#L114-L135)

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

#### [`assertAllArrayKeysExist`](../../../src/RateLimit/Storage/Filesystem.php#L27-L32)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RateLimit/Storage/Filesystem.php#L34-L41)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RateLimit/Storage/Filesystem.php#L48-L65)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RateLimit/Storage/Filesystem.php#L73-L76)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RateLimit/Storage/Filesystem.php#L84-L97)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RateLimit/Storage/Filesystem.php#L99-L107)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RateLimit/Storage/Filesystem.php#L111-L114)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RateLimit/Storage/Filesystem.php#L116-L123)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RateLimit/Storage/Filesystem.php#L131-L165)

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

#### [`assertAllArrayKeysExist`](../../../src/RateLimit/Storage/Redis.php#L27-L32)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RateLimit/Storage/Redis.php#L34-L41)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RateLimit/Storage/Redis.php#L48-L65)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RateLimit/Storage/Redis.php#L73-L76)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RateLimit/Storage/Redis.php#L84-L97)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RateLimit/Storage/Redis.php#L99-L107)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RateLimit/Storage/Redis.php#L111-L114)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RateLimit/Storage/Redis.php#L116-L123)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RateLimit/Storage/Redis.php#L131-L165)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

