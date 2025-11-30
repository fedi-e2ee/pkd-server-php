<?php
declare(strict_types=1);

use const FediE2EE\PKDServer\PKD_SERVER_ROOT;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Make lazy copies of these config classes so that they can be modified without affecting Git history.
copy(PKD_SERVER_ROOT . '/config/database.php', PKD_SERVER_ROOT . '/config/local/database.php');
copy(PKD_SERVER_ROOT . '/config/params.php', PKD_SERVER_ROOT . '/config/local/params.php');
