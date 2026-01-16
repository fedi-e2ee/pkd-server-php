<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use Exception;
use FediE2EE\PKD\Crypto\Protocol\Actions\AddKey;
use FediE2EE\PKD\Crypto\{
    AttributeEncryption\AttributeKeyMap,
    Protocol\Handler,
    Protocol\HPKEAdapter,
    SecretKey,
    SymmetricKey,
    UtilTrait
};
use FediE2EE\PKDServer\RequestHandlers\Api\{
    TotpEnroll
};
use FediE2EE\PKDServer\{ActivityPub\WebFinger,
    AppCache,
    Dependency\EasyDBHandler,
    Dependency\HPKE,
    Dependency\InjectConfigStrategy,
    Dependency\WrappedEncryptedRow,
    Math,
    Protocol,
    Protocol\Payload,
    ServerConfig,
    Table,
    TableCache};
use FediE2EE\PKDServer\Tables\{
    Actors,
    MerkleState,
    PublicKeys,
    TOTP
};
use FediE2EE\PKDServer\Traits\TOTPTrait;
use FediE2EE\PKDServer\Tables\Records\{
    Actor,
    ActorKey,
    MerkleLeaf
};
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\StreamFactory;
use ParagonIE\ConstantTime\{
    Base32,
    Base64UrlSafe
};
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use PHPUnit\Framework\TestCase;

#[CoversClass(TotpEnroll::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(EasyDBHandler::class)]
#[UsesClass(HPKE::class)]
#[UsesClass(WrappedEncryptedRow::class)]
#[UsesClass(InjectConfigStrategy::class)]
#[UsesClass(Protocol::class)]
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
#[UsesClass(TOTP::class)]
#[UsesClass(Math::class)]
class TotpEnrollTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;
    use TOTPTrait;
    use UtilTrait;

    public function setUp(): void
    {
        $this->config = $GLOBALS['pkdConfig'];
    }

    /**
     * @throws Exception
     */
    public function testHandle(): void
    {
        [, $canonical] = $this->makeDummyActor('enroll-example.com');
        $keypair = SecretKey::generate();

        /** @var TOTP $table */
        $table = $this->table('TOTP');
        if (!($table instanceof TOTP)) {
            $this->fail('type error: table() result not instance of TOTP table class');
        }

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        if (!($merkleState instanceof MerkleState)) {
            $this->fail('type error: table() result not instance of MerkleState table class');
        }

        // We need to add the key to the PKD first, so the signature can be verified.
        $config = $this->getConfig();
        $protocol = new Protocol($config);
        $this->clearOldTransaction($config);
        $latestRoot = $merkleState->getLatestRoot();
        $serverHpke = $this->config->getHPKE();
        $handler = new Handler();

        $addKey = new AddKey($canonical, $keypair->getPublicKey());
        $akm = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg = $addKey->encrypt($akm);
        $bundle = $handler->handle($encryptedMsg, $keypair, $akm, $latestRoot);
        $encryptedForServer = $handler->hpkeEncrypt(
            $bundle,
            $serverHpke->encapsKey,
            $serverHpke->cs,
        );
        $result = $protocol->addKey($encryptedForServer, $canonical);
        $this->assertNotInTransaction();
        $this->ensureMerkleStateUnlocked();
        $keyId = $result->keyID;

        // Generate TOTP secret and codes
        $totpSecret = random_bytes(20);
        $otpCurrent = self::generateTOTP($totpSecret);
        $otpPrevious = self::generateTOTP($totpSecret, time() - 30);

        // Encrypt the TOTP secret for the server
        $hpke = $this->config->getHPKE();
        $encryptedSecret = new HPKEAdapter($hpke->cs, 'fedi-e2ee:v1/api/totp/enroll')->seal(
            $hpke->getEncapsKey(),
            $totpSecret
        );
        $encodedSecret = Base32::encode($encryptedSecret);

        // Prepare enrollment request
        $enrollment = [
            'actor-id' => $canonical,
            'key-id' => $keyId,
            'current-time' => (string) time(),
            'otp-current' => $otpCurrent,
            'otp-previous' => $otpPrevious,
            'totp-secret' => $encodedSecret,
        ];
        $messageToSign = $this->preAuthEncode([
            '!pkd-context',
            'fedi-e2ee:v1/api/totp/enroll',
            'action',
            'TOTP-Enroll',
            'message',
            json_encode($enrollment),
        ]);
        $signature = $keypair->sign($messageToSign);

        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/enroll',
            'action' => 'TOTP-Enroll',
            'current-time' => (string) time(),
            'enrollment' => $enrollment,
            'signature' => Base64UrlSafe::encodeUnpadded($signature)
        ];

        // Dispatch the request
        $request = new ServerRequest(
            [],
            [],
            '/api/totp/enroll',
            'POST'
        )
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        // Assertions
        $this->assertSame(200, $response->getStatusCode(), (string) $response->getBody());
        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertTrue($responseBody['success']);
        // Verify response format includes !pkd-context
        $this->assertSame('fedi-e2ee:v1/api/totp/enroll', $responseBody['!pkd-context']);
        $this->assertArrayHasKey('time', $responseBody);
        $this->assertIsString($responseBody['time']);

        // Verify secret was stored correctly
        /** @var TOTP $totpTable */
        $totpTable = $this->table('TOTP');
        // $domain = parse_url($canonical)['host'];
        $domain = 'enroll-example.com';
        $storedSecret = $totpTable->getSecretByDomain($domain);

        $this->assertNotNull($storedSecret);
        $this->assertSame($totpSecret, $storedSecret);
        $this->assertNotInTransaction();
    }

    /**
     * Test that missing actor-id returns error.
     *
     * @throws Exception
     */
    public function testMissingActorId(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/enroll',
            'action' => 'TOTP-Enroll',
            'current-time' => (string) time(),
            'enrollment' => [
                // actor-id is missing
                'key-id' => 'some-key-id',
                'otp-current' => '12345678',
                'otp-previous' => '87654321',
                'totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/enroll', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseBody);
    }

    /**
     * Test that missing key-id returns error.
     *
     * @throws Exception
     */
    public function testMissingKeyId(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/enroll',
            'action' => 'TOTP-Enroll',
            'current-time' => (string) time(),
            'enrollment' => [
                'actor-id' => 'https://example.com/users/test',
                // key-id is missing
                'otp-current' => '12345678',
                'otp-previous' => '87654321',
                'totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/enroll', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that missing otp-current returns error.
     *
     * @throws Exception
     */
    public function testMissingOtpCurrent(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/enroll',
            'action' => 'TOTP-Enroll',
            'current-time' => (string) time(),
            'enrollment' => [
                'actor-id' => 'https://example.com/users/test',
                'key-id' => 'some-key-id',
                // otp-current is missing
                'otp-previous' => '87654321',
                'totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/enroll', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that missing otp-previous returns error.
     *
     * @throws Exception
     */
    public function testMissingOtpPrevious(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/enroll',
            'action' => 'TOTP-Enroll',
            'current-time' => (string) time(),
            'enrollment' => [
                'actor-id' => 'https://example.com/users/test',
                'key-id' => 'some-key-id',
                'otp-current' => '12345678',
                // otp-previous is missing
                'totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/enroll', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that missing totp-secret returns error.
     *
     * @throws Exception
     */
    public function testMissingTotpSecret(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/enroll',
            'action' => 'TOTP-Enroll',
            'current-time' => (string) time(),
            'enrollment' => [
                'actor-id' => 'https://example.com/users/test',
                'key-id' => 'some-key-id',
                'otp-current' => '12345678',
                'otp-previous' => '87654321',
                // totp-secret is missing
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/enroll', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that missing action returns error.
     *
     * @throws Exception
     */
    public function testMissingAction(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/enroll',
            // action is missing
            'current-time' => (string) time(),
            'enrollment' => [
                'actor-id' => 'https://example.com/users/test',
                'key-id' => 'some-key-id',
                'otp-current' => '12345678',
                'otp-previous' => '87654321',
                'totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/enroll', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that missing current-time returns error.
     *
     * @throws Exception
     */
    public function testMissingCurrentTime(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/enroll',
            'action' => 'TOTP-Enroll',
            // current-time is missing
            'enrollment' => [
                'actor-id' => 'https://example.com/users/test',
                'key-id' => 'some-key-id',
                'otp-current' => '12345678',
                'otp-previous' => '87654321',
                'totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/enroll', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that missing !pkd-context returns error.
     *
     * @throws Exception
     */
    public function testMissingPkdContext(): void
    {
        $body = [
            // !pkd-context is missing
            'action' => 'TOTP-Enroll',
            'current-time' => (string) time(),
            'enrollment' => [
                'actor-id' => 'https://example.com/users/test',
                'key-id' => 'some-key-id',
                'otp-current' => '12345678',
                'otp-previous' => '87654321',
                'totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/enroll', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that invalid JSON returns error.
     *
     * @throws Exception
     */
    public function testInvalidJson(): void
    {
        $request = new ServerRequest([], [], '/api/totp/enroll', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream('not valid json'));
        $this->clearOldTransaction($this->config);
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseBody);
    }

    /**
     * Test that stale timestamp returns error.
     *
     * @throws Exception
     */
    public function testStaleTimestamp(): void
    {
        [, $canonical] = $this->makeDummyActor('stale-enroll.com');
        $keypair = SecretKey::generate();

        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);

        $result = $this->addKeyForActor($canonical, $keypair, $protocol, $config);
        $this->assertNotInTransaction();
        $this->ensureMerkleStateUnlocked();
        $keyId = $result->keyID;

        $totpSecret = random_bytes(20);
        $otpCurrent = self::generateTOTP($totpSecret);
        $otpPrevious = self::generateTOTP($totpSecret, time() - 30);

        $hpke = $this->config->getHPKE();
        $encryptedSecret = new HPKEAdapter($hpke->cs, 'fedi-e2ee:v1/api/totp/enroll')->seal(
            $hpke->getEncapsKey(),
            $totpSecret
        );
        $encodedSecret = Base32::encode($encryptedSecret);

        // Use a stale timestamp (1 hour ago)
        $staleTime = (string) (time() - 3600);

        $enrollment = [
            'actor-id' => $canonical,
            'key-id' => $keyId,
            'current-time' => $staleTime,
            'otp-current' => $otpCurrent,
            'otp-previous' => $otpPrevious,
            'totp-secret' => $encodedSecret,
        ];
        $messageToSign = $this->preAuthEncode([
            '!pkd-context',
            'fedi-e2ee:v1/api/totp/enroll',
            'action',
            'TOTP-Enroll',
            'message',
            json_encode($enrollment),
        ]);
        $signature = $keypair->sign($messageToSign);

        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/enroll',
            'action' => 'TOTP-Enroll',
            'current-time' => $staleTime,
            'enrollment' => $enrollment,
            'signature' => Base64UrlSafe::encodeUnpadded($signature)
        ];

        $request = new ServerRequest([], [], '/api/totp/enroll', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseBody);
        $this->assertStringContainsString('stale', strtolower($responseBody['error']));
    }

    /**
     * Test that invalid signature returns error.
     *
     * @throws Exception
     */
    public function testInvalidSignature(): void
    {
        [, $canonical] = $this->makeDummyActor('invalid-sig-enroll.com');
        $keypair = SecretKey::generate();

        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);

        $result = $this->addKeyForActor($canonical, $keypair, $protocol, $config);
        $this->assertNotInTransaction();
        $this->ensureMerkleStateUnlocked();
        $keyId = $result->keyID;

        $totpSecret = random_bytes(20);
        $otpCurrent = self::generateTOTP($totpSecret);
        $otpPrevious = self::generateTOTP($totpSecret, time() - 30);

        $hpke = $this->config->getHPKE();
        $encryptedSecret = new HPKEAdapter($hpke->cs, 'fedi-e2ee:v1/api/totp/enroll')->seal(
            $hpke->getEncapsKey(),
            $totpSecret
        );
        $encodedSecret = Base32::encode($encryptedSecret);

        $enrollment = [
            'actor-id' => $canonical,
            'key-id' => $keyId,
            'current-time' => (string) time(),
            'otp-current' => $otpCurrent,
            'otp-previous' => $otpPrevious,
            'totp-secret' => $encodedSecret,
        ];

        // Use a different key to create an invalid signature
        $wrongKey = SecretKey::generate();
        $messageToSign = $this->preAuthEncode([
            '!pkd-context',
            'fedi-e2ee:v1/api/totp/enroll',
            'action',
            'TOTP-Enroll',
            'message',
            json_encode($enrollment),
        ]);
        $invalidSignature = $wrongKey->sign($messageToSign);

        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/enroll',
            'action' => 'TOTP-Enroll',
            'current-time' => (string) time(),
            'enrollment' => $enrollment,
            'signature' => Base64UrlSafe::encodeUnpadded($invalidSignature)
        ];

        $request = new ServerRequest([], [], '/api/totp/enroll', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));

        $this->expectException(\FediE2EE\PKDServer\Exceptions\ProtocolException::class);
        $this->dispatchRequest($request);
    }

    /**
     * Test that invalid TOTP codes return 406 error.
     *
     * @throws Exception
     */
    public function testInvalidTotpCodes(): void
    {
        [, $canonical] = $this->makeDummyActor('invalid-totp-enroll.com');
        $keypair = SecretKey::generate();

        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);

        $result = $this->addKeyForActor($canonical, $keypair, $protocol, $config);
        $this->assertNotInTransaction();
        $this->ensureMerkleStateUnlocked();
        $keyId = $result->keyID;

        $totpSecret = random_bytes(20);
        // Use wrong OTP codes
        $wrongOtpCurrent = '00000000';
        $wrongOtpPrevious = '00000000';

        $hpke = $this->config->getHPKE();
        $encryptedSecret = new HPKEAdapter($hpke->cs, 'fedi-e2ee:v1/api/totp/enroll')->seal(
            $hpke->getEncapsKey(),
            $totpSecret
        );
        $encodedSecret = Base32::encode($encryptedSecret);

        $enrollment = [
            'actor-id' => $canonical,
            'key-id' => $keyId,
            'current-time' => (string) time(),
            'otp-current' => $wrongOtpCurrent,
            'otp-previous' => $wrongOtpPrevious,
            'totp-secret' => $encodedSecret,
        ];
        $messageToSign = $this->preAuthEncode([
            '!pkd-context',
            'fedi-e2ee:v1/api/totp/enroll',
            'action',
            'TOTP-Enroll',
            'message',
            json_encode($enrollment),
        ]);
        $signature = $keypair->sign($messageToSign);

        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/enroll',
            'action' => 'TOTP-Enroll',
            'current-time' => (string) time(),
            'enrollment' => $enrollment,
            'signature' => Base64UrlSafe::encodeUnpadded($signature)
        ];

        $request = new ServerRequest([], [], '/api/totp/enroll', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(406, $response->getStatusCode());
        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseBody);
    }
}
