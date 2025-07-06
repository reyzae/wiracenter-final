<?php
$page_title = 'Backup & Restore';
include 'includes/header.php';

requireLogin();
if (!hasPermission('admin')) {
    redirect(ADMIN_URL . '/dashboard.php');
}

$success_message = '';
$error_message = '';

// Database credentials from config/database.php
// Note: In a real-world scenario, avoid hardcoding or directly exposing credentials.
// This is for demonstration purposes based on the existing structure.
$db_host = 'localhost';
$db_name = 'wiracenter_db2';
$db_user = 'root';
$db_pass = '';

// Paths
$backup_dir = '../backups/';
$upload_dir = '../' . UPLOAD_PATH;

// Ensure backup directory exists
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'backup_db') {
        $backup_file = $backup_dir . 'db_backup_' . date('Ymd_His') . '.sql';
        $command = sprintf('C:/xampp/mysql/bin/mysqldump.exe -h%s -u%s %s %s > %s', escapeshellarg($db_host), escapeshellarg($db_user), empty($db_pass) ? '' : '-p' . escapeshellarg($db_pass), escapeshellarg($db_name), escapeshellarg($backup_file));

        exec($command, $output, $return_var);

        if ($return_var === 0) {
            $success_message = 'Database backup created successfully: ' . basename($backup_file);
            logActivity($_SESSION['user_id'], 'Created database backup', 'backup', null);
        } else {
            $error_message = 'Failed to create database backup. Command: ' . htmlspecialchars($command) . '<br>Output: <pre>' . htmlspecialchars(implode('\n', $output)) . '</pre>Return Var: ' . $return_var;
            error_log('Database backup error: ' . implode('\n', $output));
        }
    } elseif ($_GET['action'] == 'backup_media') {
        $backup_file = $backup_dir . 'media_backup_' . date('Ymd_His') . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($backup_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($upload_dir),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($upload_dir));
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
            $success_message = 'Media backup created successfully: ' . basename($backup_file);
            logActivity($_SESSION['user_id'], 'Created media backup', 'backup', null);
        } else {
            $error_message = 'Failed to create media backup.';
        }
    } elseif ($_GET['action'] == 'download' && isset($_GET['file'])) {
        $file_to_download = basename($_GET['file']);
        $full_path = $backup_dir . $file_to_download;

        if (file_exists($full_path) && is_readable($full_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file_to_download . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($full_path));
            readfile($full_path);
            exit;
        } else {
            $error_message = 'File not found or not readable.';
        }
    } elseif ($_GET['action'] == 'delete' && isset($_GET['file'])) {
        $file_to_delete = basename($_GET['file']);
        $full_path = $backup_dir . $file_to_delete;

        if (file_exists($full_path) && is_writable($full_path)) {
            unlink($full_path);
            $success_message = 'Backup file deleted successfully.';
            logActivity($_SESSION['user_id'], 'Deleted backup file', 'backup', $file_to_delete);
        } else {
            $error_message = 'File not found or not writable.';
        }
    }
}

// Get list of existing backup files
$backup_files = [];
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $backup_files[] = [
                'name' => $file,
                'size' => filesize($backup_dir . $file),
                'date' => filemtime($backup_dir . $file)
            ];
        }
    }
    // Sort by date, newest first
    usort($backup_files, function($a, $b) { return $b['date'] - $a['date']; });
}

?>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<h1 class="h2 mb-4">Backup & Restore</h1>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Create New Backup</h5>
    </div>
    <div class="card-body">
        <p>Create a backup of your database or media files.</p>
        <a href="?action=backup_db" class="btn btn-primary me-2" onclick="return confirm('Are you sure you want to create a database backup?');">
            <i class="fas fa-database me-2"></i>Backup Database
        </a>
        <a href="?action=backup_media" class="btn btn-success" onclick="return confirm('Are you sure you want to create a media files backup?');">
            <i class="fas fa-images me-2"></i>Backup Media Files
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Existing Backups</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Size</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($backup_files)): ?>
                        <?php foreach ($backup_files as $file): ?>
                            <tr>
                                <td><?php echo $file['name']; ?></td>
                                <td><?php echo round($file['size'] / (1024 * 1024), 2); ?> MB</td>
                                <td><?php echo formatDateTime(date('Y-m-d H:i:s', $file['date'])); ?></td>
                                <td>
                                    <a href="?action=download&file=<?php echo $file['name']; ?>" class="btn btn-sm btn-outline-primary me-2"><i class="fas fa-download"></i> Download</a>
                                    <a href="?action=delete&file=<?php echo $file['name']; ?>" class="btn btn-sm btn-outline-danger delete-btn" data-item="backup file"><i class="fas fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No backups found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>