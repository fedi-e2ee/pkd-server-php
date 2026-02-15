<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use DateMalformedStringException;
use DateTime;
use FediE2EE\PKD\Crypto\Protocol\HPKEAdapter;
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

class TotpRotate implements RequestHandlerInterface, LimitingHandlerInterface
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
    #[Route("/api/totp/rotate")]
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $parsed = $this->parseTotpBody(
                $request->getBody()->getContents(),
                'rotation',
                ['actor-id', 'key-id', 'old-otp', 'new-otp-current', 'new-otp-previous', 'new-totp-secret'],
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
                'fedi-e2ee:v1/api/totp/rotate',
                'TOTP-Rotate',
            );
        } catch (ProtocolException $ex) {
            return $this->error($ex->getMessage(), 400);
        }

        $oldTotp = $this->totpTable->getTotpByDomain($domain);
        if (!$oldTotp) {
            return $this->error('TOTP not enabled', 400);
        }
        $tsOld = $this->verifyTOTP($oldTotp['secret'], $sub['old-otp']);
        if (is_null($tsOld)) {
            return $this->error('Invalid old TOTP code', 403);
        }
        if ($tsOld <= $oldTotp['last_time_step']) {
            return $this->error('Old TOTP code already used', 403);
        }

        $hpke = $this->config()->getHPKE();
        $newSecret = (new HPKEAdapter($hpke->cs, 'fedi-e2ee:v1/api/totp/rotate'))->open(
            $hpke->getDecapsKey(),
            $hpke->getEncapsKey(),
            Base32::decode($sub['new-totp-secret'])
        );
        $tsNewCurrent = $this->verifyTOTP($newSecret, $sub['new-otp-current']);
        $tsNewPrevious = $this->verifyTOTP($newSecret, $sub['new-otp-previous']);
        if (is_null($tsNewCurrent) || is_null($tsNewPrevious)) {
            return $this->error('Invalid new TOTP codes', 406);
        }
        if ($tsNewCurrent <= $tsNewPrevious) {
            return $this->error('New TOTP codes must be increasing', 406);
        }

        $this->totpTable->updateSecret($domain, $newSecret, $tsNewCurrent);

        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
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
