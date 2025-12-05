<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use Exception;
use FediE2EE\PKD\Crypto\Protocol\Actions\AddKey;
use FediE2EE\PKD\Crypto\{
    AttributeEncryption\AttributeKeyMap,
    Protocol\Handler,
    SecretKey,
    SymmetricKey,
    UtilTrait
};
use FediE2EE\PKDServer\RequestHandlers\Api\{
    TotpDisenroll
};
use FediE2EE\PKDServer\{
    ActivityPub\WebFinger,
    AppCache,
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
use ParagonIE\ConstantTime\Base64UrlSafe;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use PHPUnit\Framework\TestCase;

#[CoversClass(TotpDisenroll::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(AppCache::class)]
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
class TotpDisenrollTest extends TestCase
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
        [, $canonical] = $this->makeDummyActor('disenroll-example.com');
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
        $this->assertObjectHasProperty('keyID', $result);

        // Create TOTP Secret:
        $totpSecret = random_bytes(20);

        // $domain = parse_url($canonical)['host'];
        $domain = 'disenroll-example.com';

        // Save and verify it's saved:
        $table->saveSecret($domain, $totpSecret);
        $storedSecret = $table->getSecretByDomain($domain);
        $this->assertSame($storedSecret, $totpSecret);

        // Prepare disenrollment request:
        $otpCurrent = self::generateTOTP($totpSecret);
        $disenrollment = [
            'actor-id' => $canonical,
            'key-id' => $result->keyID,
            'otp' => $otpCurrent,
        ];
        $messageToSign = $this->preAuthEncode([
            '!pkd-context',
            'fedi-e2ee:v1/api/totp/disenroll',
            'action',
            'TOTP-Disenroll',
            'message',
            json_encode($disenrollment),
        ]);
        $signature = $keypair->sign($messageToSign);

        $body = [
            '!pkd-context' => 'fedi-e2ee:v1/api/totp/disenroll',
            'action' => 'TOTP-Disenroll',
            'current-time' => (string) time(),
            'disenrollment' => $disenrollment,
            'signature' => Base64UrlSafe::encodeUnpadded($signature)
        ];

        // Dispatch the request
        $request = new ServerRequest(
            [],
            [],
            '/api/totp/disenroll',
            'POST'
        )
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream(json_encode($body)));
        $response = $this->dispatchRequest($request);

        // Validate the HTTP response
        $this->assertSame(200, $response->getStatusCode(), (string) $response->getBody());
        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertTrue($responseBody['success']);

        // Let's check the domain now:
        $this->assertEmpty($table->getSecretByDomain($domain));
    }
}
