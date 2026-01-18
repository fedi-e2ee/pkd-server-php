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
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    FetchException
};
use FediE2EE\PKDServer\AppCache;
use FediE2EE\PKDServer\ServerConfig;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use GuzzleHttp\{
    Client,
    Exception\ClientException,
    Exception\GuzzleException,
    Exception\RequestException,
    Handler\MockHandler,
    HandlerStack,
    Middleware,
    Psr7\Request,
    Psr7\Response
};
use ParagonIE\Certainty\{
    Exception\CertaintyException,
    RemoteFetch
};
use PHPUnit\Framework\{
    Attributes\CoversClass,
    Attributes\DataProvider,
    Attributes\UsesClass,
    MockObject\Exception,
    TestCase
};
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionMethod;
use ReflectionProperty;
use SodiumException;

#[CoversClass(WebFinger::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(ServerConfig::class)]
class WebFingerTest extends TestCase
{
    use HttpTestTrait;

    public function tearDown(): void
    {
        new WebFinger($this->getConfig())->clearCaches();
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

        $webFinger = new WebFinger($this->getConfig());

        $fetchProp = new ReflectionProperty(WebFinger::class, 'fetch');
        $this->assertInstanceOf(RemoteFetch::class, $fetchProp->getValue($webFinger));

        $httpProp = new ReflectionProperty(WebFinger::class, 'http');
        $this->assertInstanceOf(Client::class, $httpProp->getValue($webFinger));
    }

    /**
     * @return void
     * @throws CertaintyException
     * @throws SodiumException
     * @throws DependencyException
     */
    public function testConstructorWithClient(): void
    {
        $client = new Client();
        $webFinger = new WebFinger($this->getConfig(), $client);

        $httpProp = new ReflectionProperty(WebFinger::class, 'http');
        $this->assertSame($client, $httpProp->getValue($webFinger));
    }

    /**
     * @throws CertaintyException
     * @throws DependencyException
     * @throws Exception
     * @throws SodiumException
     * @throws CacheException
     */
    public function testClearCaches(): void
    {
        $mockCache = $this->createMock(AppCache::class);
        $mockCache->expects($this->exactly(3))->method('clear')->willReturn(true);

        $webFinger = new WebFinger($this->getConfig());

        $canonicalCacheProp = new ReflectionProperty(WebFinger::class, 'canonicalCache');
        $canonicalCacheProp->setValue($webFinger, $mockCache);

        $inboxCacheProp = new ReflectionProperty(WebFinger::class, 'inboxCache');
        $inboxCacheProp->setValue($webFinger, $mockCache);

        $pkCacheProp = new ReflectionProperty(WebFinger::class, 'pkCache');
        $pkCacheProp->setValue($webFinger, $mockCache);

        $webFinger->clearCaches();
    }

    /**
     * @throws CertaintyException
     * @throws DependencyException
     * @throws SodiumException
     */
    public function testCacheTTLValues(): void
    {
        $webFinger = new WebFinger($this->getConfig());

        $canonicalCacheProp = new ReflectionProperty(WebFinger::class, 'canonicalCache');
        $canonicalCache = $canonicalCacheProp->getValue($webFinger);
        $this->assertSame(
            3600,
            new ReflectionProperty(AppCache::class, 'defaultTTL')->getValue($canonicalCache)
        );

        $inboxCacheProp = new ReflectionProperty(WebFinger::class, 'inboxCache');
        $inboxCache = $inboxCacheProp->getValue($webFinger);
        $this->assertSame(
            60,
            new ReflectionProperty(AppCache::class, 'defaultTTL')->getValue($inboxCache)
        );

        $pkCacheProp = new ReflectionProperty(WebFinger::class, 'pkCache');
        $pkCache = $pkCacheProp->getValue($webFinger);
        $this->assertSame(
            60,
            new ReflectionProperty(AppCache::class, 'defaultTTL')->getValue($pkCache)
        );
    }

    public function testExceptionCodes(): void
    {
        $mockHttp = $this->getMockClient([
            new RequestException("Test Exception", new Request('GET', 'test')),
            new RequestException("Test Exception", new Request('GET', 'test')),
        ]);
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);

        try {
            $webFinger->getPublicKey('https://example.com/users/alice');
            $this->fail('Expected FetchException was not thrown');
        } catch (FetchException $e) {
            $this->assertSame(0, $e->getCode());
        }

        try {
            $method = new ReflectionMethod(WebFinger::class, 'getPublicKeyFromActivityPub');
            $method->setAccessible(true);
            $method->invoke($webFinger, 'https://example.com/users/alice');
            $this->fail('Expected FetchException was not thrown');
        } catch (FetchException $e) {
            $this->assertSame(0, $e->getCode());
        }
    }

    /**
     * @throws CertaintyException
     * @throws DependencyException
     * @throws Exception
     * @throws GuzzleException
     * @throws NetworkException
     * @throws SodiumException
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
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);

        // 3. Call canonicalize twice
        $result1 = $webFinger->canonicalize('alice@example.com');
        $result2 = $webFinger->canonicalize('alice@example.com');

        // 4. Assert the result is correct and the mock expectation is met
        $this->assertSame('https://example.com/users/alice', $result1);
        $this->assertSame('https://example.com/users/alice', $result2);
    }

    public static function trimUsernameProvider(): array
    {
        return [
            ['alice', 'alice'],
            ['     alice/in/chains/    ', 'aliceinchains'],
        ];
    }

    #[DataProvider("trimUsernameProvider")]
    public function testTrimUsername(string $input, string $expected): void
    {
        $wf = new WebFinger();
        $this->assertSame($expected, $wf->trimUsername($input));
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws FetchException
     * @throws NotImplementedException
     * @throws SodiumException
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
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $fetchedPk = $webFinger->getPublicKey('https://example.com/users/alice');
        $this->assertSame($pk->toString(), $fetchedPk->toString());
    }

    public function testGetPublicKeyNoSelf(): void
    {
        $sk = SecretKey::generate();
        $pk = $sk->getPublicKey();

        $mockHttp = $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'links' => [
                    [
                        'type' => 'application/json',
                        'href' => 'https://example.com/users/alicenoself'
                    ],
                    [
                        'rel' => 'self',
                        'type' => 'text/html',
                        'href' => 'https://soatok.blog',
                    ]
                ]
            ]))
        ]);
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $webFinger->clearCaches();
        $this->expectException(FetchException::class);
        $this->expectExceptionMessage('No valid self href found for https://example.com/users/alicenoself');
        $webFinger->getPublicKey('https://example.com/users/alicenoself');
    }

    public function testGetPublicKeyEmpty(): void
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
                ]
            ]))
        ]);
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $this->expectException(FetchException::class);
        $this->expectExceptionMessage('Could not find public key for https://example.com/users/alice');
        $webFinger->getPublicKey('https://example.com/users/alice');
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws FetchException
     * @throws InvalidArgumentException
     * @throws SodiumException
     */
    public function testGetPublicKeyInvalidBecauseRsa(): void
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
            new Response(200, ['Content-Type' => 'application/activity+json'], json_encode([
                'assertionMethod' => [
                    [
                        'publicKeyMultibase' =>
                            "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyaTgTt53ph3p5GHgwoGW\nwz5hRfWXSQA08NCOwe0FEgALWos9GCjNFCd723nCHxBtN1qd74MSh/uN88JPIbwx\nKheDp4kxo4YMN5trPaF0e9G6Bj1N02HnanxFLW+gmLbgYO/SZYfWF/M8yLBcu5Y1\nOt0ZxDDDXS9wIQTtBE0ne3YbxgZJAZTU5XqyQ1DxdzYyC5lF6yBaR5UQtCYTnXAA\npVRuUI2Sd6L1E2vl9bSBumZ5IpNxkRnAwIMjeTJB/0AIELh0mE5vwdihOCbdV6al\nUyhKC1+1w/FW6HWcp/JG1kKC8DPIidZ78Bbqv9YFzkAbNni5eSBOsXVBKG78Zsc8\nowIDAQAB\n-----END PUBLIC KEY-----"
                    ]
                ]
            ]))
        ]);
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $this->expectException(FetchException::class);
        $this->expectExceptionMessage('Could not find public key for https://example.com/users/alice');
        $webFinger->getPublicKey('https://example.com/users/alice');
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
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
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $fetchedPk = $webFinger->getPublicKey('https://example.com/users/alice');
        $this->assertSame($pk->toString(), $fetchedPk->toString());
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws FetchException
     * @throws SodiumException
     */
    public function testWebfingerNetworkError(): void
    {
        $mockHttp = $this->getMockClient([
            new Response(500)
        ]);
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $this->expectException(FetchException::class);
        $webFinger->getPublicKey('https://example.com/users/alice');
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws FetchException
     * @throws SodiumException
     */
    public function testWebfingerInvalidJson(): void
    {
        $mockHttp = $this->getMockClient([
            new Response(200, ['Content-Type' => 'application/json'], 'not json')
        ]);
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $this->expectException(FetchException::class);
        $webFinger->getPublicKey('https://example.com/users/alice');
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
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
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $this->expectException(FetchException::class);
        $webFinger->getPublicKey('https://example.com/users/alice');
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
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
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $this->expectException(FetchException::class);
        $webFinger->getPublicKey('https://example.com/users/alice');
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
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
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
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
        $config = $this->getConfig();
        $http = $config->getGuzzle();
        $webFinger = new WebFinger($config, $http);
        $inboxUrl = $webFinger->getInboxUrl($in);
        $this->assertSame($expect, $inboxUrl);
    }

    /**
     * @throws CertaintyException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws NetworkException
     * @throws SodiumException
     */
    public function testFetch(): void
    {
        $jsonStr = json_encode(['links' => [['rel' => 'self', 'type' => 'application/activity+json', 'href' => 'https://example.com/users/alice']]]);
        $mockHttp = $this->getMockClient([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                $jsonStr
            ),
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                $jsonStr
            ),
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                $jsonStr
            ),
            new Response(
                404,
                ['Content-Type' => 'application/json'],
                '',
            )
        ]);
        $webFinger = new WebFinger($this->getConfig(), $mockHttp, $this->getConfig()->getCaCertFetch());
        $res1 = $webFinger->fetch('alice@example.com');
        $res2 = $webFinger->fetch('@alice@example.com');
        $res3 = $webFinger->fetch('@@alice@example.com');
        $this->assertSame($res1['links']['href'], $res2['links']['href']);
        $this->assertSame($res2['links']['href'], $res3['links']['href']);
        $this->assertSame($res3['links']['href'], $res1['links']['href']);

        // The fourth response we loaded up should throw an exception:
        $this->expectException(ClientException::class);
        $webFinger->fetch('@@alice@example.com');
    }

    /**
     * @throws CertaintyException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws NetworkException
     * @throws SodiumException
     */
    public function testLeadingAtSymbol(): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{"subject":"https://example.com/users/alice","links":[{"rel":"self","href":"https://example.com/users/alice"}]}'
            )
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $mockHttp = new Client(['handler' => $handlerStack]);

        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $result = $webFinger->canonicalize('@alice@example.com');
        $this->assertSame('https://example.com/users/alice', $result);
        $this->assertCount(1, $container);
        $this->assertSame(
            'https://example.com/.well-known/webfinger?resource=acct:alice@example.com',
            (string) $container[0]['request']->getUri()
        );
    }

    /**
     * @throws CacheException
     * @throws CertaintyException
     * @throws DependencyException
     * @throws Exception
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws NetworkException
     * @throws SodiumException
     */
    public function testAcceptHeader(): void
    {
        $mockHttp = $this->createMock(Client::class);
        $mockHttp->expects($this->once())
            ->method('get')
            ->with($this->anything(), $this->callback(function ($options) {
                $this->assertArrayHasKey('Accept', $options);
                $this->assertSame('application/activity+json', $options['Accept']);
                return true;
            }))
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{"inbox":"https://example.com/users/alice/inbox"}'
            ));

        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $webFinger->setCanonicalForTesting('https://example.com/users/alice', 'https://example.com/users/alice');
        $webFinger->getInboxUrl('https://example.com/users/alice');
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws FetchException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    public function testInvalidLinkData(): void
    {
        $sk = SecretKey::generate();
        $pk = $sk->getPublicKey();
        $mockHttp = $this->getMockClient([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'links' => [
                        ['rel' => 'self', 'type' => 'application/json', 'href' => 'https://example.com/users/alice']
                    ]
                ])
            ),
            new Response(200, ['Content-Type' => 'application/activity+json'], json_encode([
                'publicKey' => [
                    'publicKeyPem' => $pk->encodePem()
                ]
            ]))
        ]);
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $webFinger->clearCaches();
        $this->expectException(FetchException::class);
        $this->expectExceptionMessage('No valid self href found for https://example.com/users/alice');
        $webFinger->getPublicKey('https://example.com/users/alice');
    }

    /**
     * @throws CacheException
     * @throws CertaintyException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws NetworkException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    public function testMixedValidAndInvalid(): void
    {
        $sk = SecretKey::generate();
        $pk = $sk->getPublicKey();
        $mockHttp = $this->getMockClient([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'inbox' => 'https://example.com/users/alice/inbox',
                    'links' => [
                        // invalid type
                        ['rel' => 'self', 'type' => 'application/json', 'href' => 'https://example.com/users/alice'],
                        // invalid rel
                        ['rel' => 'other', 'type' => 'application/activity+json', 'href' => 'https://example.com/users/alice'],
                        // valid:
                        ['rel' => 'self', 'type' => 'application/activity+json', 'href' => 'https://example.com/users/alice'],
                    ]
                ])
            ),
            new Response(200, ['Content-Type' => 'application/activity+json'], json_encode([
                'publicKey' => [
                    'publicKeyPem' => $pk->encodePem()
                ]
            ]))
        ]);
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $webFinger->clearCaches();
        $webFinger->setCanonicalForTesting('https://example.com/users/alice', 'https://example.com/users/alice');
        $got = $webFinger->getPublicKey('https://example.com/users/alice');
        $this->assertSame($pk->toString(), $got->toString());
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws FetchException
     * @throws SodiumException
     */
    public function testLogicalOrMutant(): void
    {
        $mockHttp = $this->getMockClient([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'links' => [
                        [
                            'rel' => 'self',
                            'type' => 'application/activity+json',
                            'href' => 'https://example.com/users/alice'
                        ]
                    ]
                ])
            ),
            new Response(
                200,
                ['Content-Type' => 'application/activity+json'],
                '{"publicKey":{}}'
            )
        ]);
        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $this->expectException(FetchException::class);
        $this->expectExceptionMessage('Could not find public key for https://example.com/users/alice');
        $webFinger->getPublicKey('https://example.com/users/alice');
    }

    /**
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws FetchException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    public function testGetPublicKeyWithSlashesInPath(): void
    {
        $sk = SecretKey::generate();
        $pk = $sk->getPublicKey();

        // The path will be /users/alice
        $actorUrl = 'https://example.com/users/alice';

        $mockHttp = $this->getMockClient([
            // First call to WebFinger (triggered by getPublicKey)
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'links' => [
                    [
                        'rel' => 'self',
                        'type' => 'application/activity+json',
                        'href' => $actorUrl
                    ]
                ]
            ])),
            // Second call to ActivityPub actor
            new Response(200, ['Content-Type' => 'application/activity+json'], json_encode([
                'publicKey' => [
                    'publicKeyPem' => $pk->encodePem()
                ]
            ]))
        ]);

        $webFinger = new WebFinger($this->getConfig(), $mockHttp);
        $fetchedPk = $webFinger->getPublicKey($actorUrl);

        $this->assertSame($pk->toString(), $fetchedPk->toString());
    }
}
