<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Meta;

/**
 * Server configuration parameters
 */
readonly class Params
{
    /**
     * These parameters MUST be public and MUST have a default value
     */
    public function __construct(
        public string $hashAlgo = 'sha256',
        public int $otpMaxLife = 120,
        public string $actorUsername = 'pubkeydir',
        public string $hostname = 'localhost',
    ) {}
}
