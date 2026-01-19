<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use FediE2EE\PKDServer\Traits\ReqTrait;
use FediE2EE\PKD\Crypto\Exceptions\{
    JsonException,
    NotImplementedException
};
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\Meta\Route;
use Override;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\HPKE\KEM\DHKEM\EncapsKey;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};
use SodiumException;
use TypeError;

class ServerPublicKey implements RequestHandlerInterface
{
    use ReqTrait;

    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    #[Route("/api/server-public-key")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $hpke = $this->config()->getHPKE();
        $cs = $hpke->cs->getSuiteName();
        $encapsKey = $hpke->encapsKey;
        if (!($encapsKey instanceof EncapsKey)) {
            throw new TypeError('Only DHKEM encaps keys are expected');
        }
        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/server-public-key',
            'current-time' => $this->time(),
            'hpke-ciphersuite' => $cs,
            'hpke-public-key' => Base64UrlSafe::encodeUnpadded($encapsKey->bytes),
        ]);
    }
}
