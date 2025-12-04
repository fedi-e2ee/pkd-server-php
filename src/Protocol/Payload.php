<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Protocol;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Exceptions\JsonException;
use FediE2EE\PKD\Crypto\Protocol\{
    EncryptedProtocolMessageInterface,
    ProtocolMessageInterface
};

readonly class Payload
{
    public function __construct(
        public ProtocolMessageInterface $message,
        public AttributeKeyMap $keyMap,
        public string $rawJson,
    ) {}

    public function decrypt(): ProtocolMessageInterface
    {
        if ($this->message instanceof EncryptedProtocolMessageInterface) {
            return $this->message->decrypt($this->keyMap);
        }
        return $this->message;
    }

    /**
     * @throws JsonException
     */
    public function jsonDecode(): array
    {
        $decoded = json_decode($this->rawJson, true);
        if (!is_array($decoded)) {
            throw new JsonException('Could not decode JSON');
        }
        return $decoded;
    }

    public function getMerkleTreePayload(): string
    {
        $decoded = json_decode($this->rawJson, true);
        if (array_key_exists('key-id', $decoded)) {
            unset($decoded['key-id']);
        }
        if (array_key_exists('symmetric-keys', $decoded)) {
            unset($decoded['symmetric-keys']);
        }
        return json_encode($decoded, JSON_UNESCAPED_SLASHES);
    }
}
