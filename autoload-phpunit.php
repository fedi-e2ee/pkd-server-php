<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer;

use Exception;
use FediE2EE\PKD\Extensions\ExtensionInterface;
use ParagonIE\EasyDB\EasyDB;
use ParagonIE\EasyDB\EasyDBCache;
use PDO;
use RuntimeException;

require_once __DIR__ . '/vendor/autoload.php';

function tableExists(EasyDB $db, string $tableName): bool
{
    try {
        return match ($db->getDriver()) {
            'mysql' => $db->exists(
                "SELECT COUNT(*) FROM information_schema.tables 
                         WHERE table_schema = DATABASE() 
                         AND table_name = ?",
                $tableName
            ),
            'pgsql' => $db->exists(
                "SELECT COUNT(*) FROM information_schema.tables 
                         WHERE table_schema = 'public' 
                         AND table_name = ?",
                $tableName
            ),
            'sqlite' => $db->exists(
                "SELECT COUNT(*) FROM sqlite_master 
                         WHERE type='table' AND name=?",
                $tableName
            ),
            default => throw new RuntimeException("Unsupported driver: {$db->getDriver()}"),
        };
    } catch (Exception) {
        return false;
    }
}

if (!isset($GLOBALS['pkdConfig'])) {
    echo 'Could not load PHP config.', PHP_EOL;
    exit(255);
}
if (!($GLOBALS['pkdConfig'] instanceof ServerConfig)) {
    echo 'Config is not an instance of the correct class.', PHP_EOL;
    exit(254);
}

(function () {
    $pkdConfig = $GLOBALS['pkdConfig'];
    if (!($pkdConfig instanceof ServerConfig)) {
        echo 'Config is not an instance of the correct class.', PHP_EOL;
        exit(253);
    }
    if ($pkdConfig->getDb()->getDriver() === 'sqlite') {
        if (!is_dir(__DIR__ . '/tmp/db/')) {
            mkdir(__DIR__ . '/tmp/db/');
        }
        $temp = __DIR__ . '/tmp/db/' . sodium_bin2hex(random_bytes(16)) . '-test.db';
        $pkdConfig->withDatabase(new EasyDBCache(new PDO('sqlite:' . $temp)));
    }
    $db = $GLOBALS['pkdConfig']->getDb();
    if (!tableExists($db, 'pkd_merkle_state')) {
        $argv_backup = $_SERVER['argv'];
        $_SERVER['argv'] = [$_SERVER['argv'][0]];
        require __DIR__ . '/cmd/init-database.php';
        $_SERVER['argv'] = $argv_backup;
        if (tableExists($db, 'pkd_loh')) {
            echo 'Imported!', PHP_EOL;
        } else {
            // This is normally dangerous, but we need to trigger it again just to be sure:
            // nosemgrep: php.lang.security.exec-use.exec-use
            shell_exec(PHP_BINARY . ' ' . __DIR__ . '/cmd/init-database.php');
        }
    }

    // Create test extension:
    $testRegistry = new class implements ExtensionInterface {
        public function getAuxDataType(): string
        {
            return 'test-v1';
        }

        public function getRejectionReason(): string
        {
            return '';
        }

        public function isValid(string $auxData): bool
        {
            return true;
        }
    };
    $pkdConfig->getAuxDataRegistry()->addAuxDataType($testRegistry, 'test');
    // Add "test" extension to allow list
    $pkdConfig->withAuxDataTypeAllowList(
        $pkdConfig->getAuxDataTypeAllowList() + ['test-v1']
    );
})();
