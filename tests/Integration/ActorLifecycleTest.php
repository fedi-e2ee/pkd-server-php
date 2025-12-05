<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Integration;

use FediE2EE\PKD\Crypto\{
    AttributeEncryption\AttributeKeyMap,
    Protocol\Handler,
    SecretKey,
    SymmetricKey
};
use FediE2EE\PKD\Crypto\Protocol\Actions\{
    AddKey,
    RevokeKey
};
use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    JsonException,
    NotImplementedException,
    ParserException
};
use FediE2EE\PKDServer\RequestHandlers\Api\{
    Actor,
    ListKeys,
};
use FediE2EE\PKDServer\{
    ActivityPub\WebFinger,
    Dependency\WrappedEncryptedRow,
    Protocol,
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

#[CoversClass(ListKeys::class)]
#[CoversClass(Protocol::class)]
#[CoversClass(WebFinger::class)]
#[UsesClass(Actor::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(ActorRecord::class)]
#[UsesClass(Actors::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(MerkleState::class)]
#[UsesClass(PublicKeys::class)]
#[UsesClass(TableCache::class)]
#[UsesClass(Protocol\Payload::class)]
#[UsesClass(ServerConfig::class)]
#[UsesClass(Table::class)]
#[UsesClass(WrappedEncryptedRow::class)]
class ActorLifecycleTest extends TestCase
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
     * @throws CertaintyException
     */
    public function testAddAndRevoke(): void
    {
        [$actorId, $canonical] = $this->makeDummyActor('example.net');

        // Generate key pair for alice
        $keypair1 = SecretKey::generate();

        $config = $this->getConfig();
        $protocol = new Protocol($config);
        $webFinger = new WebFinger($config, $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], '{"subject":"' . $canonical . '"}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"subject":"' . $canonical . '"}')
        ]));
        $protocol->setWebFinger($webFinger);

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $latestRoot1 = $merkleState->getLatestRoot();

        $serverHpke = $config->getHPKE();
        $handler = new Handler();

        // 1. AddKey (self-signed)
        $addKey1 = new AddKey($canonical, $keypair1->getPublicKey());
        $akm1 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg1 = $addKey1->encrypt($akm1);
        $bundle1 = $handler->handle($encryptedMsg1, $keypair1, $akm1, $latestRoot1);
        $encryptedForServer1 = $handler->hpkeEncrypt(
            $bundle1,
            $serverHpke->encapsKey,
            $serverHpke->cs,
        );
        $protocol->addKey($encryptedForServer1);

        // Verify with HTTP request
        $request = $this->makeGetRequest('/api/actor/' . urlencode($actorId) . '/keys');
        $request = $request->withAttribute('actor_id', $actorId);

        $reflector = new ReflectionClass(ListKeys::class);
        $actorHandler = $reflector->newInstanceWithoutConstructor();
        $actorHandler->injectConfig($config);
        $actorHandler->setWebFinger($webFinger);

        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($actorHandler);
        }

        // Test the HTTP response
        $response = $actorHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('public-keys', $body);
        $this->assertCount(1, $body['public-keys']);
        $this->assertSame($keypair1->getPublicKey()->toString(), $body['public-keys'][0]['public-key']);

        // 2. RevokeKey (signed by key 1)
        $latestRoot2 = $merkleState->getLatestRoot();
        $revokeKey = new RevokeKey($canonical, $keypair1->getPublicKey());
        $akm2 = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg2 = $revokeKey->encrypt($akm2);
        $bundle2 = $handler->handle($encryptedMsg2, $keypair1, $akm2, $latestRoot2);
        $encryptedForServer2 = $handler->hpkeEncrypt(
            $bundle2,
            $serverHpke->encapsKey,
            $serverHpke->cs,
        );
        $protocol->revokeKey($encryptedForServer2);

        // Verify with HTTP request
        $response = $actorHandler->handle($request);
        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('error', $body);
        $this->assertSame('Actor not found or has no registered public keys', $body['error']);
    }
}
