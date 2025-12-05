<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\ActivityPub;

use AllowDynamicProperties;
use FediE2EE\PKDServer\Exceptions\ActivityPubException;
use JsonException;
use JsonSerializable;
use Override;
use stdClass;

#[AllowDynamicProperties]
class ActivityStream implements JsonSerializable
{
    protected const string PUBLIC_ADDRESS = 'https://www.w3.org/ns/activitystreams#Public';
    private string $internalContext = '';
    public string $id = '';
    public string $type = '';
    public string $actor = '';
    public object $object;

    /**
     * @throws ActivityPubException
     */
    public static function fromDecoded(stdClass $decoded): self
    {
        if (!property_exists($decoded, '@context')) {
            throw new ActivityPubException('The @context property must be set.');
        }
        $self = new self();
        // @phpstan-ignore foreach.nonIterable
        foreach ($decoded as $key => $value) {
            if ($key === '@context') {
                $self->internalContext = $value;
            } else {
                $self->{$key} = $value;
            }
        }
        return $self;
    }

    /**
     * @throws ActivityPubException
     */
    public static function fromString(string $input): self
    {
        return self::fromDecoded(json_decode($input, false, JSON_FORCE_OBJECT));
    }

    #[Override]
    public function jsonSerialize(): stdClass
    {
        $fields = ['@context' => $this->internalContext];
        // @phpstan-ignore foreach.nonIterable
        foreach ($this as $key => $value) {
            if ($key !== 'internalContext') {
                $fields[$key] = $value;
            }
        }
        return (object) $fields;
    }

    /**
     * @throws JsonException
     */
    public function __toString(): string
    {
        return json_encode(
            $this->jsonSerialize(),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * @return bool
     */
    public function isDirectMessage(): bool
    {
        // The object needs to have these fields set:
        if (!property_exists($this->object, 'to')) {
            return false;
        }
        if (!is_array($this->object->to)) {
            return false;
        }
        if (!property_exists($this->object, 'type')) {
            return false;
        }
        if (!property_exists($this->object, 'content')) {
            return false;
        }
        if (empty($this->id)) {
            return false;
        }
        if (!is_string($this->object->content)) {
            return false;
        }

        // If it's a public message, we disregard it:
        if (in_array(self::PUBLIC_ADDRESS, $this->object->to, true)) {
            return false;
        }
        // If it's an unlisted address, we disregard it:
        if (property_exists($this->object, 'cc') && is_array($this->object->cc)) {
            if (in_array(self::PUBLIC_ADDRESS, $this->object->cc, true)) {
                return false;
            }
        }
        // If we get this far and these two are true, then we are a DM!
        return $this->type === 'Create' && $this->object->type === 'Note';
    }
}
