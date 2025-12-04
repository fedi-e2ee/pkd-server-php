<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Exceptions\NotImplementedException;
use FediE2EE\PKDServer\{
    Meta\Route,
    Traits\ReqTrait
};
use Override;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};

class Checkpoint implements RequestHandlerInterface
{
    use ReqTrait;

    #[Route("/api/checkpoint")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new NotImplementedException('This feature has not been implemented yet.');
    }
}
