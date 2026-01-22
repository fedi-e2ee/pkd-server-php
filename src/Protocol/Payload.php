<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Protocol;

use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use JsonException;
use FediE2EE\PKD\Crypto\Protocol\{
    EncryptedProtocolMessageInterface,
    ProtocolMessageInterface
};
use FediE2EE\PKDServer\Traits\JsonTrait;

readonly class Payload
{
    use JsonTrait;
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
    public function decode(): array
    {
        return self::jsonDecode($this->rawJson);
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
        return self::jsonEncode($decoded);
    }
}
