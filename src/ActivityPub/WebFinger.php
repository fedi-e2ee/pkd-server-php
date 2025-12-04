<?php
declare(strict_types=1);

namespace FediE2EE\PKDServer\ActivityPub;

use FediE2EE\PKD\Crypto\{
    Exceptions\CryptoException,
    Exceptions\NetworkException,
    PublicKey
};
use FediE2EE\PKDServer\Exceptions\FetchException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use ParagonIE\Certainty\{
    Exception\CertaintyException,
    Fetch,
    RemoteFetch
};
use SodiumException;
use const FediE2EE\PKDServer\PKD_SERVER_ROOT;

class WebFinger
{
    protected static array $cache = [];
    protected static array $canonicalCache = [];
    protected static array $inboxCache = [];
    protected Client $http;
    protected Fetch $fetch;

    /**
     * @throws CertaintyException
     * @throws SodiumException
     */
    public function __construct(?Client $client = null, ?Fetch $fetch = null)
    {
        if (is_null($fetch)) {
            $fetch = new RemoteFetch(PKD_SERVER_ROOT . '/tmp');
        }
        $this->fetch = $fetch;
        if (is_null($client)) {
            $client = new Client(['verify' => $this->fetch->getLatestBundle()]);
        }
        $this->http = $client;
    }

    public function clearCaches(): void
    {
        self::$cache = [];
        self::$canonicalCache = [];
        self::$inboxCache = [];
    }

    /**
     * @throws NetworkException
     * @throws GuzzleException
     */
    public function canonicalize(string $actorUsernameOrUrl): string
    {
        if (!array_key_exists($actorUsernameOrUrl, self::$canonicalCache)) {
            self::$canonicalCache[$actorUsernameOrUrl] = $this->lookup($actorUsernameOrUrl);
        }
        return self::$canonicalCache[$actorUsernameOrUrl];
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
        [, $host] = explode('@', $identifier);
        $url = "https://{$host}/.well-known/webfinger?resource=acct:{$identifier}";
        $response = $this->http->get($url);
        if ($response->getStatusCode() !== 200) {
            throw new NetworkException('Could not connect to ' . $host);
        }
        $jrd = json_decode($response->getBody()->getContents(), true);
        if (!is_array($jrd)) {
            throw new NetworkException('Invalid JSON');
        }
        return $jrd;
    }

    /**
     * @param string $identifier
     * @return string
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
     * @param string $url
     * @return string
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
     * @api
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
    /**
     * @api
     */
    public static function clearCanonicalCache(): void
    {
        self::$canonicalCache = [];
    }

    public function getInboxUrl(string $actorUrl): string
    {
        if (!array_key_exists($actorUrl, self::$inboxCache)) {
            $canonicalUrl = $this->canonicalize($actorUrl);
            $raw = $this->http->get(
                $canonicalUrl . '.json',
                ['Accept' => 'application/activity+json']
            );
            $decoded = json_decode($raw->getBody()->getContents());
            if (!is_object($decoded)) {
                throw new NetworkException('Could not decode ' . $canonicalUrl);
            }
            $url = $decoded->inbox;
            self::$inboxCache[$actorUrl] = $url;
        }
        return self::$inboxCache[$actorUrl];
    }

    /**
     * @throws CryptoException
     * @throws FetchException
     */
    public function getPublicKey(string $actorUrl): PublicKey
    {
        if (isset(self::$cache[$actorUrl])) {
            return self::$cache[$actorUrl];
        }

        $parsed = parse_url($actorUrl);
        $host = $parsed['host'];
        $path = $parsed['path'];
        $username = trim(str_replace('/', '', $path));

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
            if ($link['rel'] === 'self' && $link['type'] === 'application/activity+json') {
                $publicKey = $this->getPublicKeyFromActivityPub($link['href']);
                self::$cache[$actorUrl] = $publicKey;
                return $publicKey;
            }
        }
        throw new FetchException("Could not find ActivityPub link for {$actorUrl}");
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
     */
    public static function setCanonicalForTesting(string $index, string $value): void
    {
        self::$canonicalCache[$index] = $value;
    }

    /**
     * @throws CryptoException
     */
    protected function pemToPublicKey(string $pem): PublicKey
    {
        return PublicKey::importPem($pem);
    }
}
