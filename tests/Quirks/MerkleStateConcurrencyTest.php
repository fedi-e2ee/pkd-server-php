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
        if (!extension_loaded('pcntl')) {
            $this->markTestSkipped('pcntl extension not loaded');
        }
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

        try {
            $pid = pcntl_fork();
            if ($pid === -1) {
                $this->fail('Could not fork process');
            } elseif ($pid === 0) {
                // Child process
                try {
                    $table2->insertLeaf(MerkleLeaf::from('test2', $sk), function () {
                        throw new PDOException('table2 failed');
                    }, 1);
                } catch (PDOException $e) {
                    exit(1);
                }
                exit(0);
            } else {
                // Parent process
                $this->expectException(PDOException::class);
                $this->expectExceptionMessage('intentional timeout from table1');

                $table1->insertLeaf(MerkleLeaf::from('test1', $sk), function () {
                    usleep(5_000_001);
                    throw new PDOException('intentional timeout from table1');
                });

                // Wait for child
                pcntl_waitpid($pid, $status);
            }
        } finally {
            if ($config1->getDb()->inTransaction()) {
                $config1->getDb()->rollBack();
            }
            if ($config2->getDb()->inTransaction()) {
                $config2->getDb()->rollBack();
            }
        }
    }
}
