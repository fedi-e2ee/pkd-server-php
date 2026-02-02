<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer;

use Laminas\Diactoros\{
    Response,
    Stream
};
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Abstracts an HTTP Redirect
 */
class Redirect
{
    public function __construct(
        protected UriInterface|string $url,
        protected string $message = '',
        protected int $status = 301
    ) {}

    public function respond(): ResponseInterface
    {
        $stream = new Stream('php://temp', 'wb');
        $stream->write($this->message);
        $stream->rewind();

        return new Response(
            $stream,
            $this->status,
            ['Location' => (string) $this->url]
        );
    }
}
