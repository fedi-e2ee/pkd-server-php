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
use FediE2EE\PKDServer\{
    ActivityPub\WebFinger,
    AppCache,
    Dependency\HPKE,
    Dependency\InjectConfigStrategy,
    Dependency\WrappedEncryptedRow,
    Protocol,
    Protocol\Payload,
    ServerConfig,
    Table,
    TableCache
};
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
        $protocol = new Protocol($this->getConfig());
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
        $result = $protocol->addKey($encryptedForServer);
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

        // Verify secret was stored correctly
        /** @var TOTP $totpTable */
        $totpTable = $this->table('TOTP');
        // $domain = parse_url($canonical)['host'];
        $domain = 'enroll-example.com';
        $storedSecret = $totpTable->getSecretByDomain($domain);

        $this->assertNotNull($storedSecret);
        $this->assertSame($totpSecret, $storedSecret);
    }
}
