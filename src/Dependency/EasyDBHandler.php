<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Dependency;

use DateTimeInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Override;
use ParagonIE\EasyDB\EasyDB;
use PDOStatement;

class EasyDBHandler extends AbstractProcessingHandler
{
    private EasyDB $db;
    private ?PDOStatement $statement = null;

    public function __construct(EasyDB $db, int|string|Level $level = Level::Debug, bool $bubble = true)
    {
        $this->db = $db;
        parent::__construct($level, $bubble);
    }

    private function initialize(): void
    {
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
        $this->statement->execute([
            'channel' => $record->channel,
            'level' => $record->level->value,
            'message' => $record->formatted,
        ]);
    }
}
