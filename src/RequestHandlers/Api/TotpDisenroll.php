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
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;
use SodiumException;
use Throwable;
use TypeError;

use function is_null;

class TotpDisenroll implements RequestHandlerInterface, LimitingHandlerInterface
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
            $parsed = $this->parseTotpBody(
                $request->getBody()->getContents(),
                'disenrollment',
                ['actor-id', 'key-id', 'otp'],
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
                'fedi-e2ee:v1/api/totp/disenroll',
                'TOTP-Disenroll',
            );
        } catch (ProtocolException $ex) {
            return $this->error($ex->getMessage(), 400);
        }

        $totp = $this->totpTable->getTotpByDomain($domain);
        if (!$totp) {
            return $this->error('TOTP not enabled', 409);
        }
        $ts = $this->verifyTOTP($totp['secret'], $sub['otp']);
        if (is_null($ts)) {
            return $this->error('Invalid TOTP code', 403);
        }
        if ($ts <= $totp['last_time_step']) {
            return $this->error('TOTP code already used', 403);
        }

        $this->totpTable->deleteSecret($domain);

        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/disenroll',
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
