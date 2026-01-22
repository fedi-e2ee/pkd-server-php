<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Protocol\Actions\{
    AddAuxData,
    AddKey
};
use FediE2EE\PKD\Crypto\{
    AttributeEncryption\AttributeKeyMap,
    Protocol\Handler,
    SecretKey,
    SymmetricKey
};
use FediE2EE\PKD\Crypto\Exceptions\{
    BundleException,
    CryptoException,
    JsonException,
    NotImplementedException,
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
    Redirect,
    ServerConfig,
    Table,
    TableCache
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    ProtocolException,
    TableException
};
use DateMalformedStringException;
use FediE2EE\PKDServer\RequestHandlers\Api\ListAuxData;
use FediE2EE\PKDServer\Tables\{
    Actors,
    AuxData,
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
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\HPKE\HPKEException;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
use ReflectionClass;
use ReflectionException;
use SodiumException;

#[CoversClass(ListAuxData::class)]
#[UsesClass(AppCache::class)]
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
#[UsesClass(AuxData::class)]
#[UsesClass(MerkleState::class)]
#[UsesClass(PublicKeys::class)]
#[UsesClass(Actor::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(Peer::class)]
#[UsesClass(Math::class)]
#[UsesClass(RewrapConfig::class)]
#[UsesClass(Redirect::class)]
class ListAuxDataTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws BundleException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     */
    public function testHandle(): void
    {
        [$actorId, $canonical] = $this->makeDummyActor('example.com');
        $keypair = SecretKey::generate();
        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);
        $webFinger = new WebFinger(
            $config,
            $this->getMockClient([
                new Response(200, ['Content-Type' => 'application/json'], '{"subject":"' . $canonical . '"}')
            ])
        );
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
        $this->assertNotInTransaction();
        $protocol->addKey($encryptedForServer, $canonical);
        $this->clearOldTransaction($this->config);
        $this->ensureMerkleStateUnlocked();

        // Add aux data
        $addAux = new AddAuxData($canonical, 'test', 'test-data');
        $akm = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('aux-type', SymmetricKey::generate())
            ->addKey('aux-data', SymmetricKey::generate());
        $encryptedMsg = $addAux->encrypt($akm);
        $bundle = $handler->handle($encryptedMsg, $keypair, $akm, $latestRoot);
        $encryptedForServer = $handler->hpkeEncrypt(
            $bundle,
            $serverHpke->encapsKey,
            $serverHpke->cs
        );
        $this->assertNotInTransaction();
        $protocol->addAuxData($encryptedForServer, $canonical);
        $this->clearOldTransaction($this->config);

        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/auxiliary');
        $request = $request->withAttribute('actor_id', $actorId);

        $reflector = new ReflectionClass(ListAuxData::class);
        $listAuxDataHandler = $reflector->newInstanceWithoutConstructor();
        $listAuxDataHandler->injectConfig($config);
        $listAuxDataHandler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($listAuxDataHandler);
        }

        $this->assertNotInTransaction();
        $response = $listAuxDataHandler->handle($request);
        $this->clearOldTransaction($this->config);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/actor/aux-info', $body['!pkd-context']);
        $this->assertSame($canonical, $body['actor-id']);
        $this->assertCount(1, $body['auxiliary']);
        $this->assertSame('test', $body['auxiliary'][0]['aux-type']);
        $this->assertNotInTransaction();
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     */
    public function testEmptyActorIdRedirects(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $reflector = new ReflectionClass(ListAuxData::class);
        $handler = $reflector->newInstanceWithoutConstructor();
        $handler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($handler);
        }

        $request = $this->makeGetRequest('/api/actor//auxiliary')
            ->withAttribute('actor_id', '');
        $response = $handler->handle($request);

        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
        $this->assertLessThan(400, $response->getStatusCode());
        $this->assertNotInTransaction();
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     */
    public function testWebFingerErrorReturnsError(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $webFinger = new WebFinger($config, $this->getMockClient([
            new Response(500, [], 'Internal Server Error')
        ]));

        $reflector = new ReflectionClass(ListAuxData::class);
        $handler = $reflector->newInstanceWithoutConstructor();
        $handler->injectConfig($config);
        $handler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($handler);
        }

        $request = $this->makeGetRequest('/api/actor/test@example.com/auxiliary')
            ->withAttribute('actor_id', 'test@example.com');
        $response = $handler->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringStartsWith('A WebFinger error occurred: ', $body['error']);
        $this->assertStringContainsString('Internal Server Error', $body['error']);
        $this->assertNotInTransaction();
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws RandomException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     */
    public function testActorNotFoundReturns404(): void
    {
        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $nonExistentActor = 'nonexistent' . bin2hex(random_bytes(8)) . '@example.com';
        $canonical = 'https://example.com/users/nonexistent' . bin2hex(random_bytes(8));

        $webFinger = new WebFinger($config, $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'subject' => 'acct:' . $nonExistentActor,
                'links' => [
                    [
                        'rel' => 'self',
                        'type' => 'application/activity+json',
                        'href' => $canonical
                    ]
                ]
            ])),
        ]));

        $reflector = new ReflectionClass(ListAuxData::class);
        $handler = $reflector->newInstanceWithoutConstructor();
        $handler->injectConfig($config);
        $handler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($handler);
        }

        $request = $this->makeGetRequest('/api/actor/' . urlencode($nonExistentActor) . '/auxiliary')
            ->withAttribute('actor_id', $nonExistentActor);
        $response = $handler->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('not found', strtolower($body['error']));
        $this->assertNotInTransaction();
    }
}
