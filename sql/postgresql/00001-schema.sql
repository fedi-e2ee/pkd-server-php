CREATE OR REPLACE FUNCTION update_modtime()
    RETURNS TRIGGER AS $body$
BEGIN
    NEW.modified = NOW();
    RETURN NEW;
END;
$body$ language 'plpgsql';

-- A table with one row so we can lock its state with "SELECT ... FOR UPDATE":
CREATE TABLE IF NOT EXISTS pkd_merkle_state (
    merkle_state TEXT,
    lock_challenge TEXT
);

-- The leaves of the Merkle tree. Sequential.
CREATE TABLE IF NOT EXISTS pkd_merkle_leaves (
    merkleleafid BIGSERIAL PRIMARY KEY,
    root TEXT UNIQUE,
    publickeyhash TEXT, -- SHA256 of public key that committed to merkle tree
    contenthash TEXT, -- SHA256 of contents. Not the leaf hash.
    signature TEXT, -- Ed25519 signature of contenthash and publickey
    contents TEXT, -- Protocol Message being hashes
    inclusionproof TEXT, -- JSON: encodes a proof of inclusion
    created TIMESTAMP DEFAULT NOW()
);
CREATE UNIQUE INDEX ON pkd_merkle_leaves (publickeyhash, contenthash, signature);

-- Transparency Log Witnesses
CREATE TABLE IF NOT EXISTS pkd_merkle_witnesses (
    witnessid BIGSERIAL PRIMARY KEY,
    origin TEXT,
    publickey TEXT,
    created TIMESTAMP DEFAULT NOW()
);

-- See: https://github.com/C2SP/C2SP/blob/main/tlog-witness.md
-- https://github.com/C2SP/C2SP/blob/main/tlog-cosignature.md
CREATE TABLE IF NOT EXISTS pkd_merkle_witness_cosignatures (
    cosignatureid BIGSERIAL PRIMARY KEY,
    leaf BIGINT REFERENCES pkd_merkle_leaves (merkleleafid),
    witness BIGINT REFERENCES pkd_merkle_witnesses (witnessid),
    cosignature TEXT,
    created TIMESTAMP DEFAULT NOW()
);

-- Actors contain an activitypubid (profile ID from activitypub spec) and RFC 9421 public key
CREATE TABLE IF NOT EXISTS pkd_actors (
    actorid BIGSERIAL PRIMARY KEY,
    activitypubid TEXT, -- Encrypted, client-side
    activitypubid_idx TEXT, -- Blind index
    rfc9421pubkey TEXT,
    wrap_activitypubid TEXT NULL, -- Wrapped symmetric key for the activitypubid field
    fireproof BOOLEAN DEFAULT FALSE,
    fireproofleaf BIGINT NULL REFERENCES pkd_merkle_leaves (merkleleafid),
    undofireproofleaf BIGINT NULL REFERENCES pkd_merkle_leaves (merkleleafid),
    movedleaf BIGINT NULL REFERENCES pkd_merkle_leaves (merkleleafid),
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP DEFAULT NOW()
);
CREATE UNIQUE INDEX ON pkd_actors(activitypubid);
CREATE INDEX ON pkd_actors(activitypubid_idx);

-- Public Keys
CREATE TABLE IF NOT EXISTS pkd_actors_publickeys (
    actorpublickeyid BIGSERIAL PRIMARY KEY,
    actorid BIGINT REFERENCES pkd_actors (actorid),
    publickey TEXT, -- Encrypted, client-side
    publickey_idx TEXT, -- Blind index, used for searching
    wrap_publickey TEXT NULL, -- Wrapped symmetric key for the publickey field
    key_id TEXT NULL, -- Unique, chosen by server
    insertleaf BIGINT REFERENCES pkd_merkle_leaves (merkleleafid),
    revokeleaf BIGINT NULL REFERENCES pkd_merkle_leaves (merkleleafid),
    trusted BOOLEAN DEFAULT FALSE,
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP DEFAULT NOW()
);
CREATE INDEX ON pkd_actors_publickeys (actorid, publickey_idx);

CREATE TABLE IF NOT EXISTS pkd_actors_auxdata (
    actorauxdataid BIGSERIAL PRIMARY KEY,
    actorid BIGINT REFERENCES pkd_actors (actorid),
    auxdatatype TEXT,
    auxdata TEXT, -- Encrypted, client-side
    wrap_auxdata TEXT NULL, -- Wrapped symmetric key for the auxdata field
    auxdata_idx TEXT,
    insertleaf BIGINT REFERENCES pkd_merkle_leaves (merkleleafid),
    revokeleaf BIGINT NULL REFERENCES pkd_merkle_leaves (merkleleafid),
    trusted BOOLEAN DEFAULT FALSE,
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP DEFAULT NOW()
);
CREATE INDEX ON pkd_actors_auxdata (auxdatatype);
CREATE INDEX ON pkd_actors_auxdata (auxdata_idx);
CREATE INDEX ON pkd_actors_auxdata (actorid, auxdatatype);

CREATE TABLE IF NOT EXISTS pkd_totp_secrets (
    totpid BIGSERIAL PRIMARY KEY,
    domain TEXT,
    secret TEXT,
    wrap_secret TEXT NULL,
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS pkd_activitystream_queue (
    queueid BIGSERIAL PRIMARY KEY,
    message TEXT,
    processed BOOLEAN DEFAULT FALSE,
    successful BOOLEAN DEFAULT FALSE,
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS pkd_log (
    logid BIGSERIAL PRIMARY KEY,
    channel TEXT,
    level INTEGER,
    message TEXT,
    created TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS pkd_peers (
    peerid BIGSERIAL PRIMARY KEY,
    hostname TEXT,
    publickey TEXT,
    incrementaltreestate TEXT,
    latestroot TEXT,
    replicate BOOLEAN DEFAULT FALSE,
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP DEFAULT NOW()
);

-- Update modification time triggers
DROP TRIGGER IF EXISTS update_pkd_actors_modtime ON pkd_actors;
CREATE TRIGGER update_pkd_actors_modtime
    BEFORE UPDATE ON pkd_actors
    FOR EACH ROW EXECUTE PROCEDURE update_modtime();

DROP TRIGGER IF EXISTS update_pkd_actors_publickeys_modtime ON pkd_actors_publickeys;
CREATE TRIGGER update_pkd_actors_publickeys_modtime
    BEFORE UPDATE ON pkd_actors_publickeys
    FOR EACH ROW EXECUTE PROCEDURE update_modtime();

DROP TRIGGER IF EXISTS update_pkd_actors_auxdata_modtime ON pkd_actors_auxdata;
CREATE TRIGGER update_pkd_actors_auxdata_modtime
    BEFORE UPDATE ON pkd_actors_auxdata
    FOR EACH ROW EXECUTE PROCEDURE update_modtime();
