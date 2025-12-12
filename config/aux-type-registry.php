<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Config;

use FediE2EE\PKD\Extensions\Registry;

if (file_exists(__DIR__ . '/local/aux-type-registry.php')) {
    return require_once __DIR__ . '/local/aux-type-registry.php';
}

return new Registry();
