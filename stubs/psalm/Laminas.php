<?php
declare(strict_types=1);

namespace Laminas\Diactoros;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Psalm stub for Laminas\Diactoros\Response
 * Relaxes the header type to accept array<string, string>
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Response implements ResponseInterface
{
    /**
     * @param string|resource|StreamInterface $body
     * @param int $status
     * @param array<string, string> $headers
     */
    public function __construct(
        $body = 'php://memory',
        int $status = 200,
        array $headers = []
    ) {}
}
