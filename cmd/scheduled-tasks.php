<?php

use FediE2EE\PKDServer\Scheduled\ASQueue;
use FediE2EE\PKDServer\Scheduled\Witness;
use FediE2EE\PKDServer\ServerConfig;

use const FediE2EE\PKDServer\PKD_SERVER_ROOT;

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once PKD_SERVER_ROOT . '/autoload.php';

/** @var ServerConfig $pkdConfig */

/**
 * We only run the witness stuff once per day.
 *
 * @throws DateMalformedStringException
 */
function check_witness_run(Witness $witness): void
{
    $tmpDir = PKD_SERVER_ROOT . '/tmp';
    if (!is_dir($tmpDir)) {
        @mkdir($tmpDir, 0755, true);
    }
    if (!is_writable($tmpDir)) {
        // Cannot write to the temp directory, so we cannot run the witness task.
        return;
    }

    $witnessLastRunFile = $tmpDir . '/witness-last-run';
    $runWitness = false;

    // Open the file with a lock
    try {
        $fp = fopen($witnessLastRunFile, 'c+');
        if (!$fp) {
            // Cannot open the file, so we cannot run the witness task.
            return;
        }

        // Acquire an exclusive lock (blocking)
        if (flock($fp, LOCK_EX)) {
            $lastRun = stream_get_contents($fp);
            if (empty($lastRun)) {
                // If we never ran it before, we always run it:
                $runWitness = true;
            } else {
                // If we haven't run it in 24 hours, kick it off:
                $lastRunTime = (int)$lastRun;
                if ((time() - $lastRunTime) >= 86400) {
                    $runWitness = true;
                }
            }

            if ($runWitness) {
                // Update the timestamp to buy exclusivity. then run the task:
                ftruncate($fp, 0);
                rewind($fp);
                fwrite($fp, (string)time());
                fflush($fp);
                $witness->run();
            }

            // Release the lock
            flock($fp, LOCK_UN);
        }
    } finally {
        fclose($fp);
    }
}

// We hard-cap this process at 60 seconds since it runs every minute:
set_time_limit(60);

// Current time (with microsecond resolution):
$start = microtime(true);

// We want this to stop cycling after 59 seconds, since another cron job is about to kick off after 60.
$cutoff = $start + 59;

// Initialize our schedules:
$asQueue = new ASQueue($pkdConfig);
$witness = new Witness($GLOBALS['pkdConfig']);
$cycles = 0;

// We will check the ASQueue every
do {
    $cycleStart = microtime(true);

    // Run the ActivityStream Queue every chance you get.
    $asQueue->run();

    // Try to run the Witness once a day
    if ($cycles === 0) {
        try {
            check_witness_run($witness);
        } catch (DateMalformedStringException $ex) {
            echo $ex->getMessage(), PHP_EOL;
        }
    }

    // Another cycle complete!
    ++$cycles;

    // How much time was elapsed?
    $elapsed = microtime(true) - $cycleStart;

    // We want at least one second per cycle.
    if ($elapsed < 0.1) {
        // Sleep for a second.
        sleep(1);
    } elseif ($elapsed < 1.0) {
        // Try to wait a full second:
        usleep((int) ((1 - $elapsed) * 1000));
    }
} while (microtime(true) < $cutoff);

// When we get here, it is time to kill the script.
echo 'Done.', PHP_EOL;
exit(0);
