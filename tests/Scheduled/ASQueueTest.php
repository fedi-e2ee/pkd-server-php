<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Scheduled;

use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    HttpSignatureException,
    NotImplementedException
};
use FediE2EE\PKD\Crypto\HttpSignature;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\Scheduled\ASQueue;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\PQCrypto\Exception\MLDSAInternalException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use SodiumException;

#[CoversClass(ASQueue::class)]
class ASQueueTest extends TestCase
{
    use HttpTestTrait;

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HttpSignatureException
     * @throws MLDSAInternalException
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws SodiumException
     */
    public function testBuildSignedPostVerifiesWithServerKey(): void
    {
        $config = $this->getConfig();
        $queue = new ASQueue($config);

        $body = json_encode(['content' => 'hello world']);
        $inboxUrl = 'https://peer.example.test/users/alice/inbox';

        $method = new ReflectionMethod(ASQueue::class, 'buildSignedPost');
        $request = $method->invoke($queue, $inboxUrl, $body);

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame($inboxUrl, (string) $request->getUri());

        $this->assertSame('application/activity+json', $request->getHeaderLine('Accept'));
        $this->assertSame('application/activity+json', $request->getHeaderLine('Content-Type'));

        $expectedDigest = 'sha-512=:' . Base64::encode(hash('sha512', $body, true)) . ':';
        $this->assertSame(
            $expectedDigest,
            $request->getHeaderLine('Content-Digest'),
            'Content-Digest must be sha-512 of the outbound body'
        );

        $sigInput = $request->getHeaderLine('Signature-Input');
        $this->assertStringStartsWith(
            'sig1=("@method" "@path" "content-type" "content-digest");',
            $sigInput
        );
        $params = $config->getParams();
        $expectedKeyId = 'https://' . $params->hostname . '/users/' . $params->actorUsername . '#main-key';
        $this->assertStringContainsString(
            'keyid="' . $expectedKeyId . '"',
            $sigInput
        );

        $pk = $config->getSigningKeys()->publicKey;
        $this->assertTrue(
            new HttpSignature()->verify($pk, $request),
            'Outbound AP POST must verify with the key published at UserPage'
        );
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws HttpSignatureException
     * @throws MLDSAInternalException
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws SodiumException
     */
    public function testTamperedDigestFailsVerification(): void
    {
        $config = $this->getConfig();
        $queue = new ASQueue($config);
        $method = new ReflectionMethod(ASQueue::class, 'buildSignedPost');
        $request = $method->invoke(
            $queue,
            'https://peer.example.test/inbox',
            '{"content":"original"}'
        );

        $forgedDigest = 'sha-512=:' . Base64::encode(hash('sha512', '{"content":"forged"}', true)) . ':';
        $tampered = $request->withHeader('Content-Digest', $forgedDigest);

        $pk = $config->getSigningKeys()->publicKey;
        $this->assertFalse(
            new HttpSignature()->verify($pk, $tampered),
            'Tampering with Content-Digest must break the signature'
        );
    }
}
