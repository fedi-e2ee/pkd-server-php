<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use DateTime;
use FediE2EE\PKD\Crypto\Exceptions\{
    JsonException,
    NotImplementedException
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    TableException
};
use FediE2EE\PKDServer\Tables\MerkleState;
use SodiumException;
use TypeError;
use FediE2EE\PKDServer\Meta\Route;
use FediE2EE\PKDServer\Traits\ReqTrait;
use Override;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;

class HistorySince implements RequestHandlerInterface
{
    use ReqTrait;

    protected MerkleState $merkleState;

    /**
     * @throws DependencyException
     * @throws TableException
     * @throws CacheException
     */
    public function __construct()
    {
        $merkleState = $this->table('MerkleState');
        if (!($merkleState instanceof MerkleState)) {
            throw new TypeError('Could not load MerkleState table at runtime');
        }
        $this->merkleState = $merkleState;
    }

    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    #[Route("/api/history/since/{hash}")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $lastHash = $request->getAttribute('hash') ?? '';
        if (empty($lastHash)) {
            return $this->error('No hash provided', 400);
        }
        $records = $this->merkleState->getHashesSince($lastHash, 100);
        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/history/since',
            'current-time' => $this->time(),
            'records' => $records,
        ]);
    }
}
