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

#### [`__construct`](../../../src/Scheduled/ASQueue.php#L47-L56)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`run`](../../../src/Scheduled/ASQueue.php#L68-L103)

Returns `void`

ASQueue::run() is a very dumb method.

All this method does is grab the unprocessed messages, order them, decode them, and then pass them onto Protocol::process(). The logic is entirely contained to Protocol and the Table classes.

**Throws:** `DependencyException`

#### [`appCache`](../../../src/Scheduled/ASQueue.php#L55-L58)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Scheduled/ASQueue.php#L65-L88)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Scheduled/ASQueue.php#L90-L93)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Scheduled/ASQueue.php#L98-L108)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Scheduled/ASQueue.php#L113-L117)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Scheduled/ASQueue.php#L124-L131)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Scheduled/ASQueue.php#L137-L144)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Scheduled/ASQueue.php#L152-L158)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Scheduled/ASQueue.php#L163-L169)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Scheduled/ASQueue.php#L171-L180)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Scheduled/ASQueue.php#L185-L194)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Scheduled/ASQueue.php#L201-L211)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Scheduled/ASQueue.php#L217-L227)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Scheduled/ASQueue.php#L234-L244)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Scheduled/ASQueue.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Scheduled/ASQueue.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Scheduled/ASQueue.php#L34-L40)

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

#### [`__construct`](../../../src/Scheduled/Witness.php#L88-L125)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`run`](../../../src/Scheduled/Witness.php#L132-L144)

Returns `void`

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`

#### [`getHashesSince`](../../../src/Scheduled/Witness.php#L747-L764)

Returns `array`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`

**Throws:** `CryptoException`, `GuzzleException`, `HttpSignatureException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`

#### [`appCache`](../../../src/Scheduled/Witness.php#L55-L58)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Scheduled/Witness.php#L65-L88)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Scheduled/Witness.php#L90-L93)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Scheduled/Witness.php#L98-L108)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Scheduled/Witness.php#L113-L117)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Scheduled/Witness.php#L124-L131)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`parseUrlHost`](../../../src/Scheduled/Witness.php#L137-L144)

static · Returns `?string`

**Parameters:**

- `$url`: `string`

#### [`assertArray`](../../../src/Scheduled/Witness.php#L152-L158)

static · Returns `array`

**Parameters:**

- `$result`: `object|array`

**Throws:** `TypeError`

#### [`assertString`](../../../src/Scheduled/Witness.php#L163-L169)

static · Returns `string`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`assertStringOrNull`](../../../src/Scheduled/Witness.php#L171-L180)

static · Returns `?string`

**Parameters:**

- `$value`: `mixed`

#### [`assertInt`](../../../src/Scheduled/Witness.php#L185-L194)

static · Returns `int`

**Parameters:**

- `$value`: `mixed`

**Throws:** `TypeError`

#### [`rowToStringArray`](../../../src/Scheduled/Witness.php#L201-L211)

static · Returns `array`

**Parameters:**

- `$row`: `object|array`

**Throws:** `TypeError`

#### [`decryptedString`](../../../src/Scheduled/Witness.php#L217-L227)

static · Returns `string`

**Parameters:**

- `$row`: `array`
- `$key`: `string`

**Throws:** `TypeError`

#### [`blindIndexValue`](../../../src/Scheduled/Witness.php#L234-L244)

static · Returns `string`

**Parameters:**

- `$blindIndex`: `array|string`
- `$key`: `?string` = null

#### [`jsonDecode`](../../../src/Scheduled/Witness.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Scheduled/Witness.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Scheduled/Witness.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

