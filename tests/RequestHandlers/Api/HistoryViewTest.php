<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use Exception;
use FediE2EE\PKD\Crypto\Protocol\Actions\AddKey;
use FediE2EE\PKD\Crypto\Protocol\{
    Cosignature,
    HistoricalRecord
};
use FediE2EE\PKD\Crypto\Merkle\{
    IncrementalTree,
    Tree
};
use FediE2EE\PKD\Crypto\{
    AttributeEncryption\AttributeKeyMap,
    Protocol\Handler,
    SecretKey,
    SymmetricKey
};
use FediE2EE\PKDServer\RequestHandlers\Api\{
    HistoryCosign,
    HistoryView
};
use FediE2EE\PKDServer\{
    ActivityPub\WebFinger,
    AppCache,
    Dependency\WrappedEncryptedRow,
    Math,
    Protocol,
    Protocol\KeyWrapping,
    Protocol\Payload,
    Protocol\RewrapConfig,
    ServerConfig,
    Table,
    TableCache
};
use FediE2EE\PKDServer\Tables\{
    Actors,
    MerkleState,
    Peers,
    PublicKeys
};
use FediE2EE\PKDServer\Tables\Records\{
    Actor,
    ActorKey,
    MerkleLeaf,
    Peer
};
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Throwable;

#[CoversClass(HistoryView::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(HistoryCosign::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(WrappedEncryptedRow::class)]
#[UsesClass(Protocol::class)]
#[UsesClass(KeyWrapping::class)]
#[UsesClass(Peers::class)]
#[UsesClass(Payload::class)]
#[UsesClass(ServerConfig::class)]
#[UsesClass(Table::class)]
#[UsesClass(TableCache::class)]
#[UsesClass(Actors::class)]
#[UsesClass(MerkleState::class)]
#[UsesClass(PublicKeys::class)]
#[UsesClass(Actor::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(Math::class)]
#[UsesClass(RewrapConfig::class)]
#[UsesClass(Peer::class)]
class HistoryViewTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    /**
     * @throws Throwable
     */
    public function testHandle(): void
    {
        [, $canonical] = $this->makeDummyActor('example.com');
        $keypair = SecretKey::generate();
        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);
        $webFinger = new WebFinger($config, $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], '{"subject":"' . $canonical . '"}')
        ]));
        $protocol->setWebFinger($webFinger);

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $latestRoot = $merkleState->getLatestRoot();

        $serverHpke = $config->getHPKE();
        $handler = new Handler();

        // Add a key
        $addKey = new AddKey($canonical, $keypair->getPublicKey());
        $akm = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg = $addKey->encrypt($akm);
        $bundle = $handler->handle($encryptedMsg, $keypair, $akm, $latestRoot);
        $encryptedForServer = $handler->hpkeEncrypt(
            $bundle,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $protocol->addKey($encryptedForServer, $canonical);
        $newRoot = $merkleState->getLatestRoot();

        $reflector = new ReflectionClass(HistoryView::class);
        $viewHandler = $reflector->newInstanceWithoutConstructor();
        $viewHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($viewHandler);
        }

        $request = $this->makeGetRequest('/api/history/view/' . urlencode($newRoot));
        $request = $request->withAttribute('hash', $newRoot);
        $response = $viewHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/history/view', $body['!pkd-context']);

        // Verify all expected response fields
        $this->assertArrayHasKey('created', $body);
        $this->assertArrayHasKey('encrypted-message', $body);
        $this->assertArrayHasKey('inclusion-proof', $body);
        $this->assertIsArray($body['inclusion-proof']);
        $this->assertArrayHasKey('message', $body);
        $this->assertArrayHasKey('merkle-root', $body);
        $this->assertSame($newRoot, $body['merkle-root']);
        $this->assertArrayHasKey('rewrapped-keys', $body);
        $this->assertArrayHasKey('witnesses', $body);
        $this->assertIsArray($body['witnesses']);
        $this->assertIsArray($body['message']);

        $this->assertNotInTransaction();
    }

    /**
     * Test that missing hash returns 400 error.
     *
     * @throws Exception
     */
    public function testMissingHash(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $reflector = new ReflectionClass(HistoryView::class);
        $viewHandler = $reflector->newInstanceWithoutConstructor();
        $viewHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($viewHandler);
        }

        // Request without hash attribute
        $request = $this->makeGetRequest('/api/history/view/');
        $response = $viewHandler->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertNotInTransaction();
    }

    /**
     * Test that unknown hash returns 404 error.
     *
     * @throws Exception
     */
    public function testUnknownHash(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $reflector = new ReflectionClass(HistoryView::class);
        $viewHandler = $reflector->newInstanceWithoutConstructor();
        $viewHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($viewHandler);
        }

        // Use a hash that doesn't exist
        $fakeHash = str_repeat('a', 64);
        $request = $this->makeGetRequest('/api/history/view/' . $fakeHash);
        $request = $request->withAttribute('hash', $fakeHash);
        $response = $viewHandler->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertNotInTransaction();
    }

    /**
     * Test that witnesses array contains cosignatures when they exist.
     * This test ensures the ternary condition returns cosignatures, not empty array.
     *
     * @throws Throwable
     */
    public function testWitnessesContainsCosignatures(): void
    {
        [, $canonical] = $this->makeDummyActor('witnesses-test.com');
        $keypair = SecretKey::generate();
        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);
        $webFinger = new WebFinger($config, $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], '{"subject":"' . $canonical . '"}')
        ]));
        $protocol->setWebFinger($webFinger);

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $latestRoot = $merkleState->getLatestRoot();

        $serverHpke = $config->getHPKE();
        $handler = new Handler();

        // Add a key
        $addKey = new AddKey($canonical, $keypair->getPublicKey());
        $akm = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg = $addKey->encrypt($akm);
        $bundle = $handler->handle($encryptedMsg, $keypair, $akm, $latestRoot);
        $encryptedForServer = $handler->hpkeEncrypt(
            $bundle,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $protocol->addKey($encryptedForServer, $canonical);
        $newRoot = $merkleState->getLatestRoot();

        // Create a witness keypair and register it
        $witnessSK = SecretKey::generate();
        $witnessPK = $witnessSK->getPublicKey();
        $witnessHostname = 'witness-' . bin2hex(random_bytes(8)) . '.example.com';

        $config->getDb()->insert(
            'pkd_merkle_witnesses',
            [
                'origin' => $witnessHostname,
                'publickey' => $witnessPK->toString(),
            ]
        );

        // Build the Merkle tree from scratch to create a valid cosignature
        $zerothRoot = (new Tree())->getEncodedRoot();
        $allHashes = $merkleState->getHashesSince($zerothRoot, 1000);

        $tree = new IncrementalTree();
        $targetRoot = null;
        foreach ($allHashes as $record) {
            $cosign = new Cosignature($tree);
            $thisRoot = $record['merkle-root'];
            $hist = new HistoricalRecord(
                $record['encrypted-message'],
                $record['publickeyhash'],
                $record['signature'],
            );
            $cosign->append($hist, $thisRoot);

            // When we reach our target root, create the cosignature
            if ($thisRoot === $newRoot) {
                $cosigned = $cosign->cosign($witnessSK, $config->getParams()->hostname);

                // Setup cosign handler and submit
                $reflector = new ReflectionClass(HistoryCosign::class);
                $cosignHandler = $reflector->newInstanceWithoutConstructor();
                $cosignHandler->injectConfig($config);
                $constructor = $reflector->getConstructor();
                if ($constructor) {
                    $constructor->invoke($cosignHandler);
                }

                $cosignRequest = $this->makePostRequest(
                    '/api/history/cosign/' . urlencode($thisRoot),
                    ['witness' => $witnessHostname, 'cosigned' => $cosigned],
                    ['Content-Type' => 'application/json']
                )->withAttribute('hash', $thisRoot);
                $cosignResponse = $cosignHandler->handle($cosignRequest);
                $this->assertSame(200, $cosignResponse->getStatusCode());
                $targetRoot = $thisRoot;
            }
            $tree = $cosign->getTree();
        }

        $this->assertNotNull($targetRoot, 'Should have found target root');

        // Now verify HistoryView returns the witness
        $reflector = new ReflectionClass(HistoryView::class);
        $viewHandler = $reflector->newInstanceWithoutConstructor();
        $viewHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($viewHandler);
        }

        $request = $this->makeGetRequest('/api/history/view/' . urlencode($newRoot));
        $request = $request->withAttribute('hash', $newRoot);
        $response = $viewHandler->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);

        // The key assertion: witnesses must NOT be empty when cosignatures exist
        $this->assertArrayHasKey('witnesses', $body);
        $this->assertIsArray($body['witnesses']);
        $this->assertNotEmpty($body['witnesses'], 'Witnesses array should contain cosignatures');
        $this->assertCount(1, $body['witnesses']);
        $this->assertSame($witnessHostname, $body['witnesses'][0]['witness']);

        $this->assertNotInTransaction();
    }
}
