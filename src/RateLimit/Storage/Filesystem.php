<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RateLimit\Storage;

use DateMalformedStringException;
use FediE2EE\PKD\Crypto\Exceptions\InputException;
use FediE2EE\PKD\Crypto\UtilTrait;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\Interfaces\RateLimitStorageInterface;
use FediE2EE\PKDServer\RateLimit\RateLimitData;
use FediE2EE\PKDServer\Traits\JsonTrait;
use JsonException;
use Override;
use SensitiveParameter;
use SodiumException;

use function array_key_exists;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function is_dir;
use function is_null;
use function mkdir;
use function sodium_bin2hex;
use function sodium_crypto_generichash;
use function sodium_crypto_generichash_keygen;
use function substr;
use function time;
use function unlink;

class Filesystem implements RateLimitStorageInterface
{
    use JsonTrait;
    use UtilTrait;

    private readonly string $cacheKey;

    /**
     * @throws DependencyException
     */
    public function __construct(
        private string $baseDir,
        #[SensitiveParameter] ?string $cacheKey = null,
        private int $ttl = 86400,
    ) {
        if (is_null($cacheKey)) {
            $cacheKey = sodium_crypto_generichash_keygen();
        }
        $this->cacheKey = $cacheKey;
    }

    /**
     * @throws DateMalformedStringException
     * @throws InputException
     * @throws JsonException
     * @throws SodiumException
     */
    #[Override]
    public function get(string $type, string $identifier): ?RateLimitData
    {
        $file = $this->getFilesystemPath($type, $identifier);
        if (!file_exists($file)) {
            return null;
        }
        $contents = file_get_contents($file);
        if ($contents === false) {
            return null;
        }
        $unwrapped = self::jsonDecode($contents);
        if (!array_key_exists('expires', $unwrapped)) {
            return null;
        }
        if ($unwrapped['expires'] < time()) {
            // Evict from filesystem cache
            $this->delete($type, $identifier);
            return null;
        }
        if (!array_key_exists('data', $unwrapped)) {
            return null;
        }
        return RateLimitData::fromJson($unwrapped['data']);
    }

    /**
     * @throws SodiumException
     * @throws JsonException
     */
    #[Override]
    public function set(string $type, string $identifier, RateLimitData $data): bool
    {
        $file = $this->getFilesystemPath($type, $identifier);
        $bundled = self::jsonEncode([
            'expires' => time() + $this->ttl,
            'data' => self::jsonEncode($data),
        ]);
        return file_put_contents($file, $bundled) !== false;
    }

    /**
     * @throws SodiumException
     */
    #[Override]
    public function delete(string $type, string $identifier): bool
    {
        $file = $this->getFilesystemPath($type, $identifier);
        if (!file_exists($file)) {
            return true;
        }
        // The filepath here is created by concatenating hex-encoded hash function outputs. It's fine.
        // nosemgrep: php.lang.security.unlink-use.unlink-use
        return unlink($file);
    }

    /**
     * @throws SodiumException
     */
    public function getFilesystemPath(string $type, string $identifier): string
    {
        $hash = sodium_bin2hex(
            sodium_crypto_generichash(
                self::preAuthEncode([static::class, $type, $identifier]),
                $this->cacheKey,
                34
            )
        );
        $subdir = implode(DIRECTORY_SEPARATOR, [
            $this->baseDir,
            substr($hash, 0, 2),
            substr($hash, 2, 4),
        ]);
        if (!is_dir($subdir)) {
            mkdir($subdir, 0775, true);
        }
        return implode(DIRECTORY_SEPARATOR, [
            $subdir,
            substr($hash, 4) . '.json',
        ]);
    }
}
