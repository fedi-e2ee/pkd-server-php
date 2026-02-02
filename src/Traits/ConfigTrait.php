<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Traits;

use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException
};
use FediE2EE\PKDServer\Tables\{
    Actors,
    ActivityStreamQueue,
    AuxData,
    MerkleState,
    Peers,
    PublicKeys,
    ReplicaActors,
    ReplicaAuxData,
    ReplicaHistory,
    ReplicaPublicKeys,
    TOTP
};
use FediE2EE\PKDServer\Exceptions\TableException;
use FediE2EE\PKDServer\{
    AppCache,
    ServerConfig,
    Table,
    TableCache
};
use GuzzleHttp\Client;
use ParagonIE\Certainty\Exception\CertaintyException;
use SodiumException;
use TypeError;

use function array_key_exists;
use function get_debug_type;
use function is_array;
use function is_int;
use function is_null;
use function is_numeric;
use function is_string;
use function parse_url;
use function reset;

trait ConfigTrait
{
    use JsonTrait;

    public ?ServerConfig $config = null;
    protected ?WebFinger $webFinger = null;

    /**
     * @throws DependencyException
     */
    public function appCache(string $namespace, int $defaultTTL = 60): AppCache
    {
        return new AppCache($this->config(), $namespace, $defaultTTL);
    }

    /**
     * @throws CacheException
     * @throws DependencyException
     * @throws TableException
     */
    public function table(string $tableName): Table
    {
        $cache = TableCache::instance();
        if ($cache->hasTable($tableName)) {
            return $cache->fetchTable($tableName);
        }

        $table = match ($tableName) {
            'ActivityStreamQueue' => new ActivityStreamQueue($this->config()),
            'Actors' => new Actors($this->config()),
            'AuxData' => new AuxData($this->config()),
            'MerkleState' => new MerkleState($this->config()),
            'Peers' => new Peers($this->config()),
            'PublicKeys' => new PublicKeys($this->config()),
            'ReplicaActors' => new ReplicaActors($this->config()),
            'ReplicaAuxData' => new ReplicaAuxData($this->config()),
            'ReplicaHistory' => new ReplicaHistory($this->config()),
            'ReplicaPublicKeys' => new ReplicaPublicKeys($this->config()),
            'TOTP' => new TOTP($this->config()),
            default => throw new TableException('Unknown table name: ' . $tableName)
        };
        $cache->storeTable($tableName, $table);
        return $table;
    }

    public function injectConfig(ServerConfig $config): void
    {
        $this->config = $config;
    }

    /**
     * @throws DependencyException
     */
    public function config(): ServerConfig
    {
        if (is_null($this->config)) {
            if ($GLOBALS['pkdConfig'] instanceof ServerConfig) {
                $this->config = $GLOBALS['pkdConfig'];
            } else {
                throw new DependencyException('config not injected!');
            }
        }
        return $this->config;
    }

    /**
     * This is intended for mocking in unit tests
     */
    public function setWebFinger(WebFinger $wf): self
    {
        $this->webFinger = $wf;
        return $this;
    }

    /**
     * @throws CertaintyException
     * @throws DependencyException
     * @throws SodiumException
     */
    public function webfinger(?Client $http = null): WebFinger
    {
        if (!is_null($this->webFinger)) {
            return $this->webFinger;
        }
        $this->webFinger = new WebFinger($this->config(), $http, $this->config()->getCaCertFetch());
        return $this->webFinger;
    }

    /**
     * @param string $url
     * @return ?string Returns null if URL is invalid or has no host
     */
    public static function parseUrlHost(string $url): ?string
    {
        $parsed = parse_url($url);
        if ($parsed === false) {
            return null;
        }
        return $parsed['host'] ?? null;
    }

    /**
     * @param array<array-key, mixed>|object $result
     * @return array<array-key, mixed>
     *
     * @throws TypeError
     */
    public static function assertArray(array|object $result): array
    {
        if (!is_array($result)) {
            throw new TypeError('Expected array, got object');
        }
        return $result;
    }

    /**
     * @throws TypeError
     */
    public static function assertString(mixed $value): string
    {
        if (!is_string($value)) {
            throw new TypeError('Expected string, got ' . get_debug_type($value));
        }
        return $value;
    }

    public static function assertStringOrNull(mixed $value): ?string
    {
        if (is_null($value)) {
            return null;
        }
        if (!is_string($value)) {
            throw new TypeError('Expected string or null, got ' . get_debug_type($value));
        }
        return $value;
    }

    /**
     * @throws TypeError
     */
    public static function assertInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        throw new TypeError('Expected int, got ' . get_debug_type($value));
    }

    /**
     * @param array<array-key, mixed>|object $row
     * @return array<string, string>
     * @throws TypeError
     */
    public static function rowToStringArray(array|object $row): array
    {
        if (!is_array($row)) {
            throw new TypeError('Expected array row');
        }
        $result = [];
        foreach ($row as $key => $value) {
            $result[(string) $key] = (string) $value;
        }
        return $result;
    }

    /**
     * @param array<string, mixed> $row
     * @throws TypeError
     */
    public static function decryptedString(array $row, string $key): string
    {
        if (!array_key_exists($key, $row)) {
            throw new TypeError("Key '$key' not found in decrypted row");
        }
        $value = $row[$key];
        if (is_array($value)) {
            throw new TypeError("Expected scalar for key '$key', got array");
        }
        return (string) $value;
    }

    /**
     * @param array<string, string>|string $blindIndex
     * @param ?string $key
     * @return string
     */
    public static function blindIndexValue(array|string $blindIndex, ?string $key = null): string
    {
        if (is_string($blindIndex)) {
            return $blindIndex;
        }
        if (!is_null($key)) {
            return $blindIndex[$key] ?? '';
        }
        // Return first value
        return reset($blindIndex) ?: '';
    }
}
