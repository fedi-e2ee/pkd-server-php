<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Traits;

use FediE2EE\PKDServer\Exceptions\CacheException;
use GuzzleHttp\Exception\GuzzleException;
use FediE2EE\PKD\Crypto\Exceptions\{
    NetworkException,
    NotImplementedException
};
use FediE2EE\PKD\Crypto\HttpSignature;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use JsonException as BaseJsonException;
use Psr\SimpleCache\InvalidArgumentException;
use Laminas\Diactoros\{
    Response,
    Stream
};
use ParagonIE\Certainty\Exception\CertaintyException;
use Psr\Http\Message\ResponseInterface;
use SodiumException;
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};
use TypeError;

use function array_key_exists;
use function time;

/**
 * Request Handler trait
 */
trait ReqTrait
{
    use ConfigTrait;

    public function time(): string
    {
        return (string) time();
    }

    /**
     * @throws CacheException
     * @throws CertaintyException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws NetworkException
     * @throws SodiumException
     */
    public function canonicalizeActor(string $actor): string
    {
        return $this->appCache('activitypub:actor')->cache(
            sodium_bin2hex($actor),
            function () use ($actor) {
                return $this->webfinger()->canonicalize($actor);
            }
        );
    }

    /**
     * @throws BaseJsonException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    public function error(string $message, int $code = 400): ResponseInterface
    {
        return $this->json(['error' => $message], $code);
    }

    /**
     * Implements an RFC 9421 HTTP Message Signature with Ed25519.
     *
     * @link https://www.rfc-editor.org/rfc/rfc9421.html#name-eddsa-using-curve-edwards25
     *
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    public function signResponse(ResponseInterface $response): ResponseInterface
    {
        $signer = new HttpSignature();
        $response = $signer->sign(
            $this->config()->getSigningKeys()->secretKey,
            $response
        );
        if (!($response instanceof ResponseInterface)) {
            throw new TypeError('PKD Crypto did not return a response');
        }
        return $response;
    }

    /**
     * Return a JSON response with HTTP Message Signature (from signResponse())
     *
     * @param array<string, mixed>|object $data
     * @param array<string, string> $headers
     * @throws DependencyException
     * @throws BaseJsonException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    public function json(
        array|object $data,
        int $status = 200,
        array $headers = []
    ): ResponseInterface {
        if (!array_key_exists('Content-Type', $headers)) {
            $headers['Content-Type'] = 'application/json';
        }
        $json = self::jsoNEncode($data);
        $stream = new Stream('php://temp', 'wb');
        $stream->write($json);
        $stream->rewind();
        return $this->signResponse(
            new Response(
                $stream,
                $status,
                $headers
            )
        );
    }

    /**
     * @param array<string, mixed> $vars
     * @param array<string, string> $headers
     * @throws DependencyException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function twig(
        string $template,
        array $vars = [],
        array $headers = [],
        int $status = 200
    ): ResponseInterface {
        if (!array_key_exists('Content-Type', $headers)) {
            $headers['Content-Type'] = 'text/html; charset=UTF-8';
        }
        $stream = new Stream('php://temp', 'wb');
        $stream->write($this->config()->getTwig()->render($template, $vars));
        $stream->rewind();
        return new Response(
            $stream,
            $status,
            $headers
        );
    }
}
