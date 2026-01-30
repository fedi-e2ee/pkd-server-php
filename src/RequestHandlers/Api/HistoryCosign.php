<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Exceptions\{
    JsonException,
    NotImplementedException
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    TableException
};
use FediE2EE\PKDServer\Meta\Route;
use FediE2EE\PKDServer\Tables\MerkleState;
use FediE2EE\PKDServer\Traits\ReqTrait;
use Override;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;
use SodiumException;
use Throwable;
use TypeError;

use function in_array;
use function is_object;
use function json_decode;
use function property_exists;

class HistoryCosign implements RequestHandlerInterface
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
    #[Override]
    #[Route("/history/cosign/{hash}")]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return $this->error('This endpoint only responds to POST requests');
        }
        $merkleRoot = $request->getAttribute('hash') ?? '';
        if (empty($merkleRoot)) {
            return $this->error('No hash provided');
        }

        $contentTypes = $request->getHeader('Content-Type');
        if (!in_array('application/json', $contentTypes, true)) {
            return $this->error('Content-Type must be application/json');
        }
        $body = $request->getBody()->getContents();
        if (empty($body)) {
            return $this->error('Empty body provided');
        }
        $decoded = json_decode($body);
        if (!is_object($decoded)) {
            return $this->error('Invalid JSON request body');
        }
        if (!property_exists($decoded, 'witness')) {
            return $this->error('Required property not found: witness');
        }
        if (!property_exists($decoded, 'cosigned')) {
            return $this->error('Required property not found: cosigned');
        }

        try {
            $status = $this->merkleState->addWitnessCosignature(
                $decoded->witness,
                $merkleRoot,
                $decoded->cosigned
            );
            return $this->json([
                '!pkd-context' => 'fedi-e2ee:v1/api/history/cosign',
                'status' => $status,
                'current-time' => $this->time(),
            ]);
        } catch (Throwable $ex) {
            $this->config->getLogger()->error(
                $ex->getMessage(),
                $ex->getTrace()
            );
            return $this->error($ex->getMessage());
        }
    }
}
