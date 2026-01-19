<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Exceptions\{
    BundleException,
    CryptoException,
    InputException,
    JsonException,
    NotImplementedException
};
use FediE2EE\PKDServer\AppCache;
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    TableException
};
use FediE2EE\PKDServer\Interfaces\HttpCacheInterface;
use FediE2EE\PKDServer\Meta\Route;
use FediE2EE\PKDServer\Tables\MerkleState;
use FediE2EE\PKDServer\Traits\HttpCacheTrait;
use Override;
use ParagonIE\HPKE\HPKEException;
use Psr\SimpleCache\InvalidArgumentException;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;
use SodiumException;
use TypeError;

class HistorySince implements RequestHandlerInterface, HttpCacheInterface
{
    use HttpCacheTrait;

    protected MerkleState $merkleState;
    protected ?AppCache $cache = null;

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

    #[Override]
    public function getPrimaryCacheKey(): string
    {
        return 'api:history-since';
    }

    /**
     * @throws BundleException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InputException
     * @throws InvalidArgumentException
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
            return $this->error('No hash provided');
        }
        // Cache the history-since response (hot path for replication)
        $response = $this->getCache()->cacheJson(
            $lastHash,
            function () use ($lastHash) {
                $records = $this->merkleState->getHashesSince($lastHash, 100);
                return [
                    '!pkd-context' => 'fedi-e2ee:v1/api/history/since',
                    'current-time' => $this->time(),
                    'records' => $records,
                ];
            }
        );
        return $this->json($response);
    }
}
