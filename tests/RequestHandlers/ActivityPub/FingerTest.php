<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\ActivityPub;

use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\AppCache;
use FediE2EE\PKDServer\RequestHandlers\ActivityPub\Finger;
use FediE2EE\PKDServer\ServerConfig;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Finger::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(ServerConfig::class)]
class FingerTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    public static function fingerProvider(): array
    {
        return [
            ['soatok@furry.engineer', ['aliases' => ['https://furry.engineer/@soatok']]],
            ['fedie2ee@mastodon.social', ['aliases' => ['https://mastodon.social/ap/users/115428847654719749']]],
            ['pubkeydir@localhost', ['aliases' => ['https://localhost/users/pubkeydir']]],
        ];
    }

    #[DataProvider("fingerProvider")]
    public function testKnownAnswers(string $actor, array $expected): void
    {
        $request = $this->makeGetRequest('/.well-known/finger')->withQueryParams([
            'resource' => 'acct:' . $actor,
        ]);

        $handler = new Finger();
        $handler->injectConfig($GLOBALS['pkdConfig']);
        $response = $handler->handle($request);
        $this->assertNotInTransaction();
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents());
        foreach ($expected['aliases'] as $alias) {
            $this->assertTrue(in_array($alias, $body->aliases, true));
        }
    }

    /**
     * Test the full response structure for the local actor.
     */
    public function testLocalActorFullResponse(): void
    {
        $config = $GLOBALS['pkdConfig'];
        $params = $config->getParams();
        $user = $params->actorUsername;
        $domain = $params->hostname;

        $request = $this->makeGetRequest('/.well-known/webfinger')->withQueryParams([
            'resource' => "acct:{$user}@{$domain}",
        ]);

        $handler = new Finger();
        $handler->injectConfig($config);
        $response = $handler->handle($request);
        $this->assertNotInTransaction();
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);

        // Verify complete response structure
        $this->assertSame("acct:{$user}@{$domain}", $body['subject']);
        $this->assertArrayHasKey('aliases', $body);
        $this->assertIsArray($body['aliases']);
        $this->assertNotEmpty($body['aliases']);
        $this->assertArrayHasKey('links', $body);
        $this->assertIsArray($body['links']);
        $this->assertNotEmpty($body['links']);

        // Verify link structure
        $link = $body['links'][0];
        $this->assertSame('self', $link['rel']);
        $this->assertSame('application/activity+json', $link['type']);
        $this->assertArrayHasKey('href', $link);
    }

    /**
     * Test that missing resource parameter returns 400 error.
     */
    public function testMissingResource(): void
    {
        $request = $this->makeGetRequest('/.well-known/webfinger');
        // No resource query param

        $handler = new Finger();
        $handler->injectConfig($GLOBALS['pkdConfig']);
        $response = $handler->handle($request);
        $this->assertNotInTransaction();
        $this->assertSame(400, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('resource', strtolower($body['error']));
    }

    /**
     * Test that invalid resource format returns 400 error.
     */
    public function testInvalidResourceFormat(): void
    {
        $request = $this->makeGetRequest('/.well-known/webfinger')->withQueryParams([
            'resource' => 'invalid-format-no-acct',
        ]);

        $handler = new Finger();
        $handler->injectConfig($GLOBALS['pkdConfig']);
        $response = $handler->handle($request);
        $this->assertNotInTransaction();
        $this->assertSame(400, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('format', strtolower($body['error']));
    }

    /**
     * Test that unknown user returns 404 error.
     */
    public function testUnknownUser(): void
    {
        $config = $GLOBALS['pkdConfig'];
        $params = $config->getParams();
        $domain = $params->hostname;

        $request = $this->makeGetRequest('/.well-known/webfinger')->withQueryParams([
            'resource' => "acct:nonexistent-user-12345@{$domain}",
        ]);

        $handler = new Finger();
        $handler->injectConfig($config);
        $response = $handler->handle($request);
        $this->assertNotInTransaction();
        $this->assertSame(404, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
    }
}
