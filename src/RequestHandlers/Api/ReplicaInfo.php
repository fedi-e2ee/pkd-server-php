<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use DateMalformedStringException;
use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    JsonException,
    NotImplementedException
};
use FediE2EE\PKDServer\{
    Meta\Route,
    Redirect,
    Traits\ReqTrait
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    TableException
};
use FediE2EE\PKDServer\Tables\{
    Peers,
    ReplicaActors,
    ReplicaAuxData,
    ReplicaHistory,
    ReplicaPublicKeys
};
use FediE2EE\PKDServer\Tables\Records\Peer;
use JsonException as BaseJsonException;
use Override;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};
use SodiumException;
use TypeError;

class ReplicaInfo implements RequestHandlerInterface
{
    use ReqTrait;

    /**
     * @throws CacheException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws SodiumException
     * @throws TableException
     */
    protected function getPeer(ServerRequestInterface $request): Peer
    {
        $peersTable = $this->table('Peers');
        if (!($peersTable instanceof Peers)) {
            throw new TypeError('peers table is not the right type');
        }
        $uniqueId = $request->getAttribute('replica_id');
        if (empty($uniqueId)) {
            throw new TableException('replica_id is missing');
        }
        $peer = $peersTable->getPeerByUniqueId((string) $uniqueId);
        if (!$peer->replicate) {
            throw new TableException('replication not enabled for peer');
        }
        return $peer;
    }

    /**
     * @throws CacheException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    #[Route("/api/replicas/{replica_id}")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $peer = $this->getPeer($request);
        } catch (TableException) {
            return (new Redirect('/api/replicas'))->respond();
        }
        $encoded = urlencode($peer->uniqueId);
        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/replica-info',
            'time' => $this->time(),
            'replica-urls' => [
                '/api/replicas/' . $encoded . '/actor/:actor_id',
                '/api/replicas/' . $encoded . '/actor/:actor_id/keys',
                '/api/replicas/' . $encoded . '/actor/:actor_id/keys/key/:key_id',
                '/api/replicas/' . $encoded . '/actor/:actor_id/auxiliary',
                '/api/replicas/' . $encoded . '/actor/:actor_id/auxiliary/:aux_data_id',
                '/api/replicas/' . $encoded . '/history',
                '/api/replicas/' . $encoded . '/history/since/:last_hash',
            ]
        ]);
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("/api/replicas/{replica_id}/actor/{actor_id}")]
    public function actor(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $peer = $this->getPeer($request);
        } catch (TableException $ex) {
            return $this->error($ex->getMessage(), 404);
        }
        $actorId = (string) $request->getAttribute('actor_id');

        /** @var ReplicaActors $replicaActors */
        $replicaActors = $this->table('ReplicaActors');
        $actor = $replicaActors->searchForActor($peer->getPrimaryKey(), $actorId);
        if (!$actor) {
            return $this->error('Actor not found', 404);
        }

        $counts = $replicaActors->getCounts($peer->getPrimaryKey(), $actor->getPrimaryKey());
        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/replica/actor/info',
            'actor-id' => $actorId,
            'count-aux' => $counts['count-aux'],
            'count-keys' => $counts['count-keys'],
        ]);
    }

    /**
     * @throws ArrayKeyException
     * @throws BaseJsonException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("/api/replicas/{replica_id}/actor/{actor_id}/keys")]
    public function actorKeys(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $peer = $this->getPeer($request);
        } catch (TableException $ex) {
            return $this->error($ex->getMessage(), 404);
        }
        $actorId = (string) $request->getAttribute('actor_id');

        /** @var ReplicaActors $replicaActors */
        $replicaActors = $this->table('ReplicaActors');
        $actor = $replicaActors->searchForActor($peer->getPrimaryKey(), $actorId);
        if (!$actor) {
            return $this->error('Actor not found', 404);
        }

        /** @var ReplicaPublicKeys $replicaKeys */
        $replicaKeys = $this->table('ReplicaPublicKeys');
        $publicKeys = $replicaKeys->getPublicKeysFor($peer->getPrimaryKey(), $actor->getPrimaryKey());
        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/replica/actor/get-keys',
            'actor-id' => $actorId,
            'public-keys' => $publicKeys,
        ]);
    }

    /**
     * @throws ArrayKeyException
     * @throws BaseJsonException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("/api/replicas/{replica_id}/actor/{actor_id}/keys/key/{key_id}")]
    public function actorKey(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $peer = $this->getPeer($request);
        } catch (TableException $ex) {
            return $this->error($ex->getMessage(), 404);
        }
        $actorId = (string) $request->getAttribute('actor_id');
        $keyId = (string) $request->getAttribute('key_id');

        /** @var ReplicaActors $replicaActors */
        $replicaActors = $this->table('ReplicaActors');
        $actor = $replicaActors->searchForActor($peer->getPrimaryKey(), $actorId);
        if (!$actor) {
            return $this->error('Actor not found', 404);
        }

        /** @var ReplicaPublicKeys $replicaKeys */
        $replicaKeys = $this->table('ReplicaPublicKeys');
        $publicKey = $replicaKeys->lookup($peer->getPrimaryKey(), $actor->getPrimaryKey(), $keyId);
        if (empty($publicKey)) {
            return $this->error('Key not found', 404);
        }

        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/replica/actor/get-key',
            'actor-id' => $actorId,
            ...$publicKey
        ]);
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("/api/replicas/{replica_id}/actor/{actor_id}/auxiliary")]
    public function actorAuxiliary(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $peer = $this->getPeer($request);
        } catch (TableException $ex) {
            return $this->error($ex->getMessage(), 404);
        }
        $actorId = (string) $request->getAttribute('actor_id');

        /** @var ReplicaActors $replicaActors */
        $replicaActors = $this->table('ReplicaActors');
        $actor = $replicaActors->searchForActor($peer->getPrimaryKey(), $actorId);
        if (!$actor) {
            return $this->error('Actor not found', 404);
        }

        /** @var ReplicaAuxData $replicaAuxData */
        $replicaAuxData = $this->table('ReplicaAuxData');
        $auxiliary = $replicaAuxData->getAuxDataForActor($peer->getPrimaryKey(), $actor->getPrimaryKey());
        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/replica/actor/list-auxiliary',
            'actor-id' => $actorId,
            'auxiliary' => $auxiliary,
        ]);
    }

    /**
     * @throws ArrayKeyException
     * @throws BaseJsonException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("/api/replicas/{replica_id}/actor/{actor_id}/auxiliary/{aux_data_id}")]
    public function actorAuxiliaryItem(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $peer = $this->getPeer($request);
        } catch (TableException $ex) {
            return $this->error($ex->getMessage(), 404);
        }
        $actorId = (string) $request->getAttribute('actor_id');
        $auxId = (string) $request->getAttribute('aux_data_id');

        /** @var ReplicaActors $replicaActors */
        $replicaActors = $this->table('ReplicaActors');
        $actor = $replicaActors->searchForActor($peer->getPrimaryKey(), $actorId);
        if (!$actor) {
            return $this->error('Actor not found', 404);
        }

        /** @var ReplicaAuxData $replicaAuxData */
        $replicaAuxData = $this->table('ReplicaAuxData');
        $auxDatum = $replicaAuxData->getAuxDataById($peer->getPrimaryKey(), $actor->getPrimaryKey(), $auxId);
        if (empty($auxDatum)) {
            return $this->error('Auxiliary data not found', 404);
        }

        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/replica/actor/get-auxiliary',
            'actor-id' => $actorId,
            ...$auxDatum
        ]);
    }

    /**
     * @throws BaseJsonException
     * @throws CacheException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("/api/replicas/{replica_id}/history")]
    public function history(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $peer = $this->getPeer($request);
        } catch (TableException $ex) {
            return $this->error($ex->getMessage(), 404);
        }

        /** @var ReplicaHistory $replicaHistory */
        $replicaHistory = $this->table('ReplicaHistory');
        $records = $replicaHistory->getHistory($peer->getPrimaryKey());
        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/replica/history',
            'replica-id' => $peer->uniqueId,
            'records' => $records,
        ]);
    }

    /**
     * @throws BaseJsonException
     * @throws CacheException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("/api/replicas/{replica_id}/history/since/{hash}")]
    public function historySince(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $peer = $this->getPeer($request);
        } catch (TableException $ex) {
            return $this->error($ex->getMessage(), 404);
        }
        $hash = (string) $request->getAttribute('hash');

        /** @var ReplicaHistory $replicaHistory */
        $replicaHistory = $this->table('ReplicaHistory');
        $records = $replicaHistory->getHistorySince($peer->getPrimaryKey(), $hash);
        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/replica/history',
            'replica-id' => $peer->uniqueId,
            'records' => $records,
        ]);
    }
}
