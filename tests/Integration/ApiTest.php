<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Integration;

use Exception;
use FediE2EE\PKD\Crypto\{
    AttributeEncryption\AttributeKeyMap,
    Protocol\Handler,
    Revocation,
    SecretKey,
    SymmetricKey
};
use FediE2EE\PKD\Crypto\Protocol\Actions\{
    AddAuxData,
    AddKey,
    RevokeKeyThirdParty
};
use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    JsonException,
    NotImplementedException,
    ParserException
};
use FediE2EE\PKDServer\RequestHandlers\Api\{
    Actor,
    Extensions,
    GetAuxData,
    GetKey,
    History,
    HistorySince,
    HistoryView,
    ListAuxData,
    ListKeys,
    Revoke,
    ServerPublicKey
};
use FediE2EE\PKDServer\{
    ActivityPub\WebFinger,
    Dependency\WrappedEncryptedRow,
    Protocol,
    Protocol\Payload,
    ServerConfig,
    Table,
    TableCache
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    ProtocolException,
    TableException,
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
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use Mdanter\Ecc\Exception\InsecureCurveException;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    BlindIndexNotFoundException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use ParagonIE\HPKE\HPKEException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use GuzzleHttp\Psr7\Response;
use ReflectionException;
use SodiumException;

#[CoversClass(Actor::class)]
#[CoversClass(Extensions::class)]
#[CoversClass(GetAuxData::class)]
#[CoversClass(GetKey::class)]
#[CoversClass(History::class)]
#[CoversClass(HistorySince::class)]
#[CoversClass(HistoryView::class)]
#[CoversClass(ListAuxData::class)]
#[CoversClass(ListKeys::class)]
#[CoversClass(Revoke::class)]
#[CoversClass(ServerPublicKey::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(ActorRecord::class)]
#[UsesClass(Actors::class)]
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
class ApiTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    public function setUp(): void
    {
        $this->config = $this->getConfig();
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     */
    public function testActorInfo(): void
    {
        [$actorId, $canonical] = $this->makeDummyActor('example.com');
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

        // Add aux data
        $addAux = new AddAuxData($canonical, 'test', 'test');
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
        $protocol->addAuxData($encryptedForServer);

        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId));
        $request = $request->withAttribute('actor_id', $actorId);

        $reflector = new ReflectionClass(Actor::class);
        $actorHandler = $reflector->newInstanceWithoutConstructor();
        $actorHandler->injectConfig($config);
        $actorHandler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($actorHandler);
        }

        $response = $actorHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/actor/info', $body['!pkd-context']);
        $this->assertSame($canonical, $body['actor-id']);
        $this->assertSame(1, $body['count-aux']);
        $this->assertSame(1, $body['count-keys']);
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     */
    public function testActorKeys(): void
    {
        [$actorId, $canonical] = $this->makeDummyActor('example.com');
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

        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/keys');
        $request = $request->withAttribute('actor_id', $actorId);

        $reflector = new ReflectionClass(ListKeys::class);
        $listKeysHandler = $reflector->newInstanceWithoutConstructor();
        $listKeysHandler->injectConfig($config);
        $listKeysHandler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($listKeysHandler);
        }

        $response = $listKeysHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/actor/get-keys', $body['!pkd-context']);
        $this->assertSame($canonical, $body['actor-id']);
        $this->assertCount(1, $body['public-keys']);
        $this->assertSame($keypair->getPublicKey()->toString(), $body['public-keys'][0]['public-key']);
        $keyId = $body['public-keys'][0]['key-id'];

        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/key/' . $keyId);
        $request = $request->withAttribute('actor_id', $actorId);
        $request = $request->withAttribute('key_id', $keyId);

        $reflector = new ReflectionClass(GetKey::class);
        $getKeyHandler = $reflector->newInstanceWithoutConstructor();
        $getKeyHandler->injectConfig($config);
        $getKeyHandler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($getKeyHandler);
        }

        $response = $getKeyHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/actor/key-info', $body['!pkd-context']);
        $this->assertSame($canonical, $body['actor-id']);
        $this->assertSame($keypair->getPublicKey()->toString(), $body['public-key']);
    }

    /**
     * @throws ArrayKeyException
     * @throws BlindIndexNotFoundException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     */
    public function testActorAuxiliary(): void
    {
        [$actorId, $canonical] = $this->makeDummyActor('example.com');
        $keypair = SecretKey::generate();
        $config = $this->getConfig();
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
        $protocol->addKey($encryptedForServer);

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
        $protocol->addAuxData($encryptedForServer);

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

        $response = $listAuxDataHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/actor/aux-info', $body['!pkd-context']);
        $this->assertSame($canonical, $body['actor-id']);
        $this->assertCount(1, $body['auxiliary']);
        $this->assertSame('test', $body['auxiliary'][0]['aux-type']);
        $auxId = $body['auxiliary'][0]['aux-id'];

        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/auxiliary/' . $auxId);
        $request = $request->withAttribute('actor_id', $actorId);
        $request = $request->withAttribute('aux_data_id', $auxId);

        $reflector = new ReflectionClass(GetAuxData::class);
        $getAuxDataHandler = $reflector->newInstanceWithoutConstructor();
        $getAuxDataHandler->injectConfig($config);
        $getAuxDataHandler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($getAuxDataHandler);
        }

        $response = $getAuxDataHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/actor/get-aux', $body['!pkd-context']);
        $this->assertSame($canonical, $body['actor-id']);
        $this->assertSame('test', $body['aux-type']);
        $this->assertSame('test-data', $body['aux-data']);
    }

    /**
     * @throws CacheException
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HPKEException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     */
    public function testHistory(): void
    {
        [$actorId, $canonical] = $this->makeDummyActor();
        $keypair = SecretKey::generate();
        $config = $this->getConfig();
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
        $protocol->addKey($encryptedForServer);
        $newRoot = $merkleState->getLatestRoot();
        $this->assertNotSame($newRoot, $latestRoot);

        $reflector = new ReflectionClass(History::class);
        $historyHandler = $reflector->newInstanceWithoutConstructor();
        $historyHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($historyHandler);
        }

        $request = $this->makeGetRequest('/api/history');
        $response = $historyHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/history', $body['!pkd-context']);
        $this->assertSame($newRoot, $body['merkle-root']);

        $reflector = new ReflectionClass(HistorySince::class);
        $sinceHandler = $reflector->newInstanceWithoutConstructor();
        $sinceHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($sinceHandler);
        }

        $request = $this->makeGetRequest('/api/history/since/' . urlencode($latestRoot));
        $request = $request->withAttribute('hash', $latestRoot);
        $response = $sinceHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/history/since', $body['!pkd-context']);
        $this->assertCount(1, $body['records']);
        $this->assertSame($newRoot, $body['records'][0]['merkle-root']);

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
        $this->assertSame($newRoot, $body['merkle-root']);
    }

    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws InsecureCurveException
     * @throws HPKEException
     * @throws ReflectionException
     * @throws SodiumException
     */
    public function testMetaEndpoints(): void
    {
        $config = $this->getConfig();
        $reflector = new ReflectionClass(Extensions::class);
        $extensionsHandler = $reflector->newInstanceWithoutConstructor();
        $extensionsHandler->injectConfig($config);

        $request = $this->makeGetRequest('/api/extensions');
        $response = $extensionsHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/extensions', $body['!pkd-context']);
        $this->assertIsArray($body['extensions']);

        $reflector = new ReflectionClass(ServerPublicKey::class);
        $spkHandler = $reflector->newInstanceWithoutConstructor();
        $spkHandler->injectConfig($config);

        $request = $this->makeGetRequest('/api/server-public-key');
        $response = $spkHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/server-public-key', $body['!pkd-context']);
        $this->assertNotEmpty($body['hpke-public-key']);
    }

    /**
     * @throws Exception
     */
    public function testRevoke(): void
    {
        [, $canonical] = $this->makeDummyActor();
        $pks = $this->table('PublicKeys');
        if (!($pks instanceof PublicKeys)) {
            $this->fail('table() did not return an instance of PublicKeys');
        }
        $keypair = SecretKey::generate();
        $config = $this->getConfig();
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
        $protocol->addKey($encryptedForServer);

        // Now, let's build a revocation token.
        $revocation = new Revocation();
        $token = $revocation->revokeThirdParty($keypair);
        $message = new RevokeKeyThirdParty($token);
        $bundle = $handler->handle($message, $keypair, new AttributeKeyMap(), $latestRoot);

        // Now, let's revoke this key.
        $request = $this->makePostRequest(
            '/api/revoke',
            $bundle->toString(),
            ['Content-Type' => 'application/json']
        );

        $reflector = new ReflectionClass(Revoke::class);
        $revokeHandler = $reflector->newInstanceWithoutConstructor();
        $revokeHandler->injectConfig($config);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($revokeHandler);
        }
        $response = $revokeHandler->handle($request);
        $this->assertSame(204, $response->getStatusCode());

        $this->assertEmpty($pks->getPublicKeysFor($canonical));
    }
}
