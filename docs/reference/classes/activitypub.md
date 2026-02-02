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

#### [`fromDecoded`](../../../src/ActivityPub/ActivityStream.php#L33-L48)

static · Returns `self`

**Parameters:**

- `$decoded`: `stdClass`

**Throws:** `ActivityPubException`

#### [`fromString`](../../../src/ActivityPub/ActivityStream.php#L53-L56)

static · Returns `self`

**Parameters:**

- `$input`: `string`

**Throws:** `ActivityPubException`

#### [`jsonSerialize`](../../../src/ActivityPub/ActivityStream.php#L59-L70)

Returns `stdClass`

**Attributes:** `#[Override]`

#### [`__toString`](../../../src/ActivityPub/ActivityStream.php#L75-L78)

Returns `string`

**Throws:** `JsonException`

#### [`isDirectMessage`](../../../src/ActivityPub/ActivityStream.php#L83-L117)

Returns `bool`

#### [`jsonDecode`](../../../src/ActivityPub/ActivityStream.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/ActivityPub/ActivityStream.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/ActivityPub/ActivityStream.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## WebFinger

**class** `FediE2EE\PKDServer\ActivityPub\WebFinger`

**File:** [`src/ActivityPub/WebFinger.php`](../../../src/ActivityPub/WebFinger.php)

### Methods

#### [`__construct`](../../../src/ActivityPub/WebFinger.php#L57-L76)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` = null
- `$client`: `?GuzzleHttp\Client` = null
- `$fetch`: `?ParagonIE\Certainty\Fetch` = null

**Throws:** `CertaintyException`, `DependencyException`, `SodiumException`

#### [`clearCaches`](../../../src/ActivityPub/WebFinger.php#L81-L88)

**API** · Returns `void`

#### [`canonicalize`](../../../src/ActivityPub/WebFinger.php#L97-L107)

Returns `string`

**Parameters:**

- `$actorUsernameOrUrl`: `string`

**Throws:** `CacheException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`fetch`](../../../src/ActivityPub/WebFinger.php#L131-L144)

Returns `array`

Fetch an entire remote WebFinger response.

**Parameters:**

- `$identifier`: `string`

**Throws:** `GuzzleException`, `NetworkException`

#### [`getInboxUrl`](../../../src/ActivityPub/WebFinger.php#L191-L210)

Returns `string`

**Parameters:**

- `$actorUrl`: `string`

**Throws:** `CacheException`, `GuzzleException`, `InvalidArgumentException`, `NetworkException`, `SodiumException`

#### [`getPublicKey`](../../../src/ActivityPub/WebFinger.php#L218-L262)

Returns `FediE2EE\PKD\Crypto\PublicKey`

**Parameters:**

- `$actorUrl`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`trimUsername`](../../../src/ActivityPub/WebFinger.php#L264-L267)

Returns `string`

**Parameters:**

- `$username`: `string`

#### [`setCanonicalForTesting`](../../../src/ActivityPub/WebFinger.php#L322-L329)

Returns `void`

Used for unit tests. Sets a canonical value to bypass the live webfinger query.

**Parameters:**

- `$index`: `string`
- `$value`: `string`

**Throws:** `CacheException`, `SodiumException`, `InvalidArgumentException`

---

