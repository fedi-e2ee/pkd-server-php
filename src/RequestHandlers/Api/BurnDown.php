<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Exceptions\CryptoException;
use FediE2EE\PKD\Crypto\Exceptions\JsonException;
use FediE2EE\PKD\Crypto\Exceptions\NotImplementedException;
use FediE2EE\PKD\Crypto\Exceptions\ParserException;
use FediE2EE\PKDServer\ActivityPub\ActivityStream;
use FediE2EE\PKDServer\Exceptions\ActivityPubException;
use FediE2EE\PKDServer\Exceptions\CacheException;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\Exceptions\ProtocolException;
use FediE2EE\PKDServer\Exceptions\TableException;
use FediE2EE\PKDServer\Meta\Route;
use FediE2EE\PKDServer\Protocol;
use FediE2EE\PKDServer\Traits\ReqTrait;
use Override;
use ParagonIE\HPKE\HPKEException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SodiumException;

class BurnDown implements RequestHandlerInterface
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
     * @throws ActivityPubException
     * @throws CacheException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("/api/revoke")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $message = (string) $request->getBody();
        try {
            $as = ActivityStream::fromString($message);
            /** @var array{action: string, result: bool, latest-root: string} $result */
            $result = $this->protocol->process($as, false);
            return $this->json([
                '!pkd-context' => 'fedi-e2ee:v1/api/burndown',
                'time' => $this->time(),
                'status' => $result['result'],
            ]);
        } catch (ProtocolException $e) {
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
