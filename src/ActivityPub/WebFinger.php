<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\ActivityPub;

use FediE2EE\PKD\Crypto\{
    Exceptions\CryptoException,
    Exceptions\NetworkException,
    PublicKey
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    FetchException
};
use FediE2EE\PKDServer\{
    AppCache,
    ServerConfig
};
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use ParagonIE\Certainty\{
    Exception\CertaintyException,
    Fetch
};
use Psr\SimpleCache\InvalidArgumentException;
use SodiumException;

class WebFinger
{
    protected AppCache $canonicalCache;
    protected AppCache $inboxCache;
    protected AppCache $pkCache;

    protected Client $http;
    protected Fetch $fetch;

    /**
     * @throws CertaintyException
     * @throws DependencyException
     * @throws SodiumException
     */
    public function __construct(?ServerConfig $config = null, ?Client $client = null, ?Fetch $fetch = null)
    {
        if (is_null($config)) {
            $config = $GLOBALS['pkdConfig'];
            if (!($config instanceof ServerConfig)) {
                throw new DependencyException('config not injected');
            }
        }
        $this->canonicalCache = new AppCache($config, 'webfinger-canonical', 3600);
        $this->inboxCache = new AppCache($config, 'webfinger-inbox', 60);
        $this->pkCache = new AppCache($config, 'webfinger-public-key', 60);
        if (is_null($fetch)) {
            $fetch = $config->getCaCertFetch();
        }
        $this->fetch = $fetch;
        if (is_null($client)) {
            $client = new Client(['verify' => $this->fetch->getLatestBundle()]);
        }
        $this->http = $client;
    }

    /**
     * @api
     */
    public function clearCaches(): void
    {
        if (!$this->canonicalCache->clear()) {
            throw new CacheException();
        }
        $this->inboxCache->clear();
        $this->pkCache->clear();
    }

    /**
     * @throws CacheException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws NetworkException
     * @throws SodiumException
     */
    public function canonicalize(string $actorUsernameOrUrl): string
    {
        $result = $this->canonicalCache->cache(
            $actorUsernameOrUrl,
            fn () => $this->lookup($actorUsernameOrUrl)
        );
        if (!$result) {
            throw new CacheException('Could not lookup actor ' . $actorUsernameOrUrl);
        }
        return $result;
    }

    /**
     * @throws NetworkException
     * @throws GuzzleException
     */
    protected function lookup(string $identifier): string
    {
        if (str_starts_with($identifier, 'https://')) {
            return $this->lookupUrl($identifier);
        }
        if (str_starts_with($identifier, '@')) {
            $identifier = substr($identifier, 1);
        }
        return $this->lookupUsername($identifier);
    }

    /**
     * Fetch an entire remote WebFinger response.
     *
     * @throws GuzzleException
     * @throws NetworkException
     */
    public function fetch(string $identifier): array
    {
        [, $host] = explode('@', ltrim($identifier, '@'));
        $url = "https://{$host}/.well-known/webfinger?resource=acct:{$identifier}";
        $response = $this->http->get($url);
        if ($response->getStatusCode() !== 200) {
            throw new NetworkException('Could not connect to ' . $host);
        }
        $jrd = json_decode($response->getBody()->getContents(), true);
        if (!is_array($jrd)) {
            throw new NetworkException('Invalid JSON: ' . json_last_error_msg());
        }
        return $jrd;
    }

    /**
     * @throws GuzzleException
     * @throws NetworkException
     */
    protected function lookupUsername(string $identifier): string
    {
        $jrd = $this->fetch($identifier);
        if (!array_key_exists('links', $jrd)) {
            throw new NetworkException('Could not lookup ' . $identifier);
        }
        foreach ($jrd['links'] as $link) {
            if ($link['rel'] === 'self') {
                return $link['href'];
            }
        }
        throw new NetworkException('Could not lookup ' . $identifier);
    }

    /**
     * @throws GuzzleException
     * @throws NetworkException
     */
    protected function lookupUrl(string $url): string
    {
        $response = $this->http->get($url);
        if ($response->getStatusCode() !== 200) {
            throw new NetworkException('Could not connect to ' . $url);
        }
        $actor = json_decode($response->getBody()->getContents(), true);
        if (!is_array($actor)) {
            throw new NetworkException('Invalid JSON');
        }
        if (empty($actor['id'])) {
            throw new NetworkException('No id found');
        }
        return $actor['id'];
    }

    /**
     * @throws CacheException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws NetworkException
     * @throws SodiumException
     */
    public function getInboxUrl(string $actorUrl): string
    {
        $url = $this->inboxCache->cache($actorUrl, function () use ($actorUrl) {
            $canonicalUrl = $this->canonicalize($actorUrl);
            $raw = $this->http->get(
                $canonicalUrl . '.json',
                ['Accept' => 'application/activity+json']
            );
            $decoded = json_decode($raw->getBody()->getContents());
            if (!is_object($decoded)) {
                throw new NetworkException('Could not decode ' . $canonicalUrl);
            }
            return $decoded->inbox;
        });
        if (!$url) {
            throw new CacheException('Could not retrieve ' . $actorUrl);
        }
        return $url;
    }

    /**
     * @throws CryptoException
     * @throws FetchException
     * @throws InvalidArgumentException
     * @throws SodiumException
     */
    public function getPublicKey(string $actorUrl): PublicKey
    {
        $publicKey = $this->pkCache->cache($actorUrl, function () use ($actorUrl) {
            $parsed = parse_url($actorUrl);
            $host = $parsed['host'];
            $path = $parsed['path'];
            $username = $this->trimUsername($path);

            try {
                $url = "https://{$host}/.well-known/webfinger?resource=acct:{$username}@{$host}";
                $response = $this->http->get($url);
            } catch (GuzzleException $e) {
                throw new FetchException("Could not fetch WebFinger data for {$actorUrl}", 0, $e);
            }
            if ($response->getStatusCode() !== 200) {
                throw new FetchException("Could not fetch WebFinger data for {$actorUrl}");
            }
            $jrd = json_decode($response->getBody()->getContents(), true);
            if (!is_array($jrd)) {
                throw new FetchException("Could not parse WebFinger data for {$actorUrl}");
            }
            foreach ($jrd['links'] as $link) {
                if (!array_key_exists('rel', $link)) {
                    continue;
                }
                if (!array_key_exists('type', $link)) {
                    continue;
                }
                if (!array_key_exists('href', $link)) {
                    continue;
                }
                if ($link['rel'] === 'self' && $link['type'] === 'application/activity+json') {
                    return $this->getPublicKeyFromActivityPub($link['href'])->toString();
                }
            }
            throw new FetchException("No valid self href found for {$actorUrl}");
        });
        if (!$publicKey) {
            throw new FetchException("Could not fetch public key for {$actorUrl}");
        }
        return PublicKey::fromString($publicKey);
    }

    public function trimUsername(string $username): string
    {
        return trim(str_replace('/', '', $username));
    }

    /**
     * @throws CryptoException
     * @throws FetchException
     */
    protected function getPublicKeyFromActivityPub(string $actorUrl): PublicKey
    {
        try {
            $response = $this->http->get($actorUrl);
        } catch (GuzzleException $e) {
            throw new FetchException("Could not fetch ActivityPub data for {$actorUrl}", 0, $e);
        }
        if ($response->getStatusCode() !== 200) {
            throw new FetchException("Could not fetch ActivityPub data for {$actorUrl}");
        }
        $actor = json_decode($response->getBody()->getContents());
        if (!is_object($actor)) {
            throw new FetchException("Could not parse ActivityPub data for {$actorUrl}");
        }

        // Prefer FEP-521a over the incumbent publicKey approach:
        if (property_exists($actor, 'assertionMethod')) {
            if (is_array($actor->assertionMethod)) {
                /** @var object{type: string, id: string, controller: string, publicKeyMultibase: string} $assertionMethod */
                foreach ($actor->assertionMethod as $assertionMethod) {
                    try {
                        return PublicKey::fromMultibase($assertionMethod->publicKeyMultibase);
                    } catch (CryptoException) {
                        // We skip this key if it cannot be used
                    }
                }
            }
        }

        // Fallback to the actor's publicKey object, which we hope isn't RSA:
        if (!isset($actor->publicKey) || !isset($actor->publicKey->publicKeyPem)) {
            throw new FetchException("Could not find public key for {$actorUrl}");
        }

        // This will throw if a non-Ed25519 public key is defined:
        return $this->pemToPublicKey($actor->publicKey->publicKeyPem);
    }

    /**
     * Used for unit tests. Sets a canonical value to bypass the live webfinger query.
     *
     * @param string $index
     * @param string $value
     * @return void
     *
     * @throws CacheException
     * @throws SodiumException
     * @throws InvalidArgumentException
     */
    public function setCanonicalForTesting(string $index, string $value): void
    {
        // Use deriveKey() to get the namespace prefix:
        $key = $this->canonicalCache->deriveKey($index);
        if (!$this->canonicalCache->set($key, $value)) {
            throw new CacheException('Could not set ' . $index . ' to ' . $key);
        }
    }

    /**
     * @throws CryptoException
     */
    protected function pemToPublicKey(string $pem): PublicKey
    {
        return PublicKey::importPem($pem);
    }
}
