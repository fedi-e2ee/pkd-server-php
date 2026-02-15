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

#### [`getVerifiedStream`](../../../src/Traits/ActivityStreamsTrait.php#L41-L64)

Returns `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ActivityPubException`, `CertaintyException`, `CryptoException`, `DependencyException`, `FetchException`, `HttpSignatureException`, `InvalidArgumentException`, `NotImplementedException`, `SodiumException`

#### [`appCache`](../../../src/Traits/ActivityStreamsTrait.php#L54-L57)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/ActivityStreamsTrait.php#L64-L87)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/ActivityStreamsTrait.php#L89-L92)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/ActivityStreamsTrait.php#L97-L107)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/ActivityStreamsTrait.php#L112-L116)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/ActivityStreamsTrait.php#L123-L130)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Traits/ActivityStreamsTrait.php#L136-L143)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Traits/ActivityStreamsTrait.php#L151-L157)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Traits/ActivityStreamsTrait.php#L162-L168)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Traits/ActivityStreamsTrait.php#L170-L179)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Traits/ActivityStreamsTrait.php#L184-L193)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Traits/ActivityStreamsTrait.php#L200-L210)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Traits/ActivityStreamsTrait.php#L216-L226)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Traits/ActivityStreamsTrait.php#L233-L243)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Traits/ActivityStreamsTrait.php#L16-L19)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/ActivityStreamsTrait.php#L24-L27)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/ActivityStreamsTrait.php#L33-L39)

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

#### [`assertAllArrayKeysExist`](../../../src/Traits/AuxDataIdTrait.php#L27-L32)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Traits/AuxDataIdTrait.php#L34-L41)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Traits/AuxDataIdTrait.php#L48-L65)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Traits/AuxDataIdTrait.php#L73-L76)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Traits/AuxDataIdTrait.php#L84-L97)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Traits/AuxDataIdTrait.php#L99-L107)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Traits/AuxDataIdTrait.php#L111-L114)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Traits/AuxDataIdTrait.php#L116-L123)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Traits/AuxDataIdTrait.php#L131-L165)

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

#### [`appCache`](../../../src/Traits/ConfigTrait.php#L54-L57)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/ConfigTrait.php#L64-L87)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/ConfigTrait.php#L89-L92)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/ConfigTrait.php#L97-L107)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/ConfigTrait.php#L112-L116)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/ConfigTrait.php#L123-L130)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Traits/ConfigTrait.php#L136-L143)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Traits/ConfigTrait.php#L151-L157)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Traits/ConfigTrait.php#L162-L168)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Traits/ConfigTrait.php#L170-L179)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Traits/ConfigTrait.php#L184-L193)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Traits/ConfigTrait.php#L200-L210)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Traits/ConfigTrait.php#L216-L226)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Traits/ConfigTrait.php#L233-L243)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Traits/ConfigTrait.php#L16-L19)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/ConfigTrait.php#L24-L27)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/ConfigTrait.php#L33-L39)

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

#### [`time`](../../../src/Traits/HttpCacheTrait.php#L38-L41)

Returns `string`

#### [`canonicalizeActor`](../../../src/Traits/HttpCacheTrait.php#L52-L60)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/Traits/HttpCacheTrait.php#L68-L71)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/Traits/HttpCacheTrait.php#L82-L93)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/Traits/HttpCacheTrait.php#L105-L124)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/Traits/HttpCacheTrait.php#L134-L151)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/Traits/HttpCacheTrait.php#L54-L57)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/HttpCacheTrait.php#L64-L87)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/HttpCacheTrait.php#L89-L92)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/HttpCacheTrait.php#L97-L107)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/HttpCacheTrait.php#L112-L116)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/HttpCacheTrait.php#L123-L130)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Traits/HttpCacheTrait.php#L136-L143)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Traits/HttpCacheTrait.php#L151-L157)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Traits/HttpCacheTrait.php#L162-L168)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Traits/HttpCacheTrait.php#L170-L179)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Traits/HttpCacheTrait.php#L184-L193)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Traits/HttpCacheTrait.php#L200-L210)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Traits/HttpCacheTrait.php#L216-L226)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Traits/HttpCacheTrait.php#L233-L243)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Traits/HttpCacheTrait.php#L16-L19)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/HttpCacheTrait.php#L24-L27)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/HttpCacheTrait.php#L33-L39)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## JsonTrait

**trait** `FediE2EE\PKDServer\Traits\JsonTrait`

**File:** [`src/Traits/JsonTrait.php`](../../../src/Traits/JsonTrait.php)

### Methods

#### [`jsonDecode`](../../../src/Traits/JsonTrait.php#L16-L19)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/JsonTrait.php#L24-L27)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/JsonTrait.php#L33-L39)

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

#### [`extractIPFromRequest`](../../../src/Traits/NetworkTrait.php#L47-L71)

Returns `string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$trustedProxies`: `array` = []

#### [`ipv4Mask`](../../../src/Traits/NetworkTrait.php#L76-L102)

Returns `string`

**Parameters:**

- `$ip`: `string`
- `$maskBits`: `int` = 32

**Throws:** `NetTraitException`

#### [`ipv6Mask`](../../../src/Traits/NetworkTrait.php#L107-L133)

Returns `string`

**Parameters:**

- `$ip`: `string`
- `$maskBits`: `int` = 128

**Throws:** `NetTraitException`

#### [`stringToByteArray`](../../../src/Traits/NetworkTrait.php#L138-L145)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`byteArrayToString`](../../../src/Traits/NetworkTrait.php#L150-L153)

Returns `string`

**Parameters:**

- `$array`: `array`

#### [`getRequestActor`](../../../src/Traits/NetworkTrait.php#L155-L175)

Returns `?string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### [`getRequestDomain`](../../../src/Traits/NetworkTrait.php#L177-L185)

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

#### [`time`](../../../src/Traits/ReqTrait.php#L38-L41)

Returns `string`

#### [`canonicalizeActor`](../../../src/Traits/ReqTrait.php#L52-L60)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/Traits/ReqTrait.php#L68-L71)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/Traits/ReqTrait.php#L82-L93)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/Traits/ReqTrait.php#L105-L124)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/Traits/ReqTrait.php#L134-L151)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/Traits/ReqTrait.php#L54-L57)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/ReqTrait.php#L64-L87)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/ReqTrait.php#L89-L92)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/ReqTrait.php#L97-L107)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/ReqTrait.php#L112-L116)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/ReqTrait.php#L123-L130)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Traits/ReqTrait.php#L136-L143)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Traits/ReqTrait.php#L151-L157)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Traits/ReqTrait.php#L162-L168)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Traits/ReqTrait.php#L170-L179)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Traits/ReqTrait.php#L184-L193)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Traits/ReqTrait.php#L200-L210)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Traits/ReqTrait.php#L216-L226)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Traits/ReqTrait.php#L233-L243)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Traits/ReqTrait.php#L16-L19)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/ReqTrait.php#L24-L27)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/ReqTrait.php#L33-L39)

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

#### [`verifyTOTP`](../../../src/Traits/TOTPTrait.php#L57-L71)

static · Returns `?int`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int` = 2

#### [`generateTOTP`](../../../src/Traits/TOTPTrait.php#L73-L89)

static · Returns `string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` = null

#### [`ord`](../../../src/Traits/TOTPTrait.php#L94-L98)

static · Returns `int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### [`throwIfTimeOutsideWindow`](../../../src/Traits/TOTPTrait.php#L159-L168)

Returns `void`

**Parameters:**

- `$currentTime`: `int`

**Throws:** `DependencyException`, `ProtocolException`

#### [`jsonDecode`](../../../src/Traits/TOTPTrait.php#L16-L19)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/TOTPTrait.php#L24-L27)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/TOTPTrait.php#L33-L39)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Traits/TOTPTrait.php#L27-L32)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Traits/TOTPTrait.php#L34-L41)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Traits/TOTPTrait.php#L48-L65)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Traits/TOTPTrait.php#L73-L76)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Traits/TOTPTrait.php#L84-L97)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Traits/TOTPTrait.php#L99-L107)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Traits/TOTPTrait.php#L111-L114)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Traits/TOTPTrait.php#L116-L123)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Traits/TOTPTrait.php#L131-L165)

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

#### [`hasPrimaryKey`](../../../src/Traits/TableRecordTrait.php#L38-L41)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Traits/TableRecordTrait.php#L47-L53)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Traits/TableRecordTrait.php#L59-L69)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Traits/TableRecordTrait.php#L75-L84)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Traits/TableRecordTrait.php#L89-L92)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Traits/TableRecordTrait.php#L108-L111)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Traits/TableRecordTrait.php#L16-L19)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/TableRecordTrait.php#L24-L27)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/TableRecordTrait.php#L33-L39)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

