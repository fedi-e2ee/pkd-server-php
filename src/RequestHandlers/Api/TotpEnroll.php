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
use FediE2EE\PKDServer\Tables\TOTP as TOTPTable;
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
use function is_null;

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
            $parsed = $this->parseTotpBody(
                $request->getBody()->getContents(),
                'enrollment',
                ['actor-id', 'key-id', 'otp-current', 'otp-previous', 'totp-secret'],
            );
        } catch (Throwable) {
            return $this->error('Missing required fields', 400);
        }
        $body = $parsed['body'];
        $sub = $parsed['sub'];

        try {
            $domain = $this->validateTotpRequest(
                $body,
                $sub['actor-id'],
                $sub['key-id'],
                'fedi-e2ee:v1/api/totp/enroll',
                'TOTP-Enroll',
            );
        } catch (ProtocolException $ex) {
            return $this->error($ex->getMessage(), 400);
        }

        $hpke = $this->config()->getHPKE();
        $decryptedSecret = (new HPKEAdapter($hpke->cs, 'fedi-e2ee:v1/api/totp/enroll'))->open(
            $hpke->getDecapsKey(),
            $hpke->getEncapsKey(),
            Base32::decode($sub['totp-secret']),
        );

        $tsCurrent = self::verifyTOTP($decryptedSecret, $sub['otp-current']);
        $tsPrevious = self::verifyTOTP($decryptedSecret, $sub['otp-previous']);
        if (is_null($tsCurrent) || is_null($tsPrevious)) {
            return $this->error('Invalid TOTP codes', 406);
        }
        if ($tsCurrent <= $tsPrevious) {
            return $this->error('TOTP codes must be increasing', 406);
        }

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
