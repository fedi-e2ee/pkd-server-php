<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKDServer\{
    AppCache,
    Protocol\KeyWrapping,
};
use FediE2EE\PKD\Crypto\Exceptions\{
    BundleException,
    CryptoException,
    InputException,
    JsonException,
    NotImplementedException
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    TableException
};
use FediE2EE\PKDServer\Interfaces\HttpCacheInterface;
use FediE2EE\PKDServer\Tables\MerkleState;
use ParagonIE\HPKE\HPKEException;
use Psr\SimpleCache\InvalidArgumentException;
use SodiumException;
use TypeError;
use FediE2EE\PKDServer\Meta\Route;
use FediE2EE\PKDServer\Traits\HttpCacheTrait;
use Override;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;

use function is_null;

class HistoryView implements RequestHandlerInterface, HttpCacheInterface
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
        return 'api:history-view';
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
    #[Route("/api/history/view/{hash}")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $hash = $request->getAttribute('hash') ?? '';
        if (empty($hash)) {
            return $this->error('No hash provided', 400);
        }
        // Cache the history view response (hot path for replication)
        $response = $this->getCache()->cacheJson(
            $hash,
            function () use ($hash) {
                $leaf = $this->merkleState->getLeafByRoot($hash);
                if (is_null($leaf)) {
                    return null;
                }
                [$message, $rewrappedKeys] = (new KeyWrapping($this->config()))
                    ->decryptAndGetRewrapped($hash, $leaf->wrappedKeys);
                return [
                    '!pkd-context' => 'fedi-e2ee:v1/api/history/view',
                    'created' => $leaf->created,
                    'encrypted-message' => $leaf->contents,
                    'inclusion-proof' => $leaf->inclusionProof,
                    'message' => $message,
                    'merkle-root' => $hash,
                    'rewrapped-keys' => $rewrappedKeys,
                    'witnesses' => $this->merkleState->getCosignatures($leaf->primaryKey),
                ];
            }
        );
        if (is_null($response)) {
            return $this->error('Not found', 404);
        }
        return $this->json($response);
    }
}
