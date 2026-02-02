<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Meta;

use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKD\Crypto\Merkle\Tree;

use function filter_var;

/**
 * Server configuration parameters
 */
readonly class Params
{
    /**
     * These parameters MUST be public and MUST have a default value
     *
     * @throws DependencyException
     */
    public function __construct(
        public string $hashAlgo = 'sha256',
        public int $otpMaxLife = 120,
        public string $actorUsername = 'pubkeydir',
        public string $hostname = 'localhost',
        public string $cacheKey = '',
        public int $httpCacheTtl = 60,
    ) {
        if (!Tree::isHashFunctionAllowed($this->hashAlgo)) {
            throw new DependencyException('Disallowed hash algorithm');
        }
        if ($this->otpMaxLife < 2) {
            throw new DependencyException('OTP max life cannot be less than 2 seconds');
        }
        if ($this->otpMaxLife > 300) {
            throw new DependencyException('OTP max life cannot be larger than 300 seconds');
        }
        if ($this->httpCacheTtl < 1) {
            throw new DependencyException('HTTP cache TTL cannot be less than 1 second');
        }
        if ($this->httpCacheTtl > 300) {
            throw new DependencyException('HTTP cache TTL cannot be greater than 300 seconds');
        }
        if (!filter_var($this->hostname, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new DependencyException('Hostname is not valid');
        }
    }

    public function getActorUsername(): string
    {
        return $this->actorUsername;
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    public function getHashFunction(): string
    {
        return $this->hashAlgo;
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function getHttpCacheTtl(): int
    {
        return $this->httpCacheTtl;
    }

    public function getOtpMaxLife(): int
    {
        return $this->otpMaxLife;
    }

    public function getEmptyTreeRoot(): string
    {
        return (new Tree([], $this->hashAlgo))->getEncodedRoot();
    }
}
