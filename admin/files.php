<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$page_title = 'File Manager';
include 'includes/header.php';

$db = new Database();
$conn = $db->connect();

$success_message = '';
$error_message = '';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get file info from database
    $stmt = $conn->prepare("SELECT file_path FROM files WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($file) {
        $full_path = '../' . $file['file_path'];
        
        // Delete from database
        $stmt = $conn->prepare("UPDATE files SET deleted_at = NOW() WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success_message = 'File moved to trash successfully!';
            logActivity($_SESSION['user_id'], 'Moved file to trash', 'file', $id);
        } else {
            $error_message = 'Failed to delete file from database.';
        }
    } else {
        $error_message = 'File not found.';
    }
    $action = 'list';
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_action'])) {
    $bulk_action = $_POST['bulk_action'];
    $selected_files = $_POST['selected_files'] ?? [];

    if (!empty($selected_files)) {
        $placeholders = implode(',', array_fill(0, count($selected_files), '?'));
        $success_count = 0;
        $error_count = 0;

        if ($bulk_action == 'delete') {
            $stmt = $conn->prepare("UPDATE files SET deleted_at = NOW() WHERE id IN ($placeholders)");
            if ($stmt->execute($selected_files)) {
                $success_count = $stmt->rowCount();
                logActivity($_SESSION['user_id'], 'Bulk moved files to trash', 'file', implode(',', $selected_files));
            } else {
                $error_count = count($selected_files);
            }
        }

        if ($success_count > 0) {
            $success_message = $success_count . ' file(s) moved to trash successfully!';
        }
        if ($error_count > 0) {
            $error_message = $error_count . ' file(s) failed to move to trash.';
        }
    }
    // Redirect to clear POST data and show updated list
    redirect(ADMIN_URL . '/files.php?action=list');
}

// Get all files for listing
$search_query = $_GET['search'] ?? '';
$type_filter = $_GET['type_filter'] ?? '';

$sql = "SELECT f.*, u.username FROM files f LEFT JOIN users u ON f.uploaded_by = u.id WHERE f.deleted_at IS NULL";
$params = [];

if (!empty($search_query)) {
    $sql .= " AND (f.original_name LIKE ? OR f.filename LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
}

if (!empty($type_filter)) {
    if ($type_filter == 'image') {
        $sql .= " AND f.file_type LIKE 'image/%'";
    } elseif ($type_filter == 'pdf') {
        $sql .= " AND f.file_type = 'application/pdf'";
    }
}

$sql .= " ORDER BY f.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<h1 class="h2 mb-4">Media Library</h1>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Upload New File</h5>
    </div>
    <div class="card-body">
        <div class="upload-area text-center p-5 border rounded mb-3">
            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
            <p class="text-muted">Drag & drop files here or click to upload</p>
            <input type="file" id="fileInput" name="files[]" multiple class="d-none" accept="image/*,application/pdf">
        </div>
        <div class="progress d-none" id="uploadProgress">
            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">All Uploaded Files</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-center mb-4">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search files..." value="<?php echo $_GET['search'] ?? ''; ?>">
            </div>
            <div class="col-md-3">
                <select name="type_filter" class="form-select">
                    <option value="">All Types</option>
                    <option value="image" <?php echo (($_GET['type_filter'] ?? '') == 'image') ? 'selected' : ''; ?>>Images</option>
                    <option value="pdf" <?php echo (($_GET['type_filter'] ?? '') == 'pdf') ? 'selected' : ''; ?>>PDFs</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>

        <form method="POST" action="" id="bulk-action-form">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <select name="bulk_action" class="form-select me-2">
                        <option value="">Bulk Actions</option>
                        <option value="delete">Move to Trash</option>
                    </select>
                    <button type="submit" class="btn btn-info" onclick="return confirm('Are you sure you want to apply this bulk action?');">Apply</button>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="select-all-files">
                    <label class="form-check-label" for="select-all-files">
                        Select All
                    </label>
                </div>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="media-grid">
                <?php if ($files): ?>
                    <?php foreach ($files as $file): ?>
                        <div class="col">
                            <div class="card h-100 media-card" data-file-url="<?php echo SITE_URL . '/' . $file['file_path']; ?>">
                                <div class="form-check position-absolute top-0 start-0 mt-2 ms-2">
                                    <input class="form-check-input file-checkbox" type="checkbox" name="selected_files[]" value="<?php echo $file['id']; ?>">
                                </div>
                                <?php if (strpos($file['file_type'], 'image') !== false): ?>
                                    <img src="../<?php echo $file['file_path']; ?>" class="card-img-top" alt="<?php echo $file['original_name']; ?>">
                                <?php else: ?>
                                    <div class="file-icon-preview d-flex justify-content-center align-items-center" style="height: 180px; background-color: #f8f9fa;">
                                        <i class="fas fa-file fa-4x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title text-truncate mb-1" title="<?php echo $file['original_name']; ?>"><?php echo $file['original_name']; ?></h6>
                                    <p class="card-text text-muted small mb-2"><?php echo round($file['file_size'] / 1024, 2); ?> KB | <?php echo formatDate($file['created_at']); ?></p>
                                    <div class="mt-auto d-flex justify-content-between">
                                        <button class="btn btn-sm btn-outline-primary copy-url-btn" type="button" data-url="<?php echo SITE_URL . '/' . $file['file_path']; ?>"><i class="fas fa-copy"></i> Copy URL</button>
                                        <a href="?action=delete&id=<?php echo $file['id']; ?>" class="btn btn-sm btn-outline-danger delete-btn" data-item="file"><i class="fas fa-trash"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted py-4">
                        <i class="fas fa-folder-open fa-3x mb-3"></i>
                        <p>No files uploaded yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode');

        if (mode === 'tinymce') {
            const mediaCards = document.querySelectorAll('.media-card');
            mediaCards.forEach(card => {
                card.style.cursor = 'pointer';
                card.addEventListener('click', function() {
                    const fileUrl = this.dataset.fileUrl;
                    const fileName = this.querySelector('.card-title').innerText;
                    window.parent.postMessage({
                        mceAction: 'fileSelected',
                        url: fileUrl,
                        title: fileName
                    }, '*');
                });
            });

            // Hide upload area and filter for TinyMCE mode
            const uploadCard = document.querySelector('.card.mb-4 .card-header h5.card-title.mb-0');
            if (uploadCard && uploadCard.innerText === 'Upload New File') {
                uploadCard.closest('.card').style.display = 'none';
            }
            const filterForm = document.querySelector('form.row.g-3');
            if (filterForm) {
                filterForm.style.display = 'none';
            }
        } else {
            // Existing copy to clipboard functionality for regular view
            document.querySelectorAll('.copy-url-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const url = this.dataset.url;
                    navigator.clipboard.writeText(url).then(() => {
                        alert('URL copied to clipboard!');
                    }).catch(err => {
                        console.error('Failed to copy URL: ', err);
                    });
                });
            });
        }
    });
</script>