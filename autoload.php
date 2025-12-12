<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer;

use FediE2EE\PKDServer\Dependency\InjectConfigStrategy;

/**
 * This file should be loaded first.
 */
const PKD_SERVER_ROOT = __DIR__;

// Ensure composer is loaded
require_once PKD_SERVER_ROOT . '/vendor/autoload.php';

// We load this config and store it in a global variable for use in public/index.php
$GLOBALS['pkdConfig'] = new ServerConfig(require_once PKD_SERVER_ROOT . '/config/params.php')
    ->withAuxDataRegistry(require_once PKD_SERVER_ROOT . '/config/aux-type-registry.php')
    ->withAuxDataTypeAllowList(require_once PKD_SERVER_ROOT . '/config/aux-type-allow-list.php')
    ->withCACertFetch(require_once PKD_SERVER_ROOT . '/config/certainty.php')
    ->withCipherSweet(require_once PKD_SERVER_ROOT . '/config/ciphersweet.php')
    ->withDatabase(require_once PKD_SERVER_ROOT . '/config/database.php')
    ->withRouter(require_once PKD_SERVER_ROOT . '/config/routes.php')
    ->withHPKE(require_once PKD_SERVER_ROOT . '/config/hpke.php')
    ->withSigningKeys(require_once PKD_SERVER_ROOT . '/config/signing-keys.php')
    ->withTwig(require_once PKD_SERVER_ROOT . '/config/twig.php')
;

// We have to guarantee `$GLOBALS['pkdConfig']` exists before calling logger.php
$GLOBALS['pkdConfig']
    ->withLogger(require_once PKD_SERVER_ROOT . '/config/logs.php');

// Optional dependencies go last:
$GLOBALS['pkdConfig']
    ->withOptionalRedisClient(require_once PKD_SERVER_ROOT . '/config/redis.php');

// Set the config injection strategy to the router
$GLOBALS['pkdConfig']
    ->getRouter()
    ->setStrategy(new InjectConfigStrategy());
