<?php
declare(strict_types=1);

if ($argc < 2) {
    echo 'No argument provided';
    exit(1);
}

$dbPath = $argv[1];

// Realpath checks
$realpath = realpath($dbPath);
$expectedPrefix = realpath(dirname(__DIR__) . '/tmp/db/');
if (!str_starts_with($realpath, $expectedPrefix)) {
    echo 'Error: Directory traversal';
    exit(1);
}
if (!str_ends_with($realpath, '-test.db')) {
    echo 'Error: Not a test DB';
    exit(1);
}


// Wait a bit for PHP to fully exit and release locks
sleep(1);

// Try to delete, with retries
$attempts = 0;
while (file_exists($dbPath) && $attempts < 5) {
    // nosemgrep: php.lang.security.unlink-use.unlink-use
    if (@unlink($dbPath)) {
        exit(0);
    }
    usleep(500000);  // 500ms between attempts
    $attempts++;
}
exit(0);
