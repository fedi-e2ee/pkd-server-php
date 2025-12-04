<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Meta;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
class Route
{
    public function __construct(public readonly string $uriPattern = '') {}
}
