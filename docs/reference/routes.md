# API Routes Reference

This document lists all API routes defined via `#[Route]` attributes.

## Routes

| Route Pattern | Handler Class | Method |
|---------------|---------------|--------|
| `/` | [`RequestHandlers\IndexPage`](../../src/RequestHandlers/IndexPage.php) | `handle` |
| `/.well-known/webfinger` | [`RequestHandlers\ActivityPub\Finger`](../../src/RequestHandlers/ActivityPub/Finger.php) | `handle` |
| `/api/checkpoint` | [`RequestHandlers\Api\Checkpoint`](../../src/RequestHandlers/Api/Checkpoint.php) | `handle` |
| `/api/extensions` | [`RequestHandlers\Api\Extensions`](../../src/RequestHandlers/Api/Extensions.php) | `handle` |
| `/api/history` | [`RequestHandlers\Api\History`](../../src/RequestHandlers/Api/History.php) | `handle` |
| `/api/history/since/{hash}` | [`RequestHandlers\Api\HistorySince`](../../src/RequestHandlers/Api/HistorySince.php) | `handle` |
| `/api/history/view/{hash}` | [`RequestHandlers\Api\HistoryView`](../../src/RequestHandlers/Api/HistoryView.php) | `handle` |
| `/api/info` | [`RequestHandlers\Api\Info`](../../src/RequestHandlers/Api/Info.php) | `handle` |
| `/api/replicas` | [`RequestHandlers\Api\Replicas`](../../src/RequestHandlers/Api/Replicas.php) | `handle` |
| `/api/replicas/{replica_id}` | [`RequestHandlers\Api\ReplicaInfo`](../../src/RequestHandlers/Api/ReplicaInfo.php) | `handle` |
| `/api/replicas/{replica_id}/actor/{actor_id}` | [`RequestHandlers\Api\ReplicaInfo`](../../src/RequestHandlers/Api/ReplicaInfo.php) | `actor` |
| `/api/replicas/{replica_id}/actor/{actor_id}/auxiliary` | [`RequestHandlers\Api\ReplicaInfo`](../../src/RequestHandlers/Api/ReplicaInfo.php) | `actorAuxiliary` |
| `/api/replicas/{replica_id}/actor/{actor_id}/auxiliary/{aux_data_id}` | [`RequestHandlers\Api\ReplicaInfo`](../../src/RequestHandlers/Api/ReplicaInfo.php) | `actorAuxiliaryItem` |
| `/api/replicas/{replica_id}/actor/{actor_id}/keys` | [`RequestHandlers\Api\ReplicaInfo`](../../src/RequestHandlers/Api/ReplicaInfo.php) | `actorKeys` |
| `/api/replicas/{replica_id}/actor/{actor_id}/keys/key/{key_id}` | [`RequestHandlers\Api\ReplicaInfo`](../../src/RequestHandlers/Api/ReplicaInfo.php) | `actorKey` |
| `/api/replicas/{replica_id}/history` | [`RequestHandlers\Api\ReplicaInfo`](../../src/RequestHandlers/Api/ReplicaInfo.php) | `history` |
| `/api/replicas/{replica_id}/history/since/{hash}` | [`RequestHandlers\Api\ReplicaInfo`](../../src/RequestHandlers/Api/ReplicaInfo.php) | `historySince` |
| `/api/revoke` | [`RequestHandlers\Api\Revoke`](../../src/RequestHandlers/Api/Revoke.php) | `handle` |
| `/api/server-public-key` | [`RequestHandlers\Api\ServerPublicKey`](../../src/RequestHandlers/Api/ServerPublicKey.php) | `handle` |
| `/api/totp/disenroll` | [`RequestHandlers\Api\TotpDisenroll`](../../src/RequestHandlers/Api/TotpDisenroll.php) | `handle` |
| `/api/totp/enroll` | [`RequestHandlers\Api\TotpEnroll`](../../src/RequestHandlers/Api/TotpEnroll.php) | `handle` |
| `/api/totp/rotate` | [`RequestHandlers\Api\TotpRotate`](../../src/RequestHandlers/Api/TotpRotate.php) | `handle` |
| `/history/cosign/{hash}` | [`RequestHandlers\Api\HistoryCosign`](../../src/RequestHandlers/Api/HistoryCosign.php) | `handle` |
| `/user/{user_id}` | [`RequestHandlers\ActivityPub\UserPage`](../../src/RequestHandlers/ActivityPub/UserPage.php) | `handle` |
| `/user/{user_id}/inbox` | [`RequestHandlers\ActivityPub\Inbox`](../../src/RequestHandlers/ActivityPub/Inbox.php) | `handle` |
| `/api/actor/{actor_id}` | [`RequestHandlers\Api\Actor`](../../src/RequestHandlers/Api/Actor.php) | `handle` |
| `/api/actor/{actor_id}/auxiliary` | [`RequestHandlers\Api\ListAuxData`](../../src/RequestHandlers/Api/ListAuxData.php) | `handle` |
| `/api/actor/{actor_id}/auxiliary/{aux_data_id}` | [`RequestHandlers\Api\GetAuxData`](../../src/RequestHandlers/Api/GetAuxData.php) | `handle` |
| `/api/actor/{actor_id}/key/{key_id}` | [`RequestHandlers\Api\GetKey`](../../src/RequestHandlers/Api/GetKey.php) | `handle` |
| `/api/actor/{actor_id}/keys` | [`RequestHandlers\Api\ListKeys`](../../src/RequestHandlers/Api/ListKeys.php) | `handle` |

## Route Details

Routes are configured in [`config/routes.php`](../config/routes.php) using League\Route.
The `#[Route]` attribute on handler methods is used for documentation purposes.
