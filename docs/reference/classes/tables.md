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

#### [`getCipher`](../../../src/Tables/Actors.php#L33-L46)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getNextPrimaryKey`](../../../src/Tables/Actors.php#L66-L73)

Returns `int`

#### [`getActorByID`](../../../src/Tables/Actors.php#L87-L122)

**API** · Returns `FediE2EE\PKDServer\Tables\Records\Actor`

When you already have a database ID, just fetch the object.

**Parameters:**

- `$actorID`: `int`

**Throws:** `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `InvalidCiphertextException`, `SodiumException`, `TableException`

#### [`getCounts`](../../../src/Tables/Actors.php#L125-L139)

Returns `array`

**Parameters:**

- `$actorID`: `int`

#### [`searchForActor`](../../../src/Tables/Actors.php#L155-L193)

**API** · Returns `?FediE2EE\PKDServer\Tables\Records\Actor`

When you only have an ActivityPub Actor ID, first canonicalize it, then fetch the Actor object

from the database based on that value. May return NULL, which indicates no records found.

**Parameters:**

- `$canonicalActorID`: `string`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `InvalidCiphertextException`, `SodiumException`

#### [`createActor`](../../../src/Tables/Actors.php#L202-L222)

Returns `int`

**Parameters:**

- `$activityPubID`: `string`
- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$key`: `?FediE2EE\PKD\Crypto\PublicKey` = null

**Throws:** `ArrayKeyException`, `CryptoOperationException`, `CipherSweetException`, `SodiumException`, `ProtocolException`

#### [`clearCacheForActor`](../../../src/Tables/Actors.php#L227-L233)

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

#### [`getCipher`](../../../src/Tables/AuxData.php#L57-L68)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getAuxDataForActor`](../../../src/Tables/AuxData.php#L83-L109)

Returns `array`

**Parameters:**

- `$actorId`: `int`

**Throws:** `DateMalformedStringException`

#### [`getAuxDataById`](../../../src/Tables/AuxData.php#L120-L165)

**API** · Returns `array`

**Parameters:**

- `$actorId`: `int`
- `$auxId`: `string`

**Throws:** `CipherSweetException`, `CryptoOperationException`, `DateMalformedStringException`, `InvalidCiphertextException`, `JsonException`, `SodiumException`

#### [`addAuxData`](../../../src/Tables/AuxData.php#L177-L184)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`revokeAuxData`](../../../src/Tables/AuxData.php#L302-L310)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`getAuxDataId`](../../../src/Tables/AuxData.php#L12-L22)

static · Returns `string`

**Parameters:**

- `$auxDataType`: `string`
- `$data`: `string`

#### [`assertAllArrayKeysExist`](../../../src/Tables/AuxData.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/AuxData.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/AuxData.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/AuxData.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/AuxData.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/AuxData.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/AuxData.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/AuxData.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/AuxData.php#L107-L141)

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

#### [`getCipher`](../../../src/Tables/MerkleState.php#L48-L56)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getWitnessByOrigin`](../../../src/Tables/MerkleState.php#L77-L88)

Returns `array`

Return the witness data (including public key) for a given origin

**Parameters:**

- `$origin`: `string`

**Throws:** `TableException`

#### [`addWitnessCosignature`](../../../src/Tables/MerkleState.php#L105-L147)

**API** · Returns `bool`

**Parameters:**

- `$origin`: `string`
- `$merkleRoot`: `string`
- `$cosignature`: `string`

**Throws:** `CryptoException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`getCosignatures`](../../../src/Tables/MerkleState.php#L149-L167)

Returns `array`

**Parameters:**

- `$leafId`: `int`

#### [`countCosignatures`](../../../src/Tables/MerkleState.php#L169-L179)

Returns `int`

**Parameters:**

- `$leafId`: `int`

#### [`getLatestRoot`](../../../src/Tables/MerkleState.php#L187-L196)

**API** · Returns `string`

**Throws:** `DependencyException`, `SodiumException`

#### [`insertLeaf`](../../../src/Tables/MerkleState.php#L215-L271)

**API** · Returns `bool`

Insert leaf with retry logic for deadlocks

**Parameters:**

- `$leaf`: `FediE2EE\PKDServer\Tables\Records\MerkleLeaf`
- `$inTransaction`: `callable`
- `$maxRetries`: `int` = 5

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `RandomException`, `SodiumException`

#### [`getLeafByRoot`](../../../src/Tables/MerkleState.php#L276-L292)

**API** · Returns `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf`

**Parameters:**

- `$root`: `string`

#### [`getLeafByID`](../../../src/Tables/MerkleState.php#L297-L313)

**API** · Returns `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf`

**Parameters:**

- `$primaryKey`: `int`

#### [`getHashesSince`](../../../src/Tables/MerkleState.php#L347-L392)

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

#### [`getCipher`](../../../src/Tables/Peers.php#L33-L36)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getNextPeerId`](../../../src/Tables/Peers.php#L44-L51)

Returns `int`

#### [`create`](../../../src/Tables/Peers.php#L59-L92)

**API** · Returns `FediE2EE\PKDServer\Tables\Records\Peer`

**Parameters:**

- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`
- `$hostname`: `string`
- `$cosign`: `bool` = false
- `$replicate`: `bool` = false
- `$rewrapConfig`: `?FediE2EE\PKDServer\Protocol\RewrapConfig` = null

**Throws:** `TableException`, `RandomException`

#### [`getPeerByUniqueId`](../../../src/Tables/Peers.php#L102-L112)

**API** · Returns `FediE2EE\PKDServer\Tables\Records\Peer`

**Parameters:**

- `$uniqueId`: `string`

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`, `TableException`

#### [`getPeer`](../../../src/Tables/Peers.php#L120-L131)

Returns `FediE2EE\PKDServer\Tables\Records\Peer`

**Parameters:**

- `$hostname`: `string`

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`, `TableException`

#### [`listAll`](../../../src/Tables/Peers.php#L170-L179)

**API** · Returns `array`

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`

#### [`listReplicatingPeers`](../../../src/Tables/Peers.php#L190-L199)

Returns `array`

Lists which peers we replicate.

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`

#### [`save`](../../../src/Tables/Peers.php#L204-L213)

Returns `bool`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`

**Throws:** `TableException`

#### [`getRewrapCandidates`](../../../src/Tables/Peers.php#L220-L238)

Returns `array`

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`

#### [`rewrapKeyMap`](../../../src/Tables/Peers.php#L245-L288)

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

#### [`getCipher`](../../../src/Tables/PublicKeys.php#L67-L78)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`generateKeyID`](../../../src/Tables/PublicKeys.php#L83-L86)

Returns `string`

**Throws:** `RandomException`

#### [`lookup`](../../../src/Tables/PublicKeys.php#L109-L156)

Returns `array`

**Parameters:**

- `$actorPrimaryKey`: `int`
- `$keyID`: `string`

**Throws:** `BaseJsonException`, `CipherSweetException`, `CryptoOperationException`, `DateMalformedStringException`, `InvalidCiphertextException`, `SodiumException`

#### [`getRecord`](../../../src/Tables/PublicKeys.php#L171-L198)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$primaryKey`: `int`

**Throws:** `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DependencyException`, `InvalidCiphertextException`, `SodiumException`, `TableException`

#### [`getPublicKeysFor`](../../../src/Tables/PublicKeys.php#L214-L279)

Returns `array`

**Parameters:**

- `$actorName`: `string`
- `$keyId`: `string` = ''

**Throws:** `ArrayKeyException`, `BaseJsonException`, `BlindIndexNotFoundException`, `CacheException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `SodiumException`, `TableException`

#### [`getNextPrimaryKey`](../../../src/Tables/PublicKeys.php#L281-L288)

Returns `int`

#### [`addKey`](../../../src/Tables/PublicKeys.php#L300-L308)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`revokeKey`](../../../src/Tables/PublicKeys.php#L320-L328)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`revokeKeyThirdParty`](../../../src/Tables/PublicKeys.php#L340-L348)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`moveIdentity`](../../../src/Tables/PublicKeys.php#L360-L368)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`burnDown`](../../../src/Tables/PublicKeys.php#L689-L698)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`fireproof`](../../../src/Tables/PublicKeys.php#L803-L811)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`undoFireproof`](../../../src/Tables/PublicKeys.php#L897-L905)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`checkpoint`](../../../src/Tables/PublicKeys.php#L991-L999)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `RandomException`, `SodiumException`, `TableException`

#### [`jsonDecode`](../../../src/Tables/PublicKeys.php#L12-L15)

static · Returns `array`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonDecodeObject`](../../../src/Tables/PublicKeys.php#L20-L23)

static · Returns `object`

**Parameters:**

- `$json`: `string`

**Throws:** `BaseJsonException`

#### [`jsonEncode`](../../../src/Tables/PublicKeys.php#L28-L34)

static · Returns `string`

**Parameters:**

- `$data`: `mixed`

**Throws:** `BaseJsonException`

#### [`verifyTOTP`](../../../src/Tables/PublicKeys.php#L45-L59)

static · Returns `?int`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int` = 2

#### [`generateTOTP`](../../../src/Tables/PublicKeys.php#L61-L76)

static · Returns `string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` = null

#### [`ord`](../../../src/Tables/PublicKeys.php#L81-L84)

static · Returns `int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### [`throwIfTimeOutsideWindow`](../../../src/Tables/PublicKeys.php#L141-L150)

Returns `void`

**Parameters:**

- `$currentTime`: `int`

**Throws:** `ProtocolException`

#### [`assertAllArrayKeysExist`](../../../src/Tables/PublicKeys.php#L14-L19)

static · Returns `void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:** `InputException`

#### [`allArrayKeysExist`](../../../src/Tables/PublicKeys.php#L21-L28)

static · Returns `bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

#### [`constantTimeSelect`](../../../src/Tables/PublicKeys.php#L35-L52)

Returns `string`

**Parameters:**

- `$select`: `int`
- `$left`: `string`
- `$right`: `string`

**Throws:** `CryptoException`

#### [`dos2unix`](../../../src/Tables/PublicKeys.php#L60-L63)

static · Returns `string`

Normalize line-endings to UNIX-style (LF rather than CRLF).

**Parameters:**

- `$in`: `string`

#### [`preAuthEncode`](../../../src/Tables/PublicKeys.php#L69-L78)

static · Returns `string`

**Parameters:**

- `$pieces`: `array`

#### [`sortByKey`](../../../src/Tables/PublicKeys.php#L80-L88)

static · Returns `void`

**Parameters:**

- `$arr`: `array`

#### [`LE64`](../../../src/Tables/PublicKeys.php#L90-L93)

static · Returns `string`

**Parameters:**

- `$n`: `int`

#### [`stringToByteArray`](../../../src/Tables/PublicKeys.php#L95-L99)

Returns `array`

**Parameters:**

- `$str`: `string`

#### [`stripNewlines`](../../../src/Tables/PublicKeys.php#L107-L141)

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

#### [`getCipher`](../../../src/Tables/ReplicaActors.php#L31-L44)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getNextPrimaryKey`](../../../src/Tables/ReplicaActors.php#L64-L71)

Returns `int`

#### [`searchForActor`](../../../src/Tables/ReplicaActors.php#L82-L110)

Returns `?FediE2EE\PKDServer\Tables\Records\ReplicaActor`

**Parameters:**

- `$peerID`: `int`
- `$activityPubID`: `string`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CipherSweetException`, `CryptoException`, `CryptoOperationException`, `InvalidCiphertextException`, `SodiumException`

#### [`getCounts`](../../../src/Tables/ReplicaActors.php#L112-L132)

Returns `array`

**Parameters:**

- `$peerID`: `int`
- `$actorID`: `int`

#### [`createForPeer`](../../../src/Tables/ReplicaActors.php#L141-L164)

Returns `int`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`
- `$activityPubID`: `string`
- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$key`: `?FediE2EE\PKD\Crypto\PublicKey` = null

**Throws:** `ArrayKeyException`, `CipherSweetException`, `CryptoOperationException`, `SodiumException`, `TableException`

#### [`createSimpleForPeer`](../../../src/Tables/ReplicaActors.php#L178-L197)

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

#### [`getCipher`](../../../src/Tables/ReplicaAuxData.php#L23-L34)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getAuxDataForActor`](../../../src/Tables/ReplicaAuxData.php#L49-L75)

Returns `array`

**Parameters:**

- `$peerID`: `int`
- `$actorID`: `int`

**Throws:** `DateMalformedStringException`

#### [`getAuxDataById`](../../../src/Tables/ReplicaAuxData.php#L85-L138)

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

#### [`getCipher`](../../../src/Tables/ReplicaHistory.php#L20-L23)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`createLeaf`](../../../src/Tables/ReplicaHistory.php#L31-L47)

Returns `FediE2EE\PKDServer\Tables\Records\ReplicaLeaf`

**Parameters:**

- `$apiResponseRecord`: `array`
- `$cosignature`: `string`
- `$proof`: `FediE2EE\PKD\Crypto\Merkle\InclusionProof`

#### [`save`](../../../src/Tables/ReplicaHistory.php#L52-L60)

Returns `void`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`
- `$leaf`: `FediE2EE\PKDServer\Tables\Records\ReplicaLeaf`

**Throws:** `TableException`

#### [`getHistory`](../../../src/Tables/ReplicaHistory.php#L65-L76)

Returns `array`

**Parameters:**

- `$peerID`: `int`
- `$limit`: `int` = 100
- `$offset`: `int` = 0

**Throws:** `JsonException`

#### [`getHistorySince`](../../../src/Tables/ReplicaHistory.php#L81-L101)

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

#### [`getCipher`](../../../src/Tables/ReplicaPublicKeys.php#L24-L35)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`lookup`](../../../src/Tables/ReplicaPublicKeys.php#L54-L106)

Returns `array`

**Parameters:**

- `$peerID`: `int`
- `$actorID`: `int`
- `$keyID`: `string`

**Throws:** `CipherSweetException`, `CryptoOperationException`, `DateMalformedStringException`, `InvalidCiphertextException`, `JsonException`, `SodiumException`

#### [`getPublicKeysFor`](../../../src/Tables/ReplicaPublicKeys.php#L116-L163)

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

#### [`getCipher`](../../../src/Tables/TOTP.php#L24-L32)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getSecretByDomain`](../../../src/Tables/TOTP.php#L59-L66)

Returns `?string`

**Parameters:**

- `$domain`: `string`

**Throws:** `CipherSweetException`, `CryptoOperationException`, `InvalidCiphertextException`, `SodiumException`

#### [`getTotpByDomain`](../../../src/Tables/TOTP.php#L74-L90)

Returns `?array`

**Parameters:**

- `$domain`: `string`

**Throws:** `CipherSweetException`, `CryptoOperationException`, `InvalidCiphertextException`, `SodiumException`

#### [`saveSecret`](../../../src/Tables/TOTP.php#L100-L118)

Returns `void`

**Parameters:**

- `$domain`: `string`
- `$secret`: `string`
- `$lastTimeStep`: `int` = 0

**Throws:** `ArrayKeyException`, `CipherSweetException`, `CryptoOperationException`, `RandomException`, `SodiumException`, `TableException`

#### [`deleteSecret`](../../../src/Tables/TOTP.php#L120-L123)

Returns `void`

**Parameters:**

- `$domain`: `string`

#### [`updateSecret`](../../../src/Tables/TOTP.php#L133-L170)

Returns `void`

**Parameters:**

- `$domain`: `string`
- `$secret`: `string`
- `$lastTimeStep`: `int` = 0

**Throws:** `ArrayKeyException`, `CipherSweetException`, `CryptoOperationException`, `SodiumException`, `TableException`, `RandomException`

#### [`updateLastTimeStep`](../../../src/Tables/TOTP.php#L172-L179)

Returns `void`

**Parameters:**

- `$domain`: `string`
- `$lastTimeStep`: `int`

---

