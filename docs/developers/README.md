# Developers Guide

This section of the documentation is for developers that wish to contribute to the PKD server software.

Where possible, the software adheres to the requirements set forth in the 
[Public Key Directory specification](https://github.com/fedi-e2ee/public-key-directory-specification).

## Framework

This package does not rely on any popular PHP framework (Symfony, Laravel, etc.). Instead, we adopted reusable 
components (such as [The League's Router](https://route.thephpleague.com/5.x/)).

### Runtime Configuration

The runtime configuration is defined in [`autoload.php`](../../autoload.php), which instantiates 
a [`ServerConfig`](../reference/classes/core.md#serverconfig) object with the various files inside 
[`config/`](../reference/configuration.md).

The `ServerConfig` object contains several classes used throughout the entire application.

### Request Handlers

Commonly called "Controllers" in MVC frameworks, we instead have a bunch of classes inside 
[`src/RequestHandlers](../reference/classes/requesthandlers.md) that implement [PSR-15](https://www.php-fig.org/psr/psr-15/).

The routes are defined in [`config/routes.php`](../../config/routes.php), although you may override this by creating
a `routes.php` file inside [`config/local/`](../../config/local).

### Tables

Commonly called "Models" in MVC frameworks, the classes inside [`src/Tables`](../reference/classes/tables.md) 
extend the base [`Table`](../reference/classes/core.md#table) class.

Generally, each `Table` class corresponds to a distinct table in the SQL database.

> [!IMPORTANT]
> **[`MerkleState`](../reference/classes/tables.md#merklestate)** is a bit of an exception to this rule, but it 
> generally holds.
>
> Most updates to other tables are guarded by [`MerkleState::insertLeaf()`](../reference/classes/tables.md#insertleaf). 
> This is to ensure the exclusive locking of the `pkd_merkle_state` database table is respected by concurrent PHP 
> processes, and (more to the point) to keep the "append only" nature of the Merkle Tree intact (whereas multiple
> concurrent writes would result in an invalid state).
>
> Each change to an Actor, their list of currently-trusted Public Keys, or their list of currently-valid Auxiliary Data 
> **MUST** be tied to a specific, unique record in the Merkle Tree history.

#### Symmetric Key Wrapping

> [!NOTE]
> This is not normative to the spec, but an implementation detail we do in the PHP software.

When messages are to be stored encrypted in the database, we re-wrap the client-provided symmetric key (if you recall,
this is required in order for [attributes to be crypto-shreddable](https://github.com/fedi-e2ee/public-key-directory-specification/blob/main/Specification.md#message-attribute-shreddability))
using [CipherSweet](https://github.com/paragonie/ciphersweet).

Specifically, we have a custom implementation of CipherSweet's `EncryptedRow` class called 
[WrappedEncryptedRow](../reference/classes/dependency.md#wrappedencryptedrow) that reads/writes the per-field key to 
another field in the same SQL row.

## How To Write Protocol Messages

ActivityPub messages are accepted at the [Inbox](../../src/RequestHandlers/ActivityPub/Inbox.php) Request Handler. This
loads them up into a message queue to be processed asynchronously at a later time.

Next, the [ASQueue](../../src/Scheduled/ASQueue.php) class processes the queued up ActivityStream messages.

From the ASQueue class, messages will be passed to the appropriate method inside [`Protocol`](../../src/Protocol.php).

Finally, updates to the appropriate SQL table will occur in a transaction on the [appropriate table class](#tables).

## Testing Changes

### Unit Tests

> [!TIP]
> We recommend running unit tests on a Unix-based OS, since Redis doesn't support Windows.

Make sure you update your dependencies with Composer, then run PHPUnit:

```terminal
vendor/bin/phpunit
```

This will run a battery of unit tests against the server software.

### Fuzz-Testing

See [.github/workflows/fuzz.yml](../../.github/workflows/fuzz.yml) for an up-to-date list of fuzzing commands.

Next, install [PHP-Fuzzer](https://github.com/nikic/PHP-Fuzzer) globally:

```shell
composer global require --dev nikic/php-fuzzer
```

Once PHP-Fuzzer is installed, you can run this in a terminal window:

```shell
php-fuzzer fuzz --max-runs 100000000 fuzzing/PHP_SCRIPT_NAME_GOES_HERE
```

Make sure you replace the `100000000` with the desired number of fuzz runs and `PHP_SCRIPT_NAME_GOES_HERE` with an
actual filename from [the `fuzzing/` directory](../../fuzzing).

### Mutation Testing

Not to mince words: Mutation testing can be very annoying. If you don't add enough `#[UsesClass]` attributes to your
PHPUnit test case classes, it will bail out before any mutation tests are actually executed.

For this reason, you are not required to run mutation tests locally. If anything breaks, we'll assume responsibility for
playing attribute whack-a-mole with the PHPUnit coverage driver that our mutation testing framework uses.

> [!CAUTION]
> Mutation testing is also somewhat resource-intensive. If you run it on a laptop, we cannot guarantee your battery
> will remain at full charge.

However, if you'd like to go the extra mile, you first need [the pcov extension](https://github.com/krakjoe/pcov)
installed on your device.

Next, install [Infection](https://infection.github.io/guide/), like so:

```shell
composer require --dev infection/infection 
```

Finally, run this command:

```shell
vendor/bin/infection
```

If everything goes well, you should get an infection.log file with the escaped mutants. The MSI score will also be
printed out to your terminal.

### Static Analysis

We currently employ three different static analysis tools in CI.

#### Psalm

Psalm is installed by default. You can run it like so:

```shell
vendor/bin/psalm
```

#### PHPStan

PHPStan is installed by default. You can run it like so:

```shell
vendor/bin/phpstan analyze --level 5 --memory-limit 1024M
```

#### Semgrep

See [the Semgrep website](https://semgrep.dev/) for setup instructions.

Once you have Semgrep installed locally, you can run the following command to generate a SARIF file.

```shell
semgrep scan \
    --config auto \
    --config p/phpcs-security-audit \
    --config p/security-audit \
    --config p/secure-defaults \
    --config p/owasp-top-ten \
    --config p/cwe-top-25 \
    --config p/trailofbits \
    --sarif \
    --sarif-output=semgrep.sarif
```

If you use an IDE with SARIF viewer support, any security issues identified by Semgrep will show when you look at the
relevant files.

### Style Checking

We use [php-cs-fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) to ensure the source code adheres to a consistent
style. The desired code style is similar to PSR-12, with a few tweaks (for personal preference).

To identify code style violations:

```shell
vendor/bin/php-cs-fixer fix --dry-run --allow-risky=yes
```

To automatically fix code style violations, simply drop `--dry-run`:

```shell
vendor/bin/php-cs-fixer fix --allow-risky=yes
```

### Regenerating the Reference Documentation 

> [!TIP]
> This is optional for third-party contributors. If a PR fails because the reference docs are out of date, don't sweat
> it. We'll run it on the `main` branch after merging.

To regenerate the reference docs, simply run this PHP script:

```shell
php docs/reference/generate.php
```
