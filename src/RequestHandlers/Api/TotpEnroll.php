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
use FediE2EE\PKD\Crypto\Protocol\HPKEAdapter;
use FediE2EE\PKDServer\{
    Exceptions\CacheException,
    Exceptions\DependencyException,
    Exceptions\ProtocolException,
    Exceptions\TableException,
    Interfaces\LimitingHandlerInterface,
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
use ParagonIE\ConstantTime\Base32;
use ParagonIE\HPKE\HPKEException;
use Random\RandomException;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;
use SodiumException;
use Throwable;
use TypeError;

use function hash_equals;
use function is_null;
use function parse_url;

class TotpEnroll implements RequestHandlerInterface, LimitingHandlerInterface
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
     * @throws HPKEException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    #[Route("/api/totp/enroll")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $body =  self::jsonDecode($request->getBody()->getContents());
            $enrollment = $body['enrollment'] ?? [];
            $actorId = $enrollment['actor-id'] ?? '';
            $keyId = $enrollment['key-id'] ?? '';
            $otpCurrent = $enrollment['otp-current'] ?? '';
            $otpPrevious = $enrollment['otp-previous'] ?? '';
            $totpSecret = $enrollment['totp-secret'] ?? '';

            if (
                empty($actorId)
                || empty($keyId)
                || empty($otpCurrent)
                || empty($otpPrevious)
                || empty($totpSecret)
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
        if (!hash_equals('fedi-e2ee:v1/api/totp/enroll', $body['!pkd-context'])) {
            return $this->error('Invalid !pkd-context', 400);
        }
        if (!hash_equals('TOTP-Enroll', $body['action'])) {
            return $this->error('Invalid action', 400);
        }
        try {
            $this->throwIfTimeOutsideWindow((int)($body['current-time']));
        } catch (ProtocolException $ex) {
            return $this->error($ex->getMessage(), 400);
        }

        $hpke = $this->config()->getHPKE();
        $decryptedSecret = (new HPKEAdapter($hpke->cs, 'fedi-e2ee:v1/api/totp/enroll'))->open(
            $hpke->getDecapsKey(),
            $hpke->getEncapsKey(),
            Base32::decode($totpSecret),
        );

        $tsCurrent = self::verifyTOTP($decryptedSecret, $otpCurrent);
        $tsPrevious = self::verifyTOTP($decryptedSecret, $otpPrevious);
        if (is_null($tsCurrent) || is_null($tsPrevious)) {
            return $this->error('Invalid TOTP codes', 406);
        }
        if ($tsCurrent <= $tsPrevious) {
            return $this->error('TOTP codes must be increasing', 406);
        }

        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        $actor = $actorTable->searchForActor($actorId);
        $domain = parse_url($actor->actorID)['host'];

        if ($this->totpTable->getSecretByDomain($domain)) {
            return $this->error('TOTP already enabled', 409);
        }

        $this->totpTable->saveSecret($domain, $decryptedSecret, $tsCurrent);

        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/enroll',
            'success' => true,
            'time' => (string) (new DateTime())->getTimestamp(),
        ]);
    }

    #[Override]
    public function getEnabledRateLimits(): array
    {
        return ['ip', 'domain'];
    }
}
