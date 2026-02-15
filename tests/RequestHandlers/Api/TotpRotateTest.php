<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use FediE2EE\PKD\Crypto\Protocol\Actions\AddKey;
use FediE2EE\PKD\Crypto\{
    AttributeEncryption\AttributeKeyMap,
    Protocol\Handler,
    Protocol\HPKEAdapter,
    SecretKey,
    SymmetricKey,
    UtilTrait
};
use FediE2EE\PKD\Crypto\Exceptions\{
    BundleException,
    CryptoException,
    InputException,
    JsonException,
    NetworkException,
    NotImplementedException,
    ParserException
};
use GuzzleHttp\Exception\GuzzleException;
use FediE2EE\PKDServer\RequestHandlers\Api\{
    TotpRotate
};
use FediE2EE\PKDServer\{
    ActivityPub\WebFinger,
    AppCache,
    Dependency\EasyDBHandler,
    Dependency\HPKE,
    Dependency\InjectConfigStrategy,
    Dependency\WrappedEncryptedRow,
    Math,
    Middleware\RateLimitMiddleware,
    Protocol,
    Protocol\KeyWrapping,
    Protocol\Payload,
    Protocol\RewrapConfig,
    RateLimit\DefaultRateLimiting,
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
use FediE2EE\PKDServer\Tables\{
    Actors,
    MerkleState,
    Peers,
    PublicKeys,
    TOTP
};
use FediE2EE\PKDServer\Traits\{
    ConfigTrait,
    TOTPTrait
};
use FediE2EE\PKDServer\Tables\Records\{
    Actor,
    ActorKey,
    MerkleLeaf,
    Peer
};
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use Laminas\Diactoros\{
    ServerRequest,
    StreamFactory
};
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\CipherSweet\Exception\{
    ArrayKeyException,
    CipherSweetException,
    CryptoOperationException,
    InvalidCiphertextException
};
use ParagonIE\ConstantTime\{
    Base32,
    Base64UrlSafe
};
use ParagonIE\HPKE\HPKEException;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    DataProvider,
    UsesClass
};
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
use SodiumException;
use function floor;
use function hash;
use function json_decode;
use function json_encode;
use function parse_url;
use function random_bytes;
use function substr;
use function time;

#[CoversClass(TotpRotate::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(EasyDBHandler::class)]
#[UsesClass(HPKE::class)]
#[UsesClass(WrappedEncryptedRow::class)]
#[UsesClass(InjectConfigStrategy::class)]
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
#[UsesClass(Peer::class)]
#[UsesClass(TOTP::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(Math::class)]
#[UsesClass(RewrapConfig::class)]
#[UsesClass(RateLimitMiddleware::class)]
#[UsesClass(DefaultRateLimiting::class)]
class TotpRotateTest extends TestCase
{
    use HttpTestTrait;
    use ConfigTrait;
    use UtilTrait;

    public static function timeOffsetProvider(): array
    {
        return [
            [0],
            [-86400],
            [86400],
        ];
    }

    /**
     * @throws ArrayKeyException
     * @throws BundleException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws HPKEException
     * @throws InputException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    #[DataProvider("timeOffsetProvider")]
    public function testHandle(int $timeOffset): void
    {
        $sk = SecretKey::generate();
        $pk = $sk->getPublicKey();
        $hash = hash('sha256', pack('q', $timeOffset));
        $rotatedomain = substr($hash, 0, 10) . '.rotate-example.com';
        [, $canonical] = $this->makeDummyActor($rotatedomain);

        /** @var TOTP $totpTable */
        $totpTable = $this->table('TOTP');
        if (!($totpTable instanceof TOTP)) {
            $this->fail('type error: table() result not instance of TOTP table class');
        }

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        if (!($merkleState instanceof MerkleState)) {
            $this->fail('type error: table() result not instance of MerkleState table class');
        }

        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);
        $latestRoot = $merkleState->getLatestRoot();

        $serverHpke = $config->getHPKE();
        $handler = new Handler();

        $addKey = new AddKey($canonical, $pk);
        $akm = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg = $addKey->encrypt($akm);
        $this->clearOldTransaction($config);
        $bundle = $handler->handle($encryptedMsg, $sk, $akm, $latestRoot);
        $encryptedForServer = $handler->hpkeEncrypt(
            $bundle,
            $serverHpke->encapsKey,
            $serverHpke->cs,
        );
        $addKeyResult = $protocol->addKey($encryptedForServer, $canonical);
        $keyId = $addKeyResult->keyID;

        $domain = parse_url($canonical)['host'];
        $this->assertSame($rotatedomain, $domain);
        $oldSecret = random_bytes(20);
        $totpTable->saveSecret($domain, $oldSecret);

        $totpGenerator = new class() {
            use TOTPTrait;
        };

        $oldOtp = $totpGenerator->generateTOTP($oldSecret, time());

        $newSecret = random_bytes(20);
        $newOtpCurrent = $totpGenerator->generateTOTP($newSecret, time());
        $newOtpPrevious = $totpGenerator->generateTOTP($newSecret, time() - 30);

        $hpke = $this->config()->getHPKE();
        $encryptedSecret = new HPKEAdapter($hpke->cs, 'fedi-e2ee:v1/api/totp/rotate')->seal(
            $hpke->getEncapsKey(),
            $newSecret
        );

        $time = (string) (time() + $timeOffset);
        $rotation = [
            'actor-id' => $canonical,
            'key-id' => $keyId,
            'old-otp' => $oldOtp,
            'new-otp-current' => $newOtpCurrent,
            'new-otp-previous' => $newOtpPrevious,
            'new-totp-secret' => Base32::encode($encryptedSecret),
        ];

        $message = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
            'action' => 'TOTP-Rotate',
            'current-time' => $time,
            'rotation' => $rotation,
        ];

        $toSign = $this->preAuthEncode([
            '!pkd-context',
            'fedi-e2ee:v1/api/totp/rotate',
            'action',
            'TOTP-Rotate',
            'message',
            json_encode($rotation, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
        $signature = $sk->sign($toSign);
        $message['signature'] = Base64UrlSafe::encodeUnpadded($signature);

        $encodedBody = json_encode($message);
        if ($encodedBody === false) {
            $this->fail('Failed to encode message');
        }
        $request = $this->makePostRequest(
            '/api/totp/rotate',
            $encodedBody
        );
        $response = $this->dispatchRequest($request);
        $body = json_decode((string)$response->getBody(), true);
        if (abs($timeOffset) < 300) {
            $this->assertSame(200, $response->getStatusCode());
            $this->assertTrue($body['success']);
            // Verify response format includes !pkd-context
            $this->assertSame('fedi-e2ee:v1/api/totp/rotate', $body['!pkd-context']);
            $this->assertArrayHasKey('time', $body);
            $this->assertIsString($body['time']);

            $dbSecret = $totpTable->getSecretByDomain($domain);
            $this->assertSame(
                bin2hex($newSecret),
                bin2hex($dbSecret)
            );
        } elseif ($timeOffset < 0) {
            $this->assertSame(400, $response->getStatusCode());
            $this->assertArrayHasKey('error', $body);
            $this->assertSame('OTP is too stale', $body['error']);
        } else {
            $this->assertSame(400, $response->getStatusCode());
            $this->assertArrayHasKey('error', $body);
            $this->assertSame('OTP is too new; did you time travel?', $body['error']);
        }
        $this->assertNotInTransaction();
    }

    /**
     * Test that missing actor-id returns error.
     *
     * @throws DependencyException
     */
    public function testMissingActorId(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
            'action' => 'TOTP-Rotate',
            'current-time' => (string) time(),
            'rotation' => [
                // actor-id is missing
                'key-id' => 'some-key-id',
                'old-otp' => '12345678',
                'new-otp-current' => '87654321',
                'new-otp-previous' => '11111111',
                'new-totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/rotate', 'POST')
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
     * @throws DependencyException
     */
    public function testMissingKeyId(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
            'action' => 'TOTP-Rotate',
            'current-time' => (string) time(),
            'rotation' => [
                'actor-id' => 'https://example.com/users/test',
                // key-id is missing
                'old-otp' => '12345678',
                'new-otp-current' => '87654321',
                'new-otp-previous' => '11111111',
                'new-totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/rotate', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that missing old-otp returns error.
     *
     * @throws DependencyException
     */
    public function testMissingOldOtp(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
            'action' => 'TOTP-Rotate',
            'current-time' => (string) time(),
            'rotation' => [
                'actor-id' => 'https://example.com/users/test',
                'key-id' => 'some-key-id',
                // old-otp is missing
                'new-otp-current' => '87654321',
                'new-otp-previous' => '11111111',
                'new-totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/rotate', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that missing new-otp-current returns error.
     *
     * @throws DependencyException
     */
    public function testMissingNewOtpCurrent(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
            'action' => 'TOTP-Rotate',
            'current-time' => (string) time(),
            'rotation' => [
                'actor-id' => 'https://example.com/users/test',
                'key-id' => 'some-key-id',
                'old-otp' => '12345678',
                // new-otp-current is missing
                'new-otp-previous' => '11111111',
                'new-totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/rotate', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that missing new-otp-previous returns error.
     *
     * @throws DependencyException
     */
    public function testMissingNewOtpPrevious(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
            'action' => 'TOTP-Rotate',
            'current-time' => (string) time(),
            'rotation' => [
                'actor-id' => 'https://example.com/users/test',
                'key-id' => 'some-key-id',
                'old-otp' => '12345678',
                'new-otp-current' => '87654321',
                // new-otp-previous is missing
                'new-totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/rotate', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that missing new-totp-secret returns error.
     *
     * @throws DependencyException
     */
    public function testMissingNewTotpSecret(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
            'action' => 'TOTP-Rotate',
            'current-time' => (string) time(),
            'rotation' => [
                'actor-id' => 'https://example.com/users/test',
                'key-id' => 'some-key-id',
                'old-otp' => '12345678',
                'new-otp-current' => '87654321',
                'new-otp-previous' => '11111111',
                // new-totp-secret is missing
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/rotate', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that missing action returns error.
     *
     * @throws DependencyException
     */
    public function testMissingAction(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
            // action is missing
            'current-time' => (string) time(),
            'rotation' => [
                'actor-id' => 'https://example.com/users/test',
                'key-id' => 'some-key-id',
                'old-otp' => '12345678',
                'new-otp-current' => '87654321',
                'new-otp-previous' => '11111111',
                'new-totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/rotate', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that missing current-time returns error.
     *
     * @throws DependencyException
     */
    public function testMissingCurrentTime(): void
    {
        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
            'action' => 'TOTP-Rotate',
            // current-time is missing
            'rotation' => [
                'actor-id' => 'https://example.com/users/test',
                'key-id' => 'some-key-id',
                'old-otp' => '12345678',
                'new-otp-current' => '87654321',
                'new-otp-previous' => '11111111',
                'new-totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/rotate', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that missing !pkd-context returns error.
     *
     * @throws DependencyException
     */
    public function testMissingPkdContext(): void
    {
        $body = [
            // !pkd-context is missing
            'action' => 'TOTP-Rotate',
            'current-time' => (string) time(),
            'rotation' => [
                'actor-id' => 'https://example.com/users/test',
                'key-id' => 'some-key-id',
                'old-otp' => '12345678',
                'new-otp-current' => '87654321',
                'new-otp-previous' => '11111111',
                'new-totp-secret' => 'ABCDEFGH',
            ],
            'signature' => 'fake-signature'
        ];

        $request = new ServerRequest([], [], '/api/totp/rotate', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * Test that invalid JSON returns error.
     *
     * @throws DependencyException
     */
    public function testInvalidJson(): void
    {
        $request = new ServerRequest([], [], '/api/totp/rotate', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream('not valid json'));
        $response = $this->dispatchRequest($request);

        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseBody);
    }

    /**
     * @throws ArrayKeyException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function testInvalidSignature(): void
    {
        $sk = SecretKey::generate();
        $pk = $sk->getPublicKey();
        [, $canonical] = $this->makeDummyActor('invalid-sig-rotate-example.com');

        /** @var TOTP $totpTable */
        $totpTable = $this->table('TOTP');
        if (!($totpTable instanceof TOTP)) {
            $this->fail('type error: table() result not instance of TOTP table class');
        }

        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');
        if (!($merkleState instanceof MerkleState)) {
            $this->fail('type error: table() result not instance of MerkleState table class');
        }

        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);
        $latestRoot = $merkleState->getLatestRoot();

        $serverHpke = $config->getHPKE();
        $handler = new Handler();

        $addKey = new AddKey($canonical, $pk);
        $akm = new AttributeKeyMap()
            ->addKey('actor', SymmetricKey::generate())
            ->addKey('public-key', SymmetricKey::generate());
        $encryptedMsg = $addKey->encrypt($akm);
        $this->clearOldTransaction($config);
        $bundle = $handler->handle($encryptedMsg, $sk, $akm, $latestRoot);
        $encryptedForServer = $handler->hpkeEncrypt(
            $bundle,
            $serverHpke->encapsKey,
            $serverHpke->cs,
        );
        $addKeyResult = $protocol->addKey($encryptedForServer, $canonical);
        $keyId = $addKeyResult->keyID;

        $domain = parse_url($canonical)['host'];
        $this->assertSame('invalid-sig-rotate-example.com', $domain);
        $oldSecret = random_bytes(20);
        $totpTable->saveSecret($domain, $oldSecret);

        $totpGenerator = new class() {
            use TOTPTrait;
        };

        $oldOtp = $totpGenerator->generateTOTP($oldSecret, time());

        $newSecret = random_bytes(20);
        $newOtpCurrent = $totpGenerator->generateTOTP($newSecret, time());
        $newOtpPrevious = $totpGenerator->generateTOTP($newSecret, time() - 30);

        $hpke = $this->config()->getHPKE();
        $encryptedSecret = new HPKEAdapter($hpke->cs, 'fedi-e2ee:v1/api/totp/rotate')->seal(
            $hpke->getEncapsKey(),
            $newSecret
        );

        $rotation = [
            'actor-id' => $canonical,
            'key-id' => $keyId,
            'old-otp' => $oldOtp,
            'new-otp-current' => $newOtpCurrent,
            'new-otp-previous' => $newOtpPrevious,
            'new-totp-secret' => Base32::encode($encryptedSecret),
        ];

        $time = (string) time();
        $message = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
            'action' => 'TOTP-Rotate',
            'current-time' => (string) $time,
            'rotation' => $rotation,
        ];

        $toSign = $this->preAuthEncode([
            '!pkd-context',
            'fedi-e2ee:v1/api/totp/rotate',
            'action',
            'TOTP-Rotate',
            'message',
            json_encode($rotation)
        ]);
        $signature = $sk->sign($toSign);
        // Flip all bits to make invalid:
        $signature ^= str_repeat("\xFF", 64);
        $message['signature'] = Base64UrlSafe::encodeUnpadded($signature);

        $encodedBody = json_encode($message);
        if ($encodedBody === false) {
            $this->fail('Failed to encode message');
        }
        $request = $this->makePostRequest(
            '/api/totp/rotate',
            $encodedBody
        );
        $response = $this->dispatchRequest($request);
        $body = json_decode((string)$response->getBody(), true);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', $body);
        $this->assertSame('Invalid signature', $body['error']);
    }

    /**
     * @throws ArrayKeyException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function testEqualTimestepsRejected(): void
    {
        $sk = SecretKey::generate();
        [, $canonical] = $this->makeDummyActor('equal-ts-rotate.com');

        /** @var TOTP $totpTable */
        $totpTable = $this->table('TOTP');

        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);

        $addKeyResult = $this->addKeyForActor($canonical, $sk, $protocol, $config);
        $keyId = $addKeyResult->keyID;

        $domain = parse_url($canonical)['host'];
        $oldSecret = random_bytes(20);
        $totpTable->saveSecret($domain, $oldSecret);

        $totpGenerator = new class() {
            use TOTPTrait;
        };

        $oldOtp = $totpGenerator->generateTOTP($oldSecret, time());

        $newSecret = random_bytes(20);
        // Generate BOTH codes at the SAME time to get equal time steps
        $sameTime = time();
        $newOtpCurrent = $totpGenerator->generateTOTP($newSecret, $sameTime);
        $newOtpPrevious = $totpGenerator->generateTOTP($newSecret, $sameTime);

        $hpke = $this->config()->getHPKE();
        $encryptedSecret = new HPKEAdapter($hpke->cs, 'fedi-e2ee:v1/api/totp/rotate')->seal(
            $hpke->getEncapsKey(),
            $newSecret
        );

        $rotation = [
            'actor-id' => $canonical,
            'key-id' => $keyId,
            'old-otp' => $oldOtp,
            'new-otp-current' => $newOtpCurrent,
            'new-otp-previous' => $newOtpPrevious,
            'new-totp-secret' => Base32::encode($encryptedSecret),
        ];
        $this->executeRotateAndAssertError($sk, $rotation, 406, 'New TOTP codes must be increasing');
    }

    /**
     * @throws ArrayKeyException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function testInvalidNewOtpCodes(): void
    {
        $sk = SecretKey::generate();
        $pk = $sk->getPublicKey();
        [, $canonical] = $this->makeDummyActor('invalid-new-otp-rotate.com');

        /** @var TOTP $totpTable */
        $totpTable = $this->table('TOTP');
        /** @var MerkleState $merkleState */
        $merkleState = $this->table('MerkleState');

        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);

        $addKeyResult = $this->addKeyForActor($canonical, $sk, $protocol, $config);
        $keyId = $addKeyResult->keyID;

        $domain = parse_url($canonical)['host'];
        $oldSecret = random_bytes(20);
        $totpTable->saveSecret($domain, $oldSecret);

        $totpGenerator = new class() {
            use TOTPTrait;
        };

        $oldOtp = $totpGenerator->generateTOTP($oldSecret, time());

        $newSecret = random_bytes(20);
        $newOtpCurrent = $totpGenerator->generateTOTP($newSecret, time());
        $newOtpPrevious = $totpGenerator->generateTOTP($newSecret, time() - 30);

        $hpke = $this->config()->getHPKE();
        $encryptedSecret = new HPKEAdapter($hpke->cs, 'fedi-e2ee:v1/api/totp/rotate')->seal(
            $hpke->getEncapsKey(),
            $newSecret
        );

        // Case 1: new-otp-current is invalid (kills first part of LogicalOr)
        $rotation = [
            'actor-id' => $canonical,
            'key-id' => $keyId,
            'old-otp' => $oldOtp,
            'new-otp-current' => '00000000', // INVALID
            'new-otp-previous' => $newOtpPrevious,
            'new-totp-secret' => Base32::encode($encryptedSecret),
        ];
        $this->executeRotateAndAssertError($sk, $rotation, 406, 'Invalid new TOTP codes');

        // Case 2: new-otp-previous is invalid (kills second part of LogicalOr)
        $rotation = [
            'actor-id' => $canonical,
            'key-id' => $keyId,
            'old-otp' => $oldOtp,
            'new-otp-current' => $newOtpCurrent,
            'new-otp-previous' => '00000000', // INVALID
            'new-totp-secret' => Base32::encode($encryptedSecret),
        ];
        $this->executeRotateAndAssertError($sk, $rotation, 406, 'Invalid new TOTP codes');
    }

    private function executeRotateAndAssertError(
        SecretKey $sk,
        array $rotation,
        int $expectedStatus,
        string $expectedError
    ): void {
        foreach ($rotation as $k => $v) {
            if (empty($v)) {
                fwrite(STDERR, "FIELD {$k} IS EMPTY\n");
            }
        }
        $message = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
            'action' => 'TOTP-Rotate',
            'current-time' => (string) time(),
            'rotation' => $rotation,
        ];
        $toSign = $this->preAuthEncode([
            '!pkd-context',
            'fedi-e2ee:v1/api/totp/rotate',
            'action',
            'TOTP-Rotate',
            'message',
            json_encode($rotation, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
        $message['signature'] = Base64UrlSafe::encodeUnpadded($sk->sign($toSign));
        $request = $this->makePostRequest('/api/totp/rotate', $message);
        $response = $this->dispatchRequest($request);
        $body = json_decode((string)$response->getBody(), true);
        if ($response->getStatusCode() !== $expectedStatus) {
            fwrite(STDERR, "BODY: " . json_encode($body) . "\n");
        }
        $this->assertSame($expectedStatus, $response->getStatusCode());
        $this->assertSame($expectedError, $body['error']);
    }

    /**
     * @throws ArrayKeyException
     * @throws CacheException
     * @throws CertaintyException
     * @throws CipherSweetException
     * @throws CryptoException
     * @throws CryptoOperationException
     * @throws DependencyException
     * @throws HPKEException
     * @throws InvalidArgumentException
     * @throws InvalidCiphertextException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws ParserException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    public function testRotateReplay(): void
    {
        $sk = SecretKey::generate();
        [, $canonical] = $this->makeDummyActor('replay-rotate-example.com');

        /** @var TOTP $totpTable */
        $totpTable = $this->table('TOTP');

        $config = $this->getConfig();
        $this->clearOldTransaction($config);
        $protocol = new Protocol($config);

        $addKeyResult = $this->addKeyForActor($canonical, $sk, $protocol, $config);
        $keyId = $addKeyResult->keyID;

        $domain = parse_url($canonical)['host'];
        $oldSecret = random_bytes(20);
        $totpTable->saveSecret($domain, $oldSecret);

        $totpGenerator = new class() {
            use TOTPTrait;
        };

        $oldOtp = $totpGenerator->generateTOTP($oldSecret, time());

        $newSecret = random_bytes(20);
        $newOtpCurrent = $totpGenerator->generateTOTP($newSecret, time());
        $newOtpPrevious = $totpGenerator->generateTOTP($newSecret, time() - 30);

        $hpke = $this->config()->getHPKE();
        $encryptedSecret = (new HPKEAdapter($hpke->cs, 'fedi-e2ee:v1/api/totp/rotate'))->seal(
            $hpke->getEncapsKey(),
            $newSecret
        );

        $rotation = [
            'actor-id' => $canonical,
            'key-id' => $keyId,
            'old-otp' => $oldOtp,
            'new-otp-current' => $newOtpCurrent,
            'new-otp-previous' => $newOtpPrevious,
            'new-totp-secret' => Base32::encode($encryptedSecret),
        ];

        // First rotation should succeed
        $message = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
            'action' => 'TOTP-Rotate',
            'current-time' => (string) time(),
            'rotation' => $rotation
        ];
        $toSign = $this->preAuthEncode([
            '!pkd-context',
            'fedi-e2ee:v1/api/totp/rotate',
            'action',
            'TOTP-Rotate',
            'message',
            json_encode($rotation, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
        $message['signature'] = Base64UrlSafe::encodeUnpadded($sk->sign($toSign));
        $request = $this->makePostRequest('/api/totp/rotate', $message);
        $response = $this->dispatchRequest($request);
        $this->assertSame(200, $response->getStatusCode(), $response->getBody()->getContents());

        // Second rotation with the SAME old-otp should fail
        $time =  (int) floor(time() / 30);
        $totpTable->updateSecret($domain, $oldSecret, $time);

        $request = $this->makePostRequest('/api/totp/rotate', $message);
        $response = $this->dispatchRequest($request);

        $this->assertSame(403, $response->getStatusCode());
        $response->getBody()->rewind();
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('Old TOTP code already used', $body['error']);
    }

    public static function deletedKeysProvider(): array
    {
        return [
            [[
                '!pkd-context' => '',
                'current-time' => (string) (time()),
                'action' => 'TOTP-Rotate',
                'rotation' => [
                    'actor-id' => 'https://example.com/users/alice',
                    'key-id' => 'test',
                    'old-otp' => '12345678',
                    'new-otp-current' => '12345678',
                    'new-otp-previous' => '12345678',
                    'new-totp-secret' => '12345678',
                ]
            ]], [[
                '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
                'current-time' => '',
                'action' => 'TOTP-Rotate',
                'rotation' => [
                    'actor-id' => 'https://example.com/users/alice',
                    'key-id' => 'test',
                    'old-otp' => '12345678',
                    'new-otp-current' => '12345678',
                    'new-otp-previous' => '12345678',
                    'new-totp-secret' => '12345678',
                ]
            ]], [[
                '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
                'current-time' => (string) (time()),
                'action' => '',
                'rotation' => [
                    'actor-id' => 'https://example.com/users/alice',
                    'key-id' => 'test',
                    'old-otp' => '12345678',
                    'new-otp-current' => '12345678',
                    'new-otp-previous' => '12345678',
                    'new-totp-secret' => '12345678',
                ]
            ]], [[
                '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
                'current-time' => (string) (time()),
                'action' => 'TOTP-Rotate',
                'rotation' => [
                    'actor-id' => '',
                    'key-id' => 'test',
                    'old-otp' => '12345678',
                    'new-otp-current' => '12345678',
                    'new-otp-previous' => '12345678',
                    'new-totp-secret' => '12345678',
                ]
            ]], [[
                '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
                'current-time' => (string) (time()),
                'action' => 'TOTP-Rotate',
                'rotation' => [
                    'actor-id' => 'https://example.com/users/alice',
                    'key-id' => '',
                    'old-otp' => '12345678',
                    'new-otp-current' => '12345678',
                    'new-otp-previous' => '12345678',
                    'new-totp-secret' => '12345678',
                ]
            ]], [[
                '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
                'current-time' => (string) (time()),
                'action' => 'TOTP-Rotate',
                'rotation' => [
                    'actor-id' => 'https://example.com/users/alice',
                    'key-id' => 'test',
                    'old-otp' => '',
                    'new-otp-current' => '12345678',
                    'new-otp-previous' => '12345678',
                    'new-totp-secret' => '12345678',
                ]
            ]], [[
                '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
                'current-time' => (string) (time()),
                'action' => 'TOTP-Rotate',
                'rotation' => [
                    'actor-id' => 'https://example.com/users/alice',
                    'key-id' => 'test',
                    'old-otp' => '12345678',
                    'new-otp-current' => '',
                    'new-otp-previous' => '12345678',
                    'new-totp-secret' => '12345678',
                ]
            ]], [[
                '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
                'current-time' => (string) (time()),
                'action' => 'TOTP-Rotate',
                'rotation' => [
                    'actor-id' => 'https://example.com/users/alice',
                    'key-id' => 'test',
                    'old-otp' => '12345678',
                    'new-otp-current' => '12345678',
                    'new-otp-previous' => '',
                    'new-totp-secret' => '12345678',
                ]
            ]], [[
                '!pkd-context' => 'fedi-e2ee:v1/api/totp/rotate',
                'current-time' => (string) (time()),
                'action' => 'TOTP-Rotate',
                'rotation' => [
                    'actor-id' => 'https://example.com/users/alice',
                    'key-id' => 'test',
                    'old-otp' => '12345678',
                    'new-otp-current' => '12345678',
                    'new-otp-previous' => '12345678',
                    'new-totp-secret' => '',
                ]
            ]]
        ];
    }

    /**
     * @throws DependencyException
     */
    #[DataProvider("deletedKeysProvider")]
    public function testMissingFields(array $data): void
    {
        $request = $this->makePostRequest('/api/totp/rotate', $data);
        $response = $this->dispatchRequest($request);
        $this->assertSame(400, $response->getStatusCode());
        $body = $response->getBody()->getContents();
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('error', $decoded);
        $this->assertSame('Missing required fields', $decoded['error']);
    }
}
