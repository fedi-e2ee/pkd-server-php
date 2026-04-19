<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\ActivityPub;

use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    NotImplementedException
};
use JsonException;
use FediE2EE\PKDServer\{
    Exceptions\DependencyException,
    Meta\Route,
    Traits\ActivityStreamsTrait,
    Traits\ReqTrait
};
use Override;
use ParagonIE\PQCrypto\Exception\{
    MLDSAInternalException,
    PQCryptoCompatException
};
use Psr\Http\Server\RequestHandlerInterface;
use Random\RandomException;
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};
use SodiumException;

class UserPage implements RequestHandlerInterface
{
    use ActivityStreamsTrait;
    use ReqTrait;

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws JsonException
     * @throws MLDSAInternalException
     * @throws NotImplementedException
     * @throws PQCryptoCompatException
     * @throws RandomException
     * @throws SodiumException
     */
    #[Route("/users/{user_id}")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->config()->getParams();
        $requested = $request->getAttribute('user_id');
        if (!is_string($requested) || !hash_equals($params->actorUsername, $requested)) {
            return $this->error('User not found', 404);
        }

        $publicKey = $this->config()->getSigningKeys()->publicKey;
        $actorUrl = 'https://' . $params->hostname . '/users/' . $params->actorUsername;

        return $this->json(
            [
                '@context' => [
                    'https://www.w3.org/ns/activitystreams',
                    'https://w3id.org/security/v1',
                ],
                'id' => $actorUrl,
                'type' => 'Service',
                'preferredUsername' => $params->actorUsername,
                'inbox' => $actorUrl . '/inbox',
                'assertionMethod' => [
                    [
                        'type' => 'Multikey',
                        'id' => $actorUrl . '#main-key',
                        'controller' => $actorUrl,
                        'publicKeyMultibase' => $publicKey->toMultibase(),
                    ],
                ],
                'publicKey' => [
                    'id' => $actorUrl . '#main-key',
                    'owner' => $actorUrl,
                    'publicKeyPem' => $publicKey->encodePem(),
                ],
            ],
            200,
            ['Content-Type' => 'application/activity+json']
        );
    }
}
