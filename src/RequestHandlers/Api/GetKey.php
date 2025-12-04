<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Exceptions\{
    JsonException,
    NotImplementedException
};
use FediE2EE\PKDServer\{
    Exceptions\CacheException,
    Exceptions\DependencyException,
    Exceptions\TableException,
    Meta\Route,
    Redirect,
    Traits\ReqTrait
};
use FediE2EE\PKDServer\Tables\{
    Actors,
    PublicKeys
};
use Override;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;
use SodiumException;
use Throwable;
use TypeError;

class GetKey implements RequestHandlerInterface
{
    use ReqTrait;

    protected Actors $actorsTable;
    protected PublicKeys $publicKeysTable;

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

        $keysTable = $this->table('PublicKeys');
        if (!($keysTable instanceof PublicKeys)) {
            throw new TypeError('Could not load PublicKeys table at runtime');
        }
        $this->publicKeysTable = $keysTable;
    }

    /**
     * @api
     *
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("api/actor/{actor_id}/key/{key_id}")]
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

        // Make sure Key ID is populated:
        $keyID = $request->getAttribute('key_id') ?? '';
        if (empty($keyID)) {
            return (new Redirect('/api/actors/' . urlencode($actorID) . '/keys'))->respond();
        }

        // Ensure actor exists
        $actor = $this->actorsTable->searchForActor($actorID);
        if (is_null($actor)) {
            return $this->error('Actor not found', 404);
        }

        // Lookup the public key
        $pk = $this->publicKeysTable->lookup($actor->getPrimaryKey(), $keyID);
        if (empty($pk)) {
            // Redirect to keys list
            return (new Redirect('/api/actors/' . urlencode($actorID) . '/keys'))->respond();
        }
        $pk['!pkd-context'] = 'fedi-e2ee:v1/api/actor/key-info';
        $pk['actor-id'] = $actorID;
        return $this->json($pk);
    }
}
