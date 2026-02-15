<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Traits;

use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    HttpSignatureException,
    NotImplementedException
};
use FediE2EE\PKD\Crypto\HttpSignature;
use FediE2EE\PKDServer\ActivityPub\ActivityStream;
use FediE2EE\PKDServer\Exceptions\{
    ActivityPubException,
    DependencyException,
    FetchException
};
use ParagonIE\Certainty\Exception\CertaintyException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;
use SodiumException;
use stdClass;
use function json_decode;
use function json_last_error_msg;
use function property_exists;

trait ActivityStreamsTrait
{
    use ConfigTrait;

    /**
     * @throws ActivityPubException
     * @throws CertaintyException
     * @throws CryptoException
     * @throws DependencyException
     * @throws FetchException
     * @throws HttpSignatureException
     * @throws InvalidArgumentException
     * @throws NotImplementedException
     * @throws SodiumException
     */
    public function getVerifiedStream(ServerRequestInterface $message): ActivityStream
    {
        $body = $message->getBody()->getContents();
        if (!$body) {
            throw new ActivityPubException('Empty message received');
        }

        // Decode JSON
        $decoded = json_decode($body);
        if (!($decoded instanceof stdClass)) {
            throw new ActivityPubException('Invalid JSON: ' . json_last_error_msg());
        }
        if (!property_exists($decoded, 'actor')) {
            throw new ActivityPubException('No actor provided');
        }

        // Ensure HTTP Signature is valid
        $publicKey = $this->webfinger()->getPublicKey($decoded->actor);
        $sig = new HttpSignature();
        if (!$sig->verify($publicKey, $message)) {
            throw new ActivityPubException('Invalid HTTP Signature');
        }
        return ActivityStream::fromDecoded($decoded);
    }
}
