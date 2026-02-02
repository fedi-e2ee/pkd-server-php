<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\ActivityPub;

use FediE2EE\PKD\Crypto\Exceptions\NotImplementedException;
use JsonException;
use FediE2EE\PKDServer\{
    Exceptions\DependencyException,
    Meta\Route,
    Traits\ActivityStreamsTrait,
    Traits\ReqTrait
};
use Override;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};
use SodiumException;

class UserPage implements RequestHandlerInterface
{
    use ActivityStreamsTrait;
    use ReqTrait;

    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    #[Route("/user/{user_id}")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->json(['message' => 'this is just a placeholder for now']);
    }
}
