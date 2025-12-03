<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\ActivityPub;

use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    NotImplementedException,
    NetworkException
};
use FediE2EE\PKD\Crypto\SecretKey;
use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\Exceptions\FetchException;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use GuzzleHttp\{
    Client,
    Exception\GuzzleException,
    Psr7\Response
};
use ParagonIE\Certainty\{
    Exception\CertaintyException,
    RemoteFetch
};
use PHPUnit\Framework\{Attributes\CoversClass, Attributes\DataProvider, MockObject\Exception, TestCase};
use ReflectionProperty;
use SodiumException;

#[CoversClass(WebFinger::class)]
class WebFingerTest extends TestCase
{
    use HttpTestTrait;

    public function tearDown(): void
    {
        WebFinger::clearCanonicalCache();
        WebFinger::clearCache();
    }

    public function testConstructorDefaults(): void
    {
        // To prevent network calls for CA bundle
        $tmpDir = dirname(__DIR__, 2) . '/tmp';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }
        file_put_contents(
            $tmpDir . '/certainty-latest.json',
            json_encode(['latest' => '2022-12-31'])
        );
        file_put_contents(
            $tmpDir . '/certainty-2022-12-31.json',
            json_encode(['data' => ['test']])
        );

        $webFinger = new WebFinger();

        $fetchProp = new ReflectionProperty(WebFinger::class, 'fetch');
        $this->assertInstanceOf(RemoteFetch::class, $fetchProp->getValue($webFinger));

        $httpProp = new ReflectionProperty(WebFinger::class, 'http');
        $this->assertInstanceOf(Client::class, $httpProp->getValue($webFinger));
    }

    /**
     * @throws CertaintyException
     * @throws SodiumException
     */
    public function testConstructorWithClient(): void
    {
        $client = new Client();
        $webFinger = new WebFinger($client);

        $httpProp = new ReflectionProperty(WebFinger::class, 'http');
        $this->assertSame($client, $httpProp->getValue($webFinger));
    }

    /**
     * @throws CertaintyException
     * @throws GuzzleException
     * @throws NetworkException
     * @throws SodiumException
     * @throws Exception
     */
    public function testCanonicalizeCache(): void
    {
        // 1. Create a mock Client
        $mockHttp = $this->createMock(Client::class);
        $mockHttp->expects($this->once()) // Expect exactly one call
            ->method('get')
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{"subject":"https://example.com/users/alice","links":[{"rel":"self","href":"https://example.com/users/alice"}]}'
            ));

        // 2. Instantiate WebFinger with the mock client
        $webFinger = new WebFinger($mockHttp);

        // 3. Call canonicalize twice
        $result1 = $webFinger->canonicalize('alice@example.com');
        $result2 = $webFinger->canonicalize('alice@example.com');

        // 4. Assert the result is correct and the mock expectation is met
        $this->assertSame('https://example.com/users/alice', $result1);
        $this->assertSame('https://example.com/users/alice', $result2);
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws FetchException
     * @throws SodiumException
     * @throws NotImplementedException
     */
    public function testGetPublicKeyMultibase(): void
    {
        $sk = SecretKey::generate();
        $pk = $sk->getPublicKey();

        $mockHttp = $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'links' => [
                    [
                        'rel' => 'self',
                        'type' => 'application/activity+json',
                        'href' => 'https://example.com/users/alice'
                    ]
                ]
            ])),
            new Response(200, ['Content-Type' => 'application/activity+json'], json_encode([
                'assertionMethod' => [
                    [
                        'publicKeyMultibase' => $pk->toMultibase()
                    ]
                ]
            ]))
        ]);
        $webFinger = new WebFinger($mockHttp);
        $fetchedPk = $webFinger->getPublicKey('https://example.com/users/alice');
        $this->assertSame($pk->toString(), $fetchedPk->toString());
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws FetchException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    public function testGetPublicKeyPem(): void
    {
        $sk = SecretKey::generate();
        $pk = $sk->getPublicKey();

        $mockHttp = $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'links' => [
                    [
                        'rel' => 'self',
                        'type' => 'application/activity+json',
                        'href' => 'https://example.com/users/alice'
                    ]
                ]
            ])),
            new Response(200, ['Content-Type' => 'application/activity+json'], json_encode([
                'publicKey' => [
                    'publicKeyPem' => $pk->encodePem()
                ]
            ]))
        ]);
        $webFinger = new WebFinger($mockHttp);
        $fetchedPk = $webFinger->getPublicKey('https://example.com/users/alice');
        $this->assertSame($pk->toString(), $fetchedPk->toString());
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws FetchException
     * @throws SodiumException
     */
    public function testWebfingerNetworkError(): void
    {
        $mockHttp = $this->getMockClient([
            new Response(500)
        ]);
        $webFinger = new WebFinger($mockHttp);
        $this->expectException(FetchException::class);
        $webFinger->getPublicKey('https://example.com/users/alice');
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws FetchException
     * @throws SodiumException
     */
    public function testWebfingerInvalidJson(): void
    {
        $mockHttp = $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], 'not json')
        ]);
        $webFinger = new WebFinger($mockHttp);
        $this->expectException(FetchException::class);
        $webFinger->getPublicKey('https://example.com/users/alice');
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws FetchException
     * @throws SodiumException
     */
    public function testActivityPubNetworkError(): void
    {
        $mockHttp = $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'links' => [
                    [
                        'rel' => 'self',
                        'type' => 'application/activity+json',
                        'href' => 'https://example.com/users/alice'
                    ]
                ]
            ])),
            new Response(500)
        ]);
        $webFinger = new WebFinger($mockHttp);
        $this->expectException(FetchException::class);
        $webFinger->getPublicKey('https://example.com/users/alice');
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws FetchException
     * @throws SodiumException
     */
    public function testActivityPubInvalidJson(): void
    {
        $mockHttp = $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'links' => [
                    [
                        'rel' => 'self',
                        'type' => 'application/activity+json',
                        'href' => 'https://example.com/users/alice'
                    ]
                ]
            ])),
            new Response(200, ['Content-Type' => 'application/activity+json'], 'not json')
        ]);
        $webFinger = new WebFinger($mockHttp);
        $this->expectException(FetchException::class);
        $webFinger->getPublicKey('https://example.com/users/alice');
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws FetchException
     * @throws SodiumException
     */
    public function testNoPublicKey(): void
    {
        $mockHttp = $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'links' => [
                    [
                        'rel' => 'self',
                        'type' => 'application/activity+json',
                        'href' => 'https://example.com/users/alice'
                    ]
                ]
            ])),
            new Response(200, ['Content-Type' => 'application/activity+json'], json_encode([]))
        ]);
        $webFinger = new WebFinger($mockHttp);
        $this->expectException(FetchException::class);
        $webFinger->getPublicKey('https://example.com/users/alice');
    }

    public static function inboxUrlProvider(): array
    {
        return [
            ['soatok@furry.engineer', 'https://furry.engineer/users/soatok/inbox'],
            ['fedie2ee@mastodon.social', 'https://mastodon.social/ap/users/115428847654719749/inbox'],
        ];
    }

    #[DataProvider("inboxUrlProvider")]
    public function testGetInboxUrl(string $in, string $expect): void
    {
        /** @var Client $http */
        $http = $GLOBALS['config']->getGuzzle();
        $webFinger = new WebFinger($http);
        $inboxUrl = $webFinger->getInboxUrl($in);
        $this->assertSame($expect, $inboxUrl);
    }
}
