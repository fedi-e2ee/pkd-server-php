# Tables

Namespace: `FediE2EE\PKDServer\Tables`

## Classes

- [ActivityStreamQueue](#activitystreamqueue) - class
- [Actors](#actors) - class
- [AuxData](#auxdata) - class
- [MerkleState](#merklestate) - class
- [Peers](#peers) - class
- [PublicKeys](#publickeys) - class
- [ReplicaActors](#replicaactors) - class
- [ReplicaAuxData](#replicaauxdata) - class
- [ReplicaHistory](#replicahistory) - class
- [ReplicaPublicKeys](#replicapublickeys) - class
- [TOTP](#totp) - class

---

## ActivityStreamQueue

**class** `FediE2EE\PKDServer\Tables\ActivityStreamQueue`

**File:** [`src/Tables/ActivityStreamQueue.php`](../../../src/Tables/ActivityStreamQueue.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### [`getCipher`](../../../src/Tables/ActivityStreamQueue.php#L15-L23)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getNextPrimaryKey`](../../../src/Tables/ActivityStreamQueue.php#L31-L38)

Returns `int`

#### [`insert`](../../../src/Tables/ActivityStreamQueue.php#L43-L61)

Returns `int`

**Parameters:**

- `$as`: `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Throws:** `ActivityPubException`

---

## Actors

**class** `FediE2EE\PKDServer\Tables\Actors`

**File:** [`src/Tables/Actors.php`](../../../src/Tables/Actors.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### [`getCipher`](../../../src/Tables/Actors.php#L34-L47)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getNextPrimaryKey`](../../../src/Tables/Actors.php#L72-L79)

Returns `int`

#### [`getActorByID`](../../../src/Tables/Actors.php#L93-L127)

**API** · Returns `FediE2EE\PKDServer\Tables\Records\Actor`

When you already have a database ID, just fetch the object.

**Parameters:**

- `$actorID`: `int`

**Throws:** `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `InvalidCiphertextException`, `SodiumException`, `TableException`

#### [`getCounts`](../../../src/Tables/Actors.php#L133-L147)

Returns `array`

**Parameters:**

- `$actorID`: `int`

#### [`searchForActor`](../../../src/Tables/Actors.php#L163-L199)

**API** · Returns `?FediE2EE\PKDServer\Tables\Records\Actor`

When you only have an ActivityPub Actor ID, first canonicalize it, then fetch the Actor object

from the database based on that value. May return NULL, which indicates no records found.

**Parameters:**

- `$canonicalActorID`: `string`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `InvalidCiphertextException`, `SodiumException`

#### [`createActor`](../../../src/Tables/Actors.php#L209-L229)

Returns `int`

**Parameters:**

- `$activityPubID`: `string`
- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$key`: `?FediE2EE\PKD\Crypto\PublicKey` = null

**Throws:** `ArrayKeyException`, `CipherSweetException`, `CryptoOperationException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`clearCacheForActor`](../../../src/Tables/Actors.php#L234-L240)

Returns `void`

**Parameters:**

- `$actor`: `FediE2EE\PKDServer\Tables\Records\Actor`

**Throws:** `TableException`

---

## AuxData

**class** `FediE2EE\PKDServer\Tables\AuxData`

**File:** [`src/Tables/AuxData.php`](../../../src/Tables/AuxData.php)

**Extends:** `FediE2EE\PKDServer\Table`

**Uses:** `FediE2EE\PKDServer\Traits\AuxDataIdTrait`, `FediE2EE\PKDServer\Traits\ProtocolMethodTrait`

### Methods

#### [`getCipher`](../../../src/Tables/AuxData.php#L59-L70)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getAuxDataForActor`](../../../src/Tables/AuxData.php#L91-L117)

Returns `array`

**Parameters:**

- `$actorId`: `int`

**Throws:** `DateMalformedStringException`

#### [`getAuxDataById`](../../../src/Tables/AuxData.php#L130-L177)

**API** · Returns `array`

**Parameters:**

- `$actorId`: `int`
- `$auxId`: `string`

**Throws:** `CipherSweetException`, `CryptoOperationException`, `DateMalformedStringException`, `InvalidCiphertextException`, `JsonException`, `SodiumException`

#### [`addAuxData`](../../../src/Tables/AuxData.php#L189-L196)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`revokeAuxData`](../../../src/Tables/AuxData.php#L315-L323)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`getAuxDataId`](../../../src/Tables/AuxData.php#L13-L23)

static · Returns `string`

**Parameters:**

- `$auxDataType`: `string`
- `$data`: `string`

#### [`assertAllArrayKeysExist`](../../../src/Tables/AuxData.php#L29-L34)

static · Returns `void`

This method throws an InputException if any of the expected keys are absent.

It does not return anything.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/AuxData.php#L42-L49)

static · Returns `bool`

This method returns true if every expected array key is found in the target array.

Otherwise, it returns false. This is useful for input validation.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/AuxData.php#L64-L81)

Returns `string`

This is a constant-time conditional select. It should be read like a ternary operation.

$result = ClassWithTrait::constantTimeSelect(1, $left, $right); -> $result === $left. $result = ClassWithTrait::constantTimeSelect(0, $left, $right); -> $result === $right.

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/AuxData.php#L91-L94)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

This is mostly used for PEM-encoded strings.

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/AuxData.php#L112-L125)

static · Returns `string`

This is an implementation of PAE() from PASETO. It encodes an array of strings into a flat string consisting of:

1. The number of pieces. 2. For each piece: 1. The length of the piece (in bytes). 2. The contents of the piece. This allows multipart messages to have an injective canonical representation before passing ot a hash function (or other cryptographic function).

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/AuxData.php#L132-L140)

static · Returns `void`

This sorts the target array in-place, by its keys, including child arrays.

Used for ensuring arrays are sorted before JSON encoding.

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/AuxData.php#L147-L150)

static · Returns `string`

Mostly used by preAuthEncode() above. This packs an integer as 8 bytes.

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/AuxData.php#L155-L162)

Returns `array`

Get an array of bytes representing the input string.

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/AuxData.php#L170-L204)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## MerkleState

**class** `FediE2EE\PKDServer\Tables\MerkleState`

**File:** [`src/Tables/MerkleState.php`](../../../src/Tables/MerkleState.php)

Merkle State management

Insert new leaves

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### [`getCipher`](../../../src/Tables/MerkleState.php#L57-L65)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getWitnessByOrigin`](../../../src/Tables/MerkleState.php#L83-L94)

Returns `array`

Return the witness data (including public key) for a given origin

**Parameters:**

- `$origin`: `string`

**Throws:** `TableException`

#### [`addWitnessCosignature`](../../../src/Tables/MerkleState.php#L112-L157)

**API** · Returns `bool`

**Parameters:**

- `$origin`: `string`
- `$merkleRoot`: `string`
- `$cosignature`: `string`

**Throws:** `CryptoException`, `DependencyException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`getCosignatures`](../../../src/Tables/MerkleState.php#L162-L180)

Returns `array`

**Parameters:**

- `$leafId`: `int`

#### [`countCosignatures`](../../../src/Tables/MerkleState.php#L182-L192)

Returns `int`

**Parameters:**

- `$leafId`: `int`

#### [`getLatestRoot`](../../../src/Tables/MerkleState.php#L200-L209)

**API** · Returns `string`

**Throws:** `DependencyException`, `SodiumException`

#### [`insertLeaf`](../../../src/Tables/MerkleState.php#L228-L292)

**API** · Returns `bool`

Insert leaf with retry logic for deadlocks

**Parameters:**

- `$leaf`: `FediE2EE\PKDServer\Tables\Records\MerkleLeaf`
- `$inTransaction`: `callable`
- `$maxRetries`: `int` = 5

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `RandomException`, `SodiumException`

#### [`getLeafByRoot`](../../../src/Tables/MerkleState.php#L311-L327)

**API** · Returns `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf`

**Parameters:**

- `$root`: `string`

#### [`getLeafByID`](../../../src/Tables/MerkleState.php#L332-L348)

**API** · Returns `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf`

**Parameters:**

- `$primaryKey`: `int`

#### [`getHashesSince`](../../../src/Tables/MerkleState.php#L388-L430)

**API** · Returns `array`

**Parameters:**

- `$oldRoot`: `string`
- `$limit`: `int`
- `$offset`: `int` = 0

**Throws:** `BundleException`, `CryptoException`, `DependencyException`, `HPKEException`, `InputException`, `InvalidArgumentException`, `JsonException`, `SodiumException`

---

## Peers

**class** `FediE2EE\PKDServer\Tables\Peers`

**File:** [`src/Tables/Peers.php`](../../../src/Tables/Peers.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### [`getCipher`](../../../src/Tables/Peers.php#L37-L40)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getNextPeerId`](../../../src/Tables/Peers.php#L48-L55)

Returns `int`

#### [`create`](../../../src/Tables/Peers.php#L63-L96)

**API** · Returns `FediE2EE\PKDServer\Tables\Records\Peer`

**Parameters:**

- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`
- `$hostname`: `string`
- `$cosign`: `bool` = false
- `$replicate`: `bool` = false
- `$rewrapConfig`: `?FediE2EE\PKDServer\Protocol\RewrapConfig` = null

**Throws:** `TableException`, `RandomException`

#### [`getPeerByUniqueId`](../../../src/Tables/Peers.php#L106-L117)

**API** · Returns `FediE2EE\PKDServer\Tables\Records\Peer`

**Parameters:**

- `$uniqueId`: `string`

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`, `TableException`

#### [`getPeer`](../../../src/Tables/Peers.php#L125-L137)

Returns `FediE2EE\PKDServer\Tables\Records\Peer`

**Parameters:**

- `$hostname`: `string`

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`, `TableException`

#### [`listAll`](../../../src/Tables/Peers.php#L179-L188)

**API** · Returns `array`

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`

#### [`listReplicatingPeers`](../../../src/Tables/Peers.php#L199-L208)

Returns `array`

Lists which peers we replicate.

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`

#### [`save`](../../../src/Tables/Peers.php#L214-L223)

Returns `bool`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`

**Throws:** `JsonException`, `TableException`

#### [`getRewrapCandidates`](../../../src/Tables/Peers.php#L232-L250)

Returns `array`

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`

#### [`rewrapKeyMap`](../../../src/Tables/Peers.php#L257-L304)

Returns `void`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`
- `$keyMap`: `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`
- `$leafId`: `int`

**Throws:** `DependencyException`, `HPKEException`, `TableException`

---

## PublicKeys

**class** `FediE2EE\PKDServer\Tables\PublicKeys`

**File:** [`src/Tables/PublicKeys.php`](../../../src/Tables/PublicKeys.php)

**Extends:** `FediE2EE\PKDServer\Table`

**Uses:** `FediE2EE\PKDServer\Traits\ProtocolMethodTrait`, `FediE2EE\PKDServer\Traits\TOTPTrait`

### Methods

#### [`getCipher`](../../../src/Tables/PublicKeys.php#L74-L88)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`generateKeyID`](../../../src/Tables/PublicKeys.php#L93-L96)

Returns `string`

**Throws:** `RandomException`

#### [`lookup`](../../../src/Tables/PublicKeys.php#L125-L173)

Returns `array`

**Parameters:**

- `$actorPrimaryKey`: `int`
- `$keyID`: `string`

**Throws:** `BaseJsonException`, `CipherSweetException`, `CryptoOperationException`, `DateMalformedStringException`, `InvalidCiphertextException`, `SodiumException`

#### [`getRecord`](../../../src/Tables/PublicKeys.php#L188-L221)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$primaryKey`: `int`

**Throws:** `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DependencyException`, `InvalidCiphertextException`, `SodiumException`, `TableException`

#### [`getPublicKeysFor`](../../../src/Tables/PublicKeys.php#L238-L304)

Returns `array`

**Parameters:**

- `$actorName`: `string`
- `$keyId`: `string` = ''

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `SodiumException`, `TableException`

#### [`getNextPrimaryKey`](../../../src/Tables/PublicKeys.php#L306-L313)

Returns `int`

#### [`addKey`](../../../src/Tables/PublicKeys.php#L411-L419)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`revokeKey`](../../../src/Tables/PublicKeys.php#L431-L439)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`revokeKeyThirdParty`](../../../src/Tables/PublicKeys.php#L451-L459)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`moveIdentity`](../../../src/Tables/PublicKeys.php#L471-L479)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`burnDown`](../../../src/Tables/PublicKeys.php#L844-L852)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`fireproof`](../../../src/Tables/PublicKeys.php#L955-L963)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`undoFireproof`](../../../src/Tables/PublicKeys.php#L1038-L1046)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`checkpoint`](../../../src/Tables/PublicKeys.php#L1121-L1129)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`jsonDecode`](../../../src/Tables/PublicKeys.php#L15-L18)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/PublicKeys.php#L23-L26)

static · Returns `stdClass`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/PublicKeys.php#L32-L38)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`verifyTOTP`](../../../src/Tables/PublicKeys.php#L49-L63)

static · Returns `?int`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int` = 2

#### [`generateTOTP`](../../../src/Tables/PublicKeys.php#L65-L81)

static · Returns `string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` = null

#### [`ord`](../../../src/Tables/PublicKeys.php#L86-L90)

static · Returns `int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### [`throwIfTimeOutsideWindow`](../../../src/Tables/PublicKeys.php#L151-L160)

Returns `void`

**Parameters:**

- `$currentTime`: `int`

**Throws:** `DependencyException`, `ProtocolException`

#### [`assertAllArrayKeysExist`](../../../src/Tables/PublicKeys.php#L29-L34)

static · Returns `void`

This method throws an InputException if any of the expected keys are absent.

It does not return anything.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/PublicKeys.php#L42-L49)

static · Returns `bool`

This method returns true if every expected array key is found in the target array.

Otherwise, it returns false. This is useful for input validation.

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/PublicKeys.php#L64-L81)

Returns `string`

This is a constant-time conditional select. It should be read like a ternary operation.

$result = ClassWithTrait::constantTimeSelect(1, $left, $right); -> $result === $left. $result = ClassWithTrait::constantTimeSelect(0, $left, $right); -> $result === $right.

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/PublicKeys.php#L91-L94)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

This is mostly used for PEM-encoded strings.

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/PublicKeys.php#L112-L125)

static · Returns `string`

This is an implementation of PAE() from PASETO. It encodes an array of strings into a flat string consisting of:

1. The number of pieces. 2. For each piece: 1. The length of the piece (in bytes). 2. The contents of the piece. This allows multipart messages to have an injective canonical representation before passing ot a hash function (or other cryptographic function).

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/PublicKeys.php#L132-L140)

static · Returns `void`

This sorts the target array in-place, by its keys, including child arrays.

Used for ensuring arrays are sorted before JSON encoding.

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/PublicKeys.php#L147-L150)

static · Returns `string`

Mostly used by preAuthEncode() above. This packs an integer as 8 bytes.

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/PublicKeys.php#L155-L162)

Returns `array`

Get an array of bytes representing the input string.

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/PublicKeys.php#L170-L204)

static · Returns `string`

Strip all newlines (CR, LF) characters from a string.

**Parameters:**

- `$input`: `string`

---

## ReplicaActors

**class** `FediE2EE\PKDServer\Tables\ReplicaActors`

**File:** [`src/Tables/ReplicaActors.php`](../../../src/Tables/ReplicaActors.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### [`getCipher`](../../../src/Tables/ReplicaActors.php#L32-L45)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getNextPrimaryKey`](../../../src/Tables/ReplicaActors.php#L70-L77)

Returns `int`

#### [`searchForActor`](../../../src/Tables/ReplicaActors.php#L88-L117)

Returns `?FediE2EE\PKDServer\Tables\Records\ReplicaActor`

**Parameters:**

- `$peerID`: `int`
- `$activityPubID`: `string`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `InvalidCiphertextException`, `SodiumException`

#### [`getCounts`](../../../src/Tables/ReplicaActors.php#L122-L142)

Returns `array`

**Parameters:**

- `$peerID`: `int`
- `$actorID`: `int`

#### [`createForPeer`](../../../src/Tables/ReplicaActors.php#L151-L174)

Returns `int`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`
- `$activityPubID`: `string`
- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$key`: `?FediE2EE\PKD\Crypto\PublicKey` = null

**Throws:** `ArrayKeyException`, `CipherSweetException`, `CryptoOperationException`, `SodiumException`, `TableException`

#### [`createSimpleForPeer`](../../../src/Tables/ReplicaActors.php#L188-L207)

Returns `int`

Create a replica actor without requiring a Payload.

Used when replicating from source server where we have decrypted data.

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`
- `$activityPubID`: `string`
- `$key`: `?FediE2EE\PKD\Crypto\PublicKey` = null

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CipherSweetException`, `CryptoOperationException`, `SodiumException`, `TableException`

---

## ReplicaAuxData

**class** `FediE2EE\PKDServer\Tables\ReplicaAuxData`

**File:** [`src/Tables/ReplicaAuxData.php`](../../../src/Tables/ReplicaAuxData.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### [`getCipher`](../../../src/Tables/ReplicaAuxData.php#L25-L36)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getAuxDataForActor`](../../../src/Tables/ReplicaAuxData.php#L57-L83)

Returns `array`

**Parameters:**

- `$peerID`: `int`
- `$actorID`: `int`

**Throws:** `DateMalformedStringException`

#### [`getAuxDataById`](../../../src/Tables/ReplicaAuxData.php#L94-L150)

Returns `array`

**Parameters:**

- `$peerID`: `int`
- `$actorID`: `int`
- `$auxId`: `string`

**Throws:** `CipherSweetException`, `CryptoOperationException`, `DateMalformedStringException`, `InvalidCiphertextException`, `JsonException`, `SodiumException`

---

## ReplicaHistory

**class** `FediE2EE\PKDServer\Tables\ReplicaHistory`

**File:** [`src/Tables/ReplicaHistory.php`](../../../src/Tables/ReplicaHistory.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### [`getCipher`](../../../src/Tables/ReplicaHistory.php#L21-L24)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`createLeaf`](../../../src/Tables/ReplicaHistory.php#L35-L51)

Returns `FediE2EE\PKDServer\Tables\Records\ReplicaLeaf`

**Parameters:**

- `$apiResponseRecord`: `array`
- `$cosignature`: `string`
- `$proof`: `FediE2EE\PKD\Crypto\Merkle\InclusionProof`

#### [`save`](../../../src/Tables/ReplicaHistory.php#L57-L65)

Returns `void`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`
- `$leaf`: `FediE2EE\PKDServer\Tables\Records\ReplicaLeaf`

**Throws:** `JsonException`, `TableException`

#### [`getHistory`](../../../src/Tables/ReplicaHistory.php#L71-L82)

Returns `array`

**Parameters:**

- `$peerID`: `int`
- `$limit`: `int` = 100
- `$offset`: `int` = 0

**Throws:** `JsonException`

#### [`getHistorySince`](../../../src/Tables/ReplicaHistory.php#L88-L108)

Returns `array`

**Parameters:**

- `$peerID`: `int`
- `$hash`: `string`
- `$limit`: `int` = 100
- `$offset`: `int` = 0

**Throws:** `JsonException`

---

## ReplicaPublicKeys

**class** `FediE2EE\PKDServer\Tables\ReplicaPublicKeys`

**File:** [`src/Tables/ReplicaPublicKeys.php`](../../../src/Tables/ReplicaPublicKeys.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### [`getCipher`](../../../src/Tables/ReplicaPublicKeys.php#L26-L37)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`lookup`](../../../src/Tables/ReplicaPublicKeys.php#L63-L118)

Returns `array`

**Parameters:**

- `$peerID`: `int`
- `$actorID`: `int`
- `$keyID`: `string`

**Throws:** `CipherSweetException`, `CryptoOperationException`, `DateMalformedStringException`, `InvalidCiphertextException`, `JsonException`, `SodiumException`

#### [`getPublicKeysFor`](../../../src/Tables/ReplicaPublicKeys.php#L129-L179)

Returns `array`

**Parameters:**

- `$peerID`: `int`
- `$actorID`: `int`
- `$keyId`: `string` = ''

**Throws:** `CipherSweetException`, `CryptoOperationException`, `DateMalformedStringException`, `InvalidCiphertextException`, `JsonException`, `SodiumException`

---

## TOTP

**class** `FediE2EE\PKDServer\Tables\TOTP`

**File:** [`src/Tables/TOTP.php`](../../../src/Tables/TOTP.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### [`getCipher`](../../../src/Tables/TOTP.php#L25-L33)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getSecretByDomain`](../../../src/Tables/TOTP.php#L65-L72)

Returns `?string`

**Parameters:**

- `$domain`: `string`

**Throws:** `CipherSweetException`, `CryptoOperationException`, `InvalidCiphertextException`, `SodiumException`

#### [`getTotpByDomain`](../../../src/Tables/TOTP.php#L81-L97)

Returns `?array`

**Parameters:**

- `$domain`: `string`

**Throws:** `CipherSweetException`, `CryptoOperationException`, `InvalidCiphertextException`, `SodiumException`

#### [`saveSecret`](../../../src/Tables/TOTP.php#L107-L125)

Returns `void`

**Parameters:**

- `$domain`: `string`
- `$secret`: `string`
- `$lastTimeStep`: `int` = 0

**Throws:** `ArrayKeyException`, `CipherSweetException`, `CryptoOperationException`, `RandomException`, `SodiumException`, `TableException`

#### [`deleteSecret`](../../../src/Tables/TOTP.php#L127-L130)

Returns `void`

**Parameters:**

- `$domain`: `string`

#### [`updateSecret`](../../../src/Tables/TOTP.php#L140-L177)

Returns `void`

**Parameters:**

- `$domain`: `string`
- `$secret`: `string`
- `$lastTimeStep`: `int` = 0

**Throws:** `ArrayKeyException`, `CipherSweetException`, `CryptoOperationException`, `SodiumException`, `TableException`, `RandomException`

#### [`updateLastTimeStep`](../../../src/Tables/TOTP.php#L179-L186)

Returns `void`

**Parameters:**

- `$domain`: `string`
- `$lastTimeStep`: `int`

---

