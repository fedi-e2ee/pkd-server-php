<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use DateTime;
use FediE2EE\PKD\Crypto\Exceptions\{
    JsonException,
    NotImplementedException
};
use FediE2EE\PKDServer\{Exceptions\DependencyException, Meta\Route, ServerConfig, Traits\ReqTrait};
use Override;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;
use SodiumException;

class Extensions implements RequestHandlerInterface
{
    use ReqTrait;

    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    #[Route("/api/extensions")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/extensions',
            'current-time' => (string) new DateTime()->getTimestamp(),
            'extensions' => $this->config()->getAuxDataTypeAllowList(),
        ]);
    }
}
