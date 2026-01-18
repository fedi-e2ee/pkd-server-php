<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RequestHandlers\Api;

use DateTime;
use FediE2EE\PKD\Crypto\Protocol\HPKEAdapter;
use FediE2EE\PKD\Crypto\Exceptions\{
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

class TotpRotate implements RequestHandlerInterface
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
            $body = $this->jsonDecode($request->getBody()->getContents());
            $rotation = $body['rotation'] ?? [];
            $actorId = $rotation['actor-id'] ?? '';
            $keyId = $rotation['key-id'] ?? '';
            $oldOtp = $rotation['old-otp'] ?? '';
            $newOtpCurrent = $rotation['new-otp-current'] ?? '';
            $newOtpPrevious = $rotation['new-otp-previous'] ?? '';
            $newTotpSecret = $rotation['new-totp-secret'] ?? '';

            if (
                empty($actorId)
                || empty($keyId)
                || empty($oldOtp)
                || empty($newOtpCurrent)
                || empty($newOtpPrevious)
                || empty($newTotpSecret)
                || empty($body['action'])
                || empty($body['current-time'])
                || empty($body['!pkd-context'])
            ) {
                return $this->error('Missing required fields', 400);
            }
        } catch (Throwable $ex) {
            return $this->error('Invalid JSON', 400);
        }

        try {
            $this->verifySignature($body, $actorId, $keyId);
        } catch (ProtocolException $ex) {
            return $this->error($ex->getMessage(), 400);
        }

        // Validate inputs:
        if (!hash_equals('fedi-e2ee:v1/api/totp/rotate', $body['!pkd-context'])) {
            return $this->error('Invalid !pkd-context', 400);
        }
        if (!hash_equals('TOTP-Rotate', $body['action'])) {
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
        $oldSecret = $this->totpTable->getSecretByDomain($domain);
        if (!$oldSecret) {
            return $this->error('TOTP not enabled', 400);
        }

        if (!$this->verifyTOTP($oldSecret, $oldOtp)) {
            return $this->error('Invalid old TOTP code', 403);
        }

        $hpke = $this->config()->getHPKE();
        $newSecret = new HPKEAdapter($hpke->cs, 'fedi-e2ee:v1/api/totp/rotate')->open(
            $hpke->getDecapsKey(),
            $hpke->getEncapsKey(),
            Base32::decode($newTotpSecret)
        );

        if (!$this->verifyTOTP($newSecret, $newOtpCurrent) || !$this->verifyTOTP($newSecret, $newOtpPrevious)) {
            return $this->error('Invalid new TOTP codes', 406);
        }

        $this->totpTable->updateSecret($domain, $newSecret);

        return $this->json([
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
            'success' => true,
            'time' => (string) new DateTime()->getTimestamp(),
        ]);
    }
}
