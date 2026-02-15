<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Traits;

use FediE2EE\PKD\Crypto\UtilTrait;
use ParagonIE\ConstantTime\Base32;
use function hash_hmac;

trait AuxDataIdTrait
{
    use UtilTrait;

    public static function getAuxDataId(string $auxDataType, string $data): string
    {
        return Base32::encodeUnpadded(
            hash_hmac(
                'sha256',
                self::preAuthEncode(['aux_type', $auxDataType, 'data', $data]),
                "FediPKD1-Auxiliary-Data-IDKeyGen",
                true
            )
        );
    }
}
