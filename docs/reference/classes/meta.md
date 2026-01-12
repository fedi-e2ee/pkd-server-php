# Meta

Namespace: `FediE2EE\PKDServer\Meta`

## Classes

- [Params](#params) - class
- [RecordForTable](#recordfortable) - class
- [Route](#route) - class

---

## Params

**class** `FediE2EE\PKDServer\Meta\Params`

**File:** [`src/Meta/Params.php`](../../src/Meta/Params.php)

Server configuration parameters

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$hashAlgo` | `string` | (readonly)  |
| `$otpMaxLife` | `int` | (readonly)  |
| `$actorUsername` | `string` | (readonly)  |
| `$hostname` | `string` | (readonly)  |
| `$cacheKey` | `string` | (readonly)  |

### Methods

#### `__construct(string $hashAlgo = 'sha256', int $otpMaxLife = 120, string $actorUsername = 'pubkeydir', string $hostname = 'localhost', string $cacheKey = ''): void`

These parameters MUST be public and MUST have a default value

**Parameters:**

- `$hashAlgo`: `string`
- `$otpMaxLife`: `int`
- `$actorUsername`: `string`
- `$hostname`: `string`
- `$cacheKey`: `string`

---

## RecordForTable

**class** `FediE2EE\PKDServer\Meta\RecordForTable`

**File:** [`src/Meta/RecordForTable.php`](../../src/Meta/RecordForTable.php)

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$tableName` | `string` | (readonly)  |

### Methods

#### `__construct(string $tableName = ''): void`

**Parameters:**

- `$tableName`: `string`

---

## Route

**class** `FediE2EE\PKDServer\Meta\Route`

**File:** [`src/Meta/Route.php`](../../src/Meta/Route.php)

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$uriPattern` | `string` | (readonly)  |

### Methods

#### `__construct(string $uriPattern = ''): void`

**Parameters:**

- `$uriPattern`: `string`

---

