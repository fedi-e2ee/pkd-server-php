<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Exceptions\{
    BundleException,
    CryptoException,
    JsonException,
    NotImplementedException
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    ConcurrentException,
    DependencyException,
    ProtocolException,
    TableException
};
use DateMalformedStringException;
use FediE2EE\PKDServer\{
    Interfaces\LimitingHandlerInterface,
    Meta\Route,
    Protocol,
    Traits\ReqTrait
};
use JsonException as BaseJsonException;
use Laminas\Diactoros\Response;
use Override;
use ParagonIE\HPKE\HPKEException;
use Random\RandomException;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;
use SodiumException;

class Revoke implements RequestHandlerInterface, LimitingHandlerInterface
{
    use ReqTrait;

    protected Protocol $protocol;

    /**
     * @throws DependencyException
     */
    public function __construct()
    {
        $this->protocol = new Protocol($this->config());
    }

    /**
     * @throws BaseJsonException
     * @throws BundleException
     * @throws CacheException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("/api/burndown")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = (string) $request->getBody();
        if ($this->protocol->revokeKeyThirdParty($body)) {
            return $this->json([
                '!pkd-context' => 'fedi-e2ee:v1/api/revoke',
                'time' => $this->time(),
            ]);
        }
        // We just return an empty response for now:
        return new Response('php://memory', 204);
    }

    #[Override]
    public function getEnabledRateLimits(): array
    {
        return ['ip'];
    }
}
