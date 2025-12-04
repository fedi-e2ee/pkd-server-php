<?php
declare(strict_types=1);

/**
 * Run this when deploying a new environment.
 */


if (is_readable(dirname(__DIR__, 2) . '/config/local/params.php')) {
    echo 'config/local/params.php already exists.', PHP_EOL;
    exit(1);
}
require_once __DIR__ . '/init-local-config.php';
require_once __DIR__ . '/init-database.php';
