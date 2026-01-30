# Tables / Records

Namespace: `FediE2EE\PKDServer\Tables\Records`

## Classes

- [Actor](#actor) - class
- [ActorKey](#actorkey) - class
- [AuxDatum](#auxdatum) - class
- [MerkleLeaf](#merkleleaf) - class
- [Peer](#peer) - class
- [ReplicaActor](#replicaactor) - class
- [ReplicaAuxDatum](#replicaauxdatum) - class
- [ReplicaLeaf](#replicaleaf) - class
- [ReplicaPublicKey](#replicapublickey) - class

---

## Actor

**final class** `FediE2EE\PKDServer\Tables\Records\Actor`

**File:** [`src/Tables/Records/Actor.php`](../../../src/Tables/Records/Actor.php)

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

#### [`__construct`](../../../src/Tables/Records/Actor.php#L24-L31)

Returns `void`

**Parameters:**

- `$actorID`: `string`
- `$rfc9421pk`: `?FediE2EE\PKD\Crypto\PublicKey` = null
- `$fireProof`: `bool` = false
- `$primaryKey`: `?int` = null

#### [`create`](../../../src/Tables/Records/Actor.php#L36-L46)

static · Returns `self`

Instantiate a new object without a primary key

**Parameters:**

- `$actorID`: `string`
- `$rfc9421pk`: `string` = ''
- `$fireProof`: `bool` = false

#### [`toArray`](../../../src/Tables/Records/Actor.php#L48-L55)

Returns `array`

#### [`hasPrimaryKey`](../../../src/Tables/Records/Actor.php#L36-L39)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/Actor.php#L45-L51)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/Actor.php#L57-L67)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/Actor.php#L73-L82)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/Actor.php#L84-L87)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/Actor.php#L103-L106)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/Actor.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/Actor.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/Actor.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## ActorKey

**class** `FediE2EE\PKDServer\Tables\Records\ActorKey`

**File:** [`src/Tables/Records/ActorKey.php`](../../../src/Tables/Records/ActorKey.php)

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

#### [`__construct`](../../../src/Tables/Records/ActorKey.php#L18-L28)

Returns `void`

**Parameters:**

- `$actor`: `FediE2EE\PKDServer\Tables\Records\Actor`
- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`
- `$trusted`: `bool`
- `$insertLeaf`: `FediE2EE\PKDServer\Tables\Records\MerkleLeaf`
- `$revokeLeaf`: `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf` = null
- `$keyID`: `?string` = null
- `$primaryKey`: `?int` = null

#### [`hasPrimaryKey`](../../../src/Tables/Records/ActorKey.php#L36-L39)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/ActorKey.php#L45-L51)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/ActorKey.php#L57-L67)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/ActorKey.php#L73-L82)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/ActorKey.php#L84-L87)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/ActorKey.php#L103-L106)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/ActorKey.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/ActorKey.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/ActorKey.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## AuxDatum

**class** `FediE2EE\PKDServer\Tables\Records\AuxDatum`

**File:** [`src/Tables/Records/AuxDatum.php`](../../../src/Tables/Records/AuxDatum.php)

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

#### [`__construct`](../../../src/Tables/Records/AuxDatum.php#L17-L27)

Returns `void`

**Parameters:**

- `$actor`: `FediE2EE\PKDServer\Tables\Records\Actor`
- `$auxDataType`: `string`
- `$auxData`: `string`
- `$trusted`: `bool`
- `$insertLeaf`: `FediE2EE\PKDServer\Tables\Records\MerkleLeaf`
- `$revokeLeaf`: `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf` = null
- `$primaryKey`: `?int` = null

#### [`getActor`](../../../src/Tables/Records/AuxDatum.php#L32-L35)

**API** · Returns `FediE2EE\PKDServer\Tables\Records\Actor`

#### [`hasPrimaryKey`](../../../src/Tables/Records/AuxDatum.php#L36-L39)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/AuxDatum.php#L45-L51)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/AuxDatum.php#L57-L67)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/AuxDatum.php#L73-L82)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/AuxDatum.php#L84-L87)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/AuxDatum.php#L103-L106)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/AuxDatum.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/AuxDatum.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/AuxDatum.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

---

## MerkleLeaf

**class** `FediE2EE\PKDServer\Tables\Records\MerkleLeaf`

**File:** [`src/Tables/Records/MerkleLeaf.php`](../../../src/Tables/Records/MerkleLeaf.php)

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
| `$wrappedKeys` | `?string` |  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

### Methods

#### [`__construct`](../../../src/Tables/Records/MerkleLeaf.php#L28-L39)

Returns `void`

**Parameters:**

- `$contents`: `string`
- `$contentHash`: `string`
- `$signature`: `string`
- `$publicKeyHash`: `string`
- `$inclusionProof`: `?FediE2EE\PKD\Crypto\Merkle\InclusionProof` = null
- `$created`: `string` = ''
- `$wrappedKeys`: `?string` = null
- `$primaryKey`: `?int` = null

#### [`from`](../../../src/Tables/Records/MerkleLeaf.php#L45-L62)

static · Returns `self`

**Parameters:**

- `$contents`: `string`
- `$sk`: `FediE2EE\PKD\Crypto\SecretKey`
- `$rewrappedKeys`: `?string` = null

**Throws:** `NotImplementedException`, `SodiumException`

#### [`fromPayload`](../../../src/Tables/Records/MerkleLeaf.php#L70-L80)

static **API** · Returns `self`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$sk`: `FediE2EE\PKD\Crypto\SecretKey`
- `$rewrappedKeys`: `?string` = null

**Throws:** `NotImplementedException`, `SodiumException`

#### [`setPrimaryKey`](../../../src/Tables/Records/MerkleLeaf.php#L82-L86)

Returns `static`

**Parameters:**

- `$primary`: `?int`

#### [`getContents`](../../../src/Tables/Records/MerkleLeaf.php#L88-L91)

Returns `array`

#### [`getInclusionProof`](../../../src/Tables/Records/MerkleLeaf.php#L96-L99)

**API** · Returns `?FediE2EE\PKD\Crypto\Merkle\InclusionProof`

#### [`getSignature`](../../../src/Tables/Records/MerkleLeaf.php#L101-L104)

Returns `string`

#### [`serializeForMerkle`](../../../src/Tables/Records/MerkleLeaf.php#L109-L116)

Returns `string`

**Throws:** `SodiumException`

#### [`hasPrimaryKey`](../../../src/Tables/Records/MerkleLeaf.php#L36-L39)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/MerkleLeaf.php#L45-L51)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/MerkleLeaf.php#L57-L67)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/MerkleLeaf.php#L73-L82)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/MerkleLeaf.php#L84-L87)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/MerkleLeaf.php#L103-L106)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/MerkleLeaf.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/MerkleLeaf.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/MerkleLeaf.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Tables/Records/MerkleLeaf.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/Records/MerkleLeaf.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/Records/MerkleLeaf.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/Records/MerkleLeaf.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/Records/MerkleLeaf.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/Records/MerkleLeaf.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/Records/MerkleLeaf.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/Records/MerkleLeaf.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/Records/MerkleLeaf.php#L107-L141)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## Peer

**class** `FediE2EE\PKDServer\Tables\Records\Peer`

**File:** [`src/Tables/Records/Peer.php`](../../../src/Tables/Records/Peer.php)

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`, `FediE2EE\PKD\Crypto\UtilTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$hostname` | `string` |  |
| `$uniqueId` | `string` |  |
| `$publicKey` | `FediE2EE\PKD\Crypto\PublicKey` |  |
| `$tree` | `FediE2EE\PKD\Crypto\Merkle\IncrementalTree` |  |
| `$latestRoot` | `string` |  |
| `$cosign` | `bool` |  |
| `$replicate` | `bool` |  |
| `$created` | `DateTimeImmutable` |  |
| `$modified` | `DateTimeImmutable` |  |
| `$wrapConfig` | `?FediE2EE\PKDServer\Protocol\RewrapConfig` |  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

### Methods

#### [`__construct`](../../../src/Tables/Records/Peer.php#L25-L39)

Returns `void`

**Parameters:**

- `$hostname`: `string`
- `$uniqueId`: `string`
- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`
- `$tree`: `FediE2EE\PKD\Crypto\Merkle\IncrementalTree`
- `$latestRoot`: `string`
- `$cosign`: `bool`
- `$replicate`: `bool`
- `$created`: `DateTimeImmutable`
- `$modified`: `DateTimeImmutable`
- `$wrapConfig`: `?FediE2EE\PKDServer\Protocol\RewrapConfig` = null
- `$primaryKey`: `?int` = null

#### [`toArray`](../../../src/Tables/Records/Peer.php#L44-L70)

Returns `array`

**Throws:** `JsonException`

#### [`hasPrimaryKey`](../../../src/Tables/Records/Peer.php#L36-L39)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/Peer.php#L45-L51)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/Peer.php#L57-L67)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/Peer.php#L73-L82)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/Peer.php#L84-L87)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/Peer.php#L103-L106)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/Peer.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/Peer.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/Peer.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Tables/Records/Peer.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/Records/Peer.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/Records/Peer.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/Records/Peer.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/Records/Peer.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/Records/Peer.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/Records/Peer.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/Records/Peer.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/Records/Peer.php#L107-L141)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## ReplicaActor

**final class** `FediE2EE\PKDServer\Tables\Records\ReplicaActor`

**File:** [`src/Tables/Records/ReplicaActor.php`](../../../src/Tables/Records/ReplicaActor.php)

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`, `FediE2EE\PKD\Crypto\UtilTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$actorID` | `string` |  |
| `$rfc9421pk` | `?FediE2EE\PKD\Crypto\PublicKey` |  |
| `$fireProof` | `bool` |  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

### Methods

#### [`__construct`](../../../src/Tables/Records/ReplicaActor.php#L18-L25)

Returns `void`

**Parameters:**

- `$actorID`: `string`
- `$rfc9421pk`: `?FediE2EE\PKD\Crypto\PublicKey` = null
- `$fireProof`: `bool` = false
- `$primaryKey`: `?int` = null

#### [`toArray`](../../../src/Tables/Records/ReplicaActor.php#L27-L34)

Returns `array`

#### [`hasPrimaryKey`](../../../src/Tables/Records/ReplicaActor.php#L36-L39)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/ReplicaActor.php#L45-L51)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/ReplicaActor.php#L57-L67)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/ReplicaActor.php#L73-L82)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/ReplicaActor.php#L84-L87)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/ReplicaActor.php#L103-L106)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/ReplicaActor.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/ReplicaActor.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/ReplicaActor.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Tables/Records/ReplicaActor.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/Records/ReplicaActor.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/Records/ReplicaActor.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/Records/ReplicaActor.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/Records/ReplicaActor.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/Records/ReplicaActor.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/Records/ReplicaActor.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/Records/ReplicaActor.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/Records/ReplicaActor.php#L107-L141)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## ReplicaAuxDatum

**final class** `FediE2EE\PKDServer\Tables\Records\ReplicaAuxDatum`

**File:** [`src/Tables/Records/ReplicaAuxDatum.php`](../../../src/Tables/Records/ReplicaAuxDatum.php)

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`, `FediE2EE\PKD\Crypto\UtilTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$peer` | `FediE2EE\PKDServer\Tables\Records\Peer` |  |
| `$actor` | `FediE2EE\PKDServer\Tables\Records\ReplicaActor` |  |
| `$auxDataType` | `string` |  |
| `$auxData` | `string` |  |
| `$trusted` | `bool` |  |
| `$insertLeaf` | `FediE2EE\PKDServer\Tables\Records\ReplicaLeaf` |  |
| `$revokeLeaf` | `?FediE2EE\PKDServer\Tables\Records\ReplicaLeaf` |  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

### Methods

#### [`__construct`](../../../src/Tables/Records/ReplicaAuxDatum.php#L16-L27)

Returns `void`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`
- `$actor`: `FediE2EE\PKDServer\Tables\Records\ReplicaActor`
- `$auxDataType`: `string`
- `$auxData`: `string`
- `$trusted`: `bool`
- `$insertLeaf`: `FediE2EE\PKDServer\Tables\Records\ReplicaLeaf`
- `$revokeLeaf`: `?FediE2EE\PKDServer\Tables\Records\ReplicaLeaf` = null
- `$primaryKey`: `?int` = null

#### [`hasPrimaryKey`](../../../src/Tables/Records/ReplicaAuxDatum.php#L36-L39)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/ReplicaAuxDatum.php#L45-L51)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/ReplicaAuxDatum.php#L57-L67)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/ReplicaAuxDatum.php#L73-L82)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/ReplicaAuxDatum.php#L84-L87)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/ReplicaAuxDatum.php#L103-L106)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/ReplicaAuxDatum.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/ReplicaAuxDatum.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/ReplicaAuxDatum.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Tables/Records/ReplicaAuxDatum.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/Records/ReplicaAuxDatum.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/Records/ReplicaAuxDatum.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/Records/ReplicaAuxDatum.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/Records/ReplicaAuxDatum.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/Records/ReplicaAuxDatum.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/Records/ReplicaAuxDatum.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/Records/ReplicaAuxDatum.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/Records/ReplicaAuxDatum.php#L107-L141)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## ReplicaLeaf

**final class** `FediE2EE\PKDServer\Tables\Records\ReplicaLeaf`

**File:** [`src/Tables/Records/ReplicaLeaf.php`](../../../src/Tables/Records/ReplicaLeaf.php)

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`, `FediE2EE\PKD\Crypto\UtilTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$root` | `string` |  |
| `$publicKeyHash` | `string` |  |
| `$contentHash` | `string` |  |
| `$signature` | `string` |  |
| `$contents` | `string` |  |
| `$cosignature` | `string` |  |
| `$inclusionProof` | `?FediE2EE\PKD\Crypto\Merkle\InclusionProof` |  |
| `$created` | `string` | (readonly)  |
| `$replicated` | `string` | (readonly)  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

### Methods

#### [`__construct`](../../../src/Tables/Records/ReplicaLeaf.php#L20-L33)

Returns `void`

**Parameters:**

- `$root`: `string`
- `$publicKeyHash`: `string`
- `$contentHash`: `string`
- `$signature`: `string`
- `$contents`: `string`
- `$cosignature`: `string`
- `$inclusionProof`: `?FediE2EE\PKD\Crypto\Merkle\InclusionProof` = null
- `$created`: `string` = ''
- `$replicated`: `string` = ''
- `$primaryKey`: `?int` = null

#### [`toArray`](../../../src/Tables/Records/ReplicaLeaf.php#L38-L62)

Returns `array`

**Throws:** `JsonException`

#### [`serializeForMerkle`](../../../src/Tables/Records/ReplicaLeaf.php#L68-L75)

**API** · Returns `string`

**Throws:** `SodiumException`

#### [`hasPrimaryKey`](../../../src/Tables/Records/ReplicaLeaf.php#L36-L39)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/ReplicaLeaf.php#L45-L51)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/ReplicaLeaf.php#L57-L67)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/ReplicaLeaf.php#L73-L82)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/ReplicaLeaf.php#L84-L87)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/ReplicaLeaf.php#L103-L106)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/ReplicaLeaf.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/ReplicaLeaf.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/ReplicaLeaf.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Tables/Records/ReplicaLeaf.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/Records/ReplicaLeaf.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/Records/ReplicaLeaf.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/Records/ReplicaLeaf.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/Records/ReplicaLeaf.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/Records/ReplicaLeaf.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/Records/ReplicaLeaf.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/Records/ReplicaLeaf.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/Records/ReplicaLeaf.php#L107-L141)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## ReplicaPublicKey

**final class** `FediE2EE\PKDServer\Tables\Records\ReplicaPublicKey`

**File:** [`src/Tables/Records/ReplicaPublicKey.php`](../../../src/Tables/Records/ReplicaPublicKey.php)

**Uses:** `FediE2EE\PKDServer\Traits\TableRecordTrait`, `FediE2EE\PKD\Crypto\UtilTrait`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$peer` | `FediE2EE\PKDServer\Tables\Records\Peer` |  |
| `$actor` | `FediE2EE\PKDServer\Tables\Records\ReplicaActor` |  |
| `$publicKey` | `FediE2EE\PKD\Crypto\PublicKey` |  |
| `$trusted` | `bool` |  |
| `$insertLeaf` | `FediE2EE\PKDServer\Tables\Records\ReplicaLeaf` |  |
| `$revokeLeaf` | `?FediE2EE\PKDServer\Tables\Records\ReplicaLeaf` |  |
| `$keyID` | `?string` |  |
| `$primaryKey` | `?int` |  |
| `$symmetricKeys` | `array` |  |

### Methods

#### [`__construct`](../../../src/Tables/Records/ReplicaPublicKey.php#L19-L30)

Returns `void`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`
- `$actor`: `FediE2EE\PKDServer\Tables\Records\ReplicaActor`
- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`
- `$trusted`: `bool`
- `$insertLeaf`: `FediE2EE\PKDServer\Tables\Records\ReplicaLeaf`
- `$revokeLeaf`: `?FediE2EE\PKDServer\Tables\Records\ReplicaLeaf` = null
- `$keyID`: `?string` = null
- `$primaryKey`: `?int` = null

#### [`hasPrimaryKey`](../../../src/Tables/Records/ReplicaPublicKey.php#L36-L39)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/ReplicaPublicKey.php#L45-L51)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/ReplicaPublicKey.php#L57-L67)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/ReplicaPublicKey.php#L73-L82)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/ReplicaPublicKey.php#L84-L87)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/ReplicaPublicKey.php#L103-L106)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/ReplicaPublicKey.php#L13-L16)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/ReplicaPublicKey.php#L21-L24)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/ReplicaPublicKey.php#L29-L35)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Tables/Records/ReplicaPublicKey.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/Records/ReplicaPublicKey.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/Records/ReplicaPublicKey.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/Records/ReplicaPublicKey.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/Records/ReplicaPublicKey.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/Records/ReplicaPublicKey.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/Records/ReplicaPublicKey.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/Records/ReplicaPublicKey.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/Records/ReplicaPublicKey.php#L107-L141)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

