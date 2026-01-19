<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Traits;

use Closure;
use FediE2EE\PKD\Crypto\Exceptions\{
    CryptoException,
    NetworkException,
    NotImplementedException
};
use FediE2EE\PKD\Crypto\Protocol\EncryptedProtocolMessageInterface;
use FediE2EE\PKDServer\Exceptions\{
    ConcurrentException,
    DependencyException,
    ProtocolException,
    TableException
};
use FediE2EE\PKDServer\Protocol\{
    KeyWrapping,
    Payload
};
use FediE2EE\PKDServer\ServerConfig;
use GuzzleHttp\Exception\GuzzleException;
use FediE2EE\PKDServer\Tables\{
    MerkleState,
    Records\MerkleLeaf
};
use ParagonIE\Certainty\Exception\CertaintyException;
use Random\RandomException;
use SodiumException;

/**
 * @property ServerConfig $config
 */
trait ProtocolMethodTrait
{
    protected const int ENCRYPTION_REQUIRED = 1;
    protected const int ENCRYPTION_DISALLOWED = 2;
    protected const int ENCRYPTION_OPTIONAL = 3;

    /**
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws ProtocolException
     * @throws RandomException
     * @throws SodiumException
     * @throws TableException
     */
    protected function protocolMethod(
        Payload $payload,
        string $expectedAction,
        Closure $callback,
        int $encryption = self::ENCRYPTION_REQUIRED
    ): mixed {
        // Before we do any insets, we should make sure we're not in a dangling transaction:
        if ($this->config->getDb()->inTransaction()) {
            $this->config->getDb()->commit();
        }
        $message = $payload->message;
        if ($message->getAction() !== $expectedAction) {
            throw new ProtocolException('Invalid bundle for ' . $expectedAction);
        }

        if ($message instanceof EncryptedProtocolMessageInterface) {
            if ($encryption === self::ENCRYPTION_DISALLOWED) {
                throw new ProtocolException('Message must be unencrypted for ' . $expectedAction);
            }
        } else {
            if ($encryption === self::ENCRYPTION_REQUIRED) {
                throw new ProtocolException('Message must be encrypted for ' . $expectedAction);
            }
        }
        $keyWrapping = new KeyWrapping($this->config);

        $leaf = MerkleLeaf::fromPayload(
            $payload,
            $this->config()->getSigningKeys()->secretKey,
            $keyWrapping->hpkeWrapSymmetricKeys($payload->keyMap)
        );

        $result = null;
        $cb = function () use (&$result, $leaf, $payload, $callback) {
            $result = $callback($leaf, $payload);
        };
        if (new MerkleState($this->config)->insertLeaf($leaf, $cb)) {
            if ($this->config->getDb()->inTransaction()) {
                $this->config->getDb()->commit();
            }
            return $result;
        }
        // Before we do any insets, we should make sure we're not in a dangling transaction:
        if ($this->config->getDb()->inTransaction()) {
            $this->config->getDb()->rollBack();
        }
        throw new TableException('Could not insert new leaf');
    }


    /**
     * @throws CertaintyException
     * @throws DependencyException
     * @throws GuzzleException
     * @throws NetworkException
     * @throws ProtocolException
     * @throws SodiumException
     */
    protected function explicitOuterActorCheck(string $expected, string $given): void
    {
        if (hash_equals($expected, $given)) {
            return;
        }
        $this->webfinger();
        $canonicalExpected = $this->webFinger->canonicalize($expected);
        $canonicalGiven = $this->webFinger->canonicalize($given);
        if (hash_equals($canonicalExpected, $canonicalGiven)) {
            return;
        }
        throw new ProtocolException('Actor confusion attack detected and prevented');
    }
}
