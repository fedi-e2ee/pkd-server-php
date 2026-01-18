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
