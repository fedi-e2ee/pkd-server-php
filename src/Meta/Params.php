<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Meta;

use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKD\Crypto\Merkle\Tree;

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
        if (!filter_var($this->hostname, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new DependencyException('Hostname is not valid');
        }
    }
}
