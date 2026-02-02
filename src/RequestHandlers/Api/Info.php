<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Exceptions\{
    NotImplementedException
};
use FediE2EE\PKDServer\{
    Exceptions\DependencyException,
    Meta\Route,
    Traits\ReqTrait
};
use JsonException;
use Override;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};
use SodiumException;

class Info implements RequestHandlerInterface
{
    use ReqTrait;

    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    #[Route("/api/info")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $keys = $this->config()->getSigningKeys();
        $params = $this->config()->getParams();
        $actor = $params->actorUsername . '@' . $params->hostname;
        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/info',
            'current-time' => $this->time(),
            'actor' => $actor,
            'public-key' => $keys->publicKey->toString(),
        ]);
    }
}
