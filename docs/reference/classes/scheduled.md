# Scheduled

Namespace: `FediE2EE\PKDServer\Scheduled`

## Classes

- [ASQueue](#asqueue) - class
- [Witness](#witness) - class

---

## ASQueue

**class** `FediE2EE\PKDServer\Scheduled\ASQueue`

**File:** [`src/Scheduled/ASQueue.php`](../../../src/Scheduled/ASQueue.php)

### Methods

#### [`__construct`](../../../src/Scheduled/ASQueue.php#L37-L42)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`run`](../../../src/Scheduled/ASQueue.php#L52-L87)

Returns `void`

ASQueue::run() is a very dumb method.

All this method does is grab the unprocessed messages, order them, decode them, and then pass them onto Protocol::process(). The logic is entirely contained to Protocol and the Table classes.

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

#### [`__construct`](../../../src/Scheduled/Witness.php#L70-L104)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`run`](../../../src/Scheduled/Witness.php#L111-L123)

Returns `void`

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`

#### [`getHashesSince`](../../../src/Scheduled/Witness.php#L277-L294)

Returns `array`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`

**Throws:** `CryptoException`, `GuzzleException`, `HttpSignatureException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`

#### [`appCache`](../../../src/Scheduled/Witness.php#L42-L45)

Returns `FediE2EE\PKDServer\AppCache`

**Parameters:**

- `$namespace`: `string`
- `$defaultTTL`: `int` = 60

**Throws:** `DependencyException`

#### [`table`](../../../src/Scheduled/Witness.php#L52-L75)

Returns `FediE2EE\PKDServer\Table`

**Parameters:**

- `$tableName`: `string`

**Throws:** `CacheException`, `DependencyException`, `TableException`

#### [`injectConfig`](../../../src/Scheduled/Witness.php#L77-L80)

Returns `void`

**Parameters:**

- `$config`: `FediE2EE\PKDServer\ServerConfig`

#### [`config`](../../../src/Scheduled/Witness.php#L85-L95)

Returns `FediE2EE\PKDServer\ServerConfig`

**Throws:** `DependencyException`

#### [`setWebFinger`](../../../src/Scheduled/Witness.php#L100-L104)

Returns `self`

This is intended for mocking in unit tests

**Parameters:**

- `$wf`: `FediE2EE\PKDServer\ActivityPub\WebFinger`

#### [`webfinger`](../../../src/Scheduled/Witness.php#L111-L118)

Returns `FediE2EE\PKDServer\ActivityPub\WebFinger`

**Parameters:**

- `$http`: `?GuzzleHttp\Client` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

---

