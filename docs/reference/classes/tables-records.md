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

#### [`toArray`](../../../src/Tables/Records/Actor.php#L51-L58)

Returns `array`

#### [`hasPrimaryKey`](../../../src/Tables/Records/Actor.php#L39-L42)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/Actor.php#L48-L54)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/Actor.php#L60-L70)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/Actor.php#L76-L85)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/Actor.php#L90-L93)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/Actor.php#L109-L112)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/Actor.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/Actor.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/Actor.php#L34-L40)

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

#### [`hasPrimaryKey`](../../../src/Tables/Records/ActorKey.php#L39-L42)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/ActorKey.php#L48-L54)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/ActorKey.php#L60-L70)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/ActorKey.php#L76-L85)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/ActorKey.php#L90-L93)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/ActorKey.php#L109-L112)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/ActorKey.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/ActorKey.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/ActorKey.php#L34-L40)

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

#### [`hasPrimaryKey`](../../../src/Tables/Records/AuxDatum.php#L39-L42)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/AuxDatum.php#L48-L54)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/AuxDatum.php#L60-L70)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/AuxDatum.php#L76-L85)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/AuxDatum.php#L90-L93)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/AuxDatum.php#L109-L112)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/AuxDatum.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/AuxDatum.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/AuxDatum.php#L34-L40)

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

#### [`__construct`](../../../src/Tables/Records/MerkleLeaf.php#L33-L44)

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

#### [`from`](../../../src/Tables/Records/MerkleLeaf.php#L50-L67)

static · Returns `self`

**Parameters:**

- `$contents`: `string`
- `$sk`: `FediE2EE\PKD\Crypto\SecretKey`
- `$rewrappedKeys`: `?string` = null

**Throws:** `NotImplementedException`, `SodiumException`

#### [`fromPayload`](../../../src/Tables/Records/MerkleLeaf.php#L75-L85)

static **API** · Returns `self`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$sk`: `FediE2EE\PKD\Crypto\SecretKey`
- `$rewrappedKeys`: `?string` = null

**Throws:** `NotImplementedException`, `SodiumException`

#### [`setPrimaryKey`](../../../src/Tables/Records/MerkleLeaf.php#L87-L91)

Returns `static`

**Parameters:**

- `$primary`: `?int`

#### [`getContents`](../../../src/Tables/Records/MerkleLeaf.php#L96-L99)

Returns `array`

#### [`getInclusionProof`](../../../src/Tables/Records/MerkleLeaf.php#L104-L107)

**API** · Returns `?FediE2EE\PKD\Crypto\Merkle\InclusionProof`

#### [`getSignature`](../../../src/Tables/Records/MerkleLeaf.php#L109-L112)

Returns `string`

#### [`serializeForMerkle`](../../../src/Tables/Records/MerkleLeaf.php#L117-L124)

Returns `string`

**Throws:** `SodiumException`

#### [`hasPrimaryKey`](../../../src/Tables/Records/MerkleLeaf.php#L39-L42)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/MerkleLeaf.php#L48-L54)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/MerkleLeaf.php#L60-L70)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/MerkleLeaf.php#L76-L85)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/MerkleLeaf.php#L90-L93)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/MerkleLeaf.php#L109-L112)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/MerkleLeaf.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/MerkleLeaf.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/MerkleLeaf.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Tables/Records/MerkleLeaf.php#L27-L32)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/Records/MerkleLeaf.php#L34-L41)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/Records/MerkleLeaf.php#L48-L65)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/Records/MerkleLeaf.php#L73-L76)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/Records/MerkleLeaf.php#L84-L97)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/Records/MerkleLeaf.php#L99-L107)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/Records/MerkleLeaf.php#L111-L114)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/Records/MerkleLeaf.php#L116-L123)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/Records/MerkleLeaf.php#L131-L165)

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

#### [`__construct`](../../../src/Tables/Records/Peer.php#L27-L41)

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

#### [`toArray`](../../../src/Tables/Records/Peer.php#L49-L75)

Returns `array`

**Throws:** `BaseJsonException`, `JsonException`

#### [`hasPrimaryKey`](../../../src/Tables/Records/Peer.php#L39-L42)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/Peer.php#L48-L54)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/Peer.php#L60-L70)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/Peer.php#L76-L85)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/Peer.php#L90-L93)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/Peer.php#L109-L112)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/Peer.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/Peer.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/Peer.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Tables/Records/Peer.php#L27-L32)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/Records/Peer.php#L34-L41)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/Records/Peer.php#L48-L65)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/Records/Peer.php#L73-L76)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/Records/Peer.php#L84-L97)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/Records/Peer.php#L99-L107)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/Records/Peer.php#L111-L114)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/Records/Peer.php#L116-L123)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/Records/Peer.php#L131-L165)

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

#### [`toArray`](../../../src/Tables/Records/ReplicaActor.php#L30-L37)

Returns `array`

#### [`hasPrimaryKey`](../../../src/Tables/Records/ReplicaActor.php#L39-L42)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/ReplicaActor.php#L48-L54)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/ReplicaActor.php#L60-L70)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/ReplicaActor.php#L76-L85)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/ReplicaActor.php#L90-L93)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/ReplicaActor.php#L109-L112)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/ReplicaActor.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/ReplicaActor.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/ReplicaActor.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Tables/Records/ReplicaActor.php#L27-L32)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/Records/ReplicaActor.php#L34-L41)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/Records/ReplicaActor.php#L48-L65)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/Records/ReplicaActor.php#L73-L76)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/Records/ReplicaActor.php#L84-L97)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/Records/ReplicaActor.php#L99-L107)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/Records/ReplicaActor.php#L111-L114)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/Records/ReplicaActor.php#L116-L123)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/Records/ReplicaActor.php#L131-L165)

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

#### [`hasPrimaryKey`](../../../src/Tables/Records/ReplicaAuxDatum.php#L39-L42)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/ReplicaAuxDatum.php#L48-L54)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/ReplicaAuxDatum.php#L60-L70)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/ReplicaAuxDatum.php#L76-L85)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/ReplicaAuxDatum.php#L90-L93)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/ReplicaAuxDatum.php#L109-L112)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/ReplicaAuxDatum.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/ReplicaAuxDatum.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/ReplicaAuxDatum.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Tables/Records/ReplicaAuxDatum.php#L27-L32)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/Records/ReplicaAuxDatum.php#L34-L41)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/Records/ReplicaAuxDatum.php#L48-L65)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/Records/ReplicaAuxDatum.php#L73-L76)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/Records/ReplicaAuxDatum.php#L84-L97)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/Records/ReplicaAuxDatum.php#L99-L107)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/Records/ReplicaAuxDatum.php#L111-L114)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/Records/ReplicaAuxDatum.php#L116-L123)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/Records/ReplicaAuxDatum.php#L131-L165)

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

#### [`__construct`](../../../src/Tables/Records/ReplicaLeaf.php#L22-L35)

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

#### [`toArray`](../../../src/Tables/Records/ReplicaLeaf.php#L41-L65)

Returns `array`

**Throws:** `JsonException`

#### [`serializeForMerkle`](../../../src/Tables/Records/ReplicaLeaf.php#L71-L78)

**API** · Returns `string`

**Throws:** `SodiumException`

#### [`hasPrimaryKey`](../../../src/Tables/Records/ReplicaLeaf.php#L39-L42)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/ReplicaLeaf.php#L48-L54)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/ReplicaLeaf.php#L60-L70)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/ReplicaLeaf.php#L76-L85)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/ReplicaLeaf.php#L90-L93)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/ReplicaLeaf.php#L109-L112)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/ReplicaLeaf.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/ReplicaLeaf.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/ReplicaLeaf.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Tables/Records/ReplicaLeaf.php#L27-L32)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/Records/ReplicaLeaf.php#L34-L41)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/Records/ReplicaLeaf.php#L48-L65)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/Records/ReplicaLeaf.php#L73-L76)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/Records/ReplicaLeaf.php#L84-L97)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/Records/ReplicaLeaf.php#L99-L107)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/Records/ReplicaLeaf.php#L111-L114)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/Records/ReplicaLeaf.php#L116-L123)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/Records/ReplicaLeaf.php#L131-L165)

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

#### [`hasPrimaryKey`](../../../src/Tables/Records/ReplicaPublicKey.php#L39-L42)

Returns `bool`

#### [`getPrimaryKey`](../../../src/Tables/Records/ReplicaPublicKey.php#L48-L54)

Returns `int`

**Throws:** `TableException`

#### [`attachSymmetricKey`](../../../src/Tables/Records/ReplicaPublicKey.php#L60-L70)

Returns `self`

**Parameters:**

- `$property`: `string`
- `$key`: `FediE2EE\PKD\Crypto\SymmetricKey`

**Throws:** `TableException`

#### [`getSymmetricKeyForProperty`](../../../src/Tables/Records/ReplicaPublicKey.php#L76-L85)

Returns `FediE2EE\PKD\Crypto\SymmetricKey`

**Parameters:**

- `$property`: `string`

**Throws:** `TableException`

#### [`getSymmetricKeys`](../../../src/Tables/Records/ReplicaPublicKey.php#L90-L93)

Returns `array`

#### [`getRfc9421PublicKeys`](../../../src/Tables/Records/ReplicaPublicKey.php#L109-L112)

Returns `FediE2EE\PKD\Crypto\PublicKey`

Fetch the RFC 9421 public keys for an actor.

If multiple are returned (e.g., via FEP-521a), this will cycle through them until the first Ed25519 public key is found. We do not support JWS, RSA, or ECDSA keys.

**Parameters:**

- `$actorId`: `string`

**Throws:** `CryptoException`, `FetchException`, `InvalidArgumentException`, `SodiumException`

#### [`jsonDecode`](../../../src/Tables/Records/ReplicaPublicKey.php#L17-L20)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/Records/ReplicaPublicKey.php#L25-L28)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/Records/ReplicaPublicKey.php#L34-L40)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`assertAllArrayKeysExist`](../../../src/Tables/Records/ReplicaPublicKey.php#L27-L32)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/Records/ReplicaPublicKey.php#L34-L41)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/Records/ReplicaPublicKey.php#L48-L65)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/Records/ReplicaPublicKey.php#L73-L76)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/Records/ReplicaPublicKey.php#L84-L97)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/Records/ReplicaPublicKey.php#L99-L107)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/Records/ReplicaPublicKey.php#L111-L114)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/Records/ReplicaPublicKey.php#L116-L123)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/Records/ReplicaPublicKey.php#L131-L165)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

