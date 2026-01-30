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

#### [`__construct`](../../../src/Scheduled/ASQueue.php#L45-L53)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`run`](../../../src/Scheduled/ASQueue.php#L65-L100)

Returns `void`

ASQueue::run() is a very dumb method.

All this method does is grab the unprocessed messages, order them, decode them, and then pass them onto Protocol::process(). The logic is entirely contained to Protocol and the Table classes.

**Throws:** `DependencyException`

#### [`appCache`](../../../src/Scheduled/ASQueue.php#L45-L48)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Scheduled/ASQueue.php#L55-L78)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Scheduled/ASQueue.php#L80-L83)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Scheduled/ASQueue.php#L88-L98)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Scheduled/ASQueue.php#L103-L107)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Scheduled/ASQueue.php#L114-L121)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`jsonDecode`](../../../src/Scheduled/ASQueue.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Scheduled/ASQueue.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Scheduled/ASQueue.php#L29-L35)

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

#### [`__construct`](../../../src/Scheduled/Witness.php#L80-L114)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`run`](../../../src/Scheduled/Witness.php#L121-L133)

Returns `void`

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`

#### [`getHashesSince`](../../../src/Scheduled/Witness.php#L722-L739)

Returns `array`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`

**Throws:** `CryptoException`, `GuzzleException`, `HttpSignatureException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`

#### [`appCache`](../../../src/Scheduled/Witness.php#L45-L48)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Scheduled/Witness.php#L55-L78)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Scheduled/Witness.php#L80-L83)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Scheduled/Witness.php#L88-L98)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Scheduled/Witness.php#L103-L107)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Scheduled/Witness.php#L114-L121)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`jsonDecode`](../../../src/Scheduled/Witness.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Scheduled/Witness.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Scheduled/Witness.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

