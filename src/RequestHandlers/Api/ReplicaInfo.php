<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKDServer\{
    Exceptions\TableException,
    Meta\Route,
    Redirect,
    Tables\Peers,
    Traits\ReqTrait
};
use Override;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};
use TypeError;

class ReplicaInfo implements RequestHandlerInterface
{
    use ReqTrait;

    #[Route("/api/replicas/{replica_id}")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $peersTable = $this->table('Peers');
        if (!($peersTable instanceof Peers)) {
            throw new TypeError('peers table is not the right type');
        }
        $uniqueId = $request->getAttribute('replica_id');
        if (empty($uniqueId)) {
            return (new Redirect('/api/replicas'))->respond();
        }
        try {
            $peer = $peersTable->getPeerByUniqueId($uniqueId);
        } catch (TableException) {
            return (new Redirect('/api/replicas'))->respond();
        }
        if (!$peer->replicate) {
            $this->error('replication not enabled for peer', 403);
        }
        $encoded = urlencode($uniqueId);
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
}
