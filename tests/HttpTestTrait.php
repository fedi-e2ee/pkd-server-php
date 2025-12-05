<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests;

use FediE2EE\PKDServer\ActivityPub\WebFinger;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\ServerConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Helper methods for writing unit tests with HTTP messages
 *
 * @method fail(string $message = ''): never
 */
trait HttpTestTrait
{
    public function getConfig(): ServerConfig
    {
        if (!($GLOBALS['pkdConfig'] instanceof ServerConfig)) {
            $this->fail('Server config not injected');
        }
        return $GLOBALS['pkdConfig'];
    }

    public function getMockClient(array $responses = []): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }

    public function makeDummyActor(string $domain = 'example.com'): array
    {
        $username = 'test';
        $alphabet = 'abcdefghijklmnopqrstuvwxyz';
        for ($i = 0; $i < 24; ++$i) {
            $r = random_int(0, 25);
            $username .= $alphabet[$r];
        }
        $input = $username . '@' . $domain;
        $canon = 'https://' . $domain . '/users/' . $username;
        WebFinger::setCanonicalForTesting($input, $canon);
        return [$input, $canon];
    }

    public function makeGetRequest(
        string $uri,
        array $headers = []
    ): ServerRequest {
        return new ServerRequest('GET', $uri, $headers);
    }

    public function makePostRequest(
        string $uri,
        string|array|object $body = '',
        array $headers = []
    ): ServerRequest {
        if (!is_string($body)) {
            $body = json_encode(
                $body,
                JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
        }
        return new ServerRequest('POST', $uri, $headers, $body);
    }

    /**
     * @throws DependencyException
     */
    public function dispatchRequest(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getConfig()->getRouter()->dispatch($request);
    }
}
