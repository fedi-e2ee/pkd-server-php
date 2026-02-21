<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    JsonException,
    NotImplementedException
};
use DateMalformedStringException;
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
    AuxData
};
use JsonException as BaseJsonException;
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
use function is_null, urlencode;

class GetAuxData implements RequestHandlerInterface
{
    use ReqTrait;

    protected Actors $actorsTable;
    protected AuxData $auxDataTable;

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

        $dataTable = $this->table('AuxData');
        if (!($dataTable instanceof AuxData)) {
            throw new TypeError('Could not load AuxData table at runtime');
        }
        $this->auxDataTable = $dataTable;
    }

    /**
     * @api
     *
     * @throws ArrayKeyException
     * @throws BaseJsonException
     * @throws BlindIndexNotFoundException
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
    #[Route("api/actor/{actor_id}/auxiliary/{aux_data_id}")]
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

        // Make sure Aux Data ID is populated:
        $auxDataID = $request->getAttribute('aux_data_id') ?? '';
        if (empty($auxDataID)) {
            return (new Redirect('/api/actors/' . urlencode($actorID) . '/auxiliary'))->respond();
        }

        // Ensure actor exists
        $actor = $this->actorsTable->searchForActor($actorID);
        if (is_null($actor)) {
            return $this->error('Actor not found', 404);
        }

        // Lookup the auxiliary data
        $auxData = $this->auxDataTable->getAuxDataById($actor->getPrimaryKey(), (string) $auxDataID);
        if (empty($auxData)) {
            return $this->error('Auxiliary data not found', 404);
        }
        $auxData['!pkd-context'] = 'fedi-e2ee:v1/api/actor/get-aux';
        $auxData['actor-id'] = $actorID;
        return $this->json($auxData);
    }
}
