<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\ActivityPub;

use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\RequestHandlers\ActivityPub\Finger;
use FediE2EE\PKDServer\ServerConfig;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Finger::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(ServerConfig::class)]
class FingerTest extends TestCase
{
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
        $handler->injectConfig($GLOBALS['config']);
        $response = $handler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents());
        foreach ($expected['aliases'] as $alias) {
            $this->assertTrue(in_array($alias, $body->aliases, true));
        }
    }
}
