<?php
ob_start();
require_once __DIR__ . '/../config/config.php';
$page_title = 'Trash';

// Initialize variables to prevent undefined variable errors
$tab = $_GET['tab'] ?? 'articles';
$success_message = '';
$error_message = '';

$db = new Database();
$conn = $db->connect();

// Handle restore and permanent delete actions
if (isset($_GET['type']) && isset($_GET['id'])) {
    $type = $_GET['type'];
    $id = $_GET['id'];
    
    try {
        if (isset($_GET['restore'])) {
            $stmt = $conn->prepare("UPDATE $type SET deleted_at = NULL WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success_message = ucfirst($type) . ' restored successfully!';
                logActivity($_SESSION['user_id'], "Restored $type", $type, $id);
            } else {
                $error_message = 'Failed to restore ' . $type . '.';
            }
        } elseif (isset($_GET['delete'])) {
            $stmt = $conn->prepare("DELETE FROM $type WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success_message = ucfirst($type) . ' permanently deleted!';
                logActivity($_SESSION['user_id'], "Permanently deleted $type", $type, $id);
            } else {
                $error_message = 'Failed to delete ' . $type . '.';
            }
        }
    } catch (PDOException $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
    
    if (!headers_sent()) {
        header('Location: trash.php?tab=' . $tab . '&msg=' . urlencode($success_message ?: $error_message));
        ob_end_clean();
        exit();
    }
}

if (isset($_GET['msg'])) {
    $success_message = $_GET['msg'];
}

// Fetch soft-deleted items with better error handling
function getTrashed($conn, $table) {
    try {
        // Check if table exists and has deleted_at column
        $checkStmt = $conn->prepare("SHOW TABLES LIKE ?");
        $checkStmt->execute([$table]);
        if (!$checkStmt->fetch()) {
            return [];
        }
        
        // Check if deleted_at column exists
        $columnStmt = $conn->prepare("SHOW COLUMNS FROM $table LIKE 'deleted_at'");
        $columnStmt->execute();
        if (!$columnStmt->fetch()) {
            return [];
        }
        
        $sql = "SELECT * FROM $table WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getTrashed for table $table: " . $e->getMessage());
        return [];
    }
}

$trashed_articles = getTrashed($conn, 'articles');
$trashed_projects = getTrashed($conn, 'projects');
$trashed_tools = getTrashed($conn, 'tools');
$trashed_media = getTrashed($conn, 'files');

include 'includes/header.php';
// include 'includes/navigation.php';
?>
<div class="container-fluid">
    <h1 class="h2 mb-4">Trash</h1>
<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php elseif ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link<?php if ($tab == 'articles') echo ' active'; ?>" href="?tab=articles">Articles</a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?php if ($tab == 'projects') echo ' active'; ?>" href="?tab=projects">Projects</a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?php if ($tab == 'tools') echo ' active'; ?>" href="?tab=tools">Tools</a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?php if ($tab == 'media') echo ' active'; ?>" href="?tab=media">Media</a>
        </li>
    </ul>
    <div class="card">
    <div class="card-body">
            <?php if ($tab == 'articles'): ?>
                <h5 class="mb-3">Trashed Articles</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Deleted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($trashed_articles): foreach ($trashed_articles as $a): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($a['title']); ?></strong></td>
                                <td><span class="badge bg-secondary"><?php echo ucfirst($a['status']); ?></span></td>
                                <td><?php echo $a['deleted_at']; ?></td>
                                <td>
                                    <a href="?tab=articles&type=articles&id=<?php echo $a['id']; ?>&restore=1" class="btn btn-success btn-sm">Restore</a>
                                    <a href="?tab=articles&type=articles&id=<?php echo $a['id']; ?>&delete=1" class="btn btn-danger btn-sm" onclick="return confirm('Permanently delete this article?');">Delete Permanently</a>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No trashed articles.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
            </div>
            <?php elseif ($tab == 'projects'): ?>
                <h5 class="mb-3">Trashed Projects</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Deleted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($trashed_projects): foreach ($trashed_projects as $p): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($p['title']); ?></strong></td>
                                <td><span class="badge bg-secondary"><?php echo ucfirst($p['status']); ?></span></td>
                                <td><?php echo $p['deleted_at']; ?></td>
                                <td>
                                    <a href="?tab=projects&type=projects&id=<?php echo $p['id']; ?>&restore=1" class="btn btn-success btn-sm">Restore</a>
                                    <a href="?tab=projects&type=projects&id=<?php echo $p['id']; ?>&delete=1" class="btn btn-danger btn-sm" onclick="return confirm('Permanently delete this project?');">Delete Permanently</a>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No trashed projects.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
            </div>
            <?php elseif ($tab == 'tools'): ?>
                <h5 class="mb-3">Trashed Tools</h5>
        <div class="table-responsive">
                    <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Title</th>
                                <th>Status</th>
                                <th>Deleted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                        <?php if ($trashed_tools): foreach ($trashed_tools as $t): ?>
                        <tr>
                                <td><strong><?php echo htmlspecialchars($t['title']); ?></strong></td>
                                <td><span class="badge bg-secondary"><?php echo ucfirst($t['status']); ?></span></td>
                                <td><?php echo $t['deleted_at']; ?></td>
                                <td>
                                    <a href="?tab=tools&type=tools&id=<?php echo $t['id']; ?>&restore=1" class="btn btn-success btn-sm">Restore</a>
                                    <a href="?tab=tools&type=tools&id=<?php echo $t['id']; ?>&delete=1" class="btn btn-danger btn-sm" onclick="return confirm('Permanently delete this tool?');">Delete Permanently</a>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No trashed tools.</td></tr>
                                <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($tab == 'media'): ?>
                <h5 class="mb-3">Trashed Media</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Delete By</th>
                                <th>Deleted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                                <?php 
                        function formatSize($bytes) {
                            if ($bytes >= 1073741824) {
                                return number_format($bytes / 1073741824, 2) . ' GB';
                            } elseif ($bytes >= 1048576) {
                                return number_format($bytes / 1048576, 2) . ' MB';
                            } elseif ($bytes >= 1024) {
                                return number_format($bytes / 1024, 2) . ' KB';
                            } elseif ($bytes > 1) {
                                return $bytes . ' bytes';
                            } elseif ($bytes == 1) {
                                return '1 byte';
                            } else {
                                return '-';
                            }
                        }
                        // Get user id->username for quick lookup
                        $usernames = [];
                        try {
                            $userStmt = $conn->query("SELECT id, username FROM users");
                            foreach ($userStmt->fetchAll(PDO::FETCH_ASSOC) as $u) {
                                $usernames[$u['id']] = $u['username'];
                            }
                        } catch (PDOException $e) {
                            // Handle case where users table doesn't exist
                        }
                        
                        if ($trashed_media): foreach ($trashed_media as $f): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($f['original_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($f['file_type']); ?></td>
                                <td><?php echo formatSize($f['file_size']); ?></td>
                                <td><?php echo isset($usernames[$f['uploaded_by']]) ? htmlspecialchars($usernames[$f['uploaded_by']]) : 'Unknown'; ?></td>
                                <td><?php echo $f['deleted_at']; ?></td>
                                <td>
                                    <a href="?tab=media&type=files&id=<?php echo $f['id']; ?>&restore=1" class="btn btn-success btn-sm">Restore</a>
                                    <a href="?tab=media&type=files&id=<?php echo $f['id']; ?>&delete=1" class="btn btn-danger btn-sm" onclick="return confirm('Permanently delete this file?');">Delete Permanently</a>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-center text-muted">No trashed media files.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>