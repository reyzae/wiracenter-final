<?php
require_once dirname(__DIR__) . '/config/config.php';

$db = new Database();
$conn = $db->connect();

$retention_days = 7; // Items older than 7 days will be permanently deleted

// Clean up trashed files
$stmt = $conn->prepare("SELECT file_path FROM files WHERE deleted_at IS NOT NULL AND deleted_at < NOW() - INTERVAL ? DAY");
$stmt->execute([$retention_days]);
$files_to_delete = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($files_to_delete as $file) {
    $full_path = dirname(__DIR__) . '/' . $file['file_path'];
    if (file_exists($full_path)) {
        unlink($full_path);
    }
}

$stmt = $conn->prepare("DELETE FROM files WHERE deleted_at IS NOT NULL AND deleted_at < NOW() - INTERVAL ? DAY");
$stmt->execute([$retention_days]);
$deleted_files_count = $stmt->rowCount();

// Clean up trashed articles
$stmt = $conn->prepare("DELETE FROM articles WHERE deleted_at IS NOT NULL AND deleted_at < NOW() - INTERVAL ? DAY");
$stmt->execute([$retention_days]);
$deleted_articles_count = $stmt->rowCount();

// Clean up trashed projects
$stmt = $conn->prepare("DELETE FROM projects WHERE deleted_at IS NOT NULL AND deleted_at < NOW() - INTERVAL ? DAY");
$stmt->execute([$retention_days]);
$deleted_projects_count = $stmt->rowCount();

// Clean up trashed tools
$stmt = $conn->prepare("DELETE FROM tools WHERE deleted_at IS NOT NULL AND deleted_at < NOW() - INTERVAL ? DAY");
$stmt->execute([$retention_days]);
$deleted_tools_count = $stmt->rowCount();

// Log the cleanup activity (optional, but good for debugging/monitoring)
// You might want to create a separate log for cron jobs or use the existing activity_logs table
// For simplicity, we'll just print to console for now.

echo "Trash cleanup completed:\n";
echo "- Files permanently deleted: " . $deleted_files_count . "\n";
echo "- Articles permanently deleted: " . $deleted_articles_count . "\n";
echo "- Projects permanently deleted: " . $deleted_projects_count . "\n";
echo "- Tools permanently deleted: " . $deleted_tools_count . "\n";

?>