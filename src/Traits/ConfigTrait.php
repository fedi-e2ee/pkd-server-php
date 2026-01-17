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

trait ConfigTrait
{
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
        $this->webFinger = new WebFinger($this->config, $http, $this->config->getCaCertFetch());
        return $this->webFinger;
    }
}
