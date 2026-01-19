<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    HttpSignatureException,
    JsonException,
    NotImplementedException,
    ParserException
};
use FediE2EE\PKDServer\Exceptions\{
    ActivityPubException,
    CacheException,
    DependencyException,
    FetchException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\{
    Meta\Route,
    Protocol
};
use FediE2EE\PKDServer\Traits\{
    ActivityStreamsTrait,
    ReqTrait
};
use Override;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\HPKE\HPKEException;
use Psr\SimpleCache\InvalidArgumentException;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;
use SodiumException;

class BurnDown implements RequestHandlerInterface
{
    use ActivityStreamsTrait;
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
     * @throws CacheException
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws SodiumException
     * @throws TableException
     * @throws InvalidArgumentException
     */
    #[Route("/api/revoke")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $as = $this->getVerifiedStream($request);
            /** @var array{action: string, result: bool, latest-root: string} $result */
            $result = $this->protocol->process($as, false);
            return $this->json([
                '!pkd-context' => 'fedi-e2ee:v1/api/burndown',
                'time' => $this->time(),
                'status' => $result['result'],
            ]);
        } catch (FetchException|HttpSignatureException|ActivityPubException|ProtocolException $e) {
            $this->config()->getLogger()->error(
                $e->getMessage(),
                $e->getTrace(),
            );
            return $this->json([
                '!pkd-context' => 'fedi-e2ee:v1/api/burndown',
                'time' => $this->time(),
                'status' => false,
            ]);
        }
    }
}
