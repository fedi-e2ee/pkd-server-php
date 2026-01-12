# Protocol

Namespace: `FediE2EE\PKDServer\Protocol`

## Classes

- [Payload](#payload) - class

---

## Payload

**class** `FediE2EE\PKDServer\Protocol\Payload`

**File:** [`src/Protocol/Payload.php`](../../src/Protocol/Payload.php)

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$message` | `FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface` | (readonly)  |
| `$keyMap` | `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap` | (readonly)  |
| `$rawJson` | `string` | (readonly)  |

### Methods

#### `__construct(FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface $message, FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap $keyMap, string $rawJson): void`

**Parameters:**

- `$message`: `FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface`
- `$keyMap`: `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`
- `$rawJson`: `string`

#### `decrypt(): FediE2EE\PKD\Crypto\Protocol\ProtocolMessageInterface`

#### `jsonDecode(): array`

**Throws:**

- `JsonException`

#### `getMerkleTreePayload(): string`

---

