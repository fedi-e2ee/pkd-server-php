<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Traits;

use FediE2EE\PKD\Crypto\Exceptions\{
    JsonException,
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
use FediE2EE\PKDServer\Tables\PublicKeys;
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

trait TOTPTrait
{
    use UtilTrait;
    public const int TOTP_WINDOW_TIME = 30;

    /**
     * @param string $secret
     * @param string $otp
     * @param int $windows
     * @return bool
     */
    public static function verifyTOTP(
        #[SensitiveParameter] string $secret,
        #[SensitiveParameter] string $otp,
        int    $windows = 2
    ): bool {
        $time = time();
        for ($i = -$windows; $i <= $windows; $i++) {
            $trialTime = $time + ($i * self::TOTP_WINDOW_TIME);
            $expected = self::generateTOTP($secret, $trialTime);
            if (hash_equals($expected, $otp)) {
                return true;
            }
        }
        return false;
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
        $value = unpack('N', $truncatedHash)[1];
        $value &= 0x7FFFFFFF;
        return substr(sprintf('%08d', $value), -8);
    }

    /**
     * Avoid cache-timing leaks in ord() by using unpack()
     */
    public static function ord(string $chr): int
    {
        return unpack('C', $chr)[1];
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoOperationException
     * @throws CacheException
     * @throws DependencyException
     * @throws TableException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws NotImplementedException
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

            $message = json_encode($body['enrollment'] ?? $body['disenrollment'] ?? $body['rotation']);
            if (!is_string($message)) {
                throw new ProtocolException('Could not JSON encode message');
            }
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
     * @throws ProtocolException
     */
    public function throwIfTimeOutsideWindow(int $currentTime): void
    {
        $diff = time() - $currentTime;
        if ($diff >= $this->config->getParams()->otpMaxLife) {
            throw new ProtocolException('OTP is too stale');
        }
        if ($diff < 0) {
            throw new ProtocolException('OTP is too new; did you time travel?');
        }
    }
}
