<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\PublicWebRoot;

use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use FediE2EE\PKDServer\ServerConfig;
use Throwable;

require_once dirname(__DIR__) . '/autoload.php';

// Make sure teh configuration has been loaded
if (!isset($GLOBALS['pkdConfig']) || !($GLOBALS['pkdConfig'] instanceof ServerConfig)) {
    http_response_code(500);
    header('Content-Type: text/plain');
    die('Server configuration not found!');
}

// Route request
try {
    $router = $GLOBALS['pkdConfig']->getRouter();
    $request = ServerRequestFactory::fromGlobals();
    (new SapiEmitter)->emit(
        $router->dispatch($request)
    );
} catch (Throwable $ex) {
    http_response_code(500);
    if (defined('PKD_SERVER_DEBUG')) {
        header('Content-Type: text/plain');
        echo $ex->getMessage(), PHP_EOL;
        echo 'Code: ', $ex->getCode(), PHP_EOL;
        echo str_repeat('-', 76), PHP_EOL;
        echo $ex->getTraceAsString(), PHP_EOL;
    }
    exit(1);
}
