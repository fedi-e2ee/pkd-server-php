<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Traits;

use DateMalformedStringException;
use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    NotImplementedException
};
use FediE2EE\PKD\Crypto\{
    PublicKey,
    UtilTrait
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\Tables\{
    Actors,
    PublicKeys
};
use JsonException;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use ParagonIE\ConstantTime\Base64UrlSafe;
use SensitiveParameter;
use SodiumException;
use function floor, hash_equals, hash_hmac, is_null, pack, sprintf, substr, time, unpack;

trait TOTPTrait
{
    use JsonTrait;
    use UtilTrait;
    public const int TOTP_WINDOW_TIME = 30;

    /**
     * @param string $secret
     * @param string $otp
     * @param int $windows
     * @return ?int
     */
    public static function verifyTOTP(
        #[SensitiveParameter] string $secret,
        #[SensitiveParameter] string $otp,
        int    $windows = 2
    ): ?int {
        $time = time();
        for ($i = -$windows; $i <= $windows; $i++) {
            $trialTime = $time + ($i * self::TOTP_WINDOW_TIME);
            $expected = self::generateTOTP($secret, $trialTime);
            if (hash_equals($expected, $otp)) {
                return (int) floor($trialTime / self::TOTP_WINDOW_TIME);
            }
        }
        return null;
    }

    public static function generateTOTP(
        #[SensitiveParameter] string $secret,
        ?int    $time = null
    ): string {
        if (is_null($time)) {
            $time = time();
        }
        $currentTime = (int) floor($time / self::TOTP_WINDOW_TIME);
        $binaryTime = pack('N*', 0) . pack('N*', $currentTime);
        $hash = hash_hmac('sha512', $binaryTime, $secret, true);
        $offset = self::ord(substr($hash, -1)) & 0x0F;
        $truncatedHash = substr($hash, $offset, 4);
        $unpacked = unpack('N', $truncatedHash);
        $value = $unpacked === false ? 0 : $unpacked[1];
        $value &= 0x7FFFFFFF;
        return substr(sprintf('%08d', $value), -8);
    }

    /**
     * Avoid cache-timing leaks in ord() by using unpack()
     */
    public static function ord(string $chr): int
    {
        $unpacked = unpack('C', $chr);
        return $unpacked === false ? 0 : $unpacked[1];
    }

    /**
     * @param array<string, mixed> $body
     *
     * @throws ArrayKeyException
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
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    protected function verifySignature(array $body, string $actorId, string $keyId): void
    {
        /** @var PublicKeys $pkTable */
        $pkTable = $this->table('PublicKeys');
        $pks = $pkTable->getPublicKeysFor($actorId, $keyId);
        if (empty($pks)) {
            throw new ProtocolException('Public key not found');
        }
        foreach ($pks as $pk) {
            $publicKey = $pk['public-key'];
            if (!($publicKey instanceof PublicKey)) {
                continue;
            }

            $signature = $body['signature'] ?? '';
            if (empty($signature)) {
                throw new ProtocolException('Signature is missing');
            }

            $message = self::jsonEncode($body['enrollment'] ?? $body['disenrollment'] ?? $body['rotation']);
            $toSign = $this->preAuthEncode([
                '!pkd-context',
                ($body['!pkd-context'] ?? ''),
                'action',
                ($body['action'] ?? ''),
                'message',
                $message
            ]);

            if ($publicKey->verify(Base64UrlSafe::decodeNoPadding($signature), $toSign)) {
                return;
            }
        }
        throw new ProtocolException('Invalid signature');
    }

    /**
     * @param int $currentTime
     * @throws DependencyException
     * @throws ProtocolException
     */
    public function throwIfTimeOutsideWindow(int $currentTime): void
    {
        $diff = time() - $currentTime;
        if ($diff >= $this->config()->getParams()->otpMaxLife) {
            throw new ProtocolException('OTP is too stale');
        }
        if ($diff < 0) {
            throw new ProtocolException('OTP is too new; did you time travel?');
        }
    }

    /**
     * @return array{body: array<string, mixed>, sub: array<string, mixed>}
     *
     * @throws JsonException
     * @throws ProtocolException
     */
    protected function parseTotpBody(
        string $rawBody,
        string $subKey,
        array $requiredSubFields,
    ): array {
        $body = self::jsonDecode($rawBody);
        $sub = $body[$subKey] ?? [];
        foreach ($requiredSubFields as $field) {
            if (empty($sub[$field])) {
                throw new ProtocolException('Missing required fields');
            }
        }
        if (empty($body['action']) || empty($body['current-time']) || empty($body['!pkd-context'])) {
            throw new ProtocolException('Missing required fields');
        }
        return ['body' => $body, 'sub' => $sub];
    }

    /**
     * @throws ArrayKeyException
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
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    protected function validateTotpRequest(
        array $body,
        string $actorId,
        string $keyId,
        string $expectedContext,
        string $expectedAction,
    ): string {
        $this->verifySignature($body, $actorId, $keyId);
        if (!hash_equals($expectedContext, $body['!pkd-context'] ?? '')) {
            throw new ProtocolException('Invalid !pkd-context');
        }
        if (!hash_equals($expectedAction, $body['action'] ?? '')) {
            throw new ProtocolException('Invalid action');
        }
        $this->throwIfTimeOutsideWindow((int)($body['current-time'] ?? 0));

        /** @var Actors $actorTable */
        $actorTable = $this->table('Actors');
        $actor = $actorTable->searchForActor($actorId);
        if (is_null($actor)) {
            throw new ProtocolException('Actor not found');
        }
        $domain = self::parseUrlHost($actor->actorID);
        if (is_null($domain)) {
            throw new ProtocolException('Invalid actor URL');
        }
        return $domain;
    }
}
