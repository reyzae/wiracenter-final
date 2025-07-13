<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Files';
include 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$success_message = '';
$error_message = '';
$errors = [];

$db = new Database();
$conn = $db->connect();

// Handle file delete (soft delete)
if ($action == 'delete' && $id) {
    $stmt = $conn->prepare("UPDATE files SET deleted_at = NOW() WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success_message = 'File deleted successfully!';
        logActivity($_SESSION['user_id'], 'Deleted file', 'file', $id);
    } else {
        $error_message = 'Failed to delete file.';
    }
    $action = 'list';
    if (!headers_sent()) {
        header('Location: files.php?msg=' . urlencode($success_message ?: $error_message));
        ob_end_clean();
        exit();
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['files'])) {
    $uploadDir = '../' . UPLOAD_PATH;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $uploadedFiles = [];
    $errors = [];
    foreach ($_FILES['files']['name'] as $key => $name) {
        $tmpName = $_FILES['files']['tmp_name'][$key];
        $size = $_FILES['files']['size'][$key];
        $error = $_FILES['files']['error'][$key];
        $type = $_FILES['files']['type'][$key];
        if ($error !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading $name";
            continue;
        }
        if ($size > MAX_FILE_SIZE) {
            $errors[] = "$name is too large (max " . (MAX_FILE_SIZE / 1024 / 1024) . "MB)";
            continue;
        }
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        if (move_uploaded_file($tmpName, $filepath)) {
            $stmt = $conn->prepare("INSERT INTO files (filename, original_name, file_path, file_size, file_type, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$filename, $name, UPLOAD_PATH . $filename, $size, $type, $_SESSION['user_id']])) {
                $file_id = $conn->lastInsertId();
                logActivity($_SESSION['user_id'], 'Uploaded file', 'file', $file_id);
                $uploadedFiles[] = $name;
            } else {
                $errors[] = "Failed to save $name to database";
                unlink($filepath);
            }
        } else {
            $errors[] = "Failed to move $name";
        }
    }
    if (!empty($uploadedFiles)) {
        $success_message = count($uploadedFiles) . ' file(s) uploaded successfully: ' . implode(', ', $uploadedFiles);
    }
    if (!empty($errors)) {
        $error_message = implode('<br>', $errors);
    }
}

// Filter
$type_filter = $_GET['type'] ?? '';
$uploader_filter = $_GET['uploader'] ?? '';

// Get all files (not deleted)
$sql = "SELECT f.*, u.username FROM files f LEFT JOIN users u ON f.uploaded_by = u.id WHERE f.deleted_at IS NULL";
$params = [];
if ($type_filter) {
    $sql .= " AND f.file_type = ?";
    $params[] = $type_filter;
}
if ($uploader_filter) {
    $sql .= " AND f.uploaded_by = ?";
    $params[] = $uploader_filter;
}
$sql .= " ORDER BY f.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all unique file types and uploaders for filter dropdowns
$typeStmt = $conn->query("SELECT DISTINCT file_type FROM files WHERE deleted_at IS NULL");
$file_types = $typeStmt->fetchAll(PDO::FETCH_COLUMN);
$uploaderStmt = $conn->query("SELECT DISTINCT uploaded_by FROM files WHERE deleted_at IS NULL");
$uploader_ids = $uploaderStmt->fetchAll(PDO::FETCH_COLUMN);
$uploaders = [];
if ($uploader_ids) {
    $in = implode(',', array_fill(0, count($uploader_ids), '?'));
    $userStmt = $conn->prepare("SELECT id, username FROM users WHERE id IN ($in)");
    $userStmt->execute($uploader_ids);
    while ($row = $userStmt->fetch(PDO::FETCH_ASSOC)) {
        $uploaders[$row['id']] = $row['username'];
    }
}

if (isset($_GET['msg'])) {
    $success_message = urldecode($_GET['msg']);
}
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-upload me-2"></i>Files Management
        </h1>
        <form method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
            <input type="file" name="files[]" multiple required class="form-control" style="max-width: 250px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Upload</button>
        </form>
    </div>
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
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">All Files</h6>
            <form method="GET" class="d-flex gap-2 align-items-center mb-0">
                <select name="type" class="form-select form-select-sm" style="width: 140px;">
                    <option value="">All Types</option>
                    <?php foreach ($file_types as $ft): ?>
                        <option value="<?php echo htmlspecialchars($ft); ?>" <?php if ($type_filter === $ft) echo 'selected'; ?>><?php echo htmlspecialchars($ft); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="uploader" class="form-select form-select-sm" style="width: 140px;">
                    <option value="">All Uploaders</option>
                    <?php foreach ($uploaders as $uid => $uname): ?>
                        <option value="<?php echo $uid; ?>" <?php if ($uploader_filter == $uid) echo 'selected'; ?>><?php echo htmlspecialchars($uname); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="files.php" class="btn btn-outline-secondary btn-sm">Clear</a>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="filesTable" width="100%" cellspacing="0">
                    <thead class="table-primary">
                        <tr>
                            <th style="width: 36px;" class="text-center">#</th>
                            <th>Original Name</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Uploaded By</th>
                            <th>Uploaded At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($files)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-upload fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No files found.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($files as $i => $file): ?>
                                <tr>
                                    <td class="text-center"><?php echo $i+1; ?></td>
                                    <td>
                                        <a href="../<?php echo htmlspecialchars($file['file_path']); ?>" target="_blank" download><?php echo htmlspecialchars($file['original_name']); ?></a>
                                    </td>
                                    <td><?php echo htmlspecialchars($file['file_type']); ?></td>
                                    <td><?php echo number_format($file['file_size']/1024, 2); ?> KB</td>
                                    <td><?php echo htmlspecialchars($file['username'] ?? 'Unknown'); ?></td>
                                    <td><?php echo formatDateTime($file['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="../<?php echo htmlspecialchars($file['file_path']); ?>" class="btn btn-sm btn-outline-info" target="_blank" title="Download"><i class="fas fa-download"></i></a>
                                            <a href="?action=delete&id=<?php echo $file['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this file?')" title="Delete"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
