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

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$id` | `string` |  |
| `$type` | `string` |  |
| `$actor` | `string` |  |
| `$object` | `object` |  |

### Methods

#### `static fromDecoded(stdClass $decoded): self`

**Parameters:**

- `$decoded`: `stdClass`

**Throws:**

- `ActivityPubException`

#### `static fromString(string $input): self`

**Parameters:**

- `$input`: `string`

**Throws:**

- `ActivityPubException`

#### `jsonSerialize(): stdClass`

**Attributes:** `#[Override]`

#### `__toString(): string`

**Throws:**

- `JsonException`

#### `isDirectMessage(): bool`

---

## WebFinger

**class** `FediE2EE\PKDServer\ActivityPub\WebFinger`

**File:** [`src/ActivityPub/WebFinger.php`](../../../src/ActivityPub/WebFinger.php)

### Methods

#### `__construct(?FediE2EE\PKDServer\ServerConfig $config = null, ?GuzzleHttp\Client $client = null, ?ParagonIE\Certainty\Fetch $fetch = null): void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` (nullable)
- `$client`: `?GuzzleHttp\Client` (nullable)
- `$fetch`: `?ParagonIE\Certainty\Fetch` (nullable)

**Throws:**

- `CertaintyException`
- `DependencyException`
- `SodiumException`

#### `clearCaches(): void`

**API Method**

#### `canonicalize(string $actorUsernameOrUrl): string`

**Parameters:**

- `$actorUsernameOrUrl`: `string`

**Throws:**

- `NetworkException`
- `GuzzleException`

#### `fetch(string $identifier): array`

Fetch an entire remote WebFinger response.

**Parameters:**

- `$identifier`: `string`

**Throws:**

- `GuzzleException`
- `NetworkException`

#### `getInboxUrl(string $actorUrl): string`

**Parameters:**

- `$actorUrl`: `string`

**Throws:**

- `CacheException`
- `GuzzleException`
- `NetworkException`

#### `getPublicKey(string $actorUrl): FediE2EE\PKD\Crypto\PublicKey`

**Parameters:**

- `$actorUrl`: `string`

**Throws:**

- `CryptoException`
- `FetchException`

#### `setCanonicalForTesting(string $index, string $value): void`

Used for unit tests. Sets a canonical value to bypass the live webfinger query.

**Parameters:**

- `$index`: `string`
- `$value`: `string`

**Throws:**

- `SodiumException`
- `InvalidArgumentException`

---

