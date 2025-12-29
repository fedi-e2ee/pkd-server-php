BEGIN TRANSACTION;

-- A table with one row so we can lock its state:
CREATE TABLE IF NOT EXISTS pkd_merkle_state (
    merkle_state TEXT
);

-- The leaves of the Merkle tree. Sequential.
CREATE TABLE IF NOT EXISTS pkd_merkle_leaves (
    merkleleafid INTEGER PRIMARY KEY AUTOINCREMENT,
    root TEXT UNIQUE,
    publickeyhash TEXT, -- SHA256 of public key that committed to merkle tree
    contenthash TEXT, -- SHA256 of contents. Not the leaf hash.
    signature TEXT, -- Ed25519 signature of contenthash and publickey
    contents TEXT, -- Protocol Message being hashes
    inclusionproof TEXT, -- JSON: encodes a proof of inclusion
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX IF NOT EXISTS pkd_merkle_leaves_idx ON pkd_merkle_leaves (publickeyhash, contenthash, signature);

-- Transparency Log Witnesses
CREATE TABLE IF NOT EXISTS pkd_merkle_witnesses (
    witnessid INTEGER PRIMARY KEY AUTOINCREMENT,
    origin TEXT,
    publickey TEXT,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- See: https://github.com/C2SP/C2SP/blob/main/tlog-witness.md
-- https://github.com/C2SP/C2SP/blob/main/tlog-cosignature.md
CREATE TABLE IF NOT EXISTS pkd_merkle_witness_cosignatures (
    cosignatureid INTEGER PRIMARY KEY AUTOINCREMENT,
    leaf INTEGER,
    witness INTEGER,
    cosignature TEXT,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leaf) REFERENCES pkd_merkle_leaves (merkleleafid),
    FOREIGN KEY (witness) REFERENCES pkd_merkle_witnesses (witnessid)
);

-- Actors contain an activitypubid (profile ID from activitypub spec) and RFC 9421 public key
CREATE TABLE IF NOT EXISTS pkd_actors (
    actorid INTEGER PRIMARY KEY AUTOINCREMENT,
    activitypubid TEXT, -- Encrypted, client-side
    activitypubid_idx TEXT, -- Blind index
    rfc9421pubkey TEXT,
    wrap_activitypubid TEXT NULL, -- Wrapped symmetric key for the activitypubid field
    fireproof BOOLEAN DEFAULT 0,
    fireproofleaf INTEGER NULL,
    undofireproofleaf INTEGER NULL,
    movedleaf INTEGER NULL,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fireproofleaf) REFERENCES pkd_merkle_leaves (merkleleafid),
    FOREIGN KEY (undofireproofleaf) REFERENCES pkd_merkle_leaves (merkleleafid),
    FOREIGN KEY (movedleaf) REFERENCES pkd_merkle_leaves (merkleleafid)
);
CREATE UNIQUE INDEX IF NOT EXISTS pkd_actors_activitypubid_idx ON pkd_actors(activitypubid);
CREATE INDEX IF NOT EXISTS pkd_actors_activitypubid_bi_idx ON pkd_actors(activitypubid_idx);

-- Public Keys
CREATE TABLE IF NOT EXISTS pkd_actors_publickeys (
    actorpublickeyid INTEGER PRIMARY KEY AUTOINCREMENT,
    actorid INTEGER,
    keyid TEXT UNIQUE,
    publickey TEXT, -- Encrypted, client-side
    publickey_idx TEXT, -- Blind index, used for searching
    wrap_publickey TEXT NULL, -- Wrapped symmetric key for the publickey field
    key_id TEXT NULL, -- Unique, chosen by server
    insertleaf INTEGER,
    revokeleaf INTEGER NULL,
    trusted BOOLEAN DEFAULT 0,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (actorid) REFERENCES pkd_actors (actorid),
    FOREIGN KEY (insertleaf) REFERENCES pkd_merkle_leaves (merkleleafid),
    FOREIGN KEY (revokeleaf) REFERENCES pkd_merkle_leaves (merkleleafid)
);
CREATE INDEX IF NOT EXISTS pkd_actors_publickeys_actorid_publickey_idx ON pkd_actors_publickeys (actorid, publickey_idx);

CREATE TABLE IF NOT EXISTS pkd_actors_auxdata (
    actorauxdataid INTEGER PRIMARY KEY AUTOINCREMENT,
    actorid INTEGER,
    auxdatatype TEXT,
    auxdata TEXT, -- Encrypted, client-side
    wrap_auxdata TEXT NULL, -- Wrapped symmetric key for the auxdata field
    auxdata_idx TEXT, -- Blind index
    insertleaf INTEGER,
    revokeleaf INTEGER NULL,
    trusted BOOLEAN DEFAULT 0,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (actorid) REFERENCES pkd_actors (actorid),
    FOREIGN KEY (insertleaf) REFERENCES pkd_merkle_leaves (merkleleafid),
    FOREIGN KEY (revokeleaf) REFERENCES pkd_merkle_leaves (merkleleafid)
);
CREATE INDEX IF NOT EXISTS pkd_actors_auxdata_auxdatatype_idx ON pkd_actors_auxdata (auxdatatype);
CREATE INDEX IF NOT EXISTS pkd_actors_auxdata_actorid_auxdatatype_idx ON pkd_actors_auxdata (actorid, auxdatatype);

CREATE TABLE IF NOT EXISTS pkd_totp_secrets (
    totpid INTEGER PRIMARY KEY AUTOINCREMENT,
    domain TEXT,
    secret TEXT,
    wrap_secret TEXT NULL,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pkd_activitystream_queue (
    queueid INTEGER PRIMARY KEY AUTOINCREMENT,
    message TEXT,
    processed BOOLEAN DEFAULT FALSE,
    successful BOOLEAN DEFAULT FALSE,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pkd_log (
    logid INTEGER PRIMARY KEY AUTOINCREMENT,
    channel INTEGER,
    level INTEGER,
    message TEXT,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pkd_peers (
    peerid INTEGER PRIMARY KEY AUTOINCREMENT,
    hostname TEXT,
    publickey TEXT,
    incrementaltreestate TEXT,
    latestroot TEXT,
    replicate BOOLEAN DEFAULT FALSE,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Update modification time triggers
CREATE TRIGGER IF NOT EXISTS update_pkd_actors_modtime
    AFTER UPDATE ON pkd_actors
    FOR EACH ROW
BEGIN
    UPDATE pkd_actors SET modified = CURRENT_TIMESTAMP WHERE actorid = OLD.actorid;
END;

CREATE TRIGGER IF NOT EXISTS update_pkd_actors_publickeys_modtime
    AFTER UPDATE ON pkd_actors_publickeys
    FOR EACH ROW
BEGIN
    UPDATE pkd_actors_publickeys SET modified = CURRENT_TIMESTAMP WHERE actorpublickeyid = OLD.actorpublickeyid;
END;

CREATE TRIGGER IF NOT EXISTS update_pkd_actors_auxdata_modtime
    AFTER UPDATE ON pkd_actors_auxdata
    FOR EACH ROW
BEGIN
    UPDATE pkd_actors_auxdata SET modified = CURRENT_TIMESTAMP WHERE actorauxdataid = OLD.actorauxdataid;
END;

COMMIT;