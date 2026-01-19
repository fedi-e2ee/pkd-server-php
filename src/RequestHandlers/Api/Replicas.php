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
    Exceptions\CacheException,
    Exceptions\DependencyException,
    Exceptions\TableException,
    Meta\Route,
    ServerConfig,
    Tables\Peers,
    Traits\ReqTrait
};
use Override;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};
use SodiumException;
use TypeError;

class Replicas implements RequestHandlerInterface
{
    use ReqTrait;

    protected Peers $peersTable;

    /**
     * @throws CacheException
     * @throws DependencyException
     * @throws TableException
     */
    public function __construct(?ServerConfig $config = null)
    {
        if (is_null($config)) {
            $config = $GLOBALS['pkdConfig'];
        }
        $this->config = $config;
        $peersTable = $this->table('Peers');
        if (!($peersTable instanceof Peers)) {
            throw new TypeError('Wrong table returned');
        }
        $this->peersTable = $peersTable;
    }

    /**
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    #[Route("/api/replicas")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $peers = [];
        foreach ($this->peersTable->listReplicatingPeers() as $peer) {
            $peers [] = [
                'id' => $peer->uniqueId,
                'ref' => 'https://' . $peer->hostname,
            ];
        }
        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/replicas',
            'time' => $this->time(),
            'replicas' => $peers,
        ]);
    }
}
