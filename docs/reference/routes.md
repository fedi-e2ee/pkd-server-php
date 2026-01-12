# API Routes Reference

This document lists all API routes defined via `#[Route]` attributes.

| Route Pattern | Handler Class | Method |
|---------------|---------------|--------|
| `/` | `RequestHandlers\IndexPage` | `handle` |
| `/.well-known/webfinger` | `RequestHandlers\ActivityPub\Finger` | `handle` |
| `/api/checkpoint` | `RequestHandlers\Api\Checkpoint` | `handle` |
| `/api/extensions` | `RequestHandlers\Api\Extensions` | `handle` |
| `/api/history` | `RequestHandlers\Api\History` | `handle` |
| `/api/history/since/{hash}` | `RequestHandlers\Api\HistorySince` | `handle` |
| `/api/history/view/{hash}` | `RequestHandlers\Api\HistoryView` | `handle` |
| `/api/info` | `RequestHandlers\Api\Info` | `handle` |
| `/api/replicas` | `RequestHandlers\Api\Replicas` | `handle` |
| `/api/replicas/{replica_id}` | `RequestHandlers\Api\ReplicaInfo` | `handle` |
| `/api/revoke` | `RequestHandlers\Api\Revoke` | `handle` |
| `/api/server-public-key` | `RequestHandlers\Api\ServerPublicKey` | `handle` |
| `/api/totp/disenroll` | `RequestHandlers\Api\TotpDisenroll` | `handle` |
| `/api/totp/enroll` | `RequestHandlers\Api\TotpEnroll` | `handle` |
| `/api/totp/rotate` | `RequestHandlers\Api\TotpRotate` | `handle` |
| `/history/cosign/{hash}` | `RequestHandlers\Api\HistoryCosign` | `handle` |
| `/user/{user_id}` | `RequestHandlers\ActivityPub\UserPage` | `handle` |
| `/user/{user_id}/inbox` | `RequestHandlers\ActivityPub\Inbox` | `handle` |
| `/api/actor/{actor_id}` | `RequestHandlers\Api\Actor` | `handle` |
| `/api/actor/{actor_id}/auxiliary` | `RequestHandlers\Api\ListAuxData` | `handle` |
| `/api/actor/{actor_id}/auxiliary/{aux_data_id}` | `RequestHandlers\Api\GetAuxData` | `handle` |
| `/api/actor/{actor_id}/key/{key_id}` | `RequestHandlers\Api\GetKey` | `handle` |
| `/api/actor/{actor_id}/keys` | `RequestHandlers\Api\ListKeys` | `handle` |

## Route Details

Routes are configured in `config/routes.php` using League\Route.
The `#[Route]` attribute on handler methods is used for documentation purposes.
