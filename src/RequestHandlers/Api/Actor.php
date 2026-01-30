<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    JsonException,
    NotImplementedException
};
use FediE2EE\PKDServer\{
    Exceptions\CacheException,
    Exceptions\DependencyException,
    Exceptions\TableException,
    Interfaces\HttpCacheInterface,
    Meta\Route,
    Redirect,
    Traits\HttpCacheTrait
};
use FediE2EE\PKDServer\Tables\Actors;
use Override;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use Psr\SimpleCache\InvalidArgumentException;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;
use SodiumException;
use Throwable;
use TypeError;

use function is_null;

class Actor implements RequestHandlerInterface, HttpCacheInterface
{
    use HttpCacheTrait;

    protected Actors $actorsTable;

    /**
     * @throws CacheException
     * @throws DependencyException
     * @throws TableException
     */
    public function __construct()
    {
        $actorsTable = $this->table('Actors');
        if (!($actorsTable instanceof Actors)) {
            throw new TypeError('Could not load Actors table at runtime');
        }
        $this->actorsTable = $actorsTable;
    }

    #[Override]
    public function getPrimaryCacheKey(): string
    {
        return 'api:actor';
    }

    /**
     * @api
     *
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("api/actor/{actor_id}")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // If no Actor ID is given, redirect.
        $actorID = $request->getAttribute('actor_id') ?? '';
        if (empty($actorID)) {
            return (new Redirect('/api'))->respond();
        }

        // Resolve canonical Actor ID
        try {
            $actorID = $this->webfinger()->canonicalize($actorID);
        } catch (Throwable $ex) {
            return $this->error('A WebFinger error occurred: ' . $ex->getMessage());
        }

        // Cache actor lookup and counts
        $response = $this->getCache()->cacheJson(
            $actorID,
            function () use ($actorID) {
                $actor = $this->actorsTable->searchForActor($actorID);
                if (is_null($actor)) {
                    return null;
                }
                $counts = $this->actorsTable->getCounts($actor->getPrimaryKey());
                return [
                    '!pkd-context' => 'fedi-e2ee:v1/api/actor/info',
                    'actor-id' => $actorID,
                    'count-aux' => $counts['count-aux'],
                    'count-keys' => $counts['count-keys'],
                ];
            }
        );

        if (is_null($response)) {
            return $this->error('Actor not found', 404);
        }

        return $this->json($response);
    }
}
