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
    PublicKeys,
    TOTP
};
use FediE2EE\PKDServer\Exceptions\TableException;
use FediE2EE\PKDServer\{
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
            'Actors' => new Actors($this->config()),
            'ActivityStreamQueue' => new ActivityStreamQueue($this->config()),
            'AuxData' => new AuxData($this->config()),
            'MerkleState' => new MerkleState($this->config()),
            'PublicKeys' => new PublicKeys($this->config()),
            'TOTP' => new TOTP($this->config()),
            default => throw new TableException('Unknown table name: ' . $tableName)
        };
        $cache->storeTable($tableName, $table);
        return $table;
    }

    function injectConfig(ServerConfig $config): void
    {
        $this->config = $config;
    }

    /**
     * @throws DependencyException
     */
    public function config(): ServerConfig
    {
        if (is_null($this->config)) {
            if ($GLOBALS['config'] instanceof ServerConfig) {
                $this->config = $GLOBALS['config'];
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
        $this->webFinger = new WebFinger($http, $this->config->getCaCertFetch());
        return $this->webFinger;
    }
}
