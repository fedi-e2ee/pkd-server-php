-- A table with one row so we can lock its state with "SELECT ... FOR UPDATE":
CREATE TABLE IF NOT EXISTS pkd_merkle_state (
    merkle_state TEXT,
    lock_challenge TEXT
);

-- The leaves of the Merkle tree. Sequential.
CREATE TABLE IF NOT EXISTS pkd_merkle_leaves (
    merkleleafid BIGINT AUTO_INCREMENT PRIMARY KEY,
    root TEXT,
    publickeyhash TEXT, -- SHA256 of public key that committed to merkle tree
    contenthash TEXT, -- SHA256 of contents. Not the leaf hash.
    signature TEXT, -- Ed25519 signature of contenthash and publickey
    contents TEXT, -- Protocol Message being hashes
    inclusionproof TEXT, -- JSON: encodes a proof of inclusion
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (root(255)),
    UNIQUE KEY (publickeyhash(255), contenthash(255), signature(255))
);

-- Transparency Log Witnesses
CREATE TABLE IF NOT EXISTS pkd_merkle_witnesses (
    witnessid BIGINT AUTO_INCREMENT PRIMARY KEY,
    origin TEXT,
    publickey TEXT,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- See: https://github.com/C2SP/C2SP/blob/main/tlog-witness.md
-- https://github.com/C2SP/C2SP/blob/main/tlog-cosignature.md
CREATE TABLE IF NOT EXISTS pkd_merkle_witness_cosignatures (
    cosignatureid BIGINT AUTO_INCREMENT PRIMARY KEY,
    leaf BIGINT,
    witness BIGINT,
    cosignature TEXT,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leaf) REFERENCES pkd_merkle_leaves(merkleleafid),
    FOREIGN KEY (witness) REFERENCES pkd_merkle_witnesses(witnessid)
);

-- Actors contain an activitypubid (profile ID from activitypub spec) and RFC 9421 public key
CREATE TABLE IF NOT EXISTS pkd_actors (
    actorid BIGINT AUTO_INCREMENT PRIMARY KEY,
    activitypubid TEXT, -- Encrypted, client-side
    activitypubid_idx TEXT, -- Blind index
    rfc9421pubkey TEXT,
    wrap_activitypubid TEXT NULL, -- Wrapped symmetric key for the activitypubid field
    fireproof BOOLEAN DEFAULT FALSE,
    fireproofleaf BIGINT NULL,
    undofireproofleaf BIGINT NULL,
    movedleaf BIGINT NULL,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fireproofleaf) REFERENCES pkd_merkle_leaves(merkleleafid),
    FOREIGN KEY (undofireproofleaf) REFERENCES pkd_merkle_leaves(merkleleafid),
    FOREIGN KEY (movedleaf) REFERENCES pkd_merkle_leaves(merkleleafid),
    INDEX `pkd_actors_activitypubid_bi` (activitypubid_idx(255))
);

-- Public Keys
CREATE TABLE IF NOT EXISTS pkd_actors_publickeys (
    actorpublickeyid BIGINT AUTO_INCREMENT PRIMARY KEY,
    actorid BIGINT,
    publickey TEXT, -- Encrypted, client-side
    publickey_idx TEXT, -- Blind index, used for searching
    wrap_publickey TEXT NULL, -- Wrapped symmetric key for the publickey field
    key_id TEXT NULL, -- Unique, chosen by server
    insertleaf BIGINT,
    revokeleaf BIGINT NULL,
    trusted BOOLEAN DEFAULT FALSE,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (actorid) REFERENCES pkd_actors(actorid),
    FOREIGN KEY (insertleaf) REFERENCES pkd_merkle_leaves(merkleleafid),
    FOREIGN KEY (revokeleaf) REFERENCES pkd_merkle_leaves(merkleleafid),
    INDEX `pkd_actors_publickeys_actorid_publickey_idx` (actorid, publickey_idx(255))
);

CREATE TABLE IF NOT EXISTS pkd_actors_auxdata (
    actorauxdataid BIGINT AUTO_INCREMENT PRIMARY KEY,
    actorid BIGINT,
    auxdatatype TEXT,
    auxdata TEXT, -- Encrypted, client-side
    wrap_auxdata TEXT NULL, -- Wrapped symmetric key for the auxdata field
    auxdata_idx TEXT,
    insertleaf BIGINT,
    revokeleaf BIGINT NULL,
    trusted BOOLEAN DEFAULT FALSE,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (actorid) REFERENCES pkd_actors(actorid),
    FOREIGN KEY (insertleaf) REFERENCES pkd_merkle_leaves(merkleleafid),
    FOREIGN KEY (revokeleaf) REFERENCES pkd_merkle_leaves(merkleleafid),
    INDEX `pkd_actors_auxdata_auxdatatype_idx` (auxdatatype(255)),
    INDEX `pkd_actors_auxdata_auxdata_idx` (auxdata_idx(4)),
    INDEX `pkd_actors_auxdata_actorid_auxdatatype_idx` (actorid, auxdatatype(255))
);

CREATE TABLE IF NOT EXISTS pkd_totp_secrets (
    totpid BIGINT PRIMARY KEY AUTO_INCREMENT,
    domain TEXT,
    secret TEXT,
    wrap_secret TEXT NULL,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pkd_activitystream_queue (
    queueid BIGINT PRIMARY KEY AUTO_INCREMENT,
    message TEXT,
    processed BOOLEAN DEFAULT FALSE,
    successful BOOLEAN DEFAULT FALSE,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pkd_log (
    logid BIGINT PRIMARY KEY AUTO_INCREMENT,
    channel TEXT,
    level INTEGER,
    message LONGTEXT,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pkd_peers(
    peerid BIGINT PRIMARY KEY AUTO_INCREMENT,
    uniqueid TEXT NOT NULL,
    hostname TEXT,
    publickey TEXT,
    incrementaltreestate TEXT,
    latestroot TEXT,
    cosign BOOLEAN DEFAULT FALSE,
    replicate BOOLEAN DEFAULT FALSE,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE INDEX `pkd_peers_uniqueid_idx` (uniqueid(255))
);

CREATE TABLE IF NOT EXISTS pkd_replica_history (
    replicahistoryid BIGINT AUTO_INCREMENT PRIMARY KEY,
    peer BIGINT NOT NULL,
    root TEXT,
    publickeyhash TEXT,
    contenthash TEXT,
    signature TEXT,
    contents TEXT,
    cosignature TEXT,
    inclusionproof TEXT,
    created TIMESTAMP NULL,
    replicated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peer) REFERENCES pkd_peers (peerid),
    UNIQUE KEY uk_pkd_replica_history_peer_root (peer, root(255))
);

CREATE TABLE IF NOT EXISTS pkd_replica_actors (
    replicaactorid BIGINT AUTO_INCREMENT PRIMARY KEY,
    peer BIGINT NOT NULL,
    activitypubid TEXT,
    activitypubid_idx TEXT,
    rfc9421pubkey TEXT,
    wrap_activitypubid TEXT,
    fireproof BOOLEAN DEFAULT FALSE,
    fireproofleaf BIGINT,
    undofireproofleaf BIGINT,
    movedleaf BIGINT,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (peer) REFERENCES pkd_peers (peerid),
    FOREIGN KEY (fireproofleaf) REFERENCES pkd_replica_history (replicahistoryid),
    FOREIGN KEY (undofireproofleaf) REFERENCES pkd_replica_history (replicahistoryid),
    FOREIGN KEY (movedleaf) REFERENCES pkd_replica_history (replicahistoryid),
    UNIQUE KEY uk_pkd_replica_actors_peer_activitypubid (peer, activitypubid(255)),
    KEY idx_pkd_replica_actors_peer_activitypubid_idx (peer, activitypubid_idx(255))
);

CREATE TABLE IF NOT EXISTS pkd_replica_actors_publickeys (
    replicaactorpublickeyid BIGINT AUTO_INCREMENT PRIMARY KEY,
    peer BIGINT NOT NULL,
    actor BIGINT NOT NULL,
    publickey TEXT,
    publickey_idx TEXT,
    wrap_publickey TEXT,
    key_id TEXT,
    insertleaf BIGINT,
    revokeleaf BIGINT,
    trusted BOOLEAN DEFAULT FALSE,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (peer) REFERENCES pkd_peers (peerid),
    FOREIGN KEY (actor) REFERENCES pkd_replica_actors (replicaactorid),
    FOREIGN KEY (insertleaf) REFERENCES pkd_replica_history (replicahistoryid),
    FOREIGN KEY (revokeleaf) REFERENCES pkd_replica_history (replicahistoryid),
    UNIQUE KEY uk_pkd_replica_actors_publickeys_peer_publickey (peer, publickey(255)),
    KEY idx_pkd_replica_actors_publickeys_peer_publickey_idx (peer, publickey_idx(255))
);

CREATE TABLE IF NOT EXISTS pkd_replica_actors_auxdata (
    replicaactorauxdataid BIGINT AUTO_INCREMENT PRIMARY KEY,
    peer BIGINT NOT NULL,
    actor BIGINT NOT NULL,
    auxdatatype TEXT,
    auxdata TEXT,
    wrap_auxdata TEXT,
    auxdata_idx TEXT,
    insertleaf BIGINT,
    revokeleaf BIGINT,
    trusted BOOLEAN DEFAULT FALSE,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (peer) REFERENCES pkd_peers (peerid),
    FOREIGN KEY (actor) REFERENCES pkd_replica_actors (replicaactorid),
    FOREIGN KEY (insertleaf) REFERENCES pkd_replica_history (replicahistoryid),
    FOREIGN KEY (revokeleaf) REFERENCES pkd_replica_history (replicahistoryid),
    KEY idx_pkd_replica_actors_auxdata_peer_auxdata_idx (peer, auxdata_idx(255))
);
