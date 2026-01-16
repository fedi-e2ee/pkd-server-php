<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use DateTime;
use FediE2EE\PKDServer\Protocol\KeyWrapping;
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

class HistoryView implements RequestHandlerInterface
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
    #[Route("/api/history/view/{hash}")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $hash = $request->getAttribute('hash') ?? '';
        if (empty($hash)) {
            return $this->error('No hash provided', 400);
        }
        $leaf = $this->merkleState->getLeafByRoot($hash);
        if (is_null($leaf)) {
            return $this->error('Not found', 404);
        }
        [$message, $rewrappedKeys] = (new KeyWrapping($this->config()))
            ->decryptAndGetRewrapped($hash, $leaf->wrappedKeys);
        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/history/view',
            'created' => $leaf->created,
            'encrypted-message' => $leaf->contents,
            'inclusion-proof' => $leaf->inclusionProof,
            'message' => $message,
            'merkle-root' => $hash,
            'rewrapped-keys' => $rewrappedKeys,
            'witnesses' => $this->merkleState->getCosignatures($leaf->primaryKey),
        ]);
    }
}
