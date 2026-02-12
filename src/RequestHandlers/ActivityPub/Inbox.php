<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\ActivityPub;

use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    HttpSignatureException,
    NotImplementedException,
    JsonException
};
use FediE2EE\PKDServer\{
    Meta\Route,
    Tables\ActivityStreamQueue,
    Traits\ActivityStreamsTrait,
    Traits\ReqTrait
};
use FediE2EE\PKDServer\Exceptions\{
    ActivityPubException,
    CacheException,
    DependencyException,
    FetchException,
    TableException
};
use JsonException as BaseJsonException;
use Override;
use ParagonIE\Certainty\Exception\CertaintyException;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};
use SodiumException;

use function defined;

class Inbox implements RequestHandlerInterface
{
    use ActivityStreamsTrait;
    use ReqTrait;

    protected ActivityStreamQueue $asq;

    /**
     * @throws TableException
     * @throws DependencyException
     * @throws CacheException
     */
    public function __construct()
    {
        $asq = $this->table('ActivityStreamQueue');
        if (!($asq instanceof ActivityStreamQueue)) {
            throw new DependencyException('table() did not return ActivityStreamQueue');
        }
        $this->asq = $asq;
    }

    /**
     * @throws BaseJsonException
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    #[Route("/user/{user_id}/inbox")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // This endpoint just enqueues the message to be processed by the cron job.
        try {
            $as = $this->getVerifiedStream($request);
            $queueId = $this->asq->insert($as);
            if (defined('PKD_SERVER_DEBUG')) {
                return $this->json(['queue_id' => $queueId], 202);
            }
            return $this->json([], 202);
        } catch (FetchException $ex) {
            $this->config()->getLogger()->error($ex->getMessage(), $ex->getTrace());
            return $this->error('Failed to verify message origin', 500);
        } catch (HttpSignatureException $ex) {
            $this->config()->getLogger()->error($ex->getMessage(), $ex->getTrace());
            return $this->error('HTTP Signature verification failed', 500);
        } catch (ActivityPubException $ex) {
            return $this->error($ex->getMessage());
        }
    }
}
