<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests;

use FediE2EE\PKD\Crypto\Merkle\IncrementalTree;
use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\ServerConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\ServerRequest;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Certainty\Exception\CertaintyException;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
use SodiumException;

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

    public function clearOldTransaction(ServerConfig $config): void
    {
        $db = $config->getDb();
        if ($db->inTransaction()) {
            $db->rollback();
        }
    }

    public function ensureMerkleStateUnlocked(): void
    {
        $db = $this->config()->getDb();
        $lock = $db->cell("SELECT lock_challenge FROM pkd_merkle_state");
        $this->assertEmpty($lock, 'lock = "' . $lock . '" but empty string was expected');
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
        $wf = new WebFinger($this->getConfig());
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
     * @return void
     * @throws DependencyException
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
}
