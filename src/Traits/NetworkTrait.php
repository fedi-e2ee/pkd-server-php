<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Traits;

use FediE2EE\PKDServer\Exceptions\NetTraitException;
use Psr\Http\Message\ServerRequestInterface;
use function array_values;
use function filter_var;
use function in_array;
use function inet_ntop;
use function inet_pton;
use function is_object;
use function is_string;
use function json_decode;
use function min;
use function pack;
use function parse_url;
use function property_exists;
use function trim;
use function unpack;

trait NetworkTrait
{
    /**
     * @param array<int, string> $trustedProxies
     * @throws NetTraitException
     */
    public function getRequestIPSubnet(
        ServerRequestInterface $request,
        array $trustedProxies = [],
        int $ipv4MaskBits = 32,
        int $ipv6MaskBits = 128,
    ): string {
        $ip = $this->extractIPFromRequest($request, $trustedProxies);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $this->ipv4Mask($ip, $ipv4MaskBits);
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $this->ipv6Mask($ip, $ipv6MaskBits);
        }
        return $ip;
    }

    /**
     * @param array<int, string> $trustedProxies
     */
    public function extractIPFromRequest(
        ServerRequestInterface $request,
        array $trustedProxies = []
    ): string {
        $serverParams = $request->getServerParams();
        $remoteAddr = $serverParams['REMOTE_ADDR'] ?? '';

        // If it's a trusted proxy address, we check X-Forwarded-For:
        if (in_array($remoteAddr, $trustedProxies, true)) {
            $header = $request->getHeader('X-Forwarded-For');
            if (empty($header)) {
                return $remoteAddr;
            }
            foreach ($header as $potentialIP) {
                $potentialIP = trim($potentialIP);
                if (filter_var($potentialIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    return $potentialIP;
                }
                if (filter_var($potentialIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    return $potentialIP;
                }
            }
        }
        return $remoteAddr;
    }

    /**
     * @throws NetTraitException
     */
    public function ipv4Mask(string $ip, int $maskBits = 32): string
    {
        if ($maskBits < 0) {
            throw new NetTraitException('MaskBits cannot be negative');
        }
        if ($maskBits > 32) {
            return $ip . '/32';
        }
        $binarystr = inet_pton($ip);
        if (!is_string($binarystr)) {
            throw new NetTraitException('MaskBits must be a positive integer');
        }
        $binary = self::stringToByteArray($binarystr);
        $bitsToZero = 32 - $maskBits;
        $i = 3;
        while ($i >= 0 && $bitsToZero > 0) {
            $bits = min(8, $bitsToZero);
            $binary[$i] &= (0xFF << $bits);
            $bitsToZero -= $bits;
            --$i;
        }
        $normalized = inet_ntop(self::byteArrayToString($binary));
        if (!is_string($normalized)) {
            return $ip;
        }
        return $normalized  . '/' . $maskBits;
    }

    /**
     * @throws NetTraitException
     */
    public function ipv6Mask(string $ip, int $maskBits = 128): string
    {
        if ($maskBits < 0) {
            throw new NetTraitException('MaskBits cannot be negative');
        }
        if ($maskBits > 128) {
            return $ip . '/128';
        }
        $binarystr = inet_pton($ip);
        if (!is_string($binarystr)) {
            throw new NetTraitException('MaskBits must be a positive integer');
        }
        $binary = self::stringToByteArray($binarystr);
        $bitsToZero = 128 - $maskBits;
        $i = 15;
        while ($i >= 0 && $bitsToZero > 0) {
            $bits = min(8, $bitsToZero);
            $binary[$i] &= (0xFF << $bits);
            $bitsToZero -= $bits;
            --$i;
        }
        $normalized = inet_ntop(self::byteArrayToString($binary));
        if (!is_string($normalized)) {
            return $ip;
        }
        return $normalized  . '/' . $maskBits;
    }

    /**
     * @return array<int, int>
     */
    public function stringToByteArray(string $str): array
    {
        $values = unpack('C*', $str);
        if ($values === false) {
            return [];
        }
        return array_values($values);
    }

    /**
     * @param array<int, int> $array
     */
    public function byteArrayToString(array $array): string
    {
        return pack('C*', ...$array);
    }

    public function getRequestActor(ServerRequestInterface $request): ?string
    {
        $body = $request->getBody()->getContents();
        if (empty($body)) {
            return null;
        }
        $request->getBody()->rewind();

        $decoded = json_decode($body);
        if (!is_object($decoded) || !property_exists($decoded, 'actor')) {
            return null;
        }

        $actorID = $decoded->actor;
        if (!is_string($actorID)) {
            return null;
        }

        $parsed = parse_url($actorID);
        return $parsed['host'] ?? null;
    }

    public function getRequestDomain(ServerRequestInterface $request): ?string
    {
        $actorID = $this->getRequestActor($request);
        if (!is_string($actorID)) {
            return null;
        }
        $parsed = parse_url($actorID);
        return $parsed['host'] ?? null;
    }
}
