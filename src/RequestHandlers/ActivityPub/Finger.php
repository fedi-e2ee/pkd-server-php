<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\ActivityPub;

use GuzzleHttp\Exception\GuzzleException;
use FediE2EE\PKD\Crypto\Exceptions\{
    JsonException,
    NetworkException,
    NotImplementedException
};
use FediE2EE\PKDServer\{
    Exceptions\DependencyException,
    Meta\Route,
    Traits\ActivityStreamsTrait,
    Traits\ReqTrait
};
use JsonException as BaseJsonException;
use Override;
use ParagonIE\Certainty\Exception\CertaintyException;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};
use SodiumException;
use function array_key_exists;
use function hash_equals;
use function preg_match;

class Finger implements RequestHandlerInterface
{
    use ActivityStreamsTrait;
    use ReqTrait;

    /**
     * @throws BaseJsonException
     * @throws CertaintyException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    #[Route("/.well-known/webfinger")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        if (!array_key_exists("resource", $params)) {
            return $this->error('missing resource parameter', 400);
        }
        $resource = $params["resource"];
        $matches = [];
        if (!preg_match('#^acct:([^@]+)@(.+)$#', $resource, $matches)) {
            return $this->error('invalid resource format', 400);
        }
        $user = $matches[1];
        $domain = $matches[2];

        $serverParams = $this->config()->getParams();

        // Handle third-party lookups
        if (!hash_equals($serverParams->hostname, $domain)) {
            try {
                return $this->json(
                    $this->webfinger($this->config()->getGuzzle())
                        ->fetch($user . '@' . $domain)
                );
            } catch (NetworkException $e) {
                return $this->error($e->getMessage());
            }
        }

        // We only return a WebFinger for the valid configured user:
        if (!hash_equals($serverParams->actorUsername, $user)) {
            return $this->error('User not found', 404);
        }

        // Return the one valid user for this host:
        $actorUrl = "https://{$domain}/users/{$user}";
        return $this->json([
            'subject' => "acct:{$user}@{$domain}",
            'aliases' => [$actorUrl],
            'links' => [
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => $actorUrl,
                ]
            ]
        ]);
    }
}
