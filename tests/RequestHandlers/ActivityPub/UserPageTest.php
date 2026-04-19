<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\ActivityPub;

use FediE2EE\PKD\Crypto\Exceptions\CryptoException;
use FediE2EE\PKD\Crypto\PublicKey;
use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\AppCache;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\RequestHandlers\ActivityPub\UserPage;
use FediE2EE\PKDServer\ServerConfig;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionException;

#[UsesClass(ServerConfig::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(WebFinger::class)]
#[CoversClass(UserPage::class)]
class UserPageTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    /**
     * @throws CryptoException
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testReturnsActorDocumentForConfiguredUser(): void
    {
        $config = $this->getConfig();
        $handler = $this->instantiateHandler(UserPage::class, $config);
        $params = $config->getParams();

        $request = $this->makeGetRequest('/users/' . $params->actorUsername)
            ->withAttribute('user_id', $params->actorUsername);

        $response = $handler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            'application/activity+json',
            $response->getHeaderLine('Content-Type')
        );

        $body = json_decode($response->getBody()->getContents(), true);
        $actorUrl = 'https://' . $params->hostname . '/users/' . $params->actorUsername;

        $this->assertSame($actorUrl, $body['id']);
        $this->assertSame('Service', $body['type']);
        $this->assertSame($params->actorUsername, $body['preferredUsername']);
        $this->assertSame($actorUrl . '/inbox', $body['inbox']);

        $this->assertArrayHasKey('assertionMethod', $body);
        $this->assertCount(1, $body['assertionMethod']);
        $am = $body['assertionMethod'][0];
        $this->assertSame('Multikey', $am['type']);
        $this->assertSame($actorUrl . '#main-key', $am['id']);
        $this->assertSame($actorUrl, $am['controller']);

        $this->assertArrayHasKey('publicKey', $body);
        $this->assertSame($actorUrl . '#main-key', $body['publicKey']['id']);
        $this->assertSame($actorUrl, $body['publicKey']['owner']);
        $this->assertArrayHasKey('publicKeyPem', $body['publicKey']);

        $configured = $config->getSigningKeys()->publicKey;
        $fromPem = PublicKey::importPem($body['publicKey']['publicKeyPem'], $configured->getAlgo());
        $this->assertSame($configured->toString(), $fromPem->toString());
    }

    /**
     * @throws ReflectionException
     */
    public function testReturns404ForUnknownUser(): void
    {
        $config = $this->getConfig();
        $handler = $this->instantiateHandler(UserPage::class, $config);

        $request = $this
            ->makeGetRequest('/users/not-the-configured-user')
            ->withAttribute('user_id', 'not-the-configured-user');

        $response = $handler->handle($request);
        $this->assertSame(404, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);

        $this->assertArrayNotHasKey('publicKey', $body);
        $this->assertArrayNotHasKey('assertionMethod', $body);
    }
}
