<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Config;

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
    Replicas,
    ReplicaInfo,
    Revoke,
    ServerPublicKey,
    TotpRotate,
    TotpDisenroll,
    TotpEnroll
};
use FediE2EE\PKDServer\RequestHandlers\ActivityPub\{
    Finger,
    Inbox,
    UserPage
};
use FediE2EE\PKDServer\RequestHandlers\IndexPage;
use League\Route\Router;
use League\Route\RouteGroup;

/* Defer to local config (if applicable) */
if (file_exists(__DIR__ . '/local/routes.php')) {
    return require_once __DIR__ . '/local/routes.php';
}
$router = new Router();

$router->group('/api', function(RouteGroup $r) use ($router) {
    $r->map('GET', '/history/since/{hash}', HistorySince::class);
    $r->map('GET', '/history/view/{hash}', HistoryView::class);
    $r->map('GET', '/history', History::class);
    $r->map('GET', '/actor/{actor_id}/auxiliary/{aux_data_id}', GetAuxData::class);
    $r->map('GET', '/actor/{actor_id}/auxiliary', ListAuxData::class);
    $r->map('GET', '/actor/{actor_id}/key/{key_id}', GetKey::class);
    $r->map('GET', '/actor/{actor_id}/keys', ListKeys::class);
    $r->map('GET', '/actor/{actor_id}', Actor::class);
    $r->map('GET', '/extensions', Extensions::class);
    $r->map('GET', '/info', Info::class);
    $r->map('GET', '/replicas/{replica_id}', ReplicaInfo::class);
    $r->map('GET', '/replicas', Replicas::class);
    $r->map('GET', '/server-public-key', ServerPublicKey::class);

    $r->map('POST', '/checkpoint', Checkpoint::class);
    $r->map('POST', '/revoke', Revoke::class);
    $r->map('POST', '/totp/enroll', TotpEnroll::class);
    $r->map('POST', '/totp/disenroll', TotpDisenroll::class);
    $r->map('POST', '/totp/rotate', TotpRotate::class);
});
// ActivityPub integration
$router->map('GET', '/.well-known/webfinger', Finger::class);
$router->map(['GET', 'POST'], '/users/{user_id}/inbox', Inbox::class);
$router->map('GET', '/users/{user_id}', UserPage::class);

// Index page just to have something basic:
$router->map('GET', '/', IndexPage::class);

return $router;
