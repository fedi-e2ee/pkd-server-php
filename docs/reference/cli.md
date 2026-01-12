# Command-Line Scripts Reference

This document describes CLI scripts in the `cmd/` directory.

## Available Scripts

| Script | Purpose |
|--------|--------|
| `cmd\cron-setup.php` | Set up cron jobs for scheduled tasks |
| `cmd\init-database.php` | Initialize the database schema |
| `cmd\init-local-config.php` | Generate local configuration files |
| `cmd\init.php` | Run this when deploying a new environment. |
| `cmd\scheduled-tasks.php` | Run scheduled background tasks |

## Usage

Run scripts from the project root:

```bash
php cmd/init.php
```

### Initialization

For new deployments, run `cmd/init.php` which will:
1. Generate local configuration files
2. Initialize the database schema

### Scheduled Tasks

The `cmd/scheduled-tasks.php` script should be run via cron every minute:

```cron
* * * * * /usr/bin/php /path/to/pkd-server/cmd/scheduled-tasks.php
```

This handles:
- ActivityStream queue processing
- Witness co-signing (runs daily)
