# Meta

Namespace: `FediE2EE\PKDServer\Meta`

## Classes

- [Params](#params) - class
- [RecordForTable](#recordfortable) - class
- [Route](#route) - class

---

## Params

**class** `FediE2EE\PKDServer\Meta\Params`

**File:** [`src/Meta/Params.php`](../../../src/Meta/Params.php)

Server configuration parameters

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$hashAlgo` | `string` | (readonly)  |
| `$otpMaxLife` | `int` | (readonly)  |
| `$actorUsername` | `string` | (readonly)  |
| `$hostname` | `string` | (readonly)  |
| `$cacheKey` | `string` | (readonly)  |
| `$httpCacheTtl` | `int` | (readonly)  |

### Methods

#### [`__construct`](../../../src/Meta/Params.php#L20-L46)

Returns `void`

These parameters MUST be public and MUST have a default value

**Parameters:**

- `$hashAlgo`: `string` = 'sha256'
- `$otpMaxLife`: `int` = 120
- `$actorUsername`: `string` = 'pubkeydir'
- `$hostname`: `string` = 'localhost'
- `$cacheKey`: `string` = ''
- `$httpCacheTtl`: `int` = 60

**Throws:** `DependencyException`

#### [`getActorUsername`](../../../src/Meta/Params.php#L48-L51)

Returns `string`

#### [`getCacheKey`](../../../src/Meta/Params.php#L53-L56)

Returns `string`

#### [`getHashFunction`](../../../src/Meta/Params.php#L58-L61)

Returns `string`

#### [`getHostname`](../../../src/Meta/Params.php#L63-L66)

Returns `string`

#### [`getHttpCacheTtl`](../../../src/Meta/Params.php#L68-L71)

Returns `int`

#### [`getOtpMaxLife`](../../../src/Meta/Params.php#L73-L76)

Returns `int`

#### [`getEmptyTreeRoot`](../../../src/Meta/Params.php#L78-L81)

Returns `string`

---

## RecordForTable

**class** `FediE2EE\PKDServer\Meta\RecordForTable`

**File:** [`src/Meta/RecordForTable.php`](../../../src/Meta/RecordForTable.php)

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$tableName` | `string` | (readonly)  |

### Methods

#### [`__construct`](../../../src/Meta/RecordForTable.php#L10)

Returns `void`

**Parameters:**

- `$tableName`: `string` = ''

---

## Route

**class** `FediE2EE\PKDServer\Meta\Route`

**File:** [`src/Meta/Route.php`](../../../src/Meta/Route.php)

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$uriPattern` | `string` | (readonly)  |

### Methods

#### [`__construct`](../../../src/Meta/Route.php#L10)

Returns `void`

**Parameters:**

- `$uriPattern`: `string` = ''

---

