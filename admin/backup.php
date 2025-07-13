<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Backup & Restore';
include 'includes/header.php';

$success_message = '';
$error_message = '';
$backup_dir = '../backups/';

// Handle backup action
if (isset($_POST['backup_now'])) {
    $filename = 'db_backup_' . date('Ymd_His') . '.sql';
    $filepath = $backup_dir . $filename;
    // Ambil konfigurasi database tanpa mendefinisikan ulang class
    require_once '../config/config.php';
    $db = new Database();
    $dbhost = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $dbname = $_ENV['DB_NAME'] ?? 'wiracent_db2';
    $dbuser = $_ENV['DB_USER'] ?? 'wiracent_admin';
    $dbpass = $_ENV['DB_PASS'] ?? 'Wiracenter!';
    $mysqldump = 'mysqldump';
    $command = "$mysqldump -h$dbhost -u$dbuser -p$dbpass $dbname > $filepath";
    $result = null;
    $output = null;
    @exec($command, $output, $result);
    if ($result === 0 && file_exists($filepath)) {
        $success_message = 'Database backup created successfully!';
        logActivity($_SESSION['user_id'], 'Created database backup', 'backup', null);
    } else {
        $error_message = 'Failed to create database backup. Please check server permissions or mysqldump path.';
    }
}

// Handle delete backup
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $filepath = $backup_dir . $file;
    if (file_exists($filepath) && strpos($file, 'db_backup_') === 0) {
        if (unlink($filepath)) {
            $success_message = 'Backup file deleted successfully!';
            logActivity($_SESSION['user_id'], 'Deleted backup file', 'backup', null);
        } else {
            $error_message = 'Failed to delete backup file.';
        }
    } else {
        $error_message = 'File not found or invalid.';
    }
}

// Handle restore backup (khusus admin)
if (isset($_GET['restore']) && isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
    $file = basename($_GET['restore']);
    $filepath = $backup_dir . $file;
    if (file_exists($filepath) && strpos($file, 'db_backup_') === 0) {
        // Ambil konfigurasi database
        require_once '../config/config.php';
        $dbhost = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $dbname = $_ENV['DB_NAME'] ?? 'wiracent_db2';
        $dbuser = $_ENV['DB_USER'] ?? 'wiracent_admin';
        $dbpass = $_ENV['DB_PASS'] ?? 'Wiracenter!';
        $mysql = 'mysql';
        $command = "$mysql -h$dbhost -u$dbuser -p$dbpass $dbname < $filepath";
        $result = null;
        $output = null;
        @exec($command, $output, $result);
        if ($result === 0) {
            $success_message = 'Database restored successfully from backup!';
            logActivity($_SESSION['user_id'], 'Restored database from backup', 'backup', null);
        } else {
            $error_message = 'Failed to restore database. Please check server permissions or mysql path.';
        }
    } else {
        $error_message = 'Backup file not found or invalid.';
    }
}

// List backup files
$backups = [];
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if (strpos($file, 'db_backup_') === 0 && substr($file, -4) === '.sql') {
            $backups[] = [
                'name' => $file,
                'size' => filesize($backup_dir . $file),
                'date' => date('Y-m-d H:i', filemtime($backup_dir . $file)),
            ];
        }
    }
    usort($backups, function($a, $b) { return strcmp($b['name'], $a['name']); });
}
?>
<div class="container-fluid">
    <h1 class="h2 mb-4"><i class="fas fa-save me-2"></i>Backup & Restore</h1>
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-database me-2"></i>Database Backup</h5>
            <form method="POST" class="mb-0">
                <button type="submit" name="backup_now" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Backup Now
                </button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>File Name</th>
                            <th>Size</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($backups): foreach ($backups as $b): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($b['name']); ?></td>
                            <td><?php echo number_format($b['size']/1024, 2); ?> KB</td>
                            <td><?php echo $b['date']; ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?php echo $backup_dir . $b['name']; ?>" class="btn btn-sm btn-success" download><i class="fas fa-download"></i> Download</a>
                                    <?php if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin'): ?>
                                        <a href="?restore=<?php echo urlencode($b['name']); ?>" class="btn btn-sm btn-warning" onclick="return confirm('Restore database from this backup? Seluruh data saat ini akan diganti!')"><i class="fas fa-undo"></i> Restore</a>
                                    <?php endif; ?>
                                    <a href="?delete=<?php echo urlencode($b['name']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this backup file?')"><i class="fas fa-trash"></i> Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-save fa-2x mb-3 d-block"></i>
                                <strong>No backup files found</strong><br>
                                <small>Click "Backup Now" to create a new database backup</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="alert alert-info mt-3">
        <i class="fas fa-info-circle me-2"></i>
        <b>Note:</b> Backup file contains the full database structure and data. Download and store it securely. Restore can be done via phpMyAdmin or MySQL CLI.
    </div>
</div>
<?php include 'includes/footer.php'; ?>
