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

#### [`get`](../../../src/RateLimit/Storage/Filesystem.php#L58-L77)

Returns `?FediE2EE\PKDServer\RateLimit\RateLimitData`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `InputException`, `JsonException`, `SodiumException`

#### [`set`](../../../src/RateLimit/Storage/Filesystem.php#L84-L92)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`
- `$data`: `FediE2EE\PKDServer\RateLimit\RateLimitData`

**Throws:** `SodiumException`, `JsonException`

#### [`delete`](../../../src/RateLimit/Storage/Filesystem.php#L98-L105)

Returns `bool`

**Attributes:** `#[Override]`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `SodiumException`

#### [`getFilesystemPath`](../../../src/RateLimit/Storage/Filesystem.php#L110-L131)

Returns `string`

**Parameters:**

- `$type`: `string`
- `$identifier`: `string`

**Throws:** `SodiumException`

#### [`jsonDecode`](../../../src/RateLimit/Storage/Filesystem.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RateLimit/Storage/Filesystem.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RateLimit/Storage/Filesystem.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/RateLimit/Storage/Filesystem.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RateLimit/Storage/Filesystem.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RateLimit/Storage/Filesystem.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RateLimit/Storage/Filesystem.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RateLimit/Storage/Filesystem.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RateLimit/Storage/Filesystem.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RateLimit/Storage/Filesystem.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RateLimit/Storage/Filesystem.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RateLimit/Storage/Filesystem.php#L107-L141)

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

#### [`jsonDecode`](../../../src/RateLimit/Storage/Redis.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/RateLimit/Storage/Redis.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/RateLimit/Storage/Redis.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/RateLimit/Storage/Redis.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/RateLimit/Storage/Redis.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/RateLimit/Storage/Redis.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/RateLimit/Storage/Redis.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/RateLimit/Storage/Redis.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/RateLimit/Storage/Redis.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/RateLimit/Storage/Redis.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/RateLimit/Storage/Redis.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/RateLimit/Storage/Redis.php#L107-L141)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

