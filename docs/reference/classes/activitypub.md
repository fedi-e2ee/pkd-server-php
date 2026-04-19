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

#### [`fromDecoded`](../../../src/ActivityPub/ActivityStream.php#L28-L43)

static · Returns `self`

**Parameters:**

- `$decoded`: `stdClass`

**Throws:** `ActivityPubException`

#### [`fromString`](../../../src/ActivityPub/ActivityStream.php#L48-L51)

static · Returns `self`

**Parameters:**

- `$input`: `string`

**Throws:** `ActivityPubException`

#### [`jsonSerialize`](../../../src/ActivityPub/ActivityStream.php#L54-L65)

Returns `stdClass`

**Attributes:** `#[Override]`

#### [`__toString`](../../../src/ActivityPub/ActivityStream.php#L70-L73)

Returns `string`

**Throws:** `JsonException`

#### [`isDirectMessage`](../../../src/ActivityPub/ActivityStream.php#L78-L112)

Returns `bool`

#### [`jsonDecode`](../../../src/ActivityPub/ActivityStream.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/ActivityPub/ActivityStream.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/ActivityPub/ActivityStream.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## WebFinger

**class** `FediE2EE\PKDServer\ActivityPub\WebFinger`

**File:** [`src/ActivityPub/WebFinger.php`](../../../src/ActivityPub/WebFinger.php)

### Methods

#### [`__construct`](../../../src/ActivityPub/WebFinger.php#L56-L80)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` = null
- `$client`: `?GuzzleHttp\Client` = null
- `$fetch`: `?ParagonIE\Certainty\Fetch` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`clearCaches`](../../../src/ActivityPub/WebFinger.php#L85-L92)

**API** · Returns `void`

#### [`canonicalize`](../../../src/ActivityPub/WebFinger.php#L101-L111)

Returns `string`

**Parameters:**

- `$actorUsernameOrUrl`: `string`

**Throws:** `CacheException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`fetch`](../../../src/ActivityPub/WebFinger.php#L135-L148)

Returns `array`

Fetch an entire remote WebFinger response.

**Parameters:**

- `$identifier`: `string`

**Throws:** `GuzzleException`, `NetworkException`

#### [`getInboxUrl`](../../../src/ActivityPub/WebFinger.php#L195-L211)

Returns `string`

**Parameters:**

- `$actorUrl`: `string`

**Throws:** `CacheException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`getPublicKey`](../../../src/ActivityPub/WebFinger.php#L219-L263)

Returns `FediE2EE\PKD\Crypto\PublicKey`

**Parameters:**

- `$actorUrl`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`trimUsername`](../../../src/ActivityPub/WebFinger.php#L265-L268)

Returns `string`

**Parameters:**

- `$username`: `string`

#### [`setCanonicalForTesting`](../../../src/ActivityPub/WebFinger.php#L323-L330)

Returns `void`

Used for unit tests. Sets a canonical value to bypass the live webfinger query.

**Parameters:**

- `$index`: `string`
- `$value`: `string`

**Throws:** `CacheException`, `SodiumException`, `InvalidArgumentException`

---

