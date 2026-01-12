# Tables / Records

Namespace: `FediE2EE\PKDServer\Tables\Records`

## Classes

- [Actor](#actor) - class
- [ActorKey](#actorkey) - class
- [AuxDatum](#auxdatum) - class
- [MerkleLeaf](#merkleleaf) - class
- [Peer](#peer) - class

---

## Actor

**final class** `FediE2EE\PKDServer\Tables\Records\Actor`

**File:** [`src/Tables/Records/Actor.php`](../../src/Tables/Records/Actor.php)

Abstraction for a row in the Actors table

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$actorID` | `string` |  |
| `$rfc9421pk` | `?FediE2EE\PKD\Crypto\PublicKey` |  |
| `$fireProof` | `bool` |  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

### Methods

#### `__construct(string $actorID, ?FediE2EE\PKD\Crypto\PublicKey $rfc9421pk = null, bool $fireProof = false, ?int $primaryKey = null): void`

**Parameters:**

- `$actorID`: `string`
- `$rfc9421pk`: `?FediE2EE\PKD\Crypto\PublicKey` (nullable)
- `$fireProof`: `bool`
- `$primaryKey`: `?int` (nullable)

#### `static create(string $actorID, string $rfc9421pk = '', bool $fireProof = false): self`

Instantiate a new object without a primary key

**Parameters:**

- `$actorID`: `string`
- `$rfc9421pk`: `string`
- `$fireProof`: `bool`

#### `toArray(): array`

#### `hasPrimaryKey(): bool`

#### `getPrimaryKey(): int`

**Throws:**

- `TableException`

#### `attachSymmetricKey(string $property, FediE2EE\PKD\Crypto\SymmetricKey $key): self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:**

- `TableException`

#### `getSymmetricKeyForProperty(string $property): FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:**

- `TableException`

#### `getSymmetricKeys(): array`

#### `getRfc9421PublicKeys(string $actorId): FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

**Parameters:**

- `$actorId`: `string`

**Throws:**

- `CryptoException`
- `FetchException`

---

## ActorKey

**class** `FediE2EE\PKDServer\Tables\Records\ActorKey`

**File:** [`src/Tables/Records/ActorKey.php`](../../src/Tables/Records/ActorKey.php)

Abstraction for a row in the PublicKeys table

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$actor` | `FediE2EE\PKDServer\Tables\Records\Actor` |  |
| `$publicKey` | `FediE2EE\PKD\Crypto\PublicKey` |  |
| `$trusted` | `bool` |  |
| `$insertLeaf` | `FediE2EE\PKDServer\Tables\Records\MerkleLeaf` |  |
| `$revokeLeaf` | `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf` |  |
| `$keyID` | `?string` |  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

### Methods

#### `__construct(FediE2EE\PKDServer\Tables\Records\Actor $actor, FediE2EE\PKD\Crypto\PublicKey $publicKey, bool $trusted, FediE2EE\PKDServer\Tables\Records\MerkleLeaf $insertLeaf, ?FediE2EE\PKDServer\Tables\Records\MerkleLeaf $revokeLeaf = null, ?string $keyID = null, ?int $primaryKey = null): void`

**Parameters:**

- `$actor`: `FediE2EE\PKDServer\Tables\Records\Actor`
- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`
- `$trusted`: `bool`
- `$insertLeaf`: `FediE2EE\PKDServer\Tables\Records\MerkleLeaf`
- `$revokeLeaf`: `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf` (nullable)
- `$keyID`: `?string` (nullable)
- `$primaryKey`: `?int` (nullable)

#### `hasPrimaryKey(): bool`

#### `getPrimaryKey(): int`

**Throws:**

- `TableException`

#### `attachSymmetricKey(string $property, FediE2EE\PKD\Crypto\SymmetricKey $key): self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:**

- `TableException`

#### `getSymmetricKeyForProperty(string $property): FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:**

- `TableException`

#### `getSymmetricKeys(): array`

#### `getRfc9421PublicKeys(string $actorId): FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

**Parameters:**

- `$actorId`: `string`

**Throws:**

- `CryptoException`
- `FetchException`

---

## AuxDatum

**class** `FediE2EE\PKDServer\Tables\Records\AuxDatum`

**File:** [`src/Tables/Records/AuxDatum.php`](../../src/Tables/Records/AuxDatum.php)

Abstraction for a row in the AuxData table

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$actor` | `FediE2EE\PKDServer\Tables\Records\Actor` |  |
| `$auxDataType` | `string` |  |
| `$auxData` | `string` |  |
| `$trusted` | `bool` |  |
| `$insertLeaf` | `FediE2EE\PKDServer\Tables\Records\MerkleLeaf` |  |
| `$revokeLeaf` | `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf` |  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

### Methods

#### `__construct(FediE2EE\PKDServer\Tables\Records\Actor $actor, string $auxDataType, string $auxData, bool $trusted, FediE2EE\PKDServer\Tables\Records\MerkleLeaf $insertLeaf, ?FediE2EE\PKDServer\Tables\Records\MerkleLeaf $revokeLeaf = null, ?int $primaryKey = null): void`

**Parameters:**

- `$actor`: `FediE2EE\PKDServer\Tables\Records\Actor`
- `$auxDataType`: `string`
- `$auxData`: `string`
- `$trusted`: `bool`
- `$insertLeaf`: `FediE2EE\PKDServer\Tables\Records\MerkleLeaf`
- `$revokeLeaf`: `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf` (nullable)
- `$primaryKey`: `?int` (nullable)

#### `getActor(): FediE2EE\PKDServer\Tables\Records\Actor`

#### `hasPrimaryKey(): bool`

#### `getPrimaryKey(): int`

**Throws:**

- `TableException`

#### `attachSymmetricKey(string $property, FediE2EE\PKD\Crypto\SymmetricKey $key): self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:**

- `TableException`

#### `getSymmetricKeyForProperty(string $property): FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:**

- `TableException`

#### `getSymmetricKeys(): array`

#### `getRfc9421PublicKeys(string $actorId): FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

**Parameters:**

- `$actorId`: `string`

**Throws:**

- `CryptoException`
- `FetchException`

---

## MerkleLeaf

**class** `FediE2EE\PKDServer\Tables\Records\MerkleLeaf`

**File:** [`src/Tables/Records/MerkleLeaf.php`](../../src/Tables/Records/MerkleLeaf.php)

Abstraction for a row in the MerkleState table

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`, `FediE2EE\PKD\Crypto\UtilTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$contents` | `string` | (readonly)  |
| `$contentHash` | `string` | (readonly)  |
| `$signature` | `string` | (readonly)  |
| `$publicKeyHash` | `string` | (readonly)  |
| `$inclusionProof` | `?FediE2EE\PKD\Crypto\Merkle\InclusionProof` |  |
| `$created` | `string` | (readonly)  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

### Methods

#### `__construct(string $contents, string $contentHash, string $signature, string $publicKeyHash, ?FediE2EE\PKD\Crypto\Merkle\InclusionProof $inclusionProof = null, string $created = '', ?int $primaryKey = null): void`

**Parameters:**

- `$contents`: `string`
- `$contentHash`: `string`
- `$signature`: `string`
- `$publicKeyHash`: `string`
- `$inclusionProof`: `?FediE2EE\PKD\Crypto\Merkle\InclusionProof` (nullable)
- `$created`: `string`
- `$primaryKey`: `?int` (nullable)

#### `static from(string $contents, FediE2EE\PKD\Crypto\SecretKey $sk): self`

**Parameters:**

- `$contents`: `string`
- `$sk`: `FediE2EE\PKD\Crypto\SecretKey`

**Throws:**

- `NotImplementedException`
- `SodiumException`

#### `static fromPayload(FediE2EE\PKDServer\Protocol\Payload $payload, FediE2EE\PKD\Crypto\SecretKey $sk): self`

**API Method**

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$sk`: `FediE2EE\PKD\Crypto\SecretKey`

#### `setPrimaryKey(?int $primary): static`

**Parameters:**

- `$primary`: `?int` (nullable)

#### `getContents(): array`

#### `getInclusionProof(): ?FediE2EE\PKD\Crypto\Merkle\InclusionProof`

**API Method**

#### `getSignature(): string`

#### `serializeForMerkle(): string`

#### `hasPrimaryKey(): bool`

#### `getPrimaryKey(): int`

**Throws:**

- `TableException`

#### `attachSymmetricKey(string $property, FediE2EE\PKD\Crypto\SymmetricKey $key): self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:**

- `TableException`

#### `getSymmetricKeyForProperty(string $property): FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:**

- `TableException`

#### `getSymmetricKeys(): array`

#### `getRfc9421PublicKeys(string $actorId): FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

**Parameters:**

- `$actorId`: `string`

**Throws:**

- `CryptoException`
- `FetchException`

#### `constantTimeSelect(int $select, string $left, string $right): string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:**

- `CryptoException`

#### `static dos2unix(string $in): string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### `static preAuthEncode(array $pieces): string`

**Parameters:**

- `$pieces`: `array`

#### `static sortByKey(array $arr): void`

**Parameters:**

- `$arr`: `array`

#### `static LE64(int $n): string`

**Parameters:**

- `$n`: `int`

#### `stringToByteArray(string $str): array`

**Parameters:**

- `$str`: `string`

#### `static stripNewlines(string $input): string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## Peer

**class** `FediE2EE\PKDServer\Tables\Records\Peer`

**File:** [`src/Tables/Records/Peer.php`](../../src/Tables/Records/Peer.php)

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`, `FediE2EE\PKD\Crypto\UtilTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$hostname` | `string` |  |
| `$publicKey` | `FediE2EE\PKD\Crypto\PublicKey` |  |
| `$tree` | `FediE2EE\PKD\Crypto\Merkle\IncrementalTree` |  |
| `$latestRoot` | `string` |  |
| `$created` | `DateTimeImmutable` |  |
| `$modified` | `DateTimeImmutable` |  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

### Methods

#### `__construct(string $hostname, FediE2EE\PKD\Crypto\PublicKey $publicKey, FediE2EE\PKD\Crypto\Merkle\IncrementalTree $tree, string $latestRoot, DateTimeImmutable $created, DateTimeImmutable $modified, ?int $primaryKey = null): void`

**Parameters:**

- `$hostname`: `string`
- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`
- `$tree`: `FediE2EE\PKD\Crypto\Merkle\IncrementalTree`
- `$latestRoot`: `string`
- `$created`: `DateTimeImmutable`
- `$modified`: `DateTimeImmutable`
- `$primaryKey`: `?int` (nullable)

#### `toArray(): array`

#### `hasPrimaryKey(): bool`

#### `getPrimaryKey(): int`

**Throws:**

- `TableException`

#### `attachSymmetricKey(string $property, FediE2EE\PKD\Crypto\SymmetricKey $key): self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:**

- `TableException`

#### `getSymmetricKeyForProperty(string $property): FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:**

- `TableException`

#### `getSymmetricKeys(): array`

#### `getRfc9421PublicKeys(string $actorId): FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

**Parameters:**

- `$actorId`: `string`

**Throws:**

- `CryptoException`
- `FetchException`

#### `constantTimeSelect(int $select, string $left, string $right): string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:**

- `CryptoException`

#### `static dos2unix(string $in): string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### `static preAuthEncode(array $pieces): string`

**Parameters:**

- `$pieces`: `array`

#### `static sortByKey(array $arr): void`

**Parameters:**

- `$arr`: `array`

#### `static LE64(int $n): string`

**Parameters:**

- `$n`: `int`

#### `stringToByteArray(string $str): array`

**Parameters:**

- `$str`: `string`

#### `static stripNewlines(string $input): string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

