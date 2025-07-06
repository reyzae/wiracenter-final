<?php
require_once __DIR__ . '/../config/config.php';

// This script is intended to be run via a cron job or similar scheduled task.
// It should NOT be accessible directly via a web browser.

// Ensure this script is not accessed via HTTP
if (php_sapi_name() !== 'cli' && !defined('CRON_RUN')) {
    die('Access denied.');
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    error_log('Cleanup script: Database connection failed.');
    exit(1);
}

// Get log retention days from settings
$log_retention_days = (int)getSetting('log_retention_days', '30');

if ($log_retention_days > 0) {
    $cutoff_date = date('Y-m-d H:i:s', strtotime('-' . $log_retention_days . ' days'));

    // Clean up old activity logs
    $stmt = $conn->prepare("DELETE FROM activity_logs WHERE created_at < ?");
    if ($stmt->execute([$cutoff_date])) {
        $deleted_rows = $stmt->rowCount();
        error_log('Cleanup script: Deleted ' . $deleted_rows . ' activity logs older than ' . $log_retention_days . ' days.');
    } else {
        error_log('Cleanup script: Failed to delete old activity logs.');
    }
}

// You can add more cleanup tasks here, e.g., old drafts, temporary files, etc.

exit(0);
?>