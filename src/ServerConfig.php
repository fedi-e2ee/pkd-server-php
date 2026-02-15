<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer;

use FediE2EE\PKD\Extensions\Registry;
use FediE2EE\PKDServer\Meta\Params;
use GuzzleHttp\Client;
use FediE2EE\PKDServer\Dependency\{
    HPKE,
    SigningKeys
};
use FediE2EE\PKDServer\Interfaces\RateLimitInterface;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use League\Route\Router;
use Monolog\Logger;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\Certainty\Fetch;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\EasyDB\EasyDB;
use Predis\Client as RedisClient;
use SodiumException;
use Twig\Environment;
use Throwable;
use function is_null;

class ServerConfig
{
    /** @var array<int, string> */
    private array $auxDataTypeAllowList = [];
    private ?Registry $auxDataRegistry = null;
    private ?CipherSweet $ciphersweet = null;
    private ?EasyDB $db = null;
    private ?Fetch $caCertFetch = null;
    private ?HPKE $hpke = null;
    private ?RedisClient $redis = null;
    private ?Logger $logger = null;
    private ?RateLimitInterface $rateLimit = null;
    private ?Router $router = null;
    private ?SigningKeys $signingKeys = null;
    private ?Environment $twig = null;

    public function __construct(private readonly Params $params) {}

    /**
     * @throws DependencyException
     */
    public function getCaCertFetch(): Fetch
    {
        if (is_null($this->caCertFetch)) {
            throw new DependencyException('caCertFetch is not injected');
        }
        return $this->caCertFetch;
    }

    /**
     * @return array<int, string>
     * @api
     */
    public function getAuxDataTypeAllowList(): array
    {
        return $this->auxDataTypeAllowList;
    }

    public function getAuxDataRegistry(): Registry
    {
        if (is_null($this->auxDataRegistry)) {
            throw new DependencyException('registry is not injected');
        }
        return $this->auxDataRegistry;
    }

    /**
     * @throws CertaintyException
     * @throws DependencyException
     * @throws SodiumException
     */
    public function getGuzzle(): Client
    {
        return new Client([
            'verify' => $this->getCaCertFetch()->getLatestBundle()->getFilePath()
        ]);
    }

    /**
     * @throws DependencyException
     */
    public function getCipherSweet(): CipherSweet
    {
        if (is_null($this->ciphersweet)) {
            throw new DependencyException('ciphersweet is not injected');
        }
        return $this->ciphersweet;
    }

    /**
     * @throws DependencyException
     * @api
     */
    public function getDb(): EasyDB
    {
        if (is_null($this->db)) {
            throw new DependencyException('db not injected');
        }
        return $this->db;
    }

    /**
     * @throws DependencyException
     * @api
     */
    public function getHPKE(): HPKE
    {
        if (is_null($this->hpke)) {
            throw new DependencyException('hpke not injected');
        }
        return $this->hpke;
    }

    public function getLogger(): Logger
    {
        if (is_null($this->logger)) {
            throw new DependencyException('logger not injected');
        }
        return $this->logger;
    }

    public function getParams(): Params
    {
        return $this->params;
    }

    /**
     * @throws DependencyException
     * @api
     */
    public function getSigningKeys(): SigningKeys
    {
        if (is_null($this->signingKeys)) {
            throw new DependencyException('signing keys not injected');
        }
        return $this->signingKeys;
    }

    /**
     * @throws DependencyException
     * @api
     */
    public function getRateLimit(): RateLimitInterface
    {
        if (is_null($this->rateLimit)) {
            throw new DependencyException('rate-limiting logic not injected');
        }
        return $this->rateLimit;
    }

    /**
     * @throws DependencyException
     * @api
     */
    public function getRouter(): Router
    {
        if (is_null($this->router)) {
            throw new DependencyException('router not injected');
        }
        return $this->router;
    }

    /**
     * @throws DependencyException
     * @api
     */
    public function getTwig(): Environment
    {
        if (is_null($this->twig)) {
            throw new DependencyException('twig not injected');
        }
        return $this->twig;
    }

    public function getRedis(): ?RedisClient
    {
        return $this->redis;
    }

    public function hasRedis(): bool
    {
        return !is_null($this->redis);
    }

    /**
     * @param string[] $allowList
     * @return static
     */
    public function withAuxDataTypeAllowList(array $allowList = []): static
    {
        $this->auxDataTypeAllowList = $allowList;
        return $this;
    }

    public function withAuxDataRegistry(Registry $registry): static
    {
        $this->auxDataRegistry = $registry;
        return $this;
    }

    public function withCACertFetch(Fetch $fetch): static
    {
        $this->caCertFetch = $fetch;
        return $this;
    }

    public function withCipherSweet(CipherSweet $ciphersweet): static
    {
        $this->ciphersweet = $ciphersweet;
        return $this;
    }

    public function withDatabase(EasyDB $db): static
    {
        $this->db = $db;
        return $this;
    }

    public function withHPKE(HPKE $hpke): static
    {
        $this->hpke = $hpke;
        return $this;
    }

    public function withLogger(Logger $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function withOptionalRedisClient(?RedisClient $redis = null): static
    {
        if (!is_null($redis)) {
            try {
                $redis->connect();
            } catch (Throwable) {
                // If we cannot connect, fallback to in-memory caching
                $redis = null;
            }
        }
        $this->redis = $redis;
        return $this;
    }

    public function withRateLimit(RateLimitInterface $rateLimit): static
    {
        $this->rateLimit = $rateLimit;
        return $this;
    }

    public function withRouter(Router $router): static
    {
        $this->router = $router;
        return $this;
    }

    public function withSigningKeys(SigningKeys $signingKeys): static
    {
        $this->signingKeys = $signingKeys;
        return $this;
    }

    public function withTwig(Environment $twig): static
    {
        $this->twig = $twig;
        return $this;
    }
}
