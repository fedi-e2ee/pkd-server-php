<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Config;

use FediE2EE\PKDServer\RequestHandlers\Api\{
    Actor,
    BurnDown,
    Checkpoint,
    Extensions,
    GetAuxData,
    GetKey,
    History,
    HistoryCosign,
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
use FediE2EE\PKDServer\Middleware\RateLimitMiddleware;
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
    $r->map('GET', '/replicas/{replica_id}/actor/{actor_id}/keys/key/{key_id}', [ReplicaInfo::class, 'actorKey']);
    $r->map('GET', '/replicas/{replica_id}/actor/{actor_id}/keys', [ReplicaInfo::class, 'actorKeys']);
    $r->map('GET', '/replicas/{replica_id}/actor/{actor_id}/auxiliary/{aux_data_id}', [ReplicaInfo::class, 'actorAuxiliaryItem']);
    $r->map('GET', '/replicas/{replica_id}/actor/{actor_id}/auxiliary', [ReplicaInfo::class, 'actorAuxiliary']);
    $r->map('GET', '/replicas/{replica_id}/actor/{actor_id}', [ReplicaInfo::class, 'actor']);
    $r->map('GET', '/replicas/{replica_id}/history/since/{hash}', [ReplicaInfo::class, 'historySince']);
    $r->map('GET', '/replicas/{replica_id}/history', [ReplicaInfo::class, 'history']);
    $r->map('GET', '/replicas/{replica_id}', ReplicaInfo::class);
    $r->map('GET', '/replicas', Replicas::class);
    $r->map('GET', '/server-public-key', ServerPublicKey::class);

    $r->map('POST', '/checkpoint', Checkpoint::class);
    $r->map('POST', '/history/cosign/{hash}', HistoryCosign::class);
    $r->map('POST', '/burndown', BurnDown::class)
        ->lazyMiddleware(RateLimitMiddleware::class);
    $r->map('POST', '/revoke', Revoke::class)
        ->lazyMiddleware(RateLimitMiddleware::class);
    $r->map('POST', '/totp/enroll', TotpEnroll::class)
        ->lazyMiddleware(RateLimitMiddleware::class);
    $r->map('POST', '/totp/disenroll', TotpDisenroll::class)
        ->lazyMiddleware(RateLimitMiddleware::class);
    $r->map('POST', '/totp/rotate', TotpRotate::class)
        ->lazyMiddleware(RateLimitMiddleware::class);
});
// ActivityPub integration
$router->map('GET', '/.well-known/webfinger', Finger::class);
$router->map(['GET', 'POST'], '/users/{user_id}/inbox', Inbox::class);
$router->map('GET', '/users/{user_id}', UserPage::class);

// Index page just to have something basic:
$router->map('GET', '/', IndexPage::class);

return $router;
