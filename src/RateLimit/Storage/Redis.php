<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\RateLimit\Storage;

use FediE2EE\PKD\Crypto\Exceptions\InputException;
use FediE2EE\PKD\Crypto\UtilTrait;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\Interfaces\RateLimitStorageInterface;
use FediE2EE\PKDServer\RateLimit\RateLimitData;
use FediE2EE\PKDServer\Traits\JsonTrait;
use JsonException;
use Override;
use Predis\Client as PredisClient;
use SensitiveParameter;
use SodiumException;
use function is_null;
use function sodium_bin2hex;
use function sodium_crypto_generichash;
use function sodium_crypto_generichash_keygen;

class Redis implements RateLimitStorageInterface
{
    use JsonTrait;
    use UtilTrait;

    private readonly string $cacheKey;

    /**
     * @throws DependencyException
     */
    public function __construct(
        private PredisClient $redis,
        #[SensitiveParameter] ?string $cacheKey = null
    ) {
        if (is_null($cacheKey)) {
            $cacheKey = sodium_crypto_generichash_keygen();
        }
        $this->cacheKey = $cacheKey;
    }

    /**
     * @throws InputException
     * @throws JsonException
     * @throws SodiumException
     */
    #[Override]
    public function get(string $type, string $identifier): ?RateLimitData
    {
        $key = $this->getKey($type, $identifier);
        $stored = $this->redis->get($key);
        if (is_null($stored)) {
            return null;
        }
        return RateLimitData::fromJson($stored);
    }

    /**
     * @throws JsonException
     * @throws SodiumException
     */
    #[Override]
    public function set(string $type, string $identifier, RateLimitData $data): bool
    {
        $key = $this->getKey($type, $identifier);
        $this->redis->set($key, self::jsonEncode($data));
        return $this->redis->exists($key) > 0;
    }

    /**
     * @throws SodiumException
     */
    #[Override]
    public function delete(string $type, string $identifier): bool
    {
        $key = $this->getKey($type, $identifier);
        $this->redis->del($key);
        return $this->redis->exists($key) < 1;
    }

    /**
     * @throws SodiumException
     */
    protected function getKey(string $type, string $identifier): string
    {
        return 'rate-limiting:' . $type . ':' . sodium_bin2hex(
            sodium_crypto_generichash(
                self::preAuthEncode([static::class, $type, $identifier]),
                $this->cacheKey
            )
        );
    }
}
