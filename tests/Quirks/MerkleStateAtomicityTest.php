<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Quirks;

use FediE2EE\PKD\Crypto\Exceptions\CryptoException;
use FediE2EE\PKD\Crypto\Exceptions\NotImplementedException;
use FediE2EE\PKDServer\Exceptions\ConcurrentException;
use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\Tables\MerkleState;
use FediE2EE\PKDServer\Tables\Records\MerkleLeaf;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use FediE2EE\PKDServer\Traits\ConfigTrait;
use ParagonIE\EasyDB\EasyDB;
use PDOException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Random\RandomException;
use SodiumException;
use Throwable;
use function array_key_exists;

#[CoversNothing]
class MerkleStateAtomicityTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    /**
     * @throws DependencyException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->config();

        // Clear any stuck locks from previous tests
        if ($config->getDb()->inTransaction()) {
            $config->getDb()->rollBack();
        }

        // Force clear the lock_challenge
        $config->getDb()->exec("UPDATE pkd_merkle_state SET lock_challenge = ''");
        usleep(100000);
    }

    /**
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws RandomException
     * @throws SodiumException
     */
    public function testLockChallengeNotClearedDuringCallback(): void
    {
        $config = $this->config();
        $table = new MerkleState($config);
        $sk = $config->getSigningKeys()->secretKey;

        $lockDuringCallback = null;

        $result = $table->insertLeaf(
            MerkleLeaf::from('test-lock-during', $sk),
            function () use ($config, &$lockDuringCallback) {
                $lockDuringCallback = $config->getDb()->cell(
                    "SELECT lock_challenge FROM pkd_merkle_state WHERE TRUE"
                );
            }
        );

        $this->assertTrue($result, 'Insert should succeed');
        $this->assertNotEmpty(
            $lockDuringCallback,
            'CRITICAL: lock_challenge must NOT be cleared during transaction callback'
        );
    }

    /**
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws RandomException
     * @throws SodiumException
     */
    public function testLockChallengeReleasedAfterSuccess(): void
    {
        $config = $this->config();
        $table = new MerkleState($config);
        $sk = $config->getSigningKeys()->secretKey;

        $table->insertLeaf(
            MerkleLeaf::from('test-lock-release-success', $sk),
            function () {}
        );
        usleep(50000);

        $lockAfterSuccess = $config->getDb()->cell("SELECT lock_challenge FROM pkd_merkle_state");

        $this->assertEmpty(
            $lockAfterSuccess,
            'lock_challenge must be cleared after successful transaction'
        );
    }

    /**
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws RandomException
     * @throws SodiumException
     */
    public function testLockChallengeReleasedAfterFailure(): void
    {
        $config = $this->config();
        $table = new MerkleState($config);
        $sk = $config->getSigningKeys()->secretKey;

        try {
            $table->insertLeaf(
                MerkleLeaf::from('test-lock-release-failure', $sk),
                function () {
                    throw new PDOException('intentional failure for test');
                }
            );
            $this->fail('Expected PDOException to be thrown');
        } catch (PDOException $e) {
            $this->assertStringContainsString('intentional failure', $e->getMessage());
        }

        // Wait briefly for finally block to complete
        usleep(50000);

        $lockAfterFailure = $config->getDb()->cell(
            "SELECT lock_challenge FROM pkd_merkle_state WHERE TRUE"
        );

        $this->assertEmpty(
            $lockAfterFailure,
            'lock_challenge must be cleared even after failed transaction'
        );
    }

    /**
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws RandomException
     * @throws SodiumException
     */
    public function testConcurrentAccessBlocked(): void
    {
        if (!extension_loaded('pcntl')) {
            $this->markTestSkipped('pcntl extension not loaded');
        }
        if (!array_key_exists('PKD_PHPUNIT_DB', $GLOBALS)) {
            $this->markTestSkipped('autoload-phpunit was not triggered');
        }
        if (!($GLOBALS['PKD_PHPUNIT_DB'] instanceof EasyDB)) {
            $this->markTestSkipped('global variable is of wrong type');
        }

        $config1 = $this->config();
        $config2 = clone $config1;
        $config2->withDatabase($GLOBALS['PKD_PHPUNIT_DB']);

        $table1 = new MerkleState($config1);
        $table2 = new MerkleState($config2);
        $sk = $config1->getSigningKeys()->secretKey;

        try {
            $pid = pcntl_fork();
            if ($pid === -1) {
                $this->fail('Could not fork process');
            }

            if ($pid === 0) {
                try {
                    usleep(50000);
                    $table2->insertLeaf(
                        MerkleLeaf::from('test-child-blocked', $sk),
                        function () {
                            exit(99);
                        },
                        1 // Only 1 retry - should fail immediately
                    );
                    exit(98); // Should not get here
                } catch (ConcurrentException) {
                    exit(0); // Expected - was properly blocked
                } catch (PDOException) {
                    // Also acceptable - database-level locking
                    exit(0);
                } catch (Throwable $e) {
                    // Log unexpected error for debugging
                    file_put_contents('php://stderr', "Child error: " . $e->getMessage() . "\n");
                    exit(1);
                }
            } else {
                try {
                    $table1->insertLeaf(
                        MerkleLeaf::from('test-parent-blocking', $sk),
                        function () {
                            // Hold lock for long enough for child to attempt
                            usleep(100000); // 100ms
                        }
                    );
                    usleep(50000);
                    pcntl_waitpid($pid, $status);
                    $childExitCode = pcntl_wexitstatus($status);

                    $this->assertSame(
                        0,
                        $childExitCode,
                        'Child should be blocked by lock (exit 0=blocked, 98=no exception, 99=got lock, 1=error)'
                    );
                } finally {
                    // Cleanup
                    if ($config1->getDb()->inTransaction()) {
                        $config1->getDb()->rollBack();
                    }
                }
            }
        } finally {
            if ($config2->getDb()->inTransaction()) {
                $config2->getDb()->rollBack();
            }
        }
    }

    /**
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws RandomException
     * @throws SodiumException
     */
    public function testLockBehaviorWithRollback(): void
    {
        $config = $this->config();
        $table = new MerkleState($config);
        $sk = $config->getSigningKeys()->secretKey;

        $lockSeenDuringCallback = null;

        try {
            $table->insertLeaf(
                MerkleLeaf::from('test-rollback', $sk),
                function () use ($config, &$lockSeenDuringCallback) {
                    // Check lock is held
                    $lockSeenDuringCallback = $config->getDb()->cell(
                        "SELECT lock_challenge FROM pkd_merkle_state WHERE TRUE"
                    );

                    // Force rollback by throwing
                    throw new PDOException('forcing rollback');
                }
            );
            $this->fail('Should have thrown PDOException');
        } catch (PDOException $e) {
            $this->assertStringContainsString('forcing rollback', $e->getMessage());
        }

        $this->assertNotEmpty(
            $lockSeenDuringCallback,
            'Lock must be held during callback even when rollback will occur'
        );

        // Wait for cleanup
        usleep(50000);

        $lockAfterRollback = $config->getDb()->cell(
            "SELECT lock_challenge FROM pkd_merkle_state WHERE TRUE"
        );

        $this->assertEmpty(
            $lockAfterRollback,
            'Lock must be released after rollback'
        );
    }

    /**
     * @throws ConcurrentException
     * @throws CryptoException
     * @throws DependencyException
     * @throws NotImplementedException
     * @throws RandomException
     * @throws SodiumException
     */
    public function testRegressionScenario(): void
    {
        $config = $this->config();
        $table = new MerkleState($config);
        $sk = $config->getSigningKeys()->secretKey;
        $stateChecks = [];
        $table->insertLeaf(
            MerkleLeaf::from('test-regression', $sk),
            function () use ($config, &$stateChecks) {
                $stateChecks['before_work'] = $config->getDb()->cell(
                    "SELECT lock_challenge FROM pkd_merkle_state"
                );

                // Simulate some work
                usleep(10000);

                $stateChecks['after_work'] = $config->getDb()->cell(
                    "SELECT lock_challenge FROM pkd_merkle_state"
                );
            }
        );

        $this->assertNotEmpty(
            $stateChecks['before_work'],
            'Lock must be held at start of callback'
        );
        $this->assertNotEmpty(
            $stateChecks['after_work'],
            'Lock must be held throughout callback'
        );
        $this->assertSame(
            $stateChecks['before_work'],
            $stateChecks['after_work'],
            'Lock challenge should not change during callback'
        );
    }
}
