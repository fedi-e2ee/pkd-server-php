<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use DateTime;
use FediE2EE\PKD\Crypto\Exceptions\{
    JsonException,
    NotImplementedException
};
use FediE2EE\PKD\Crypto\Protocol\HPKEAdapter;
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
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;
use SodiumException;
use Throwable;
use TypeError;

class TotpEnroll implements RequestHandlerInterface
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
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     * @throws HPKEException
     */
    #[Route("/api/totp/enroll")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
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

        $this->verifySignature($body, $actorId, $keyId);

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

        if (!self::verifyTOTP($decryptedSecret, $otpCurrent) || !self::verifyTOTP($decryptedSecret, $otpPrevious)) {
            return $this->error('Invalid TOTP codes', 406);
        }

        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        $actor = $actorTable->searchForActor($actorId);
        $domain = parse_url($actor->actorID)['host'];

        if ($this->totpTable->getSecretByDomain($domain)) {
            return $this->error('TOTP already enabled', 409);
        }

        $this->totpTable->saveSecret($domain, $decryptedSecret);

        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/enroll',
            'success' => true,
            'time' => (string) (new DateTime())->getTimestamp(),
        ]);
    }
}
