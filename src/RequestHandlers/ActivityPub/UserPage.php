<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\ActivityPub;

use FediE2EE\PKDServer\{
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

class UserPage implements RequestHandlerInterface
{
    use ActivityStreamsTrait;
    use ReqTrait;

    #[Route("/user/{user_id}")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->json(['this is just a placeholder for now']);
    }
}
