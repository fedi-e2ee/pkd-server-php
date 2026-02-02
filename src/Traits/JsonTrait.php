<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Traits;

use JsonException as BaseJsonException;
use stdClass;

use function json_decode;
use function json_encode;

trait JsonTrait
{
    /**
     * @return array<string, mixed>
     * @throws BaseJsonException
     */
    public static function jsonDecode(string $json): array
    {
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws BaseJsonException
     */
    public static function jsonDecodeObject(string $json): stdClass
    {
        return json_decode($json, false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param mixed $data
     * @throws BaseJsonException
     */
    public static function jsonEncode(mixed $data): string
    {
        return json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}
