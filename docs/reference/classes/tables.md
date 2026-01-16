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

#### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### `getNextPrimaryKey(): int`

#### `insert(FediE2EE\PKDServer\ActivityPub\ActivityStream $as): int`

**Parameters:**

- `$as`: `FediE2EE\PKDServer\ActivityPub\ActivityStream`

**Throws:**

- `ActivityPubException`

---

## Actors

**class** `FediE2EE\PKDServer\Tables\Actors`

**File:** [`src/Tables/Actors.php`](../../../src/Tables/Actors.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### `getNextPrimaryKey(): int`

#### `getActorByID(int $actorID): FediE2EE\PKDServer\Tables\Records\Actor`

**API Method**

When you already have a database ID, just fetch the object.

**Parameters:**

- `$actorID`: `int`

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`
- `InvalidCiphertextException`
- `SodiumException`
- `TableException`

#### `getCounts(int $actorID): array`

**Parameters:**

- `$actorID`: `int`

#### `searchForActor(string $canonicalActorID): ?FediE2EE\PKDServer\Tables\Records\Actor`

**API Method**

When you only have an ActivityPub Actor ID, first canonicalize it, then fetch the Actor object

**Parameters:**

- `$canonicalActorID`: `string`

**Throws:**

- `ArrayKeyException`
- `BlindIndexNotFoundException`
- `CipherSweetException`
- `CryptoOperationException`
- `InvalidCiphertextException`
- `SodiumException`

#### `createActor(string $activityPubID, FediE2EE\PKDServer\Protocol\Payload $payload, ?FediE2EE\PKD\Crypto\PublicKey $key = null): int`

**Parameters:**

- `$activityPubID`: `string`
- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$key`: `?FediE2EE\PKD\Crypto\PublicKey` (nullable)

**Throws:**

- `ArrayKeyException`
- `CryptoOperationException`
- `CipherSweetException`
- `SodiumException`
- `ProtocolException`

#### `clearCacheForActor(FediE2EE\PKDServer\Tables\Records\Actor $actor): void`

**Parameters:**

- `$actor`: `FediE2EE\PKDServer\Tables\Records\Actor`

**Throws:**

- `TableException`

---

## AuxData

**class** `FediE2EE\PKDServer\Tables\AuxData`

**File:** [`src/Tables/AuxData.php`](../../../src/Tables/AuxData.php)

**Extends:** `FediE2EE\PKDServer\Table`

**Uses:** `FediE2EE\PKDServer\Traits\ProtocolMethodTrait`

### Methods

#### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### `getAuxDataForActor(int $actorId): array`

**Parameters:**

- `$actorId`: `int`

**Throws:**

- `DateMalformedStringException`

#### `getAuxDataById(int $actorId, string $auxId): array`

**API Method**

**Parameters:**

- `$actorId`: `int`
- `$auxId`: `string`

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`
- `DateMalformedStringException`
- `InvalidCiphertextException`
- `JsonException`
- `SodiumException`

#### `addAuxData(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `revokeAuxData(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

---

## MerkleState

**class** `FediE2EE\PKDServer\Tables\MerkleState`

**File:** [`src/Tables/MerkleState.php`](../../../src/Tables/MerkleState.php)

Merkle State management

Insert new leaves

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### `getWitnessByOrigin(string $origin): array`

Return the witness data (including public key) for a given origin

**Parameters:**

- `$origin`: `string`

**Throws:**

- `TableException`

#### `addWitnessCosignature(string $origin, string $merkleRoot, string $cosignature): bool`

**API Method**

**Parameters:**

- `$origin`: `string`
- `$merkleRoot`: `string`
- `$cosignature`: `string`

**Throws:**

- `CryptoException`
- `JsonException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `getCosignatures(int $leafId): array`

**Parameters:**

- `$leafId`: `int`

#### `countCosignatures(int $leafId): int`

**Parameters:**

- `$leafId`: `int`

#### `getLatestRoot(): string`

**API Method**

#### `insertLeaf(FediE2EE\PKDServer\Tables\Records\MerkleLeaf $leaf, callable $inTransaction, int $maxRetries = 5): bool`

**API Method**

Insert leaf with retry logic for deadlocks

**Parameters:**

- `$leaf`: `FediE2EE\PKDServer\Tables\Records\MerkleLeaf`
- `$inTransaction`: `callable`
- `$maxRetries`: `int`

**Throws:**

- `ConcurrentException`
- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `RandomException`
- `SodiumException`

#### `getLeafByRoot(string $root): ?FediE2EE\PKDServer\Tables\Records\MerkleLeaf`

**API Method**

**Parameters:**

- `$root`: `string`

#### `getLeafByID(int $primaryKey): ?FediE2EE\PKDServer\Tables\Records\MerkleLeaf`

**API Method**

**Parameters:**

- `$primaryKey`: `int`

#### `getHashesSince(string $oldRoot, int $limit, int $offset = 0): array`

**API Method**

**Parameters:**

- `$oldRoot`: `string`
- `$limit`: `int`
- `$offset`: `int`

---

## Peers

**class** `FediE2EE\PKDServer\Tables\Peers`

**File:** [`src/Tables/Peers.php`](../../../src/Tables/Peers.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### `getNextPeerId(): int`

#### `create(FediE2EE\PKD\Crypto\PublicKey $publicKey, string $hostname, bool $cosign = false, bool $replicate = false, ?FediE2EE\PKDServer\Protocol\RewrapConfig $rewrapConfig = null): FediE2EE\PKDServer\Tables\Records\Peer`

**API Method**

**Parameters:**

- `$publicKey`: `FediE2EE\PKD\Crypto\PublicKey`
- `$hostname`: `string`
- `$cosign`: `bool`
- `$replicate`: `bool`
- `$rewrapConfig`: `?FediE2EE\PKDServer\Protocol\RewrapConfig` (nullable)

#### `getPeerByUniqueId(string $uniqueId): FediE2EE\PKDServer\Tables\Records\Peer`

**API Method**

**Parameters:**

- `$uniqueId`: `string`

**Throws:**

- `CryptoException`
- `DateMalformedStringException`
- `SodiumException`
- `TableException`

#### `getPeer(string $hostname): FediE2EE\PKDServer\Tables\Records\Peer`

**Parameters:**

- `$hostname`: `string`

#### `listAll(): array`

**API Method**

**Throws:**

- `CryptoException`
- `DateMalformedStringException`
- `SodiumException`

#### `save(FediE2EE\PKDServer\Tables\Records\Peer $peer): bool`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`

#### `getRewrapCandidates(): array`

#### `rewrapKeyMap(FediE2EE\PKDServer\Tables\Records\Peer $peer, FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap $keyMap, int $leafId): void`

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

#### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### `generateKeyID(): string`

**Throws:**

- `RandomException`

#### `lookup(int $actorPrimaryKey, string $keyID): array`

**Parameters:**

- `$actorPrimaryKey`: `int`
- `$keyID`: `string`

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`
- `InvalidCiphertextException`
- `SodiumException`
- `DateMalformedStringException`
- `BaseJsonException`

#### `getRecord(int $primaryKey): FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$primaryKey`: `int`

**Throws:**

- `CacheException`
- `CipherSweetException`
- `CryptoOperationException`
- `DependencyException`
- `InvalidCiphertextException`
- `SodiumException`
- `TableException`

#### `getPublicKeysFor(string $actorName, string $keyId = ''): array`

**Parameters:**

- `$actorName`: `string`
- `$keyId`: `string`

**Throws:**

- `BaseJsonException`
- `CacheException`
- `CipherSweetException`
- `CryptoOperationException`
- `DateMalformedStringException`
- `DependencyException`
- `InvalidCiphertextException`
- `SodiumException`
- `TableException`

#### `getNextPrimaryKey(): int`

#### `addKey(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `revokeKey(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): FediE2EE\PKDServer\Tables\Records\ActorKey`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `revokeKeyThirdParty(FediE2EE\PKDServer\Protocol\Payload $payload): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `moveIdentity(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `burnDown(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `fireproof(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `undoFireproof(FediE2EE\PKDServer\Protocol\Payload $payload, string $outerActor): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$outerActor`: `string`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `checkpoint(FediE2EE\PKDServer\Protocol\Payload $payload): bool`

**Parameters:**

- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`

**Throws:**

- `CryptoException`
- `DependencyException`
- `NotImplementedException`
- `ProtocolException`
- `SodiumException`
- `TableException`

#### `static verifyTOTP(string $secret, string $otp, int $windows = 2): bool`

**Parameters:**

- `$secret`: `string`
- `$otp`: `string`
- `$windows`: `int`

#### `static generateTOTP(string $secret, ?int $time = null): string`

**Parameters:**

- `$secret`: `string`
- `$time`: `?int` (nullable)

#### `static ord(string $chr): int`

Avoid cache-timing leaks in ord() by using unpack()

**Parameters:**

- `$chr`: `string`

#### `throwIfTimeOutsideWindow(int $currentTime): void`

**Parameters:**

- `$currentTime`: `int`

**Throws:**

- `ProtocolException`

#### `static assertAllArrayKeysExist(array $target, string $arrayKeys): void`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

**Throws:**

- `InputException`

#### `static allArrayKeysExist(array $target, string $arrayKeys): bool`

**Parameters:**

- `$target`: `array`
- `...$arrayKeys`: `string`

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

## ReplicaActors

**class** `FediE2EE\PKDServer\Tables\ReplicaActors`

**File:** [`src/Tables/ReplicaActors.php`](../../../src/Tables/ReplicaActors.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### `getNextPrimaryKey(): int`

#### `createForPeer(FediE2EE\PKDServer\Tables\Records\Peer $peer, string $activityPubID, FediE2EE\PKDServer\Protocol\Payload $payload, ?FediE2EE\PKD\Crypto\PublicKey $key = null): int`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`
- `$activityPubID`: `string`
- `$payload`: `FediE2EE\PKDServer\Protocol\Payload`
- `$key`: `?FediE2EE\PKD\Crypto\PublicKey` (nullable)

---

## ReplicaAuxData

**class** `FediE2EE\PKDServer\Tables\ReplicaAuxData`

**File:** [`src/Tables/ReplicaAuxData.php`](../../../src/Tables/ReplicaAuxData.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

---

## ReplicaHistory

**class** `FediE2EE\PKDServer\Tables\ReplicaHistory`

**File:** [`src/Tables/ReplicaHistory.php`](../../../src/Tables/ReplicaHistory.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### `createLeaf(array $apiResponseRecord, string $cosignature, FediE2EE\PKD\Crypto\Merkle\InclusionProof $proof): FediE2EE\PKDServer\Tables\Records\ReplicaLeaf`

**Parameters:**

- `$apiResponseRecord`: `array`
- `$cosignature`: `string`
- `$proof`: `FediE2EE\PKD\Crypto\Merkle\InclusionProof`

#### `save(FediE2EE\PKDServer\Tables\Records\Peer $peer, FediE2EE\PKDServer\Tables\Records\ReplicaLeaf $leaf): void`

**Parameters:**

- `$peer`: `FediE2EE\PKDServer\Tables\Records\Peer`
- `$leaf`: `FediE2EE\PKDServer\Tables\Records\ReplicaLeaf`

---

## ReplicaPublicKeys

**class** `FediE2EE\PKDServer\Tables\ReplicaPublicKeys`

**File:** [`src/Tables/ReplicaPublicKeys.php`](../../../src/Tables/ReplicaPublicKeys.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

---

## TOTP

**class** `FediE2EE\PKDServer\Tables\TOTP`

**File:** [`src/Tables/TOTP.php`](../../../src/Tables/TOTP.php)

**Extends:** `FediE2EE\PKDServer\Table`

### Methods

#### `getCipher(): FediE2EE\PKDServer\Dependency\WrappedEncryptedRow`

**Attributes:** `#[Override]`

#### `getSecretByDomain(string $domain): ?string`

**Parameters:**

- `$domain`: `string`

**Throws:**

- `CipherSweetException`
- `CryptoOperationException`
- `SodiumException`
- `InvalidCiphertextException`

#### `saveSecret(string $domain, string $secret): void`

**Parameters:**

- `$domain`: `string`
- `$secret`: `string`

**Throws:**

- `ArrayKeyException`
- `CipherSweetException`
- `CryptoOperationException`
- `RandomException`
- `SodiumException`
- `TableException`

#### `deleteSecret(string $domain): void`

**Parameters:**

- `$domain`: `string`

#### `updateSecret(string $domain, string $secret): void`

**Parameters:**

- `$domain`: `string`
- `$secret`: `string`

**Throws:**

- `ArrayKeyException`
- `CipherSweetException`
- `CryptoOperationException`
- `SodiumException`
- `TableException`
- `RandomException`

---

