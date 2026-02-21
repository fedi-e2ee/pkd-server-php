<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Exceptions\{
    BundleException,
    CryptoException,
    HttpSignatureException,
    JsonException,
    NotImplementedException,
    ParserException
};
use FediE2EE\PKDServer\Exceptions\{
    ActivityPubException,
    CacheException,
    ConcurrentException,
    DependencyException,
    FetchException,
    ProtocolException,
    TableException
};
use DateMalformedStringException;
use FediE2EE\PKDServer\{
    Meta\Route,
    Protocol
};
use FediE2EE\PKDServer\Traits\{
    ActivityStreamsTrait,
    ReqTrait
};
use JsonException as BaseJsonException;
use Override;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\HPKE\HPKEException;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
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
     * @throws BaseJsonException
     * @throws BundleException
     * @throws CacheException
     * @throws CertaintyException
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("/api/burndown")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->config()->getParams()->getBurnDownEnabled()) {
            return $this->error('BurnDown is not enabled');
        }
        try {
            $as = $this->getVerifiedStream($request);
            // We set $isActivityPub to false here because this payload is sent over HTTP.
            // This is important because BurnDown MUST NOT be sent over ActivityPub.
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
            ], 400);
        }
    }
}
