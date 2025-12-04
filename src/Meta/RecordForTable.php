<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Meta;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RecordForTable
{
    public function __construct(public readonly string $tableName = '') {}
}
