<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Integration;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    JsonException,
    NotImplementedException,
    ParserException
};
use FediE2EE\PKD\Crypto\Protocol\{Actions\AddKey, Cosignature, Handler, HistoricalRecord};
use FediE2EE\PKD\Crypto\{Merkle\IncrementalTree, Merkle\Tree, SecretKey, SymmetricKey};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\{
    ActivityPub\WebFinger,
    AppCache,
    Dependency\WrappedEncryptedRow,
    Protocol,
    Protocol\Payload,
    ServerConfig,
    Table,
    TableCache,
    Traits\ConfigTrait
};
use FediE2EE\PKDServer\Tables\{
    Actors,
    AuxData,
    MerkleState,
    PublicKeys
};
use FediE2EE\PKDServer\Tables\Records\{
    Actor as ActorRecord,
    ActorKey,
    MerkleLeaf
};
use FediE2EE\PKDServer\RequestHandlers\Api\{
    HistoryCosign,
    HistoryView
};
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use GuzzleHttp\Psr7\Response;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\HPKE\HPKEException;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
use ReflectionClass;
use SodiumException;

#[CoversClass(HistoryCosign::class)]
#[CoversClass(HistoryView::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(ActorRecord::class)]
#[UsesClass(Actors::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(AuxData::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(MerkleState::class)]
#[UsesClass(Payload::class)]
#[UsesClass(PublicKeys::class)]
#[UsesClass(Protocol::class)]
#[UsesClass(ServerConfig::class)]
#[UsesClass(Table::class)]
#[UsesClass(TableCache::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(WrappedEncryptedRow::class)]
class CosignLifecycleTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    public function setUp(): void
    {
        $this->config = $this->getConfig();
    }

    /**
     * @return void
     * @throws CacheException
     * @throws DependencyException
     * @throws ProtocolException
     * @throws TableException
     * @throws CryptoException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws CertaintyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws RandomException
     * @throws SodiumException
     */
    private function makeDummyEntries(): void
    {
        [, $canonical] = $this->makeDummyActor();
        $keypair = SecretKey::generate();
        $config = $this->getConfig();
        $protocol = new Protocol($config);
        $webFinger = new WebFinger($config, $this->getMockClient([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{"subject":"' . $canonical . '"}'
            )
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
        $protocol->addKey($encryptedForServer);
    }

    public function testCosignLifecycle(): void
    {
        // First, we're going to generate a witness keypair
        $witnessSK = SecretKey::generate();
        $witnessPK = $witnessSK->getPublicKey();

        // Create a dummy hostname
        $hostname = 'phpunit-' . bin2hex(random_bytes(16)) . '.example.com';

        // Insert as a witness for testing:
        $this->config->getDb()->insert(
            'pkd_merkle_witnesses',
            [
                'origin' => $hostname,
                'publickey' => $witnessPK->toString(),
            ]
        );

        $tree = new IncrementalTree();
        // Let's get all history since the beginning:
        $zerothRoot = new Tree()->getEncodedRoot();
        $merkleState = $this->table('MerkleState');
        if (!($merkleState instanceof MerkleState)) {
            $this->fail('Could not load table: MerkleState');
        }
        $allHashes = $merkleState->getHashesSince($zerothRoot, 100);

        // We need to guarantee non-zero amounts of history:
        if (count($allHashes) < 1) {
            $this->makeDummyEntries();
            $allHashes = $merkleState->getHashesSince($zerothRoot, 10);
        }

        // Setup $cosignHandler
        $reflector = new ReflectionClass(HistoryCosign::class);
        $cosignHandler = $reflector->newInstanceWithoutConstructor();
        $cosignHandler->injectConfig($this->config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($cosignHandler);
        }

        // For every hash
        foreach ($allHashes as $record) {
            $cosign = new Cosignature($tree);
            $thisRoot = $record['merkle-root'];
            $leaf = $merkleState->getLeafByRoot($thisRoot);

            // Count number of cosignatures
            $numCosigs = $merkleState->countCosignatures($leaf->primaryKey);

            // Create cosignature
            $hist = new HistoricalRecord(
                $record['encrypted-message'],
                $record['publickeyhash'],
                $record['signature'],
            );
            $cosign->append($hist, $thisRoot);

            $cosigned = $cosign->cosign($witnessSK, $this->config->getParams()->hostname);

            // Push cosignature to PKD
            $request = $this->makePostRequest(
                '/api/history/cosign/' . urlencode($thisRoot),
                [
                    'witness' => $hostname,
                    'cosigned' => $cosigned,
                ],
                [
                    'Content-Type' => 'application/json',
                ]
            )
                ->withAttribute('hash', $thisRoot);
            $response = $cosignHandler->handle($request);
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertSame(200, $response->getStatusCode());
            $this->assertTrue($body['status']);
            $countAgain = $merkleState->countCosignatures($leaf->primaryKey);
            $this->assertNotSame($numCosigs, $countAgain, 'Number of cosignatures did not increase');
            $tree = $cosign->getTree();
        }
    }
}
