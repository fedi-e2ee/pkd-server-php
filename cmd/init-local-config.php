<?php
declare(strict_types=1);

use const FediE2EE\PKDServer\PKD_SERVER_ROOT;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Make lazy copies of these config classes so that they can be modified without affecting Git history.
function lazy_copy(string $filename): void
{
    @copy(
        PKD_SERVER_ROOT . '/config/' . $filename . '.php',
        PKD_SERVER_ROOT . '/config/local/' . $filename . '.php'
    );
}
lazy_copy('aux-type-allow-list');
lazy_copy('aux-type-registry');
lazy_copy('database');
lazy_copy('params');
