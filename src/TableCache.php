<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer;

use FediE2EE\PKDServer\Exceptions\CacheException;
use function array_key_exists;

class TableCache
{
    private static ?TableCache $instance = null;

    /**
     * @param array<string, Table> $tables
     */
    protected function __construct(
        private array $tables = []
    ) {}

    public static function instance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function clearCache(): void
    {
        $this->tables = [];
    }

    /**
     * @throws CacheException
     */
    public function fetchTable(string $tableName): Table
    {
        if (!array_key_exists($tableName, $this->tables)) {
            throw new CacheException('Cache miss');
        }
        return $this->tables[$tableName];
    }

    public function hasTable(string $tableName): bool
    {
        return array_key_exists($tableName, $this->tables);
    }

    public function storeTable(string $tableName, Table $table): static
    {
        $this->tables[$tableName] = $table;
        return $this;
    }
}
