<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Middleware;

use FediE2EE\PKDServer\Exceptions\{
    ActivityPubException,
    DependencyException,
    FetchException,
    ProtocolException,
    RateLimitException,
};
use FediE2EE\PKD\Crypto\Exceptions\HttpSignatureException;
use FediE2EE\PKDServer\Interfaces\LimitingHandlerInterface;
use FediE2EE\PKDServer\ServerConfig;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function in_array;
use function is_null;

class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(private ?ServerConfig $config = null)
    {
        if (is_null($this->config)) {
            $this->config = $GLOBALS['pkdConfig'];
        }
    }

    public function getConfig(): ServerConfig
    {
        if (is_null($this->config)) {
            $this->config = $GLOBALS['pkdConfig'];
        }
        return $this->config;
    }

    /**
     * Pre-process the request BEFORE it reaches the request handler.
     *
     * If there is no rate-limiting implementation enabled, just handle the request.
     * If there is rate-limiting enabled, this will either:
     *
     * 1. Throw a RateLimitException if you are being rate-limited.
     * 2. Wrap request handling in a try/catch which increases rate-limiting penalties when
     *    an uncaught exception occurs.
     *
     * If a RateLimitException occurs, it will be handled by public/index.php and the request
     * never actually reaches the RequestHandler.
     *
     * @throws RateLimitException
     */
    #[Override]
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Load the configured rate-limiting implementation here:
        try {
            $rateLimit = $this->getConfig()->getRateLimit();
        } catch (DependencyException) {
            // No rate-limiting -> just handle it
            return $handler->handle($request);
        }
        if (!$rateLimit->isEnabled()) {
            // Rate-limiting disabled -> just handle it
            return $handler->handle($request);
        }
        if (!($handler instanceof LimitingHandlerInterface)) {
            // Only handlers that implement LimitingHandlerInterface are rate-limited.
            return $handler->handle($request);
        }

        // /** @var RequestHandlerInterface & LimitingHandlerInterface $handler */
        // Check if this IP is currently penalized
        $subnet = $rateLimit->getRequestSubnet($request);
        $rateLimit->enforceRateLimit($request, $handler);
        $enforce = $handler->getEnabledRateLimits();

        // Call handler and detect failures:
        try {
            return $handler->handle($request);
        } catch (HttpSignatureException | ActivityPubException | FetchException | ProtocolException $e) {
            $rateLimit->recordPenalty('ip', $subnet);
            if ($rateLimit->shouldEnforce('actor') && in_array('actor', $enforce, true)) {
                $actor = $rateLimit->getRequestActor($request);
                if (!is_null($actor)) {
                    $rateLimit->recordPenalty('actor', $actor);
                }
            }
            if ($rateLimit->shouldEnforce('domain') && in_array('domain', $enforce, true)) {
                $domain = $rateLimit->getRequestDomain($request);
                if (!is_null($domain)) {
                    $rateLimit->recordPenalty('domain', $domain);
                }
            }
            throw new RateLimitException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
