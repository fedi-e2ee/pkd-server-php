<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\ActivityPub;

use FediE2EE\PKDServer\ActivityPub\ActivityStream;
use FediE2EE\PKDServer\Exceptions\ActivityPubException;
use JsonException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class ActivityStreamTest extends TestCase
{
    public static function decodedProvider(): array
    {
        return [
            ['{"@context":"https://www.w3.org/ns/activitystreams","id":"https://mastodon.social/users/alice/statuses/123456/activity","type":"Create","actor":"https://mastodon.social/users/alice","object":{}}'],
            ['{"@context":"https://www.w3.org/ns/activitystreams","id":"https://mastodon.social/users/alice/statuses/123457/activity","type":"Create","actor":"https://mastodon.social/users/alice","object":{"type":"Note","content":"<p>Hello!</p>","to":["https://mastodon.social/users/alice"]}}'],
        ];
    }

    /**
     * @throws ActivityPubException
     * @throws JsonException
     */
    #[DataProvider("decodedProvider")]
    public function testFromDecoded(string $json): void
    {
        $decoded = json_decode($json);
        $this->assertIsObject($decoded);
        $as = ActivityStream::fromDecoded($decoded);
        $this->assertObjectHasProperty('internalContext', $as);
        $this->assertObjectHasProperty('type', $as);
        $this->assertObjectHasProperty('actor', $as);
        $encoded = json_encode($as, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $this->assertIsString($encoded);
        $this->assertSame($json, $encoded);
        $encoded2 = (string) $as;
        $this->assertSame($encoded2, $encoded);
    }

    public static function directMessageProvider(): array
    {
        return [
            ['{"@context":"https://www.w3.org/ns/activitystreams","type":"Create","actor":"https://mastodon.social/users/alice","object":{"type":"Note","content":"<p>Hello!</p>","to":["https://mastodon.social/users/alice"]},"id":"https://mastodon.social/users/alice/statuses/123458/activity"}', true],
            ['{"@context":"https://www.w3.org/ns/activitystreams","type":"Create","actor":"https://mastodon.social/users/alice","object":{"type":"Note","content":"<p>Hello!</p>","to":["https://mastodon.social/users/alice"]}}', false],
            ['{"@context":"https://www.w3.org/ns/activitystreams","type":"Create","actor":"https://mastodon.social/users/alice","object":{"type":"Note","content":"<p>Hello!</p>","to":["https://www.w3.org/ns/activitystreams#Public","https://mastodon.social/users/alice"]},"id":"https://mastodon.social/users/alice/statuses/123459/activity"}', false],
        ];
    }

    /**
     * @throws ActivityPubException
     */
    #[DataProvider("directMessageProvider")]
    public function testIsDirectMessage(string $json, bool $expected): void
    {
        $decoded = json_decode($json);
        $this->assertIsObject($decoded);
        $as = ActivityStream::fromDecoded($decoded);
        $isDM = $as->isDirectMessage();
        $this->assertSame($expected, $isDM);
    }
}
