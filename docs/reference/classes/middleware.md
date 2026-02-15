# Middleware

Namespace: `FediE2EE\PKDServer\Middleware`

## Classes

- [RateLimitMiddleware](#ratelimitmiddleware) - class

---

## RateLimitMiddleware

**class** `FediE2EE\PKDServer\Middleware\RateLimitMiddleware`

**File:** [`src/Middleware/RateLimitMiddleware.php`](../../../src/Middleware/RateLimitMiddleware.php)

**Implements:** `Psr\Http\Server\MiddlewareInterface`

### Methods

#### [`__construct`](../../../src/Middleware/RateLimitMiddleware.php#L25-L30)

Returns `void`

**Parameters:**

- `$config`: `?FediE2EE\PKDServer\ServerConfig` = null

#### [`getConfig`](../../../src/Middleware/RateLimitMiddleware.php#L32-L38)

Returns `FediE2EE\PKDServer\ServerConfig`

#### [`process`](../../../src/Middleware/RateLimitMiddleware.php#L56-L101)

Returns `Psr\Http\Message\ResponseInterface`

**Attributes:** `#[Override]`

Pre-process the request BEFORE it reaches the request handler.

If there is no rate-limiting implementation enabled, just handle the request. If there is rate-limiting enabled, this will either: 1. Throw a RateLimitException if you are being rate-limited. 2. Wrap request handling in a try/catch which increases rate-limiting penalties when an uncaught exception occurs. If a RateLimitException occurs, it will be handled by public/index.php and the request never actually reaches the RequestHandler.

**Parameters:**

- `$request`: `Psr\Http\Message\ServerRequestInterface`
- `$handler`: `Psr\Http\Server\RequestHandlerInterface`

**Throws:** `RateLimitException`

---

