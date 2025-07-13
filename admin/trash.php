<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Trash';
include 'includes/header.php';

$db = new Database();
$conn = $db->connect();

$success_message = '';
$error_message = '';
$type = $_GET['type'] ?? 'articles';
$allowed_types = ['articles', 'projects', 'tools', 'files', 'pages'];
if (!in_array($type, $allowed_types)) $type = 'articles';

// Handle restore
if (isset($_GET['action'], $_GET['id'], $_GET['type']) && $_GET['action'] === 'restore' && in_array($_GET['type'], $allowed_types)) {
    $id = (int)$_GET['id'];
    $table = $_GET['type'];
    $stmt = $conn->prepare("UPDATE $table SET deleted_at = NULL WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success_message = ucfirst($table) . ' restored successfully!';
        logActivity($_SESSION['user_id'], 'Restored item from trash', $table, $id);
    } else {
        $error_message = 'Failed to restore item.';
    }
}
// Handle permanent delete
if (isset($_GET['action'], $_GET['id'], $_GET['type']) && $_GET['action'] === 'delete' && in_array($_GET['type'], $allowed_types)) {
    $id = (int)$_GET['id'];
    $table = $_GET['type'];
    $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success_message = ucfirst($table) . ' deleted permanently!';
        logActivity($_SESSION['user_id'], 'Permanently deleted item from trash', $table, $id);
    } else {
        $error_message = 'Failed to delete item.';
    }
}

// Fetch trashed items
$trashed = [];
foreach ($allowed_types as $t) {
    $sql = "SELECT * FROM $t WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $trashed[$t] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTitle($item, $type) {
    if ($type === 'files') return $item['original_name'] ?? $item['filename'];
    if ($type === 'pages') return $item['title'] ?? $item['slug'];
    return $item['title'] ?? $item['name'] ?? $item['slug'] ?? '';
}
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-trash me-2"></i>Trash Management</h1>
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
    <ul class="nav nav-tabs mb-3">
        <?php foreach ($allowed_types as $t): ?>
            <li class="nav-item">
                <a class="nav-link<?php if ($type == $t) echo ' active'; ?>" href="?type=<?php echo $t; ?>"><?php echo ucfirst($t); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Trashed <?php echo ucfirst($type); ?></h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Title/Name</th>
                            <th>Deleted At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trashed[$type])): ?>
                            <tr><td colspan="4" class="text-center py-4"><i class="fas fa-trash fa-3x text-muted mb-3"></i><p class="text-muted">No trashed <?php echo $type; ?> found.</p></td></tr>
                        <?php else: ?>
                            <?php foreach ($trashed[$type] as $i => $item): ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td><?php echo htmlspecialchars(getTitle($item, $type)); ?></td>
                                    <td><?php echo formatDateTime($item['deleted_at']); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="?action=restore&id=<?php echo $item['id']; ?>&type=<?php echo $type; ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Restore this item?')"><i class="fas fa-undo"></i> Restore</a>
                                            <a href="?action=delete&id=<?php echo $item['id']; ?>&type=<?php echo $type; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete permanently?')"><i class="fas fa-trash"></i> Delete</a>
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
