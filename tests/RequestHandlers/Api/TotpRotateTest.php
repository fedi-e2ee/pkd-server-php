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
    TotpRotate
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
use ParagonIE\ConstantTime\{
    Base32,
    Base64UrlSafe
};
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use PHPUnit\Framework\TestCase;

#[CoversClass(TotpRotate::class)]
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
#[UsesClass(WebFinger::class)]
class TotpRotateTest extends TestCase
{
    use HttpTestTrait;
    use ConfigTrait;
    use UtilTrait;

    /**
     * @throws Exception
     */
    public function testHandle(): void
    {
        $sk = SecretKey::generate();
        $pk = $sk->getPublicKey();
        [, $canonical] = $this->makeDummyActor('rotate-example.com');

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
        $bundle = $handler->handle($encryptedMsg, $sk, $akm, $latestRoot);
        $encryptedForServer = $handler->hpkeEncrypt(
            $bundle,
            $serverHpke->encapsKey,
            $serverHpke->cs,
        );
        $addKeyResult = $protocol->addKey($encryptedForServer, $canonical);
        $keyId = $addKeyResult->keyID;

        $domain = parse_url($canonical)['host'];
        $this->assertSame('rotate-example.com', $domain);
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

        $time = (string) time();
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
            json_encode($rotation)
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
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertTrue($body['success']);

        $dbSecret = $totpTable->getSecretByDomain($domain);
        $this->assertSame(
            bin2hex($newSecret),
            bin2hex($dbSecret)
        );
        $this->assertNotInTransaction();
    }
}
