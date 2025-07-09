<?php
$page_title = 'Pages';
include 'includes/header.php';

// Inisialisasi $id agar tidak undefined
$id = $_GET['id'] ?? null;

// Global variables for TinyMCE
$pageContentType = 'page';
$pageContentId = json_encode($id);

// Pastikan $pages selalu terdefinisi
$pages = [];

$db = new Database();
$conn = $db->connect();

$action = $_GET['action'] ?? 'list';

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $slug = generateSlug($_POST['slug'] ?: $title);
    $content = $_POST['content'];
    $status = $_POST['status'];

    $errors = [];

    // Validation
    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    if (strlen($title) > 255) {
        $errors[] = 'Title cannot exceed 255 characters.';
    }
    if (empty($content)) {
        $errors[] = 'Content is required.';
    }
    if (!empty($slug) && !preg_match('/^[a-z0-9-]+$/', $slug)) {
        $errors[] = 'Slug can only contain lowercase letters, numbers, and hyphens.';
    }
    if (!in_array($status, ['draft', 'published'])) {
        $errors[] = 'Invalid status selected.';
    }

    if (empty($errors)) {
        if ($action == 'new') {
            // Create new page
            $sql = "INSERT INTO pages (title, slug, content, status, created_by) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute([$title, $slug, $content, $status, $_SESSION['user_id']])) {
                $success_message = 'Page created successfully!';
                $action = 'list';
                logActivity($_SESSION['user_id'], 'Created page', 'page', $conn->lastInsertId());
            } else {
                $error_message = 'Failed to create page.';
            }
        } elseif ($action == 'edit' && $id) {
            // Update existing page
            $sql = "UPDATE pages SET title=?, slug=?, content=?, status=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute([$title, $slug, $content, $status, $id])) {
                $success_message = 'Page updated successfully!';
                $action = 'list';
                logActivity($_SESSION['user_id'], 'Updated page', 'page', $id);
            } else {
                $error_message = 'Failed to update page.';
            }
        }
    }
} else {
    $error_message = implode('<br>', $errors);
}

// Handle delete action
if ($action == 'delete' && $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM pages WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success_message = 'Page deleted successfully!';
        } else {
            $error_message = 'Failed to delete page.';
        }
    } catch (PDOException $e) {
        $error_message = 'Failed to delete page: ' . $e->getMessage();
    }
    header('Location: pages.php?action=list&msg=' . urlencode($success_message ?: $error_message));
    exit();
}

// Get page for editing
$page = null;
if ($action == 'edit' && $id) {
    $stmt = $conn->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Ambil semua pages untuk listing
if ($action == 'list') {
    $search_query = $_GET['search'] ?? '';
    $status_filter = $_GET['status_filter'] ?? '';
    $sql = "SELECT p.*, u.username FROM pages p LEFT JOIN users u ON p.created_by = u.id WHERE 1=1";
    $params = [];
    if (!empty($search_query)) {
        $sql .= " AND (p.title LIKE ? OR p.content LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }
    if (!empty($status_filter)) {
        $sql .= " AND p.status = ?";
        $params[] = $status_filter;
    }
    $sql .= " ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_action'])) {
    $bulk_action = $_POST['bulk_action'];
    $selected_pages = $_POST['selected_pages'] ?? [];

    if (!empty($selected_pages)) {
        $placeholders = implode(',', array_fill(0, count($selected_pages), '?'));
        $success_count = 0;
        $error_count = 0;

        if ($bulk_action == 'delete') {
            $stmt = $conn->prepare("UPDATE pages SET deleted_at = NOW() WHERE id IN ($placeholders)");
            if ($stmt->execute($selected_pages)) {
                $success_count = $stmt->rowCount();
                logActivity($_SESSION['user_id'], 'Bulk moved pages to trash', 'page', implode(',', $selected_pages));
            } else {
                $error_count = count($selected_pages);
            }
        } elseif (in_array($bulk_action, ['publish', 'draft'])) {
            $new_status = str_replace(['publish'], ['published'], $bulk_action);
            $stmt = $conn->prepare("UPDATE pages SET status = ? WHERE id IN ($placeholders)");
            $bulk_params = array_merge([$new_status], $selected_pages);
            if ($stmt->execute($bulk_params)) {
                $success_count = $stmt->rowCount();
                logActivity($_SESSION['user_id'], 'Bulk updated page status to ' . $new_status, 'page', implode(',', $selected_pages));
            } else {
                $error_count = count($selected_pages);
            }
        }

        if ($success_count > 0) {
            $success_message = $success_count . ' page(s) ' . str_replace(['publish'], ['published'], $bulk_action) . ' successfully!';
        }
        if ($error_count > 0) {
            $error_message = $error_count . ' page(s) failed to ' . $bulk_action . '.';
        }
    }
    // Redirect to clear POST data and show updated list
    redirect(ADMIN_URL . '/pages.php?action=list');
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

<?php if (isset($_GET['msg']) && $_GET['msg']) {
    echo '<div class="alert alert-success alert-dismissible fade show">' . htmlspecialchars($_GET['msg']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
} ?>

<?php if ($action == 'list'): ?>
    <!-- Pages List -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">All Pages</h1>
        <a href="?action=new" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>New Page
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center mb-4">
                <input type="hidden" name="action" value="list">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search pages..." value="<?php echo $_GET['search'] ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <select name="status_filter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="draft" <?php echo (($_GET['status_filter'] ?? '') == 'draft') ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo (($_GET['status_filter'] ?? '') == 'published') ? 'selected' : ''; ?>>Published</option>
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
                            <option value="delete">Delete</option>
                            <option value="publish">Change Status to Published</option>
                            <option value="draft">Change Status to Draft</option>
                        </select>
                        <button type="submit" class="btn btn-info" onclick="return confirm('Are you sure you want to apply this bulk action?');">Apply</button>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="select-all-pages">
                        <label class="form-check-label" for="select-all-pages">
                            Select All
                        </label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all-pages"></th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Author</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($pages)): ?>
                            <?php foreach ($pages as $page): ?>
                                <tr>
                                    <td><input type="checkbox" name="selected_pages[]" value="<?php echo $page['id']; ?>" class="page-checkbox"></td>
                                    <td>
                                        <strong><?php echo $page['title']; ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo $page['slug']; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            if ($page['status'] == 'published') echo 'success';
                                            else if ($page['status'] == 'draft') echo 'secondary';
                                            else echo 'warning'; // Fallback for unknown status
                                        ?>">
                                            <?php echo ucfirst($page['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $page['username']; ?></td>
                                    <td><?php echo formatDate($page['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?action=edit&id=<?php echo $page['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                            <a href="?action=delete&id=<?php echo $page['id']; ?>" class="btn btn-outline-danger delete-btn" data-item="page">Delete</a>
                                            <?php if ($page['status'] == 'published'): ?>
                                                <a href="../page.php?slug=<?php echo $page['slug']; ?>" class="btn btn-outline-success" target="_blank">View</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No pages found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($action == 'new' || $action == 'edit'): ?>
    <!-- Page Form -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><?php echo $action == 'new' ? 'New Page' : 'Edit Page'; ?></h1>
        <a href="?action=list" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>
    
    <form method="POST">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Content</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $page['title'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="<?php echo $page['slug'] ?? ''; ?>">
                            <small class="form-text text-muted">Leave blank to auto-generate from title</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Content *</label>
                            <textarea name="content" class="tinymce" id="content" name="content" rows="20"><?php echo $page['content'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="draft" <?php echo ($page['status'] ?? '') == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($page['status'] ?? '') == 'published' ? 'selected' : ''; ?>>Published</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i><?php echo $action == 'new' ? 'Create Page' : 'Update Page'; ?>
                        </button>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Preview</h6>
                    </div>
                    <div class="card-body" id="preview-panel">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>