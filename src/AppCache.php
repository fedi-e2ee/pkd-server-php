<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer;

use DateInterval;
use DateTime;
use Override;
use Predis\Client as RedisClient;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use SodiumException;

class AppCache implements CacheInterface
{
    private static array $inMemoryCache = [];
    private ?RedisClient $redis;
    private string $namespace;
    private string $hashKey;
    private int $defaultTTL;

    public function __construct(ServerConfig $serverConfig, string $namespace = '', int $defaultTTL = 60)
    {
        $this->hashKey = $serverConfig->getParams()->cacheKey;
        $this->redis = $serverConfig->getRedis();
        $this->namespace = $namespace;
        if (!array_key_exists($namespace, self::$inMemoryCache)) {
            self::$inMemoryCache[$namespace] = [];
        }
        $this->defaultTTL = $defaultTTL;
    }

    /**
     * Cache as a JSON-serialized string, deserialize from cache.
     *
     * Used for caching entire HTTP response data (arrays, etc.).)
     *
     * @throws SodiumException
     * @throws InvalidArgumentException
     */
    public function cacheJson(string $lookup, callable $fallback, DateInterval|int|null $ttl = null): mixed
    {
        $key = $this->deriveKey($lookup);
        if (!$this->has($key)) {
            $value = $fallback();
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_UNICODE);
            $this->set($key, $encoded, $ttl);
            return $value;
        }
        return json_decode($this->get($key), true);
    }

    /**
     * If there is a cache-hit, it returns the value.
     *
     * Otherwise, it invokes the fallback to determine the value.
     *
     * @throws InvalidArgumentException
     * @throws SodiumException
     */
    public function cache(string $lookup, callable $fallback, DateInterval|int|null $ttl = null): mixed
    {
        $key = $this->deriveKey($lookup);
        if (!$this->has($key)) {
            $value = $fallback();
            $this->set($key, $value, $ttl);
            return $value;
        }
        return $this->get($key);
    }

    /**
     * @throws SodiumException
     */
    public function deriveKey(string $input): string
    {
        return $this->namespace . ':' . sodium_bin2hex(sodium_crypto_generichash($input, $this->hashKey));
    }

    #[Override]
    public function get(string $key, mixed $default = null): mixed
    {
        if (!is_null($this->redis)) {
            return $this->redis->get($key);
        } elseif (array_key_exists($key, self::$inMemoryCache[$this->namespace])) {
            return self::$inMemoryCache[$this->namespace][$key];
        }
        return null;
    }

    #[Override]
    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
    {
        if (!is_null($this->redis)) {
            $this->redis->setex($key, $this->processTTL($ttl), $value);
        } else {
            self::$inMemoryCache[$this->namespace][$key] = $value;
        }
        return true;
    }

    #[Override]
    public function delete(string $key): bool
    {
        if (!is_null($this->redis)) {
            $this->redis->del($key);
        } elseif (array_key_exists($key, self::$inMemoryCache[$this->namespace])) {
            unset(self::$inMemoryCache[$this->namespace][$key]);
        }
        return true;
    }

    #[Override]
    public function clear(): bool
    {
        if (!is_null($this->redis)) {
            $this->redis->flushdb();
        } else {
            self::$inMemoryCache[$this->namespace] = [];
        }
        return true;
    }

    #[Override]
    public function getMultiple(iterable $keys, mixed $default = null): array
    {
        if (!is_null($this->redis)) {
            /** @var array<string, mixed> $results */
            $results = $this->redis->mget($keys);
            return $results;
        }
        $collected = [];
        foreach ($keys as $key) {
            $collected[$key] = $this->get($key);
        }
        return $collected;
    }

    #[Override]
    public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
    {
        if (!is_null($this->redis)) {
            $this->redis->mset((array) $values);
            return true;
        }
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
        return true;
    }

    #[Override]
    public function deleteMultiple(iterable $keys): bool
    {
        if (!is_null($this->redis)) {
            $arrayKeys = (array) $keys;
            $this->redis->del(...$arrayKeys);
            return true;
        }
        foreach ($keys as $key) {
            if (array_key_exists($key, self::$inMemoryCache[$this->namespace])) {
                unset(self::$inMemoryCache[$key]);
            }
        }
        return true;
    }

    #[Override]
    public function has(string $key): bool
    {
        if (!is_null($this->redis)) {
            return $this->redis->exists($key) > 0;
        }
        return array_key_exists($key, self::$inMemoryCache[$this->namespace]);
    }

    /**
     * Collapse multiple types into a number of seconds for Redis.
     *
     * @param DateInterval|int|null $ttl
     * @return int
     */
    public function processTTL(DateInterval|int|null $ttl): int
    {
        if (is_null($ttl)) {
            return $this->defaultTTL;
        }
        if (is_int($ttl)) {
            return $ttl;
        }

        // Add interval from zero, cast to unix timestamp to get number of seconds.
        $start = new DateTime('@0');
        $end = $start->add($ttl);
        $seconds = (int) $end->format('U');

        // If invert is true, we negate:
        return $ttl->invert ? -$seconds : $seconds;
    }
}
