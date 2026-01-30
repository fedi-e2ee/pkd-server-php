# Traits

Namespace: `FediE2EE\PKDServer\Traits`

## Classes

- [ActivityStreamsTrait](#activitystreamstrait) - trait
- [AuxDataIdTrait](#auxdataidtrait) - trait
- [ConfigTrait](#configtrait) - trait
- [HttpCacheTrait](#httpcachetrait) - trait
- [JsonTrait](#jsontrait) - trait
- [NetworkTrait](#networktrait) - trait
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

#### [`getVerifiedStream`](../../../src/Traits/ActivityStreamsTrait.php#L38-L61)

Returns `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ActivityPubException`, `CertaintyException`, `CryptoException`, `DependencyException`, `FetchException`, `HttpSignatureException`, `InvalidArgumentException`, `NotImplementedException`, `SodiumException`

#### [`appCache`](../../../src/Traits/ActivityStreamsTrait.php#L45-L48)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/ActivityStreamsTrait.php#L55-L78)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/ActivityStreamsTrait.php#L80-L83)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/ActivityStreamsTrait.php#L88-L98)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/ActivityStreamsTrait.php#L103-L107)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/ActivityStreamsTrait.php#L114-L121)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`jsonDecode`](../../../src/Traits/ActivityStreamsTrait.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/ActivityStreamsTrait.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/ActivityStreamsTrait.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## AuxDataIdTrait

**trait** `FediE2EE\PKDServer\Traits\AuxDataIdTrait`

**File:** [`src/Traits/AuxDataIdTrait.php`](../../../src/Traits/AuxDataIdTrait.php)

**Uses:** `FediE2EE\PKD\Crypto\UtilTrait`

### Methods

#### [`getAuxDataId`](../../../src/Traits/AuxDataIdTrait.php#L13-L23)

static · Returns `string`

**Parameters:**

- `$auxDataType`: `string`
- `$data`: `string`

#### [`assertAllArrayKeysExist`](../../../src/Traits/AuxDataIdTrait.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Traits/AuxDataIdTrait.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Traits/AuxDataIdTrait.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Traits/AuxDataIdTrait.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Traits/AuxDataIdTrait.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Traits/AuxDataIdTrait.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Traits/AuxDataIdTrait.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Traits/AuxDataIdTrait.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Traits/AuxDataIdTrait.php#L107-L141)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## ConfigTrait

**trait** `FediE2EE\PKDServer\Traits\ConfigTrait`

**File:** [`src/Traits/ConfigTrait.php`](../../../src/Traits/ConfigTrait.php)

**Uses:** `FediE2EE\PKDServer\Traits\JsonTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`appCache`](../../../src/Traits/ConfigTrait.php#L45-L48)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/ConfigTrait.php#L55-L78)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/ConfigTrait.php#L80-L83)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/ConfigTrait.php#L88-L98)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/ConfigTrait.php#L103-L107)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/ConfigTrait.php#L114-L121)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`jsonDecode`](../../../src/Traits/ConfigTrait.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/ConfigTrait.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/ConfigTrait.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

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

#### [`time`](../../../src/Traits/HttpCacheTrait.php#L36-L39)

Returns `string`

#### [`canonicalizeActor`](../../../src/Traits/HttpCacheTrait.php#L48-L52)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/Traits/HttpCacheTrait.php#L60-L63)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/Traits/HttpCacheTrait.php#L74-L86)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/Traits/HttpCacheTrait.php#L96-L115)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/Traits/HttpCacheTrait.php#L123-L140)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/Traits/HttpCacheTrait.php#L45-L48)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/HttpCacheTrait.php#L55-L78)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/HttpCacheTrait.php#L80-L83)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/HttpCacheTrait.php#L88-L98)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/HttpCacheTrait.php#L103-L107)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/HttpCacheTrait.php#L114-L121)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`jsonDecode`](../../../src/Traits/HttpCacheTrait.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/HttpCacheTrait.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/HttpCacheTrait.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## JsonTrait

**trait** `FediE2EE\PKDServer\Traits\JsonTrait`

**File:** [`src/Traits/JsonTrait.php`](../../../src/Traits/JsonTrait.php)

### Methods

#### [`jsonDecode`](../../../src/Traits/JsonTrait.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/JsonTrait.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/JsonTrait.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## NetworkTrait

**trait** `FediE2EE\PKDServer\Traits\NetworkTrait`

**File:** [`src/Traits/NetworkTrait.php`](../../../src/Traits/NetworkTrait.php)

### Methods

#### [`getRequestIPSubnet`](../../../src/Traits/NetworkTrait.php#L28-L42)

Returns `string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$trustedProxies`: `array` = []
- `$ipv4MaskBits`: `int` = 32
- `$ipv6MaskBits`: `int` = 128

**Throws:** `NetTraitException`

#### [`extractIPFromRequest`](../../../src/Traits/NetworkTrait.php#L44-L68)

Returns `string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$trustedProxies`: `array` = []

#### [`ipv4Mask`](../../../src/Traits/NetworkTrait.php#L73-L99)

Returns `string`

**Parameters:**

- `$ip`: `string`
- `$maskBits`: `int` = 32

**Throws:** `NetTraitException`

#### [`ipv6Mask`](../../../src/Traits/NetworkTrait.php#L101-L127)

Returns `string`

**Parameters:**

- `$ip`: `string`
- `$maskBits`: `int` = 128

#### [`stringToByteArray`](../../../src/Traits/NetworkTrait.php#L129-L133)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`byteArrayToString`](../../../src/Traits/NetworkTrait.php#L135-L138)

Returns `string`

**Parameters:**

- `$array`: `array`

#### [`getRequestActor`](../../../src/Traits/NetworkTrait.php#L140-L160)

Returns `?string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### [`getRequestDomain`](../../../src/Traits/NetworkTrait.php#L162-L170)

Returns `?string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

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

#### [`time`](../../../src/Traits/ReqTrait.php#L36-L39)

Returns `string`

#### [`canonicalizeActor`](../../../src/Traits/ReqTrait.php#L48-L52)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `DependencyException`, `GuzzleException`, `NetworkException`, `SodiumException`, `CertaintyException`

#### [`error`](../../../src/Traits/ReqTrait.php#L60-L63)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `DependencyException`, `JsonException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/Traits/ReqTrait.php#L74-L86)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/Traits/ReqTrait.php#L96-L115)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/Traits/ReqTrait.php#L123-L140)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/Traits/ReqTrait.php#L45-L48)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/ReqTrait.php#L55-L78)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/ReqTrait.php#L80-L83)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/ReqTrait.php#L88-L98)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/ReqTrait.php#L103-L107)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/ReqTrait.php#L114-L121)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`jsonDecode`](../../../src/Traits/ReqTrait.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/ReqTrait.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/ReqTrait.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## TOTPTrait

**trait** `FediE2EE\PKDServer\Traits\TOTPTrait`

**File:** [`src/Traits/TOTPTrait.php`](../../../src/Traits/TOTPTrait.php)

**Uses:** `FediE2EE\PKDServer\Traits\JsonTrait`, `FediE2EE\PKD\Crypto\UtilTrait`

### Methods

#### [`verifyTOTP`](../../../src/Traits/TOTPTrait.php#L46-L60)

static · Returns `?int`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int` = 2

#### [`generateTOTP`](../../../src/Traits/TOTPTrait.php#L62-L77)

static · Returns `string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` = null

#### [`ord`](../../../src/Traits/TOTPTrait.php#L82-L85)

static · Returns `int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### [`throwIfTimeOutsideWindow`](../../../src/Traits/TOTPTrait.php#L142-L151)

Returns `void`

**Parameters:**

- `$currentTime`: `int`

**Throws:** `ProtocolException`

#### [`jsonDecode`](../../../src/Traits/TOTPTrait.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/TOTPTrait.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/TOTPTrait.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

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

**Uses:** `FediE2EE\PKDServer\Traits\JsonTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

### Methods

#### [`hasPrimaryKey`](../../../src/Traits/TableRecordTrait.php#L36-L39)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Traits/TableRecordTrait.php#L45-L51)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Traits/TableRecordTrait.php#L57-L67)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Traits/TableRecordTrait.php#L73-L82)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Traits/TableRecordTrait.php#L84-L87)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Traits/TableRecordTrait.php#L103-L106)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Traits/TableRecordTrait.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/TableRecordTrait.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/TableRecordTrait.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

