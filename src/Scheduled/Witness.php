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
use FediE2EE\PKD\Crypto\HttpSignature;
use FediE2EE\PKD\Crypto\Protocol\{
    Cosignature,
    HistoricalRecord
};
use FediE2EE\PKD\Crypto\Merkle\InclusionProof;
use FediE2EE\PKDServer\ServerConfig;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use FediE2EE\PKDServer\Tables\Records\Peer;
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
use ParagonIE\EasyDB\EasyDB;
use SodiumException;
use Throwable;

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
    // @phpstan-ignore property.onlyWritten
    private readonly ReplicaHistory $replicaHistory;
    // @phpstan-ignore property.onlyWritten
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
     * @throws CryptoException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws HttpSignatureException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws ScheduledTaskException
     * @throws SodiumException
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

    protected function replicate(
        Peer $peer,
        array $record,
        string $cosigned,
        InclusionProof $proof
    ): bool {
        // 1. Create a Merkle Leaf
        $leaf = $this->replicaHistory->createLeaf($record, $cosigned, $proof);
        $this->replicaHistory->save($peer, $leaf);
        // 2. Understand what action to take to the replicated data set
        // TODO: Decrypt with rewrapped keys
        // 3. Take the action.
        // TODO: Process Action -> affect subsidiary table
        // 4. Return success if all is well
        return true;
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
