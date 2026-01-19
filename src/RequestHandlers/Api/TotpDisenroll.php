<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use DateMalformedStringException;
use DateTime;
use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    JsonException,
    NotImplementedException
};
use FediE2EE\PKDServer\{
    Exceptions\CacheException,
    Exceptions\DependencyException,
    Exceptions\ProtocolException,
    Exceptions\TableException,
    Meta\Route,
    Traits\ReqTrait,
    Traits\TOTPTrait
};
use FediE2EE\PKDServer\Tables\{
    Actors,
    TOTP as TOTPTable
};
use JsonException as BaseJsonException;
use Override;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;
use SodiumException;
use Throwable;
use TypeError;

class TotpDisenroll implements RequestHandlerInterface
{
    use ReqTrait;
    use TOTPTrait;

    protected TOTPTable $totpTable;

    /**
     * @throws DependencyException
     * @throws TableException
     * @throws CacheException
     */
    public function __construct()
    {
        $totpTable = $this->table('TOTP');
        if (!($totpTable instanceof TOTPTable)) {
            throw new TypeError('Could not load TOTP table at runtime');
        }
        $this->totpTable = $totpTable;
    }

    /**
     * @throws ArrayKeyException
     * @throws BaseJsonException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("/api/totp/disenroll")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $body = $this->jsonDecode($request->getBody()->getContents());
            $disenrollment = $body['disenrollment'] ?? [];
            $actorId = $disenrollment['actor-id'] ?? '';
            $keyId = $disenrollment['key-id'] ?? '';
            $otp = $disenrollment['otp'] ?? '';

            if (
                empty($actorId)
                || empty($keyId)
                || empty($otp)
                || empty($body['action'])
                || empty($body['current-time'])
                || empty($body['!pkd-context'])
            ) {
                return $this->error('Missing required fields', 400);
            }
        } catch (Throwable $ex) {
            $this->config()->getLogger()->error($ex->getMessage());
            return $this->error('Invalid JSON', 400);
        }

        try {
            $this->verifySignature($body, $actorId, $keyId);
        } catch (ProtocolException $ex) {
            return $this->error($ex->getMessage(), 400);
        }

        // Validate inputs:
        if (!hash_equals('fedi-e2ee:v1/api/totp/disenroll', $body['!pkd-context'])) {
            return $this->error('Invalid !pkd-context', 400);
        }
        if (!hash_equals('TOTP-Disenroll', $body['action'])) {
            return $this->error('Invalid action', 400);
        }
        try {
            $this->throwIfTimeOutsideWindow((int)($body['current-time']));
        } catch (ProtocolException $ex) {
            return $this->error($ex->getMessage(), 400);
        }

        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        $actor = $actorTable->searchForActor($actorId);
        $domain = parse_url($actor->actorID)['host'];
        $secret = $this->totpTable->getSecretByDomain($domain);
        if (!$secret) {
            return $this->error('TOTP not enabled', 409);
        }

        if (!$this->verifyTOTP($secret, $otp)) {
            return $this->error('Invalid TOTP code', 403);
        }

        $this->totpTable->deleteSecret($domain);

        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/disenroll',
            'success' => true,
            'time' => (string) (new DateTime())->getTimestamp(),
        ]);
    }
}
