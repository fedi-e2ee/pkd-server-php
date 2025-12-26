<?php
declare(strict_types=1);

use const FediE2EE\PKDServer\PKD_SERVER_ROOT;

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once PKD_SERVER_ROOT . '/autoload.php';

$scheduledTasksScript = PKD_SERVER_ROOT . '/cmd/scheduled-tasks.php';
$phpExecutable = PHP_BINARY;
$cronJob = "* * * * * {$phpExecutable} {$scheduledTasksScript}";

// Get current crontab content. The '2>/dev/null' suppresses errors if no crontab exists.
$currentCrontab = shell_exec('crontab -l 2>/dev/null');

// Check if the job already exists to avoid duplicates
if (str_contains($currentCrontab, $cronJob)) {
    echo "Cron job already exists. No changes made." . PHP_EOL;
    exit(0);
}

// Append the new cron job to the existing crontab content
$newCrontab = $currentCrontab . $cronJob . PHP_EOL;

// Create a temporary file to hold the new crontab content
$tempFile = tempnam(sys_get_temp_dir(), 'cron-' . sodium_bin2hex(random_bytes(32)));
if ($tempFile === false) {
    echo "Failed to create temporary file." . PHP_EOL;
    exit(1);
}
file_put_contents($tempFile, $newCrontab);

// Load the new crontab from the temporary file
$output = '';
// nosemgrep: php.lang.security.exec-use.exec-use
exec("crontab {$tempFile}", $output, $return_var);

// Clean up the temporary file
// This is not actually based on user input, but the OS temporary directory features.
// nosemgrep: php.lang.security.unlink-use.unlink-use
unlink($tempFile);

if ($return_var === 0) {
    echo "Cron job installed successfully." . PHP_EOL;
    exit(0);
} else {
    echo "Error installing cron job. `crontab` command failed with exit code {$return_var}." . PHP_EOL;
    echo $output, PHP_EOL;
    exit(1);
}
