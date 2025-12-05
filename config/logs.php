<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Config;

use FediE2EE\PKDServer\Dependency\EasyDBHandler;
use Monolog\Logger;

// use Monolog\Handler\StreamHandler;
// use Psr\Log\LogLevel;
// use const FediE2EE\PKDServer\PKD_SERVER_ROOT;

/* Defer to local config (if applicable) */
if (file_exists(__DIR__ . '/local/logs.php')) {
    return require_once __DIR__ . '/local/logs.php';
}

$logger = new Logger('logs');
$logger->pushHandler(new EasyDBHandler($GLOBALS['pkdConfig']->getDb()));
// $logger->pushHandler(new StreamHandler(PKD_SERVER_ROOT . '/logs/' . date('Y-m-d') . '.log', LogLevel::ERROR));
return $logger;
