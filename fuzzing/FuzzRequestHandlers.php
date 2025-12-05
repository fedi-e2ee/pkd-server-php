<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Fuzzing;

use FediE2EE\PKDServer\Meta\Route;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionMethod;
use ReflectionObject;
use Laminas\Diactoros\{
    Stream,
    ServerRequest
};
use FediE2EE\PKDServer\RequestHandlers\{
    IndexPage
};
use FediE2EE\PKDServer\RequestHandlers\ActivityPub\{
    Finger,
    Inbox,
    UserPage
};
use FediE2EE\PKDServer\RequestHandlers\Api\{
    Actor,
    Checkpoint,
    Extensions,
    GetAuxData,
    GetKey,
    History,
    HistorySince,
    HistoryView,
    Info,
    ListAuxData,
    ListKeys,
    ReplicaInfo,
    Replicas,
    ServerPublicKey,
    TotpDisenroll,
    TotpEnroll,
    TotpRotate
};
use PhpFuzzer\Config;

/** @var Config $config */

require_once dirname(__DIR__) . '/vendor/autoload.php';

function get_route(RequestHandlerInterface $handler): ?string
{
    $reflection = new ReflectionObject($handler);
    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->getName() === 'handle') {
            $route = $method->getAttributes(Route::class);
            if (count($route) === 1) {
                $args = $route[0]->getArguments();
                return array_shift($args);
            }
        }
    }
    return null;
}

$fuzzableHandlerClasses = [
    IndexPage::class,
    Finger::class,
    Inbox::class,
    UserPage::class,
    Actor::class,
    Checkpoint::class,
    Extensions::class,
    GetAuxData::class,
    GetKey::class,
    History::class,
    HistorySince::class,
    HistoryView::class,
    Info::class,
    ListAuxData::class,
    ListKeys::class,
    ReplicaInfo::class,
    Replicas::class,
    ServerPublicKey::class,
    TotpDisenroll::class,
    TotpEnroll::class,
    TotpRotate::class,
];
$fuzzableHandlers = [];
foreach ($fuzzableHandlerClasses as $class) {
    $cl = new $class();
    if (method_exists($cl, 'injectConfig')) {
        $cl->injectConfig($GLOBALS['pkdConfig']);
    }
    $fuzzableHandlers []= $cl;
}

$config->setTarget(function (string $input) use ($fuzzableHandlers) {
    $r = random_int(0, count($fuzzableHandlers) - 1);
    $handler = $fuzzableHandlers[$r];
    $stream = new Stream('php://memory', 'rw');
    $stream->write($input);
    $stream->rewind();

    $uri = get_route($handler);

    $request = new ServerRequest([], [], $uri, 'POST', $stream);
    $handler->handle($request);
});
