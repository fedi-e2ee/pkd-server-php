<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Traits;

use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    HttpSignatureException,
    NotImplementedException
};
use FediE2EE\PKD\Crypto\HttpSignature;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\ServerConfig;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use FediE2EE\PKDServer\Traits\{
    ConfigTrait,
    ReqTrait
};
use JsonException;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\PQCrypto\Exception\MLDSAInternalException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use SodiumException;

#[CoversNothing]
class ReqTraitTest extends TestCase
{
    use HttpTestTrait;

    private function makeHandler(ServerConfig $config): object
    {
        $handler = new class() {
            use ConfigTrait;
            use ReqTrait;
            public function emit(array $data, int $status = 200): ResponseInterface
            {
                return $this->json($data, $status);
            }
        };
        $handler->injectConfig($config);
        return $handler;
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws HttpSignatureException
     * @throws MLDSAInternalException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    public function testSignedJsonResponseRoundTripsThroughVerifier(): void
    {
        $config = $this->getConfig();
        $handler = $this->makeHandler($config);

        $response = $handler->emit(['ok' => true, 'n' => 42]);

        $this->assertTrue($response->hasHeader('Signature'));
        $this->assertTrue($response->hasHeader('Signature-Input'));
        $this->assertTrue($response->hasHeader('Content-Digest'));

        $sigInput = $response->getHeaderLine('Signature-Input');
        $this->assertStringStartsWith('sig1=(', $sigInput);
        $this->assertStringNotContainsString('sig1=();', $sigInput);
        $this->assertStringContainsString('"@status"', $sigInput);
        $this->assertStringContainsString('"content-type"', $sigInput);
        $this->assertStringContainsString('"content-digest"', $sigInput);

        $params = $config->getParams();
        $expectedKeyId = 'https://' . $params->hostname . '/users/' . $params->actorUsername . '#main-key';
        $this->assertStringContainsString(
            'keyid="' . $expectedKeyId . '"',
            $sigInput
        );

        $pk = $config->getSigningKeys()->publicKey;
        $verifier = new HttpSignature();
        $this->assertTrue(
            $verifier->verifyThrow($pk, $response),
            'Signed response must verify with the server public key'
        );
    }

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws HttpSignatureException
     * @throws MLDSAInternalException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    public function testContentDigestBindsToBody(): void
    {
        $config = $this->getConfig();
        $handler = $this->makeHandler($config);
        $pk = $config->getSigningKeys()->publicKey;

        $response = $handler->emit(['message' => 'authentic']);
        $digest = $response->getHeaderLine('Content-Digest');
        $this->assertMatchesRegularExpression('#^sha-512=:[A-Za-z0-9+/=]+:$#', $digest);

        $forgedBody = '{"message":"forged"}';
        $forgedDigest = 'sha-512=:' . Base64::encode(hash('sha512', $forgedBody, true)) . ':';
        $this->assertNotSame($digest, $forgedDigest);

        $this->assertSame(
            'sha-512=:' . Base64::encode(hash('sha512', (string) $response->getBody(), true)) . ':',
            $digest,
            'Content-Digest must be the sha-512 of the emitted body'
        );

        $tampered = $response->withHeader('Content-Digest', $forgedDigest);
        $this->assertFalse(
            (new HttpSignature())->verify($pk, $tampered),
            'Tampering with Content-Digest must invalidate the signature'
        );
    }
}
