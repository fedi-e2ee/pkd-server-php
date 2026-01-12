# Configuration Reference

This document describes configuration files in the `config/` directory.

## Configuration Files

| File | Purpose |
|------|--------|
| [`config/aux-type-allow-list.php`](../config/aux-type-allow-list.php) | Auxiliary data type allowlist |
| [`config/aux-type-registry.php`](../config/aux-type-registry.php) | Auxiliary data type registry |
| [`config/certainty.php`](../config/certainty.php) | CA certificate bundle configuration |
| [`config/ciphersweet.php`](../config/ciphersweet.php) | CipherSweet encryption configuration |
| [`config/database.php`](../config/database.php) | Database connection configuration |
| [`config/hpke.php`](../config/hpke.php) | HPKE encryption configuration |
| [`config/logs.php`](../config/logs.php) | Logging configuration |
| [`config/params.php`](../config/params.php) | Server parameters configuration |
| [`config/redis.php`](../config/redis.php) | Redis cache configuration |
| [`config/routes.php`](../config/routes.php) | HTTP routing configuration |
| [`config/signing-keys.php`](../config/signing-keys.php) | Signing keys configuration |
| [`config/twig.php`](../config/twig.php) | Twig template engine configuration |

## Local Configuration

Local configuration overrides are stored in `config/local/`. These files are not tracked in version control and allow deployment-specific settings.

To create local configuration, copy the base config file:

```bash
cp config/params.php config/local/params.php
```

Then modify the local copy as needed.
