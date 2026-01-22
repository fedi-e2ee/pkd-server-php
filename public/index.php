<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\PublicWebRoot;

use DateTimeInterface;
use FediE2EE\PKDServer\Exceptions\RateLimitException;
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
    if (defined('PKD_SERVER_DEBUG')) {
        http_response_code(500);
        header('Content-Type: text/plain');
        echo 'Exception type: ', $ex::class, PHP_EOL, PHP_EOL;
        echo $ex->getMessage(), PHP_EOL;
        echo 'Code: ', $ex->getCode(), PHP_EOL;
        echo str_repeat('-', 76), PHP_EOL;
        echo $ex->getTraceAsString(), PHP_EOL;
    } elseif ($ex instanceof RateLimitException) {
        // Rate-limited by the Middleware
        http_response_code(420);
        echo $ex->getMessage(), PHP_EOL;
        if (!is_null($ex->rateLimitedUntil)) {
            echo 'Try again after: ',
                $ex->rateLimitedUntil->format(DateTimeInterface::ATOM),
                PHP_EOL;
        }
    } else {
        http_response_code(500);
    }
    exit(1);
}
