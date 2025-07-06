<?php
$page_title = 'Trash';
include 'includes/header.php';

$db = new Database();
$conn = $db->connect();

$success_message = '';
$error_message = '';

// Handle restore action
if (isset($_GET['action']) && $_GET['action'] == 'restore' && isset($_GET['id']) && isset($_GET['item_type'])) {
    $id = $_GET['id'];
    $item_type = $_GET['item_type'];
    $table = '';
    $log_type = '';

    switch ($item_type) {
        case 'file':
            $table = 'files';
            $log_type = 'file';
            break;
        case 'article':
            $table = 'articles';
            $log_type = 'article';
            break;
        case 'project':
            $table = 'projects';
            $log_type = 'project';
            break;
        case 'tool':
            $table = 'tools';
            $log_type = 'tool';
            break;
        default:
            $error_message = 'Invalid item type for restore.';
            break;
    }

    if (!empty($table)) {
        $stmt = $conn->prepare("UPDATE {$table} SET deleted_at = NULL WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success_message = ucfirst($item_type) . ' restored successfully!';
            logActivity($_SESSION['user_id'], 'Restored ' . $log_type . ' from trash', $log_type, $id);
        } else {
            $error_message = 'Failed to restore ' . $item_type . '.';
        }
    }
}

// Handle permanent delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete_permanently' && isset($_GET['id']) && isset($_GET['item_type'])) {
    $id = $_GET['id'];
    $item_type = $_GET['item_type'];
    $table = '';
    $log_type = '';

    switch ($item_type) {
        case 'file':
            $table = 'files';
            $log_type = 'file';
            break;
        case 'article':
            $table = 'articles';
            $log_type = 'article';
            break;
        case 'project':
            $table = 'projects';
            $log_type = 'project';
            break;
        case 'tool':
            $table = 'tools';
            $log_type = 'tool';
            break;
        default:
            $error_message = 'Invalid item type for permanent delete.';
            break;
    }

    if (!empty($table)) {
        if ($item_type == 'file') {
            // Get file info from database for file system deletion
            $stmt = $conn->prepare("SELECT file_path FROM files WHERE id = ?");
            $stmt->execute([$id]);
            $file = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($file) {
                $full_path = '../' . $file['file_path'];
                $stmt = $conn->prepare("DELETE FROM {$table} WHERE id = ?");
                if ($stmt->execute([$id])) {
                    if (file_exists($full_path)) {
                        unlink($full_path);
                    }
                    $success_message = ucfirst($item_type) . ' permanently deleted!';
                    logActivity($_SESSION['user_id'], 'Permanently deleted ' . $log_type, $log_type, $id);
                } else {
                    $error_message = 'Failed to permanently delete ' . $item_type . ' from database.';
                }
            } else {
                $error_message = ucfirst($item_type) . ' not found.';
            }
        } else {
            $stmt = $conn->prepare("DELETE FROM {$table} WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success_message = ucfirst($item_type) . ' permanently deleted!';
                logActivity($_SESSION['user_id'], 'Permanently deleted ' . $log_type, $log_type, $id);
            } else {
                $error_message = 'Failed to permanently delete ' . $item_type . ' from database.';
            }
        }
    }
}

// Get all trashed items for listing
$search_query = $_GET['search'] ?? '';
$item_type_filter = $_GET['item_type_filter'] ?? ''; // New filter for item type

$sql_parts = [];
$params = [];

// Files
$sql_parts[] = "SELECT id, original_name AS title, file_type AS type_specific, deleted_at, uploaded_by AS created_by, 'file' AS item_type, file_path FROM files WHERE deleted_at IS NOT NULL";
// Articles
$sql_parts[] = "SELECT id, title, status AS type_specific, deleted_at, created_by, 'article' AS item_type, NULL AS file_path FROM articles WHERE deleted_at IS NOT NULL";
// Projects
$sql_parts[] = "SELECT id, title, status AS type_specific, deleted_at, created_by, 'project' AS item_type, NULL AS file_path FROM projects WHERE deleted_at IS NOT NULL";
// Tools
$sql_parts[] = "SELECT id, title, category AS type_specific, deleted_at, created_by, 'tool' AS item_type, NULL AS file_path FROM tools WHERE deleted_at IS NOT NULL";

$full_sql = implode(" UNION ALL ", $sql_parts);

$where_clauses = [];

if (!empty($search_query)) {
    $where_clauses[] = "(title LIKE ? OR type_specific LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
}

if (!empty($item_type_filter)) {
    $where_clauses[] = "item_type = ?";
    $params[] = $item_type_filter;
}

$final_sql = "SELECT t.*, u.username FROM ($full_sql) t LEFT JOIN users u ON t.created_by = u.id";

if (!empty($where_clauses)) {
    $final_sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$final_sql .= " ORDER BY deleted_at DESC";

$stmt = $conn->prepare($final_sql);
$stmt->execute($params);
$trashed_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<h1 class="h2 mb-4">Trash</h1>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Trashed Items</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-center mb-4">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search items..." value="<?php echo $_GET['search'] ?? ''; ?>">
            </div>
            <div class="col-md-3">
                <select name="item_type_filter" class="form-select">
                    <option value="">All Types</option>
                    <option value="file" <?php echo (($_GET['item_type_filter'] ?? '') == 'file') ? 'selected' : ''; ?>>Files</option>
                    <option value="article" <?php echo (($_GET['item_type_filter'] ?? '') == 'article') ? 'selected' : ''; ?>>Articles</option>
                    <option value="project" <?php echo (($_GET['item_type_filter'] ?? '') == 'project') ? 'selected' : ''; ?>>Projects</option>
                    <option value="tool" <?php echo (($_GET['item_type_filter'] ?? '') == 'tool') ? 'selected' : ''; ?>>Tools</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Deleted Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($trashed_items): ?>
                    <?php foreach ($trashed_items as $item): ?>
                        <tr>
                            <td>
                                <strong><?php echo $item['title']; ?></strong>
                                <?php if ($item['item_type'] == 'file'): ?>
                                    <br>
                                    <small class="text-muted"><?php echo $item['file_path']; ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo ucfirst($item['item_type']); ?></td>
                            <td>
                                <?php 
                                    if ($item['item_type'] == 'file') {
                                        echo 'File Type: ' . $item['type_specific'];
                                    } else if ($item['item_type'] == 'article' || $item['item_type'] == 'project') {
                                        echo 'Status: ' . ucfirst($item['type_specific']);
                                    } else if ($item['item_type'] == 'tool') {
                                        echo 'Category: ' . $item['type_specific'];
                                    }
                                ?>
                            </td>
                            <td><?php echo formatDate($item['deleted_at']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="?action=restore&id=<?php echo $item['id']; ?>&item_type=<?php echo $item['item_type']; ?>" class="btn btn-outline-success"><i class="fas fa-undo"></i> Restore</a>
                                    <a href="?action=delete_permanently&id=<?php echo $item['id']; ?>&item_type=<?php echo $item['item_type']; ?>" class="btn btn-outline-danger delete-btn" data-item="<?php echo $item['item_type']; ?> permanently"><i class="fas fa-times-circle"></i> Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fas fa-trash-alt fa-3x mb-3"></i>
                            <p>No items in trash.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>