# ActivityPub

Namespace: `FediE2EE\PKDServer\ActivityPub`

## Classes

- [ActivityStream](#activitystream) - class
- [WebFinger](#webfinger) - class

---

## ActivityStream

**class** `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**File:** [`src/ActivityPub/ActivityStream.php`](../../../src/ActivityPub/ActivityStream.php)

**Implements:** `JsonSerializable`, `Stringable`

**Uses:** `FediE2EE\PKDServer\Traits\JsonTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$id` | `string` |  |
| `$type` | `string` |  |
| `$actor` | `string` |  |
| `$object` | `object` |  |

### Methods

#### [`fromDecoded`](../../../src/ActivityPub/ActivityStream.php#L27-L42)

static · Returns `self`

**Parameters:**

- `$decoded`: `stdClass`

**Throws:** `ActivityPubException`

#### [`fromString`](../../../src/ActivityPub/ActivityStream.php#L47-L50)

static · Returns `self`

**Parameters:**

- `$input`: `string`

**Throws:** `ActivityPubException`

#### [`jsonSerialize`](../../../src/ActivityPub/ActivityStream.php#L53-L63)

Returns `stdClass`

**Attributes:** `#[Override]`

#### [`__toString`](../../../src/ActivityPub/ActivityStream.php#L68-L71)

Returns `string`

**Throws:** `JsonException`

#### [`isDirectMessage`](../../../src/ActivityPub/ActivityStream.php#L76-L110)

Returns `bool`

#### [`jsonDecode`](../../../src/ActivityPub/ActivityStream.php#L12-L15)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/ActivityPub/ActivityStream.php#L20-L23)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/ActivityPub/ActivityStream.php#L28-L34)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## WebFinger

**class** `FediE2EE\PKDServer\ActivityPub\WebFinger`

**File:** [`src/ActivityPub/WebFinger.php`](../../../src/ActivityPub/WebFinger.php)

### Methods

#### [`__construct`](../../../src/ActivityPub/WebFinger.php#L42-L61)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` = null
- `$client`: `?GuzzleHttp\Client` = null
- `$fetch`: `?ParagonIE\Certainty\Fetch` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`clearCaches`](../../../src/ActivityPub/WebFinger.php#L66-L73)

**API** · Returns `void`

#### [`canonicalize`](../../../src/ActivityPub/WebFinger.php#L82-L92)

Returns `string`

**Parameters:**

- `$actorUsernameOrUrl`: `string`

**Throws:** `CacheException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`fetch`](../../../src/ActivityPub/WebFinger.php#L115-L128)

Returns `array`

Fetch an entire remote WebFinger response.

**Parameters:**

- `$identifier`: `string`

**Throws:** `GuzzleException`, `NetworkException`

#### [`getInboxUrl`](../../../src/ActivityPub/WebFinger.php#L175-L193)

Returns `string`

**Parameters:**

- `$actorUrl`: `string`

**Throws:** `CacheException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`getPublicKey`](../../../src/ActivityPub/WebFinger.php#L201-L242)

Returns `FediE2EE\PKD\Crypto\PublicKey`

**Parameters:**

- `$actorUrl`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`trimUsername`](../../../src/ActivityPub/WebFinger.php#L244-L247)

Returns `string`

**Parameters:**

- `$username`: `string`

#### [`setCanonicalForTesting`](../../../src/ActivityPub/WebFinger.php#L302-L309)

Returns `void`

Used for unit tests. Sets a canonical value to bypass the live webfinger query.

**Parameters:**

- `$index`: `string`
- `$value`: `string`

**Throws:** `CacheException`, `SodiumException`, `InvalidArgumentException`

---

