<?php
$page_title = 'Navigation';
include 'includes/header.php';

$db = new Database();
$conn = $db->connect();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$success_message = '';
$error_message = '';

$navigation_items = [];
$navigation_item = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Protection
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid CSRF token. Please try again.';
        if (!headers_sent()) {
            header('Location: navigation.php?error=' . urlencode($error_message));
            exit();
        }
    }
    

    $name = sanitize($_POST['name']);
    $url = sanitize($_POST['url']);
    $display_order = (int)$_POST['display_order'];
    $status = $_POST['status'];

    $errors = [];

    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (empty($url)) {
        $errors[] = 'URL is required.';
    }
    if (!filter_var($url, FILTER_VALIDATE_URL) && !preg_match('/^[a-zA-Z0-9_\-]+\.php(\?.*)?$/', $url) && !preg_match('/^page\.php\?slug=[a-zA-Z0-9_\-]+$/', $url)) {
        $errors[] = 'Invalid URL format. Must be a valid URL or a relative path to a .php file (e.g., index.php, page.php?slug=about).';
    }
    if (!in_array($status, ['active', 'inactive'])) {
        $errors[] = 'Invalid status selected.';
    }

    if (empty($errors)) {
        if ($action == 'new') {
            // Create new navigation item
            try {
                $sql = "INSERT INTO navigation_items (name, url, display_order, status) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$name, $url, $display_order, $status])) {
                    $success_message = 'Navigation item created successfully!';
                    $action = 'list';
                    logActivity($_SESSION['user_id'], 'Created navigation item', 'navigation_item', $conn->lastInsertId());
                } else {
                    $error_message = 'Failed to create navigation item.';
                }
            } catch (PDOException $e) {
                $error_message = 'Table navigation_items not found in database.';
            }
        } elseif ($action == 'edit' && $id) {
            // Update existing navigation item
            try {
                $sql = "UPDATE navigation_items SET name=?, url=?, display_order=?, status=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$name, $url, $display_order, $status, $id])) {
                    $success_message = 'Navigation item updated successfully!';
                    $action = 'list';
                    logActivity($_SESSION['user_id'], 'Updated navigation item', 'navigation_item', $id);
                } else {
                    $error_message = 'Failed to update navigation item.';
                }
            } catch (PDOException $e) {
                $error_message = 'Table navigation_items not found in database.';
            }
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Handle delete action
if ($action == 'delete' && $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM navigation_items WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success_message = 'Navigation item deleted successfully!';
            logActivity($_SESSION['user_id'], 'Deleted navigation item', 'navigation_item', $id);
        } else {
            $error_message = 'Failed to delete navigation item.';
        }
    } catch (PDOException $e) {
        $error_message = 'Table navigation_items not found in database.';
    }
    $action = 'list';
}

// Get navigation item for editing
if ($action == 'edit' && $id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM navigation_items WHERE id = ?");
        $stmt->execute([$id]);
        $navigation_item = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $navigation_item = null;
        $error_message = 'Table navigation_items not found in database.';
    }
}

// Get all navigation items for listing
if ($action == 'list') {
    $search_query = $_GET['search'] ?? '';
    $status_filter = $_GET['status_filter'] ?? '';
    $sql = "SELECT * FROM navigation_items WHERE 1=1";
    $params = [];

    if (!empty($search_query)) {
        $sql .= " AND (name LIKE ? OR url LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }

    if (!empty($status_filter)) {
        $sql .= " AND status = ?";
        $params[] = $status_filter;
    }

    $sql .= " ORDER BY display_order ASC";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $navigation_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $navigation_items = [];
        $error_message = 'Table navigation_items not found in database.';
    }
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

<?php if ($action == 'list'): ?>
    <!-- Navigation Items List -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Navigation Items</h1>
        <a href="?action=new" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Item
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center mb-4">
                <input type="hidden" name="action" value="list">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search items..." value="<?php echo $_GET['search'] ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <select name="status_filter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo (($_GET['status_filter'] ?? '') == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo (($_GET['status_filter'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
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
                            <th>Name</th>
                            <th>URL</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($navigation_items): ?>
                        <?php foreach ($navigation_items as $item): ?>
                            <tr>
                                <td><?php echo $item['name']; ?></td>
                                <td><?php echo $item['url']; ?></td>
                                <td><?php echo $item['display_order']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($item['status'] == 'active') ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                        <a href="?action=delete&id=<?php echo $item['id']; ?>" class="btn btn-outline-danger delete-btn" data-item="navigation item">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No navigation items found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($action == 'new' || $action == 'edit'): ?>
    <!-- Navigation Item Form -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><?php echo $action == 'new' ? 'Add New Navigation Item' : 'Edit Navigation Item'; ?></h1>
        <a href="?action=list" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>
    
    <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Name *</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $navigation_item['name'] ?? ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="url" class="form-label">URL *</label>
                    <input type="text" class="form-control" id="url" name="url" value="<?php echo $navigation_item['url'] ?? ''; ?>" required>
                    <small class="form-text text-muted">e.g., index.php, page.php?slug=about, https://external.com</small>
                </div>
                
                <div class="mb-3">
                    <label for="display_order" class="form-label">Display Order</label>
                    <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $navigation_item['display_order'] ?? 0; ?>">
                    <small class="form-text text-muted">Items with lower numbers appear first.</small>
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?php echo ($navigation_item['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($navigation_item['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i><?php echo $action == 'new' ? 'Add Item' : 'Update Item'; ?>
                </button>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>