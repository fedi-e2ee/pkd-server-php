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

#### [`getCipher`](../../../src/Tables/ActivityStreamQueue.php#L16-L24)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getNextPrimaryKey`](../../../src/Tables/ActivityStreamQueue.php#L32-L39)

Returns `int`

#### [`insert`](../../../src/Tables/ActivityStreamQueue.php#L44-L62)

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

#### [`getCipher`](../../../src/Tables/Actors.php#L32-L45)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getNextPrimaryKey`](../../../src/Tables/Actors.php#L65-L72)

Returns `int`

#### [`getActorByID`](../../../src/Tables/Actors.php#L85-L120)

**API** · Returns `FediE2EE\PKDServer\Tables\Records\Actor`

When you already have a database ID, just fetch the object.

**Parameters:**

- `$actorID`: `int`

**Throws:** `CipherSweetException`, `CryptoOperationException`, `InvalidCiphertextException`, `SodiumException`, `TableException`

#### [`getCounts`](../../../src/Tables/Actors.php#L123-L137)

Returns `array`

**Parameters:**

- `$actorID`: `int`

#### [`searchForActor`](../../../src/Tables/Actors.php#L152-L190)

**API** · Returns `?FediE2EE\PKDServer\Tables\Records\Actor`

When you only have an ActivityPub Actor ID, first canonicalize it, then fetch the Actor object

from the database based on that value. May return NULL, which indicates no records found.

**Parameters:**

- `$canonicalActorID`: `string`

**Throws:** `ArrayKeyException`, `BlindIndexNotFoundException`, `CipherSweetException`, `CryptoOperationException`, `InvalidCiphertextException`, `SodiumException`

#### [`createActor`](../../../src/Tables/Actors.php#L199-L219)

Returns `int`

**Parameters:**

- `$activityPubID`: `string`
- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$key`: `?FediE2EE\PKD\Crypto\PublicKey` = null

**Throws:** `ArrayKeyException`, `CryptoOperationException`, `CipherSweetException`, `SodiumException`, `ProtocolException`

#### [`clearCacheForActor`](../../../src/Tables/Actors.php#L224-L230)

Returns `void`

**Parameters:**

- `$actor`: `FediE2EE\PKDServer\Tables\Records\Actor`

**Throws:** `TableException`

---

## AuxData

**class** `FediE2EE\PKDServer\Tables\AuxData`

**File:** [`src/Tables/AuxData.php`](../../../src/Tables/AuxData.php)

**Extends:** `FediE2EE\PKDServer\Table`

**Uses:** `FediE2EE\PKDServer\Traits\ProtocolMethodTrait`

### Methods

#### [`getCipher`](../../../src/Tables/AuxData.php#L49-L60)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getAuxDataForActor`](../../../src/Tables/AuxData.php#L75-L100)

Returns `array`

**Parameters:**

- `$actorId`: `int`

**Throws:** `DateMalformedStringException`

#### [`getAuxDataById`](../../../src/Tables/AuxData.php#L111-L156)

**API** · Returns `array`

**Parameters:**

- `$actorId`: `int`
- `$auxId`: `string`

**Throws:** `CipherSweetException`, `CryptoOperationException`, `DateMalformedStringException`, `InvalidCiphertextException`, `JsonException`, `SodiumException`

#### [`addAuxData`](../../../src/Tables/AuxData.php#L166-L173)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`revokeAuxData`](../../../src/Tables/AuxData.php#L285-L293)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

---

## MerkleState

**class** `FediE2EE\PKDServer\Tables\MerkleState`

**File:** [`src/Tables/MerkleState.php`](../../../src/Tables/MerkleState.php)

Merkle State management

Insert new leaves

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### [`getCipher`](../../../src/Tables/MerkleState.php#L43-L51)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getWitnessByOrigin`](../../../src/Tables/MerkleState.php#L72-L83)

Returns `array`

Return the witness data (including public key) for a given origin

**Parameters:**

- `$origin`: `string`

**Throws:** `TableException`

#### [`addWitnessCosignature`](../../../src/Tables/MerkleState.php#L100-L142)

**API** · Returns `bool`

**Parameters:**

- `$origin`: `string`
- `$merkleRoot`: `string`
- `$cosignature`: `string`

**Throws:** `CryptoException`, `JsonException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`getCosignatures`](../../../src/Tables/MerkleState.php#L144-L162)

Returns `array`

**Parameters:**

- `$leafId`: `int`

#### [`countCosignatures`](../../../src/Tables/MerkleState.php#L164-L174)

Returns `int`

**Parameters:**

- `$leafId`: `int`

#### [`getLatestRoot`](../../../src/Tables/MerkleState.php#L179-L188)

**API** · Returns `string`

#### [`insertLeaf`](../../../src/Tables/MerkleState.php#L207-L263)

**API** · Returns `bool`

Insert leaf with retry logic for deadlocks

**Parameters:**

- `$leaf`: `FediE2EE\PKDServer\Tables\Records\MerkleLeaf`
- `$inTransaction`: `callable`
- `$maxRetries`: `int` = 5

**Throws:** `ConcurrentException`, `CryptoException`, `DependencyException`, `NotImplementedException`, `RandomException`, `SodiumException`

#### [`getLeafByRoot`](../../../src/Tables/MerkleState.php#L268-L284)

**API** · Returns `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf`

**Parameters:**

- `$root`: `string`

#### [`getLeafByID`](../../../src/Tables/MerkleState.php#L289-L305)

**API** · Returns `?FediE2EE\PKDServer\Tables\Records\MerkleLeaf`

**Parameters:**

- `$primaryKey`: `int`

#### [`getHashesSince`](../../../src/Tables/MerkleState.php#L332-L377)

**API** · Returns `array`

**Parameters:**

- `$oldRoot`: `string`
- `$limit`: `int`
- `$offset`: `int` = 0

**Throws:** `DependencyException`

---

## Peers

**class** `FediE2EE\PKDServer\Tables\Peers`

**File:** [`src/Tables/Peers.php`](../../../src/Tables/Peers.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### [`getCipher`](../../../src/Tables/Peers.php#L28-L31)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`getNextPeerId`](../../../src/Tables/Peers.php#L39-L46)

Returns `int`

#### [`create`](../../../src/Tables/Peers.php#L51-L84)

**API** · Returns `FediE2EE\PKDServer\Tables\Records\Peer`

**Parameters:**

- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`
- `$hostname`: `string`
- `$cosign`: `bool` = false
- `$replicate`: `bool` = false
- `$rewrapConfig`: `?FediE2EE\PKDServer\Protocol\RewrapConfig` = null

#### [`getPeerByUniqueId`](../../../src/Tables/Peers.php#L94-L101)

**API** · Returns `FediE2EE\PKDServer\Tables\Records\Peer`

**Parameters:**

- `$uniqueId`: `string`

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`, `TableException`

#### [`getPeer`](../../../src/Tables/Peers.php#L103-L111)

Returns `FediE2EE\PKDServer\Tables\Records\Peer`

**Parameters:**

- `$hostname`: `string`

#### [`listAll`](../../../src/Tables/Peers.php#L150-L157)

**API** · Returns `array`

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`

#### [`listReplicatingPeers`](../../../src/Tables/Peers.php#L167-L174)

Returns `array`

Lists which peers we replicate.

**Throws:** `CryptoException`, `DateMalformedStringException`, `SodiumException`

#### [`save`](../../../src/Tables/Peers.php#L176-L185)

Returns `bool`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`

#### [`getRewrapCandidates`](../../../src/Tables/Peers.php#L187-L203)

Returns `array`

#### [`rewrapKeyMap`](../../../src/Tables/Peers.php#L205-L248)

Returns `void`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`
- `$keyMap`: `FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap`
- `$leafId`: `int`

---

## PublicKeys

**class** `FediE2EE\PKDServer\Tables\PublicKeys`

**File:** [`src/Tables/PublicKeys.php`](../../../src/Tables/PublicKeys.php)

**Extends:** `FediE2EE\PKDServer\Table`

**Uses:** `FediE2EE\PKDServer\Traits\ProtocolMethodTrait`, `FediE2EE\PKDServer\Traits\TOTPTrait`

### Methods

#### [`getCipher`](../../../src/Tables/PublicKeys.php#L68-L79)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`generateKeyID`](../../../src/Tables/PublicKeys.php#L84-L87)

Returns `string`

**Throws:** `RandomException`

#### [`lookup`](../../../src/Tables/PublicKeys.php#L109-L156)

Returns `array`

**Parameters:**

- `$actorPrimaryKey`: `int`
- `$keyID`: `string`

**Throws:** `CipherSweetException`, `CryptoOperationException`, `InvalidCiphertextException`, `SodiumException`, `DateMalformedStringException`, `BaseJsonException`

#### [`getRecord`](../../../src/Tables/PublicKeys.php#L169-L196)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$primaryKey`: `int`

**Throws:** `CacheException`, `CipherSweetException`, `CryptoOperationException`, `DependencyException`, `InvalidCiphertextException`, `SodiumException`, `TableException`

#### [`getPublicKeysFor`](../../../src/Tables/PublicKeys.php#L209-L274)

Returns `array`

**Parameters:**

- `$actorName`: `string`
- `$keyId`: `string` = ''

**Throws:** `BaseJsonException`, `CacheException`, `CipherSweetException`, `CryptoOperationException`, `DateMalformedStringException`, `DependencyException`, `InvalidCiphertextException`, `SodiumException`, `TableException`

#### [`getNextPrimaryKey`](../../../src/Tables/PublicKeys.php#L276-L283)

Returns `int`

#### [`addKey`](../../../src/Tables/PublicKeys.php#L293-L301)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`revokeKey`](../../../src/Tables/PublicKeys.php#L311-L319)

Returns `FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`revokeKeyThirdParty`](../../../src/Tables/PublicKeys.php#L329-L337)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`

**Throws:** `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`moveIdentity`](../../../src/Tables/PublicKeys.php#L347-L355)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`burnDown`](../../../src/Tables/PublicKeys.php#L666-L675)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`fireproof`](../../../src/Tables/PublicKeys.php#L771-L779)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`undoFireproof`](../../../src/Tables/PublicKeys.php#L862-L870)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:** `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`checkpoint`](../../../src/Tables/PublicKeys.php#L952-L960)

Returns `bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`

**Throws:** `CryptoException`, `DependencyException`, `NotImplementedException`, `ProtocolException`, `SodiumException`, `TableException`

#### [`verifyTOTP`](../../../src/Tables/PublicKeys.php#L42-L56)

static · Returns `bool`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int` = 2

#### [`generateTOTP`](../../../src/Tables/PublicKeys.php#L58-L73)

static · Returns `string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` = null

#### [`ord`](../../../src/Tables/PublicKeys.php#L78-L81)

static · Returns `int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### [`throwIfTimeOutsideWindow`](../../../src/Tables/PublicKeys.php#L139-L148)

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

#### [`getCipher`](../../../src/Tables/ReplicaHistory.php#L19-L22)

Returns `FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### [`createLeaf`](../../../src/Tables/ReplicaHistory.php#L30-L46)

Returns `FediE2EE\PKDServer\Tables\Records\ReplicaLeaf`

**Parameters:**

- `$apiResponseRecord`: `array`
- `$cosignature`: `string`
- `$proof`: `FediE2EE\PKD\Crypto\Merkle\InclusionProof`

#### [`save`](../../../src/Tables/ReplicaHistory.php#L48-L56)

Returns `void`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`
- `$leaf`: `FediE2EE\PKDServer\Tables\Records\ReplicaLeaf`

#### [`getHistory`](../../../src/Tables/ReplicaHistory.php#L61-L72)

Returns `array`

**Parameters:**

- `$peerID`: `int`
- `$limit`: `int` = 100
- `$offset`: `int` = 0

**Throws:** `JsonException`

#### [`getHistorySince`](../../../src/Tables/ReplicaHistory.php#L77-L97)

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

#### [`getSecretByDomain`](../../../src/Tables/TOTP.php#L59-L72)

Returns `?string`

**Parameters:**

- `$domain`: `string`

**Throws:** `CipherSweetException`, `CryptoOperationException`, `SodiumException`, `InvalidCiphertextException`

#### [`saveSecret`](../../../src/Tables/TOTP.php#L82-L96)

Returns `void`

**Parameters:**

- `$domain`: `string`
- `$secret`: `string`

**Throws:** `ArrayKeyException`, `CipherSweetException`, `CryptoOperationException`, `RandomException`, `SodiumException`, `TableException`

#### [`deleteSecret`](../../../src/Tables/TOTP.php#L98-L101)

Returns `void`

**Parameters:**

- `$domain`: `string`

#### [`updateSecret`](../../../src/Tables/TOTP.php#L111-L143)

Returns `void`

**Parameters:**

- `$domain`: `string`
- `$secret`: `string`

**Throws:** `ArrayKeyException`, `CipherSweetException`, `CryptoOperationException`, `SodiumException`, `TableException`, `RandomException`

---

