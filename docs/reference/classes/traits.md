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

#### [`getVerifiedStream`](../../../src/Traits/ActivityStreamsTrait.php#L42-L65)

Returns `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Parameters:**

- `$message`: `Psr\Http\Message\ServerRequestInterface`

**Throws:** `ActivityPubException`, `CertaintyException`, `CryptoException`, `DependencyException`, `FetchException`, `HttpSignatureException`, `InvalidArgumentException`, `NotImplementedException`, `SodiumException`

#### [`appCache`](../../../src/Traits/ActivityStreamsTrait.php#L55-L58)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/ActivityStreamsTrait.php#L65-L88)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/ActivityStreamsTrait.php#L90-L93)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/ActivityStreamsTrait.php#L98-L108)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/ActivityStreamsTrait.php#L113-L117)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/ActivityStreamsTrait.php#L124-L131)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Traits/ActivityStreamsTrait.php#L137-L144)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Traits/ActivityStreamsTrait.php#L152-L158)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Traits/ActivityStreamsTrait.php#L163-L169)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Traits/ActivityStreamsTrait.php#L171-L180)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Traits/ActivityStreamsTrait.php#L185-L194)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Traits/ActivityStreamsTrait.php#L201-L211)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Traits/ActivityStreamsTrait.php#L217-L227)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Traits/ActivityStreamsTrait.php#L234-L244)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Traits/ActivityStreamsTrait.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/ActivityStreamsTrait.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/ActivityStreamsTrait.php#L34-L40)

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

#### [`getAuxDataId`](../../../src/Traits/AuxDataIdTrait.php#L14-L24)

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

#### [`appCache`](../../../src/Traits/ConfigTrait.php#L55-L58)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/ConfigTrait.php#L65-L88)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/ConfigTrait.php#L90-L93)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/ConfigTrait.php#L98-L108)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/ConfigTrait.php#L113-L117)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/ConfigTrait.php#L124-L131)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Traits/ConfigTrait.php#L137-L144)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Traits/ConfigTrait.php#L152-L158)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Traits/ConfigTrait.php#L163-L169)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Traits/ConfigTrait.php#L171-L180)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Traits/ConfigTrait.php#L185-L194)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Traits/ConfigTrait.php#L201-L211)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Traits/ConfigTrait.php#L217-L227)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Traits/ConfigTrait.php#L234-L244)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Traits/ConfigTrait.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/ConfigTrait.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/ConfigTrait.php#L34-L40)

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

#### [`time`](../../../src/Traits/HttpCacheTrait.php#L39-L42)

Returns `string`

#### [`canonicalizeActor`](../../../src/Traits/HttpCacheTrait.php#L53-L61)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/Traits/HttpCacheTrait.php#L69-L72)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/Traits/HttpCacheTrait.php#L83-L95)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/Traits/HttpCacheTrait.php#L107-L126)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/Traits/HttpCacheTrait.php#L136-L153)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/Traits/HttpCacheTrait.php#L55-L58)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/HttpCacheTrait.php#L65-L88)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/HttpCacheTrait.php#L90-L93)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/HttpCacheTrait.php#L98-L108)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/HttpCacheTrait.php#L113-L117)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/HttpCacheTrait.php#L124-L131)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Traits/HttpCacheTrait.php#L137-L144)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Traits/HttpCacheTrait.php#L152-L158)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Traits/HttpCacheTrait.php#L163-L169)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Traits/HttpCacheTrait.php#L171-L180)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Traits/HttpCacheTrait.php#L185-L194)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Traits/HttpCacheTrait.php#L201-L211)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Traits/HttpCacheTrait.php#L217-L227)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Traits/HttpCacheTrait.php#L234-L244)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Traits/HttpCacheTrait.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/HttpCacheTrait.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/HttpCacheTrait.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## JsonTrait

**trait** `FediE2EE\PKDServer\Traits\JsonTrait`

**File:** [`src/Traits/JsonTrait.php`](../../../src/Traits/JsonTrait.php)

### Methods

#### [`jsonDecode`](../../../src/Traits/JsonTrait.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/JsonTrait.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/JsonTrait.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## NetworkTrait

**trait** `FediE2EE\PKDServer\Traits\NetworkTrait`

**File:** [`src/Traits/NetworkTrait.php`](../../../src/Traits/NetworkTrait.php)

### Methods

#### [`getRequestIPSubnet`](../../../src/Traits/NetworkTrait.php#L29-L43)

Returns `string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$trustedProxies`: `array` = []
- `$ipv4MaskBits`: `int` = 32
- `$ipv6MaskBits`: `int` = 128

**Throws:** `NetTraitException`

#### [`extractIPFromRequest`](../../../src/Traits/NetworkTrait.php#L48-L72)

Returns `string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$trustedProxies`: `array` = []

#### [`ipv4Mask`](../../../src/Traits/NetworkTrait.php#L77-L103)

Returns `string`

**Parameters:**

- `$ip`: `string`
- `$maskBits`: `int` = 32

**Throws:** `NetTraitException`

#### [`ipv6Mask`](../../../src/Traits/NetworkTrait.php#L108-L134)

Returns `string`

**Parameters:**

- `$ip`: `string`
- `$maskBits`: `int` = 128

**Throws:** `NetTraitException`

#### [`stringToByteArray`](../../../src/Traits/NetworkTrait.php#L139-L146)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`byteArrayToString`](../../../src/Traits/NetworkTrait.php#L151-L154)

Returns `string`

**Parameters:**

- `$array`: `array`

#### [`getRequestActor`](../../../src/Traits/NetworkTrait.php#L156-L176)

Returns `?string`

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`

#### [`getRequestDomain`](../../../src/Traits/NetworkTrait.php#L178-L186)

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

#### [`time`](../../../src/Traits/ReqTrait.php#L39-L42)

Returns `string`

#### [`canonicalizeActor`](../../../src/Traits/ReqTrait.php#L53-L61)

Returns `string`

**Parameters:**

- `$actor`: `string`

**Throws:** `CacheException`, `CertaintyException`, `DependencyException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`error`](../../../src/Traits/ReqTrait.php#L69-L72)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$message`: `string`
- `$code`: `int` = 400

**Throws:** `BaseJsonException`, `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`signResponse`](../../../src/Traits/ReqTrait.php#L83-L95)

Returns `Psr\Http\Message\ResponseInterface`

Implements an RFC 9421 HTTP Message Signature with Ed25519.

**Parameters:**

- `$response`: `Psr\Http\Message\ResponseInterface`

**Throws:** `DependencyException`, `NotImplementedException`, `SodiumException`

#### [`json`](../../../src/Traits/ReqTrait.php#L107-L126)

Returns `Psr\Http\Message\ResponseInterface`

Return a JSON response with HTTP Message Signature (from signResponse())

**Parameters:**

- `$data`: `object|array`
- `$status`: `int` = 200
- `$headers`: `array` = []

**Throws:** `DependencyException`, `BaseJsonException`, `NotImplementedException`, `SodiumException`

#### [`twig`](../../../src/Traits/ReqTrait.php#L136-L153)

Returns `Psr\Http\Message\ResponseInterface`

**Parameters:**

- `$template`: `string`
- `$vars`: `array` = []
- `$headers`: `array` = []
- `$status`: `int` = 200

**Throws:** `DependencyException`, `LoaderError`, `RuntimeError`, `SyntaxError`

#### [`appCache`](../../../src/Traits/ReqTrait.php#L55-L58)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Traits/ReqTrait.php#L65-L88)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Traits/ReqTrait.php#L90-L93)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Traits/ReqTrait.php#L98-L108)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Traits/ReqTrait.php#L113-L117)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Traits/ReqTrait.php#L124-L131)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Traits/ReqTrait.php#L137-L144)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Traits/ReqTrait.php#L152-L158)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Traits/ReqTrait.php#L163-L169)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Traits/ReqTrait.php#L171-L180)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Traits/ReqTrait.php#L185-L194)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Traits/ReqTrait.php#L201-L211)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Traits/ReqTrait.php#L217-L227)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Traits/ReqTrait.php#L234-L244)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Traits/ReqTrait.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/ReqTrait.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/ReqTrait.php#L34-L40)

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

#### [`verifyTOTP`](../../../src/Traits/TOTPTrait.php#L55-L69)

static · Returns `?int`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int` = 2

#### [`generateTOTP`](../../../src/Traits/TOTPTrait.php#L71-L87)

static · Returns `string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` = null

#### [`ord`](../../../src/Traits/TOTPTrait.php#L92-L96)

static · Returns `int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### [`throwIfTimeOutsideWindow`](../../../src/Traits/TOTPTrait.php#L157-L166)

Returns `void`

**Parameters:**

- `$currentTime`: `int`

**Throws:** `DependencyException`, `ProtocolException`

#### [`jsonDecode`](../../../src/Traits/TOTPTrait.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/TOTPTrait.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/TOTPTrait.php#L34-L40)

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

#### [`hasPrimaryKey`](../../../src/Traits/TableRecordTrait.php#L39-L42)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Traits/TableRecordTrait.php#L48-L54)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Traits/TableRecordTrait.php#L60-L70)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Traits/TableRecordTrait.php#L76-L85)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Traits/TableRecordTrait.php#L90-L93)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Traits/TableRecordTrait.php#L109-L112)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Traits/TableRecordTrait.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Traits/TableRecordTrait.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Traits/TableRecordTrait.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

