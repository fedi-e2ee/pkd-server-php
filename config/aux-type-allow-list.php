<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Config;

/*
use FediE2EE\PKD\Extensions\AuxDataTypes\{
    AgeV1,
    SshV2
};
*/

if (file_exists(__DIR__ . '/local/aux-type-allow-list.php')) {
    return require_once __DIR__ . '/local/aux-type-allow-list.php';
}

/*
return [AgeV1::AUX_DATA_TYPE, SshV2::AUX_DATA_TYPE];
*/
return [];
