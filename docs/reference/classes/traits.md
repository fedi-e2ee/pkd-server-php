# Traits

Namespace: `FediE2EE\PKDServer\Traits`

## Classes

- [ActivityStreamsTrait](#activitystreamstrait) - trait
- [ConfigTrait](#configtrait) - trait
- [HttpCacheTrait](#httpcachetrait) - trait
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

#### [`getVerifiedStream`](../../../src/Traits/ActivityStreamsTrait.php#L35-L58)

Returns `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ActivityPubException`, `DependencyException`, `FetchException`, `CryptoException`, `HttpSignatureException`, `NotImplementedException`, `CertaintyException`, `SodiumException`

#### [`appCache`](../../../src/Traits/ActivityStreamsTrait.php#L42-L45)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/ActivityStreamsTrait.php#L52-L75)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/ActivityStreamsTrait.php#L77-L80)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/ActivityStreamsTrait.php#L85-L95)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/ActivityStreamsTrait.php#L100-L104)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/ActivityStreamsTrait.php#L111-L118)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

---

## ConfigTrait

**trait** `FediE2EE\PKDServer\Traits\ConfigTrait`

**File:** [`src/Traits/ConfigTrait.php`](../../../src/Traits/ConfigTrait.php)

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`appCache`](../../../src/Traits/ConfigTrait.php#L42-L45)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/ConfigTrait.php#L52-L75)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/ConfigTrait.php#L77-L80)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/ConfigTrait.php#L85-L95)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/ConfigTrait.php#L100-L104)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/ConfigTrait.php#L111-L118)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

---

## HttpCacheTrait

**trait** `FediE2EE\PKDServer\Traits\HttpCacheTrait`

**File:** [`src/Traits/HttpCacheTrait.php`](../../../src/Traits/HttpCacheTrait.php)

**Uses:** `FediE2EE\PKDServer\Traits\ReqTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`clearCache`](../../../src/Traits/HttpCacheTrait.php#L34-L37)

Returns `bool`

**Throws:** `DependencyException`

#### [`time`](../../../src/Traits/HttpCacheTrait.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/Traits/HttpCacheTrait.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/Traits/HttpCacheTrait.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/Traits/HttpCacheTrait.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/Traits/HttpCacheTrait.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/Traits/HttpCacheTrait.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/Traits/HttpCacheTrait.php#L42-L45)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/HttpCacheTrait.php#L52-L75)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/HttpCacheTrait.php#L77-L80)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/HttpCacheTrait.php#L85-L95)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/HttpCacheTrait.php#L100-L104)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/HttpCacheTrait.php#L111-L118)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

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

#### [`time`](../../../src/Traits/ReqTrait.php#L34-L37)

Returns `string`

#### [`canonicalizeActor`](../../../src/Traits/ReqTrait.php#L46-L50)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/Traits/ReqTrait.php#L58-L61)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/Traits/ReqTrait.php#L72-L84)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/Traits/ReqTrait.php#L94-L119)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/Traits/ReqTrait.php#L127-L144)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/Traits/ReqTrait.php#L42-L45)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/ReqTrait.php#L52-L75)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/ReqTrait.php#L77-L80)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/ReqTrait.php#L85-L95)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/ReqTrait.php#L100-L104)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/ReqTrait.php#L111-L118)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

---

## TOTPTrait

**trait** `FediE2EE\PKDServer\Traits\TOTPTrait`

**File:** [`src/Traits/TOTPTrait.php`](../../../src/Traits/TOTPTrait.php)

**Uses:** `FediE2EE\PKD\Crypto\UtilTrait`

### Methods

#### [`verifyTOTP`](../../../src/Traits/TOTPTrait.php#L42-L56)

static · Returns `bool`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int` = 2

#### [`generateTOTP`](../../../src/Traits/TOTPTrait.php#L58-L73)

static · Returns `string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` = null

#### [`ord`](../../../src/Traits/TOTPTrait.php#L78-L81)

static · Returns `int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### [`throwIfTimeOutsideWindow`](../../../src/Traits/TOTPTrait.php#L139-L148)

Returns `void`

**Parameters:**

- `$currentTime`: `int`

**Throws:** `ProtocolException`

#### [`assertAllArrayKeysExist`](../../../src/Traits/TOTPTrait.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Traits/TOTPTrait.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Traits/TOTPTrait.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Traits/TOTPTrait.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Traits/TOTPTrait.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Traits/TOTPTrait.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Traits/TOTPTrait.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Traits/TOTPTrait.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Traits/TOTPTrait.php#L107-L141)

static · Returns `string`

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

#### [`hasPrimaryKey`](../../../src/Traits/TableRecordTrait.php#L31-L34)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Traits/TableRecordTrait.php#L40-L46)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Traits/TableRecordTrait.php#L52-L62)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Traits/TableRecordTrait.php#L68-L77)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Traits/TableRecordTrait.php#L79-L82)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Traits/TableRecordTrait.php#L96-L99)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`

---

