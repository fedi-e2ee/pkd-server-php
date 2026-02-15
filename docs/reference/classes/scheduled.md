# Scheduled

Namespace: `FediE2EE\PKDServer\Scheduled`

## Classes

- [ASQueue](#asqueue) - class
- [Witness](#witness) - class

---

## ASQueue

**class** `FediE2EE\PKDServer\Scheduled\ASQueue`

**File:** [`src/Scheduled/ASQueue.php`](../../../src/Scheduled/ASQueue.php)

**Uses:** `FediE2EE\PKDServer\Traits\ConfigTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/Scheduled/ASQueue.php#L46-L55)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`run`](../../../src/Scheduled/ASQueue.php#L67-L102)

Returns `void`

ASQueue::run() is a very dumb method.

All this method does is grab the unprocessed messages, order them, decode them, and then pass them onto Protocol::process(). The logic is entirely contained to Protocol and the Table classes.

**Throws:** `DependencyException`

#### [`appCache`](../../../src/Scheduled/ASQueue.php#L54-L57)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Scheduled/ASQueue.php#L64-L87)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Scheduled/ASQueue.php#L89-L92)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Scheduled/ASQueue.php#L97-L107)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Scheduled/ASQueue.php#L112-L116)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Scheduled/ASQueue.php#L123-L130)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Scheduled/ASQueue.php#L136-L143)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Scheduled/ASQueue.php#L151-L157)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Scheduled/ASQueue.php#L162-L168)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Scheduled/ASQueue.php#L170-L179)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Scheduled/ASQueue.php#L184-L193)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Scheduled/ASQueue.php#L200-L210)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Scheduled/ASQueue.php#L216-L226)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Scheduled/ASQueue.php#L233-L243)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Scheduled/ASQueue.php#L16-L19)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Scheduled/ASQueue.php#L24-L27)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Scheduled/ASQueue.php#L33-L39)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## Witness

**class** `FediE2EE\PKDServer\Scheduled\Witness`

**File:** [`src/Scheduled/Witness.php`](../../../src/Scheduled/Witness.php)

Perform witness co-signatures for third-porty Public Key Directory instances.

**Uses:** `FediE2EE\PKDServer\Traits\ConfigTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `?FediE2EE\PKDServer\ServerConfig` |  |

### Methods

#### [`__construct`](../../../src/Scheduled/Witness.php#L86-L123)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`run`](../../../src/Scheduled/Witness.php#L130-L142)

Returns `void`

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`

#### [`getHashesSince`](../../../src/Scheduled/Witness.php#L751-L768)

Returns `array`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`

**Throws:** `CryptoException`, `GuzzleException`, `HttpSignatureException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`

#### [`appCache`](../../../src/Scheduled/Witness.php#L54-L57)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Scheduled/Witness.php#L64-L87)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Scheduled/Witness.php#L89-L92)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Scheduled/Witness.php#L97-L107)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Scheduled/Witness.php#L112-L116)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Scheduled/Witness.php#L123-L130)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Scheduled/Witness.php#L136-L143)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Scheduled/Witness.php#L151-L157)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Scheduled/Witness.php#L162-L168)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Scheduled/Witness.php#L170-L179)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Scheduled/Witness.php#L184-L193)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Scheduled/Witness.php#L200-L210)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Scheduled/Witness.php#L216-L226)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Scheduled/Witness.php#L233-L243)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Scheduled/Witness.php#L16-L19)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Scheduled/Witness.php#L24-L27)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Scheduled/Witness.php#L33-L39)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

