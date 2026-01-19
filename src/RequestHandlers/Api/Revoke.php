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
    DependencyException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\{
    Meta\Route,
    Protocol,
    Traits\ReqTrait
};
use Laminas\Diactoros\Response;
use Override;
use ParagonIE\HPKE\HPKEException;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;
use SodiumException;

class Revoke implements RequestHandlerInterface
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
     * @throws BundleException
     * @throws CacheException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("/api/revoke")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $message = (string) $request->getBody();

        try {
            if (!$this->protocol->revokeKeyThirdParty($message)) {
                return $this->signResponse(new Response('php://memory', 404));
            }
        } catch (ProtocolException) {
            // Invalid token
        }
        return $this->signResponse(new Response('php://memory', 204));
    }
}
