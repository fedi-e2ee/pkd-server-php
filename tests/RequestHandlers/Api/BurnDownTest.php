<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use DateMalformedStringException;
use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    JsonException as CryptoJsonException,
    NotImplementedException,
    ParserException
};
use FediE2EE\PKD\Crypto\Protocol\Actions\{
    AddKey,
    BurnDown as BurnDownAction
};
use FediE2EE\PKD\Crypto\{
    ActivityPub\WebFinger as CryptoWebFinger,
    AttributeEncryption\AttributeKeyMap,
    HttpSignature,
    Protocol\Handler,
    SecretKey,
    SymmetricKey
};
use FediE2EE\PKDServer\ActivityPub\{
    ActivityStream,
    WebFinger
};
use DateTimeImmutable;
use FediE2EE\PKDServer\Dependency\{
    EasyDBHandler,
    WrappedEncryptedRow
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\{
    AppCache,
    Traits\ConfigTrait,
    Math,
    Protocol,
    Protocol\KeyWrapping,
    Protocol\Payload,
    Protocol\RewrapConfig,
    ServerConfig,
    Table,
    TableCache,
};
use FediE2EE\PKDServer\RequestHandlers\Api\BurnDown;
use FediE2EE\PKDServer\Tables\{
    Actors,
    MerkleState,
    Peers,
    PublicKeys,
    TOTP
};
use FediE2EE\PKDServer\Tables\Records\{
    Actor,
    ActorKey,
    MerkleLeaf,
    Peer
};
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use GuzzleHttp\Psr7\{
    Response,
    ServerRequest
};
use JsonException;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\CipherSweet\Exception\{
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use ParagonIE\HPKE\HPKEException;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
use ReflectionClass;
use ReflectionException;
use SodiumException;

#[CoversClass(BurnDown::class)]
#[UsesClass(ActivityStream::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(EasyDBHandler::class)]
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
#[UsesClass(TOTP::class)]
#[UsesClass(Actor::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(Math::class)]
#[UsesClass(RewrapConfig::class)]
#[UsesClass(Peer::class)]
class BurnDownTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    /**
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoJsonException
     * @throws CryptoOperationException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws ReflectionException
     * @throws SodiumException
     * @throws TableException
     */
    public function testHandle(): void
    {
        // Create two actors: one to burn down, one as operator
        [$actorHandle, $canonActor] = $this->makeDummyActor('victim.example.com');
        [$operatorHandle, $canonOperator] = $this->makeDummyActor('operator.example.com');

        $actorKey = SecretKey::generate();
        $operatorKey = SecretKey::generate();

        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        $protocol = new Protocol($config);

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        $serverHpke = $config->getHPKE();
        $handler = new Handler();

        // 1. Add key for the actor (victim)
        $latestRoot1 = $merkleState->getLatestRoot();
        $addKey1 = new AddKey($canonActor, $actorKey->getPublicKey());
        $akm1 = (new AttributeKeyMap())
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $bundle1 = $handler->handle($addKey1->encrypt($akm1), $actorKey, $akm1, $latestRoot1);
        $encrypted1 = $handler->hpkeEncrypt($bundle1, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->addKey($encrypted1, $canonActor);
        $this->assertNotInTransaction();

        // 2. Add key for the operator
        $latestRoot2 = $merkleState->getLatestRoot();
        $addKey2 = new AddKey($canonOperator, $operatorKey->getPublicKey());
        $akm2 = (new AttributeKeyMap())
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $bundle2 = $handler->handle($addKey2->encrypt($akm2), $operatorKey, $akm2, $latestRoot2);
        $encrypted2 = $handler->hpkeEncrypt($bundle2, $serverHpke->encapsKey, $serverHpke->cs);
        $protocol->addKey($encrypted2, $canonOperator);
        $this->assertNotInTransaction();

        /** @var PublicKeys $pkTable */
        $pkTable = $this->table('PublicKeys');
        $this->assertCount(1, $pkTable->getPublicKeysFor($canonActor));
        $this->assertCount(1, $pkTable->getPublicKeysFor($canonOperator));

        // 3. Create BurnDown message (plaintext, not HPKE encrypted)
        // Note: BurnDownAction expects actor handles (with @), not canonical URLs
        // Set up mock WebFinger for the pkd-crypto Handler to resolve actor handles
        Handler::$wf = new CryptoWebFinger($this->getMockClient([
            new Response(200, ['Content-Type' => 'application/jrd+json'], json_encode([
                'subject' => "acct:{$actorHandle}",
                'links' => [[
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => $canonActor
                ]]
            ])),
            new Response(200, ['Content-Type' => 'application/jrd+json'], json_encode([
                'subject' => "acct:{$operatorHandle}",
                'links' => [[
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => $canonOperator
                ]]
            ]))
        ]));

        $latestRoot3 = $merkleState->getLatestRoot();
        // Note: BurnDownAction canonicalizes actor but NOT operator, so operator must be canonical URL
        $otp = '12345678';
        $now = (new DateTimeImmutable('NOW'));
        $burnDown = new BurnDownAction($actorHandle, $canonOperator, $now, $otp);
        // BurnDown is in UNENCRYPTED_ACTIONS - use empty AttributeKeyMap (plaintext fields)
        $emptyKeyMap = new AttributeKeyMap();
        $bundle3 = $handler->handle($burnDown, $operatorKey, $emptyKeyMap, $latestRoot3);

        // 4. Create ActivityStream wrapper (direct message format)
        $activityStream = $this->createActivityStream($canonOperator, $bundle3->toString());

        // 5. Create and sign the HTTP request
        $request = $this->createSignedRequest(
            '/api/burndown',
            $activityStream,
            $operatorKey,
            $canonOperator . '#main-key'
        );

        // 6. Create WebFinger mock for HTTP signature verification (JRD + Actor fetch)
        // Note: explicitOuterActorCheck doesn't need WebFinger since operator is already canonical
        $webFinger = new WebFinger($config, $this->getMockClient([
            // HTTP signature verification: JRD lookup for operator
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'subject' => $canonOperator,
                'links' => [[
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => $canonOperator
                ]]
            ])),
            // HTTP signature verification: Actor fetch with public key
            new Response(200, ['Content-Type' => 'application/activity+json'], json_encode([
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'id' => $canonOperator,
                'type' => 'Person',
                'publicKey' => [
                    'id' => $canonOperator . '#main-key',
                    'owner' => $canonOperator,
                    'publicKeyPem' => $operatorKey->getPublicKey()->encodePem()
                ]
            ]))
        ]));

        // 7. Instantiate handler and inject dependencies
        $reflector = new ReflectionClass(BurnDown::class);
        $burnDownHandler = $reflector->newInstanceWithoutConstructor();
        $burnDownHandler->injectConfig($config);
        $burnDownHandler->setWebFinger($webFinger);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $constructor->invoke($burnDownHandler);
        }

        // Also set WebFinger on the Protocol and PublicKeys table (they share the same mock)
        $protocolProp = $reflector->getProperty('protocol');
        $protocol = $protocolProp->getValue($burnDownHandler);
        $protocol->setWebFinger($webFinger);
        $pkTable->setWebFinger($webFinger);

        // 8. Handle request and verify response
        $response = $burnDownHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/burndown', $body['!pkd-context']);
        // Verify time field is present and is a string
        $this->assertArrayHasKey('time', $body);
        $this->assertIsString($body['time']);
        $this->assertTrue($body['status']);

        // 9. Verify actor's keys were burned
        $this->assertCount(0, $pkTable->getPublicKeysFor($canonActor));
        $this->assertCount(1, $pkTable->getPublicKeysFor($canonOperator));
        $this->assertNotInTransaction();

        // Clean up for test isolation
        $this->clearMerkleStateLock();
        Handler::$wf = null;
    }

    /**
     * Test that invalid HTTP signature returns false status.
     *
     * @throws CertaintyException
     * @throws DependencyException
     * @throws InvalidArgumentException
     * @throws NotImplementedException
     * @throws RandomException
     * @throws ReflectionException
     * @throws SodiumException
     */
    public function testHandleInvalidSignature(): void
    {
        [, $canonOperator] = $this->makeDummyActor('operator.example.com');
        $operatorKey = SecretKey::generate();
        $wrongKey = SecretKey::generate(); // Different key to invalidate signature

        $config = $this->getConfig();
        $this->clearOldTransaction($config);

        // Create a minimal ActivityStream message
        $activityStream = $this->createActivityStream($canonOperator, '{"test": "data"}');

        // Sign with the wrong key (signature will be invalid)
        $request = $this->createSignedRequest(
            '/api/burndown',
            $activityStream,
            $wrongKey, // Wrong key!
            $canonOperator . '#main-key'
        );

        // Mock WebFinger to return the CORRECT public key (which won't match the signature)
        $webFinger = new WebFinger($config, $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'subject' => $canonOperator,
                'links' => [[
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => $canonOperator
                ]]
            ])),
            new Response(200, ['Content-Type' => 'application/activity+json'], json_encode([
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'id' => $canonOperator,
                'type' => 'Person',
                'publicKey' => [
                    'id' => $canonOperator . '#main-key',
                    'owner' => $canonOperator,
                    'publicKeyPem' => $operatorKey->getPublicKey()->encodePem() // Correct key
                ]
            ]))
        ]));

        // Instantiate handler
        $burnDownHandler = $this->instantiateHandler(BurnDown::class, $config, $webFinger);

        // Handle request - should fail signature verification
        $this->clearOldTransaction($config);
        $response = $burnDownHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('fedi-e2ee:v1/api/burndown', $body['!pkd-context']);
        // Verify time field is present and is a string even on failure
        $this->assertArrayHasKey('time', $body);
        $this->assertIsString($body['time']);
        $this->assertFalse($body['status']); // Should fail due to invalid signature

        $this->clearMerkleStateLock();
    }

    /**
     * Create an ActivityStream direct message containing protocol content.
     *
     * @throws DependencyException
     * @throws RandomException
     */
    private function createActivityStream(string $actor, string $protocolContent): string
    {
        $messageId = 'https://example.com/messages/' . bin2hex(random_bytes(16));
        $pkdServer = $this->config()->getParams()->hostname;

        return json_encode([
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $messageId,
            'type' => 'Create',
            'actor' => $actor,
            'object' => [
                'id' => $messageId . '/object',
                'type' => 'Note',
                'attributedTo' => $actor,
                'to' => ['https://' . $pkdServer . '/actor'],
                'content' => json_encode([
                    '!pkd-context' => 'fedi-e2ee:v1-plaintext-message',
                    'actor' => $actor,
                    'plaintext-message' => $protocolContent
                ])
            ]
        ], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Create an HTTP request with valid RFC 9421 signature.
     *
     * @throws NotImplementedException
     * @throws SodiumException
     */
    private function createSignedRequest(
        string $uri,
        string $body,
        SecretKey $signingKey,
        string $keyId
    ): ServerRequestInterface {
        $request = new ServerRequest(
            'POST',
            $uri,
            [
                'Content-Type' => 'application/activity+json',
                'Date' => gmdate('D, d M Y H:i:s') . ' GMT'
            ],
            $body
        );

        $signer = new HttpSignature();
        $signed = $signer->sign(
            $signingKey,
            $request,
            ['@method', '@path', 'content-type', 'date'],
            $keyId
        );
        assert($signed instanceof ServerRequestInterface);
        return $signed;
    }
}
