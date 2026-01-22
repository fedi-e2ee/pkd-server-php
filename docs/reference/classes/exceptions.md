# Exceptions

Namespace: `FediE2EE\PKDServer\Exceptions`

## Classes

- [ActivityPubException](#activitypubexception) - class
- [BaseException](#baseexception) - class
- [CacheException](#cacheexception) - class
- [ConcurrentException](#concurrentexception) - class
- [DependencyException](#dependencyexception) - class
- [FetchException](#fetchexception) - class
- [NetTraitException](#nettraitexception) - class
- [ProtocolException](#protocolexception) - class
- [RateLimitException](#ratelimitexception) - class
- [ScheduledTaskException](#scheduledtaskexception) - class
- [TableException](#tableexception) - class

---

## ActivityPubException

**class** `FediE2EE\PKDServer\Exceptions\ActivityPubException`

**File:** [`src/Exceptions/ActivityPubException.php`](../../../src/Exceptions/ActivityPubException.php)

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

---

## BaseException

**class** `FediE2EE\PKDServer\Exceptions\BaseException`

**File:** [`src/Exceptions/BaseException.php`](../../../src/Exceptions/BaseException.php)

**Extends:** `Exception`

**Implements:** `Throwable`, `Stringable`

---

## CacheException

**class** `FediE2EE\PKDServer\Exceptions\CacheException`

**File:** [`src/Exceptions/CacheException.php`](../../../src/Exceptions/CacheException.php)

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

---

## ConcurrentException

**class** `FediE2EE\PKDServer\Exceptions\ConcurrentException`

**File:** [`src/Exceptions/ConcurrentException.php`](../../../src/Exceptions/ConcurrentException.php)

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

---

## DependencyException

**class** `FediE2EE\PKDServer\Exceptions\DependencyException`

**File:** [`src/Exceptions/DependencyException.php`](../../../src/Exceptions/DependencyException.php)

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

---

## FetchException

**class** `FediE2EE\PKDServer\Exceptions\FetchException`

**File:** [`src/Exceptions/FetchException.php`](../../../src/Exceptions/FetchException.php)

**Extends:** `Exception`

**Implements:** `Throwable`, `Stringable`

---

## NetTraitException

**class** `FediE2EE\PKDServer\Exceptions\NetTraitException`

**File:** [`src/Exceptions/NetTraitException.php`](../../../src/Exceptions/NetTraitException.php)

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

---

## ProtocolException

**class** `FediE2EE\PKDServer\Exceptions\ProtocolException`

**File:** [`src/Exceptions/ProtocolException.php`](../../../src/Exceptions/ProtocolException.php)

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

---

## RateLimitException

**class** `FediE2EE\PKDServer\Exceptions\RateLimitException`

**File:** [`src/Exceptions/RateLimitException.php`](../../../src/Exceptions/RateLimitException.php)

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$rateLimitedUntil` | `?DateTimeImmutable` |  |

---

## ScheduledTaskException

**class** `FediE2EE\PKDServer\Exceptions\ScheduledTaskException`

**File:** [`src/Exceptions/ScheduledTaskException.php`](../../../src/Exceptions/ScheduledTaskException.php)

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

---

## TableException

**class** `FediE2EE\PKDServer\Exceptions\TableException`

**File:** [`src/Exceptions/TableException.php`](../../../src/Exceptions/TableException.php)

**Extends:** `FediE2EE\PKDServer\Exceptions\BaseException`

**Implements:** `Stringable`, `Throwable`

---

