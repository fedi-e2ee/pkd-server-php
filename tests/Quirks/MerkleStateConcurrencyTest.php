<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Quirks;

use FediE2EE\PKDServer\Tables\MerkleState;
use FediE2EE\PKDServer\Tables\Records\MerkleLeaf;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use ParagonIE\EasyDB\EasyDB;
use PDOException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class MerkleStateConcurrencyTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    public function testConcurrency(): void
    {
        // Let's make sure we have two database connections open:
        if (!array_key_exists('PKD_PHPUNIT_DB', $GLOBALS)) {
            $this->markTestSkipped('autoload-phpunit was not triggered');
        }
        if (!($GLOBALS['PKD_PHPUNIT_DB'] instanceof EasyDB)) {
            $this->markTestSkipped('global variable is of wrong type');
        }
        $config1 = $this->config();
        $sk = $config1->getSigningKeys()->secretKey;
        $config2 = clone $config1;
        $config2->withDatabase($GLOBALS['PKD_PHPUNIT_DB']);

        $table1 = new MerkleState($this->config());
        $table2 = new MerkleState($config2);

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('intentional timeout from table1');
        // Let's try to write both. Only one should be thrown.
        $table1->insertLeaf(MerkleLeaf::from('test1', $sk), function () {
            usleep(1000);
            throw new PDOException('intentional timeout from table1');
        });
        $table2->insertLeaf(MerkleLeaf::from('test2', $sk), function () {
            throw new PDOException('table2 failed');
        }, 1);
    }
}
