<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Interfaces;

use FediE2EE\PKDServer\Exceptions\RateLimitException;
use FediE2EE\PKDServer\RateLimit\RateLimitData;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RateLimitInterface
{
    public function getStorage(): RateLimitStorageInterface;
    public function isEnabled(): bool;
    public function getBaseDelay(): int;

    /**
     * @throws RateLimitException
     */
    public function enforceRateLimit(
        ServerRequestInterface $request,
        RequestHandlerInterface & LimitingHandlerInterface $handler
    ): void;
    public function shouldEnforce(string $type): bool;
    public function recordPenalty(string $type, string $lookup): void;
    public function getCooledDown(RateLimitData $data): RateLimitData;

    public function getRequestActor(ServerRequestInterface $request): ?string;
    public function getRequestDomain(ServerRequestInterface $request): ?string;
    public function getRequestSubnet(ServerRequestInterface $request): string;
}
