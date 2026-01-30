<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Scheduled;

use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    ProtocolException,
    ScheduledTaskException,
    TableException
};
use DateMalformedStringException;
use DateTimeImmutable;
use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    HttpSignatureException,
    JsonException,
    NotImplementedException
};
use FediE2EE\PKD\Crypto\{
    HttpSignature,
    Merkle\InclusionProof,
    PublicKey
};
use FediE2EE\PKD\Crypto\Protocol\{
    Cosignature,
    HistoricalRecord
};
use FediE2EE\PKDServer\ServerConfig;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use FediE2EE\PKDServer\Tables\Records\Peer;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use FediE2EE\PKDServer\Tables\{
    Peers,
    ReplicaActors,
    ReplicaAuxData,
    ReplicaHistory,
    ReplicaPublicKeys
};
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Monolog\Logger;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\EasyDB\EasyDB;
use Random\RandomException;
use SodiumException;
use Throwable;
use function count, is_array, is_string, json_decode, json_last_error_msg, random_bytes, substr, urlencode;

/**
 * Perform witness co-signatures for third-porty Public Key Directory instances.
 */
class Witness
{
    use ConfigTrait;
    private readonly EasyDB $db;
    private readonly Client $http;
    private readonly Logger $logger;
    private readonly Peers $peers;

    private readonly ReplicaActors $replicaActors;

    // @phpstan-ignore property.onlyWritten
    private readonly ReplicaAuxData $replicaAuxData;
    private readonly ReplicaHistory $replicaHistory;
    private readonly ReplicaPublicKeys $replicaPublicKeys;
    private readonly HttpSignature $rfc9421;

    /**
     * @throws CacheException
     * @throws DependencyException
     * @throws TableException
     */
    public function __construct(?ServerConfig $config)
    {
        $this->config = $config;
        $this->db = $config->getDB();
        $peers = $this->table('Peers');
        if (!($peers instanceof Peers)) {
            throw new TableException('Could not load table class for Peers');
        }
        $this->peers = $peers;
        $this->http = $this->config->getGuzzle();
        $this->logger = $this->config->getLogger();
        $this->rfc9421 = new HttpSignature();

        // For replication support:
        $replicaActors = $this->table('ReplicaActors');
        if (!($replicaActors instanceof ReplicaActors)) {
            throw new TableException('Could not load table class for Replica Actors');
        }
        $this->replicaActors = $replicaActors;
        $replicaAuxData = $this->table('ReplicaAuxData');
        if (!($replicaAuxData instanceof ReplicaAuxData)) {
            throw new TableException('Could not load table class for Replica AuxData');
        }
        $this->replicaAuxData = $replicaAuxData;
        $replicaHistory = $this->table('ReplicaHistory');
        if (!($replicaHistory instanceof ReplicaHistory)) {
            throw new TableException('Could not load table class for Replica History');
        }
        $this->replicaHistory = $replicaHistory;
        $replicaPublicKeys = $this->table('ReplicaPublicKeys');
        if (!($replicaPublicKeys instanceof ReplicaPublicKeys)) {
            throw new TableException('Could not load table class for Replica PublicKeys');
        }
        $this->replicaPublicKeys = $replicaPublicKeys;
    }

    /**
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws SodiumException
     */
    public function run(): void
    {
        foreach ($this->peers->listAll() as $peer) {
            try {
                $this->witnessFor($peer);
            } catch (Throwable $ex) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $this->logger->error($ex->getMessage(), $ex->getTrace());
            }
        }
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws HttpSignatureException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws ScheduledTaskException
     * @throws SodiumException
     * @throws TableException
     */
    protected function witnessFor(Peer $peer): void
    {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }
        if (!$peer->cosign && !$peer->replicate) {
            throw new ScheduledTaskException('Neither cosigning nor replication are enabled for this peer');
        }
        // Try to lock the table in case another process hits it too:
        switch ($this->db->getDriver()) {
            case 'pgsql':
            case 'mysql':
                $this->db->beginTransaction();
                $this->db->cell(
                    "SELECT incrementaltreestate FROM pkd_peers WHERE peerid = ? FOR UPDATE",
                    $peer->primaryKey
                );
                break;
            case "sqlite":
                $this->db->exec("PRAGMA busy_timeout=5000");
                $this->db->beginTransaction();
                $this->db->cell(
                    "SELECT incrementaltreestate FROM pkd_peers WHERE peerid = ?",
                    $peer->primaryKey
                );
                break;
            default:
                throw new NotImplementedException('Database driver support not implemented');
        }
        if (!$this->db->inTransaction()) {
            throw new DependencyException('DB transaction failed');
        }

        // Let's begin by fetching some hashes since the latest
        $response1 = $this->getHashesSince($peer);
        if (count($response1['records']) < 1) {
            // We have nothing else to do here:
            $peer->modified = new DateTimeImmutable('NOW');
            $this->peers->save($peer);
            $this->db->commit();
            return;
        }

        // Let's verify then cosign the Merkle tree:
        $cosignature = new Cosignature($peer->tree);
        foreach ($response1['records'] as $record) {
            try {
                $expectedMerkleRoot = $record['merkle-root'];
                $historical = new HistoricalRecord(
                    $record['encrypted-message'],
                    $record['publickeyhash'],
                    $record['signature'],
                );
                $cosignature->append($historical, $expectedMerkleRoot);
                $inclusionProof = $cosignature
                    ->getTree()
                    ->getInclusionProof(
                        $historical->serializeForMerkle()
                    );
            } catch (Throwable $ex) {
                // Log error, bail out;
                $this->logger->error($ex->getMessage(), $ex->getTrace());
                $this->db->rollBack();
                return;
            }
            // Let's calculate a cosignature:
            $cosigned = $cosignature->cosign(
                $this->config->getSigningKeys()->secretKey,
                $this->config->getParams()->hostname
            );

            // If we are sharing the cosignature upstream, let's toss it forward
            if ($peer->cosign) {
                if (!$this->cosign($peer, $cosigned, $expectedMerkleRoot)) {
                    // We had an invalid response:
                    $this->db->rollBack();
                    throw new CryptoException('Invalid HTTP Signature from peer response');
                }
            }
            // If we are replicating the contents of the source Public Key Directory server:
            if ($peer->replicate) {
                if (!$this->replicate($peer, $record, $cosigned, $inclusionProof)) {
                    $this->db->rollBack();
                    throw new CryptoException('Replication failed and no other exception was thrown');
                }
            }
            // Save progress:
            $peer->tree = $cosignature->getTree();
            $this->peers->save($peer);
            $this->db->commit();
        }
    }

    /**
     * @throws GuzzleException
     * @throws HttpSignatureException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    protected function cosign(Peer $peer, string $cosigned, string $expectedMerkleRoot): bool
    {
        // Let's send the cosignature to the peer:
        $response = $this->http->request(
            'POST',
            'https://' . $peer->hostname . '/api/history/cosign/' . urlencode($expectedMerkleRoot),
            [
                'json' => [
                    'witness' => $this->config->getParams()->hostname,
                    'cosigned' => $cosigned,
                ]
            ]
        );
        return $this->rfc9421->verify($peer->publicKey, $response);
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    protected function replicate(
        Peer $peer,
        array $record,
        string $cosigned,
        InclusionProof $proof
    ): bool {
        // 1. Create a Merkle Leaf
        $leaf = $this->replicaHistory->createLeaf($record, $cosigned, $proof);
        $this->replicaHistory->save($peer, $leaf);

        // 2. Check if we have decrypted message from source server
        // The source server provides the decrypted message contents in 'message'
        $decryptedMessage = $record['message'] ?? null;
        if (!is_array($decryptedMessage) || empty($decryptedMessage)) {
            // No decrypted message available, skip action processing
            return true;
        }

        // 3. Get the action type from the decrypted message
        $action = $decryptedMessage['action'] ?? null;
        if (empty($action) || !is_string($action)) {
            return true;
        }

        // 4. Process the action with decrypted message data
        $leafId = $this->getReplicaLeafId($peer, $leaf->root);
        $this->processReplicatedAction($peer, $action, $decryptedMessage, $leafId);

        return true;
    }

    /**
     * Process the replicated action and update replica tables.
     *
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     * @throws TableException
     * @throws RandomException
     */
    protected function processReplicatedAction(
        Peer $peer,
        string $action,
        array $message,
        int $leafId
    ): void {
        match ($action) {
            'AddKey' =>
                $this->processAddKey($peer, $message, $leafId),
            'RevokeKey' =>
                $this->processRevokeKey($peer, $message, $leafId),
            'RevokeKeyThirdParty' =>
                $this->processRevokeKeyThirdParty($peer, $message, $leafId),
            'AddAuxData' =>
                $this->processAddAuxData($peer, $message, $leafId),
            'RevokeAuxData' =>
                $this->processRevokeAuxData($peer, $message, $leafId),
            'Fireproof' =>
                $this->processFireproof($peer, $message),
            'UndoFireproof' =>
                $this->processUndoFireproof($peer, $message),
            'MoveIdentity' =>
                $this->processMoveIdentity($peer, $message, $leafId),
            'BurnDown' =>
                $this->processBurnDown($peer, $message),
            'Checkpoint' =>
                null, // Checkpoints don't affect replica data
            default =>
                $this->logger->warning("Unknown action for replication: {$action}"),
        };
    }

    /**
     * @throws TableException
     */
    protected function getReplicaLeafId(Peer $peer, string $root): int
    {
        $id = $this->db->cell(
            "SELECT replicahistoryid FROM pkd_replica_history WHERE peer = ? AND root = ?",
            $peer->getPrimaryKey(),
            $root
        );
        return (int) $id;
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     * @throws TableException
     * @throws RandomException
     */
    protected function processAddKey(Peer $peer, array $message, int $leafId): void
    {
        $actor = $message['actor'] ?? '';
        $publicKeyStr = $message['public-key'] ?? '';
        if (empty($actor) || empty($publicKeyStr)) {
            return;
        }
        $peerID = $peer->getPrimaryKey();
        if (!$peer->hasPrimaryKey()) {
            return;
        }

        // Find or create actor
        $replicaActor = $this->replicaActors->searchForActor($peer->getPrimaryKey(), $actor);
        if ($replicaActor === null) {
            $actorId = $this->replicaActors->createSimpleForPeer($peer, $actor);
        } else {
            $actorId = $replicaActor->primaryKey;
        }

        // Generate a new key_id for this replica (server-generated, not from protocol)
        $keyId = Base64UrlSafe::encodeUnpadded(random_bytes(32));

        // Get the blind index for the public key
        $cipher = $this->replicaPublicKeys->getCipher();
        [$encrypted, $indexes] = $cipher->prepareRowForStorage([
            'peer' => $peerID,
            'actor' => $actorId,
            'publickey' => $publicKeyStr,
            'key_id' => $keyId,
            'insertleaf' => $leafId,
            'trusted' => true,
        ]);
        $encrypted['publickey_idx'] = (string) $indexes['publickey_idx'];
        $this->db->insert('pkd_replica_actors_publickeys', $encrypted);
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     * @throws TableException
     */
    protected function processRevokeKey(Peer $peer, array $message, int $leafId): void
    {
        $actor = $message['actor'] ?? '';
        $publicKeyStr = $message['public-key'] ?? '';
        if (empty($actor) || empty($publicKeyStr)) {
            return;
        }

        $replicaActor = $this->replicaActors->searchForActor($peer->getPrimaryKey(), $actor);
        if ($replicaActor === null) {
            return; // Actor not found, nothing to revoke
        }

        // Look up the key by its public key content via blind index
        $cipher = $this->replicaPublicKeys->getCipher();
        $bi = $cipher->getBlindIndex('publickey_idx', ['publickey' => $publicKeyStr]);

        $this->db->update(
            'pkd_replica_actors_publickeys',
            [
                'trusted' => false,
                'revokeleaf' => $leafId,
            ],
            [
                'peer' => $peer->getPrimaryKey(),
                'actor' => $replicaActor->primaryKey,
                'publickey_idx' => $bi,
            ]
        );
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws SodiumException
     * @throws TableException
     */
    protected function processRevokeKeyThirdParty(Peer $peer, array $message, int $leafId): void
    {
        // RevokeKeyThirdParty contains a revocation token with the public key embedded
        $token = $message['revocation-token'] ?? '';
        if (empty($token)) {
            return;
        }
        $decoded = Base64UrlSafe::decodeNoPadding($token);
        $pkStart = 8 + 32 + 17; // 57 bytes
        $pkBytes = substr($decoded, $pkStart, 32);
        $publicKey = new PublicKey($pkBytes);

        // Look up the key by its public key content via blind index
        $cipher = $this->replicaPublicKeys->getCipher();
        $bi = $cipher->getBlindIndex('publickey_idx', ['publickey' => $publicKey->toString()]);

        // Find all actors with this key and revoke it
        $rows = $this->db->run(
            "SELECT actor FROM pkd_replica_actors_publickeys
             WHERE peer = ? AND publickey_idx = ? AND trusted",
            $peer->getPrimaryKey(),
            $bi
        );

        foreach ($rows as $row) {
            $this->db->update(
                'pkd_replica_actors_publickeys',
                [
                    'trusted' => false,
                    'revokeleaf' => $leafId,
                ],
                [
                    'peer' => $peer->getPrimaryKey(),
                    'actor' => $row['actor'],
                    'publickey_idx' => $bi,
                ]
            );
        }
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     * @throws TableException
     */
    protected function processAddAuxData(Peer $peer, array $message, int $leafId): void
    {
        $actor = $message['actor'] ?? '';
        $auxType = $message['aux-type'] ?? '';
        $auxData = $message['aux-data'] ?? '';
        if (empty($actor) || empty($auxType)) {
            return;
        }

        $replicaActor = $this->replicaActors->searchForActor($peer->getPrimaryKey(), $actor);
        if ($replicaActor === null) {
            $actorId = $this->replicaActors->createSimpleForPeer($peer, $actor);
        } else {
            $actorId = $replicaActor->primaryKey;
        }

        $this->db->insert('pkd_replica_actors_auxdata', [
            'peer' => $peer->getPrimaryKey(),
            'actor' => $actorId,
            'auxdatatype' => $auxType,
            'auxdata' => $auxData,
            'insertleaf' => $leafId,
            'trusted' => true,
        ]);
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     * @throws TableException
     */
    protected function processRevokeAuxData(Peer $peer, array $message, int $leafId): void
    {
        $actor = $message['actor'] ?? '';
        $auxType = $message['aux-type'] ?? '';
        if (empty($actor) || empty($auxType)) {
            return;
        }

        $replicaActor = $this->replicaActors->searchForActor($peer->getPrimaryKey(), $actor);
        if ($replicaActor === null) {
            return;
        }

        $this->db->update(
            'pkd_replica_actors_auxdata',
            [
                'trusted' => false,
                'revokeleaf' => $leafId,
            ],
            [
                'peer' => $peer->getPrimaryKey(),
                'actor' => $replicaActor->primaryKey,
                'auxdatatype' => $auxType,
            ]
        );
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     * @throws TableException
     */
    protected function processFireproof(Peer $peer, array $message): void
    {
        $actor = $message['actor'] ?? '';
        if (empty($actor)) {
            return;
        }

        $replicaActor = $this->replicaActors->searchForActor($peer->getPrimaryKey(), $actor);
        if ($replicaActor === null) {
            return;
        }

        $this->db->update(
            'pkd_replica_actors',
            ['fireproof' => true],
            ['peer' => $peer->getPrimaryKey(), 'replicaactorid' => $replicaActor->primaryKey]
        );
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     * @throws TableException
     */
    protected function processUndoFireproof(Peer $peer, array $message): void
    {
        $actor = $message['actor'] ?? '';
        if (empty($actor)) {
            return;
        }

        $replicaActor = $this->replicaActors->searchForActor($peer->getPrimaryKey(), $actor);
        if ($replicaActor === null) {
            return;
        }

        $this->db->update(
            'pkd_replica_actors',
            ['fireproof' => false],
            ['peer' => $peer->getPrimaryKey(), 'replicaactorid' => $replicaActor->primaryKey]
        );
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     * @throws TableException
     */
    protected function processMoveIdentity(Peer $peer, array $message, int $leafId): void
    {
        $oldActor = $message['old-actor'] ?? '';
        $newActor = $message['new-actor'] ?? '';
        if (empty($oldActor) || empty($newActor)) {
            return;
        }

        // Find old actor
        $oldReplicaActor = $this->replicaActors->searchForActor($peer->getPrimaryKey(), $oldActor);
        if ($oldReplicaActor === null) {
            return;
        }

        // Create new actor
        $newActorId = $this->replicaActors->createSimpleForPeer($peer, $newActor);

        // Move keys to new actor
        $this->db->update(
            'pkd_replica_actors_publickeys',
            ['actor' => $newActorId],
            ['peer' => $peer->getPrimaryKey(), 'actor' => $oldReplicaActor->primaryKey, 'trusted' => true]
        );

        // Move aux data to new actor
        $this->db->update(
            'pkd_replica_actors_auxdata',
            ['actor' => $newActorId],
            ['peer' => $peer->getPrimaryKey(), 'actor' => $oldReplicaActor->primaryKey, 'trusted' => true]
        );
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws SodiumException
     * @throws TableException
     */
    protected function processBurnDown(Peer $peer, array $message): void
    {
        $actor = $message['actor'] ?? '';
        if (empty($actor)) {
            return;
        }

        $replicaActor = $this->replicaActors->searchForActor($peer->getPrimaryKey(), $actor);
        if ($replicaActor === null) {
            return;
        }

        // BurnDown: revoke all keys and aux data for this actor
        $this->db->update(
            'pkd_replica_actors_publickeys',
            ['trusted' => false],
            ['peer' => $peer->getPrimaryKey(), 'actor' => $replicaActor->primaryKey]
        );
        $this->db->update(
            'pkd_replica_actors_auxdata',
            ['trusted' => false],
            ['peer' => $peer->getPrimaryKey(), 'actor' => $replicaActor->primaryKey]
        );
    }

    /**
     * @throws CryptoException
     * @throws GuzzleException
     * @throws HttpSignatureException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     */
    public function getHashesSince(Peer $peer): array
    {
        $response = $this->http->get(
            'https://' . $peer->hostname . '/api/history/since/' . urlencode($peer->latestRoot)
        );
        if (!$this->rfc9421->verify($peer->publicKey, $response)) {
            throw new CryptoException('Invalid HTTP Signature from peer response');
        }
        $body = $response->getBody()->getContents();
        if (!$body) {
            throw new ProtocolException('Invalid response body');
        }
        $json = json_decode($body, true);
        if (!is_array($json)) {
            throw new JsonException('Invalid JSON response: ' . json_last_error_msg());
        }
        return $json;
    }
}
