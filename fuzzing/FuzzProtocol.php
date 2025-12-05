<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Fuzzing;

use FediE2EE\PKDServer\Exceptions\ActivityPubException;
use FediE2EE\PKDServer\ActivityPub\ActivityStream;
use FediE2EE\PKDServer\Protocol;
use PhpFuzzer\Config;
use TypeError;

/** @var Config $config */

require_once dirname(__DIR__) . '/vendor/autoload.php';

$protocol = new Protocol($GLOBALS['pkdConfig']);
$config->setTarget(function (string $input) use ($protocol) {
    try {
        $protocol->process(ActivityStream::fromString($input));
    } catch (TypeError|ActivityPubException) {
        // We don't care about invalid inputs, only other behavior.
    }
});
