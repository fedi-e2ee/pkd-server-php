<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    JsonException,
    NotImplementedException,
    ParserException
};
use FediE2EE\PKD\Crypto\Merkle\IncrementalTree;
use FediE2EE\PKD\Crypto\Protocol\{
    Actions\AddKey,
    Handler
};
use ParagonIE\Certainty\Fetch;
use ParagonIE\Certainty\RemoteFetch;
use FediE2EE\PKD\Crypto\{
    SecretKey,
    SymmetricKey
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\{
    ActivityPub\WebFinger,
    Protocol,
    ServerConfig
};
use FediE2EE\PKDServer\Tables\{
    MerkleState,
    Records\ActorKey
};
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\{
    Response,
    ServerRequest
};
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\HPKE\HPKEException;
use PDOException;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
use ReflectionClass;
use ReflectionException;
use SodiumException;
use TypeError;

use const FediE2EE\PKDServer\PKD_SERVER_ROOT;

/**
 * Helper methods for writing unit tests with HTTP messages
 *
 * @method fail(string $message = ''): never
 */
trait HttpTestTrait
{
    public function getConfig(): ServerConfig
    {
        if (!($GLOBALS['pkdConfig'] instanceof ServerConfig)) {
            $this->fail('Server config not injected');
        }
        return $GLOBALS['pkdConfig'];
    }

    /**
     * @throws DependencyException
     */
    public function clearOldTransaction(ServerConfig $config): void
    {
        $db = $config->getDb();
        if ($db->inTransaction()) {
            $db->rollback();
        }
    }

    /**
     * @throws DependencyException
     */
    public function ensureMerkleStateUnlocked(): void
    {
        $db = $this->config()->getDb();
        $lock = $db->cell("SELECT lock_challenge FROM pkd_merkle_state");
        $this->assertEmpty($lock, "lock = \"{$lock}\" but empty string was expected");
    }

    /**
     * Force-clear the Merkle state lock for test isolation.
     *
     * @throws DependencyException
     */
    public function clearMerkleStateLock(): void
    {
        $db = $this->config()->getDb();
        $db->safeQuery("UPDATE pkd_merkle_state SET lock_challenge = ''");
    }

    public function assertNotInTransaction(): void
    {
        $db = $this->config()->getDb();
        $this->assertFalse($db->inTransaction(), 'we should not be in transaction');
    }

    public function getMockClient(array $responses = []): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }

    /**
     * @throws RandomException
     * @throws CertaintyException
     * @throws DependencyException
     * @throws InvalidArgumentException
     * @throws SodiumException
     */
    public function makeDummyActor(string $domain = 'example.com'): array
    {
        $username = 'test';
        $alphabet = 'abcdefghijklmnopqrstuvwxyz';
        for ($i = 0; $i < 24; ++$i) {
            $r = random_int(0, 25);
            $username .= $alphabet[$r];
        }
        $input = $username . '@' . $domain;
        $canon = 'https://' . $domain . '/users/' . $username;
        if (file_exists(PKD_SERVER_ROOT . '/tmp/ca-certs.json')) {
            $fetch = new Fetch(PKD_SERVER_ROOT . '/tmp');
        } else {

            $fetch = new RemoteFetch(PKD_SERVER_ROOT . '/tmp');
        }
        $wf = new WebFinger(
            $this->getConfig(),
            new Client([
                'verify' => $fetch->getLatestBundle(false, false)->getFilePath()
            ]),
            $fetch
        );
        $wf->setCanonicalForTesting($input, $canon);
        return [$input, $canon];
    }

    public function makeGetRequest(
        string $uri,
        array $headers = []
    ): ServerRequest {
        return new ServerRequest('GET', $uri, $headers);
    }

    public function makePostRequest(
        string $uri,
        string|array|object $body = '',
        array $headers = []
    ): ServerRequest {
        if (!is_string($body)) {
            $body = json_encode(
                $body,
                JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
        }
        return new ServerRequest('POST', $uri, $headers, $body);
    }

    /**
     * @throws DependencyException
     */
    public function dispatchRequest(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getConfig()->getRouter()->dispatch($request);
    }

    /**
     * This nukes every table in the database.
     *
     * @throws DependencyException
     * @throws SodiumException
     */
    public function truncateTables(): void
    {
        if (method_exists($this, 'getConfig')) {
            $db = $this->getConfig()->getDb();
        } else {
            $db = $GLOBALS['pkdConfig']->getDb();
        }
        $tables = [
            'pkd_merkle_witness_cosignatures',
            'pkd_merkle_witnesses',
            'pkd_actors_publickeys',
            'pkd_actors_auxdata',
            'pkd_actors',
            'pkd_merkle_leaves',
            'pkd_totp_secrets',
            'pkd_activitystream_queue',
            'pkd_log',
            'pkd_peers',
            'pkd_merkle_leaf_rewrapped_keys',
            'pkd_replica_history',
            'pkd_replica_actors',
            'pkd_replica_actors_publickeys',
            'pkd_replica_actors_auxdata',
        ];

        switch ($db->getDriver()) {
            case 'pgsql':
                $tableList = implode(', ', $tables);
                $db->exec("TRUNCATE {$tableList} RESTART IDENTITY CASCADE");
                break;
            case 'mysql':
                $db->exec('SET FOREIGN_KEY_CHECKS = 0;');
                foreach ($tables as $table) {
                    $db->exec("TRUNCATE TABLE `{$table}`");
                }
                $db->exec('SET FOREIGN_KEY_CHECKS = 1;');
                break;
            case 'sqlite':
                foreach ($tables as $table) {
                    $db->exec("DELETE FROM `{$table}`");
                    try {
                        $db->exec("DELETE FROM `sqlite_sequence` WHERE `name` = '{$table}'");
                    } catch (PDOException $ex) {
                        // This is fine.
                    }
                }
                break;
            default:
                throw new \RuntimeException("Unsupported driver: {$db->getDriver()}");
        }

        $incremental = new IncrementalTree([], $GLOBALS['pkdConfig']->getParams()->hashAlgo);
        $db->safeQuery(
            "UPDATE pkd_merkle_state SET merkle_state = ?, lock_challenge = ''",
            [
                Base64UrlSafe::encodeUnpadded($incremental->toJson())
            ]
        );
    }

    /**
     * Instantiate a request handler using reflection (bypasses DI container).
     *
     * @param class-string<RequestHandlerInterface> $handlerClass
     *
     * @throws ReflectionException
     */
    public function instantiateHandler(
        string $handlerClass,
        ServerConfig $config,
        ?WebFinger $webFinger = null
    ): RequestHandlerInterface {
        $reflector = new ReflectionClass($handlerClass);
        $handler = $reflector->newInstanceWithoutConstructor();
        $handler->injectConfig($config);
        if ($webFinger !== null && method_exists($handler, 'setWebFinger')) {
            $handler->setWebFinger($webFinger);
        }
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($handler);
        }
        if (!($handler instanceof RequestHandlerInterface)) {
            throw new TypeError('reflection failed to instantiate a class that implements RequestHandlerInterface');
        }
        return $handler;
    }

    /**
     * Create a simple WebFinger mock that returns the canonical URL.
     *
     * @throws CertaintyException
     * @throws DependencyException
     * @throws SodiumException
     */
    public function createWebFingerMock(
        ServerConfig $config,
        string $canonical,
        int $responseCount = 1
    ): WebFinger {
        $responses = [];
        for ($i = 0; $i < $responseCount; $i++) {
            $responses[] = new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['subject' => $canonical])
            );
        }
        return new WebFinger($config, $this->getMockClient($responses));
    }

    /**
     * Add a key for an actor via the Protocol class.
     *
     * @throws CacheException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws SodiumException
     * @throws TableException
     */
    public function addKeyForActor(
        string $canonical,
        SecretKey $keypair,
        Protocol $protocol,
        ServerConfig $config
    ): ActorKey {
        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $latestRoot = $merkleState->getLatestRoot();

        $serverHpke = $config->getHPKE();
        $handler = new Handler();

        $addKey = new AddKey($canonical, $keypair->getPublicKey());
        $akm = new AttributeKeyMap();
        $akm->addKey('actor', SymmetricKey::generate());
        $akm->addKey('public-key', SymmetricKey::generate());

        $bundle = $handler->handle($addKey->encrypt($akm), $keypair, $akm, $latestRoot);
        $encrypted = $handler->hpkeEncrypt($bundle, $serverHpke->encapsKey, $serverHpke->cs);

        return $protocol->addKey($encrypted, $canonical);
    }
}
