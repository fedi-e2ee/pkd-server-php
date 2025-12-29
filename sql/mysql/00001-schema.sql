START TRANSACTION;

-- A table with one row so we can lock its state with "SELECT ... FOR UPDATE":
CREATE TABLE IF NOT EXISTS pkd_merkle_state (
    merkle_state TEXT
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
    hostname TEXT,
    publickey TEXT,
    incrementaltreestate TEXT,
    latestroot TEXT,
    replicate BOOLEAN DEFAULT FALSE,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMIT;