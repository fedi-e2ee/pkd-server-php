<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Dependency;

use Exception;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Override;
use ParagonIE\EasyDB\EasyDB;
use PDOStatement;
use RuntimeException;

use function is_null;

class EasyDBHandler extends AbstractProcessingHandler
{
    private EasyDB $db;
    private ?PDOStatement $statement = null;

    public function __construct(EasyDB $db, int|string|Level $level = Level::Debug, bool $bubble = true)
    {
        $this->db = $db;
        /** @psalm-suppress ArgumentTypeCoercion */
        parent::__construct($level, $bubble);
    }

    private function initialize(): void
    {
        $this->ensureTableExists();
        $this->statement = $this->db->prepare(
            "INSERT INTO pkd_log (channel, level, message) VALUES (:channel, :level, :message)"
        );
    }

    #[Override]
    protected function write(LogRecord $record): void
    {
        if (is_null($this->statement)) {
            $this->initialize();
        }
        // After initialize(), $this->statement is guaranteed to be non-null
        /** @psalm-suppress PossiblyNullReference */
        $this->statement->execute([
            'channel' => $record->channel,
            'level' => $record->level->value,
            'message' => $record->formatted,
        ]);
    }

    private function ensureTableExists(): void
    {
        $tries = 0;
        while (!$this->tableExists() && ++$tries <= 10) {
            match ($this->db->getDriver()) {
                'mysql' => $this->db->exec("CREATE TABLE IF NOT EXISTS pkd_log (
                        logid BIGINT PRIMARY KEY AUTO_INCREMENT,
                        channel TEXT,
                        level INTEGER,
                        message LONGTEXT,
                        created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );"),
                'pgsql' => $this->db->exec("CREATE TABLE IF NOT EXISTS pkd_log (
                        logid BIGSERIAL PRIMARY KEY,
                        channel TEXT,
                        level INTEGER,
                        message TEXT,
                        created TIMESTAMP DEFAULT NOW()
                    );"),
                'sqlite' => $this->db->exec("CREATE TABLE IF NOT EXISTS pkd_log (
                        logid INTEGER PRIMARY KEY AUTOINCREMENT,
                        channel INTEGER,
                        level INTEGER,
                        message TEXT,
                        created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );"),
                default =>
                    throw new RuntimeException("Unsupported driver: {$this->db->getDriver()}"),
            };
        }
        if ($tries >= 10) {
            throw new RuntimeException('Cannot create pkd_log table as a contingency');
        }
    }

    private function tableExists(): bool
    {
        try {
            return match ($this->db->getDriver()) {
                'mysql' => $this->db->exists(
                    "SELECT COUNT(*) FROM information_schema.tables 
                         WHERE table_schema = DATABASE() 
                         AND table_name = 'pkd_log'"
                ),
                'pgsql' => $this->db->exists(
                    "SELECT COUNT(*) FROM information_schema.tables 
                         WHERE table_schema = 'public' 
                         AND table_name = 'pkd_log'"
                ),
                'sqlite' => $this->db->exists(
                    "SELECT COUNT(*) FROM sqlite_master 
                         WHERE type = 'table' AND name = 'pkd_log'"
                ),
                default =>
                    throw new RuntimeException("Unsupported driver: {$this->db->getDriver()}"),
            };
        } catch (Exception) {
            return false;
        }
    }
}
