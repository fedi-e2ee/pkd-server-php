<?php
declare(strict_types=1);

use GetOpt\GetOpt;
use GetOpt\Option;
use FediE2EE\PKDServer\ServerConfig;
use const FediE2EE\PKDServer\PKD_SERVER_ROOT;;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$getopt = new GetOpt([
    Option::create(null, 'force', GetOpt::NO_ARGUMENT)
        ->setDescription('Force re-installation'),
]);
$getopt->process();

if (!($GLOBALS['pkdConfig'] instanceof ServerConfig)) {
    throw new TypeError();
}
$db = $GLOBALS['pkdConfig']->getDb();
$driver = $db->getDriver();
$dbDriver = $driver;
if ($dbDriver === 'pgsql') {
    $dbDriver = 'postgresql';
}

$dir = dirname(__DIR__) . '/sql/' . $dbDriver;
if (!is_dir($dir)) {
    echo 'Unsupported driver: ', $driver, PHP_EOL;
    exit(1);
}

$files = scandir($dir);
foreach ($files as $file) {
    if (in_array($file, ['.', '..'])) {
        continue;
    }
    $path = $dir . '/' . $file;
    if (is_file($path)) {
        if (pathinfo($path, PATHINFO_EXTENSION) !== 'sql') {
            continue;
        }
        $sql = file_get_contents($path);
        if (empty($sql)) {
            continue;
        }
        try {
            $db->exec($sql);
        } catch (Throwable $ex) {
            echo 'Error running ', $path, ':', PHP_EOL,
            $ex->getMessage(), PHP_EOL;
            exit(1);
        }
    }
}

// Make lazy copies of these config classes so that they can be modified without affecting Git history.
if (!file_exists(PKD_SERVER_ROOT . '/config/local/database.php')) {
    @copy(PKD_SERVER_ROOT . '/config/database.php', PKD_SERVER_ROOT . '/config/local/database.php');
}
if (!file_exists(PKD_SERVER_ROOT . '/config/local/params.php')) {
    @copy(PKD_SERVER_ROOT . '/config/params.php', PKD_SERVER_ROOT . '/config/local/params.php');
}
