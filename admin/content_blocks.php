<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Content Blocks';
include 'includes/header.php';

// --- Setup ---
$db = new Database();
$conn = $db->connect();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$tab = $_GET['tab'] ?? 'blocks';

$success_message = '';
$error_message = '';
$errors = [];

// --- Handle Tabs ---
if (isset($_GET['tab']) && in_array($_GET['tab'], ['blocks', 'types'])) {
    $tab = $_GET['tab'];
}

// --- Content Blocks Management Logic ---
$content_blocks = [];
$content_block = null;
$content_block_types = [];

if ($tab === 'blocks') {
    // Handle form submissions for content blocks
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form_type'] ?? '') === 'content_block') {
        $name = sanitize($_POST['name'] ?? '');
        $title = sanitize($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $type = sanitize($_POST['type'] ?? '');
        $icon_class = sanitize($_POST['icon_class'] ?? '');
        $display_order = (int)($_POST['display_order'] ?? 0);
        $page_slug = sanitize($_POST['page_slug'] ?? '');
        $status = $_POST['status'] ?? 'active';
        
        // Validation
        if (empty($name)) $errors[] = 'Name is required.';
        if (empty($type)) $errors[] = 'Type is required.';
        if (strlen($name) > 100) $errors[] = 'Name cannot exceed 100 characters.';
        if (strlen($title) > 255) $errors[] = 'Title cannot exceed 255 characters.';
        if (!in_array($status, ['active', 'inactive'])) $errors[] = 'Invalid status selected.';
        if ($display_order < 0) $errors[] = 'Display order must be 0 or greater.';
        
        // Check if name is unique (except for current record when editing)
        if (empty($errors)) {
            $check_sql = "SELECT id FROM content_blocks WHERE name = ?";
            $check_params = [$name];
            if ($action == 'edit' && $id) {
                $check_sql .= " AND id != ?";
                $check_params[] = $id;
            }
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute($check_params);
            if ($check_stmt->fetch()) {
                $errors[] = 'Name must be unique.';
            }
        }

        if (empty($errors)) {
            if ($action == 'new') {
                $sql = "INSERT INTO content_blocks (name, title, content, type, icon_class, display_order, page_slug, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$name, $title, $content, $type, $icon_class, $display_order, $page_slug, $status])) {
                    $success_message = 'Content block created successfully!';
                    $action = 'list';
                    logActivity($_SESSION['user_id'], 'Created content block', 'content_block', $conn->lastInsertId());
                    if (!headers_sent()) {
                        header('Location: content_blocks.php?action=list&tab=blocks&msg=' . urlencode($success_message));
                        ob_end_clean();
                        exit();
                    }
                } else {
                    $error_message = 'Failed to create content block.';
                }
            } elseif ($action == 'edit' && $id) {
                $sql = "UPDATE content_blocks SET name=?, title=?, content=?, type=?, icon_class=?, display_order=?, page_slug=?, status=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$name, $title, $content, $type, $icon_class, $display_order, $page_slug, $status, $id])) {
                    $success_message = 'Content block updated successfully!';
                    $action = 'list';
                    logActivity($_SESSION['user_id'], 'Updated content block', 'content_block', $id);
                    if (!headers_sent()) {
                        header('Location: content_blocks.php?action=list&tab=blocks&msg=' . urlencode($success_message));
                        ob_end_clean();
                        exit();
                    }
                } else {
                    $error_message = 'Failed to update content block.';
                }
            }
        } else {
            $error_message = implode('<br>', $errors);
        }
    }
    
    // Handle delete action for content blocks
    if ($action == 'delete' && $id) {
        $stmt = $conn->prepare("DELETE FROM content_blocks WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success_message = 'Content block deleted successfully!';
            logActivity($_SESSION['user_id'], 'Deleted content block', 'content_block', $id);
        } else {
            $error_message = 'Failed to delete content block.';
        }
        $action = 'list';
        if (!headers_sent()) {
            header('Location: content_blocks.php?action=list&tab=blocks&msg=' . urlencode($success_message ?: $error_message));
            ob_end_clean();
            exit();
        }
    }
    
    // Get content block for editing
    if ($action == 'edit' && $id) {
        $stmt = $conn->prepare("SELECT * FROM content_blocks WHERE id = ?");
        $stmt->execute([$id]);
        $content_block = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get all content blocks for listing
    if ($action == 'list') {
        $search_query = $_GET['search'] ?? '';
        $type_filter = $_GET['type_filter'] ?? '';
        $status_filter = $_GET['status_filter'] ?? '';
        $page_filter = $_GET['page_filter'] ?? '';
        
        $sql = "SELECT cb.*, cbt.display_name as type_display_name FROM content_blocks cb 
                LEFT JOIN content_block_types cbt ON cb.type = cbt.type_name 
                WHERE 1=1";
        $params = [];
        
        if (!empty($search_query)) {
            $sql .= " AND (cb.name LIKE ? OR cb.title LIKE ? OR cb.content LIKE ?)";
            $params[] = '%' . $search_query . '%';
            $params[] = '%' . $search_query . '%';
            $params[] = '%' . $search_query . '%';
        }
        if (!empty($type_filter)) {
            $sql .= " AND cb.type = ?";
            $params[] = $type_filter;
        }
        if (!empty($status_filter)) {
            $sql .= " AND cb.status = ?";
            $params[] = $status_filter;
        }
        if (!empty($page_filter)) {
            $sql .= " AND cb.page_slug = ?";
            $params[] = $page_filter;
        }
        
        $sql .= " ORDER BY cb.display_order ASC, cb.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $content_blocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get content block types for dropdown
    $stmt = $conn->prepare("SELECT * FROM content_block_types ORDER BY display_name");
    $stmt->execute();
    $content_block_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unique page slugs for filter
    $stmt = $conn->prepare("SELECT DISTINCT page_slug FROM content_blocks WHERE page_slug IS NOT NULL AND page_slug != '' ORDER BY page_slug");
    $stmt->execute();
    $page_slugs = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// --- Content Block Types Management Logic ---
$block_types = [];
$block_type = null;

if ($tab === 'types') {
    // Handle form submissions for block types
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form_type'] ?? '') === 'block_type') {
        $type_name = sanitize($_POST['type_name'] ?? '');
        $display_name = sanitize($_POST['display_name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        
        // Validation
        if (empty($type_name)) $errors[] = 'Type name is required.';
        if (empty($display_name)) $errors[] = 'Display name is required.';
        if (strlen($type_name) > 50) $errors[] = 'Type name cannot exceed 50 characters.';
        if (strlen($display_name) > 100) $errors[] = 'Display name cannot exceed 100 characters.';
        if (!preg_match('/^[a-z_]+$/', $type_name)) $errors[] = 'Type name can only contain lowercase letters and underscores.';
        
        // Check if type_name is unique (except for current record when editing)
        if (empty($errors)) {
            $check_sql = "SELECT id FROM content_block_types WHERE type_name = ?";
            $check_params = [$type_name];
            if ($action == 'edit' && $id) {
                $check_sql .= " AND id != ?";
                $check_params[] = $id;
            }
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute($check_params);
            if ($check_stmt->fetch()) {
                $errors[] = 'Type name must be unique.';
            }
        }

        if (empty($errors)) {
            if ($action == 'new') {
                $sql = "INSERT INTO content_block_types (type_name, display_name, description) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$type_name, $display_name, $description])) {
                    $success_message = 'Content block type created successfully!';
                    $action = 'list';
                    logActivity($_SESSION['user_id'], 'Created content block type', 'content_block_type', $conn->lastInsertId());
                    if (!headers_sent()) {
                        header('Location: content_blocks.php?action=list&tab=types&msg=' . urlencode($success_message));
                        ob_end_clean();
                        exit();
                    }
                } else {
                    $error_message = 'Failed to create content block type.';
                }
            } elseif ($action == 'edit' && $id) {
                $sql = "UPDATE content_block_types SET type_name=?, display_name=?, description=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$type_name, $display_name, $description, $id])) {
                    $success_message = 'Content block type updated successfully!';
                    $action = 'list';
                    logActivity($_SESSION['user_id'], 'Updated content block type', 'content_block_type', $id);
                    if (!headers_sent()) {
                        header('Location: content_blocks.php?action=list&tab=types&msg=' . urlencode($success_message));
                        ob_end_clean();
                        exit();
                    }
                } else {
                    $error_message = 'Failed to update content block type.';
                }
            }
        } else {
            $error_message = implode('<br>', $errors);
        }
    }
    
    // Handle delete action for block types
    if ($action == 'delete' && $id) {
        // Check if type is being used by any content blocks
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM content_blocks WHERE type = (SELECT type_name FROM content_block_types WHERE id = ?)");
        $check_stmt->execute([$id]);
        $usage_count = $check_stmt->fetchColumn();
        
        if ($usage_count > 0) {
            $error_message = "Cannot delete this type as it is being used by $usage_count content block(s).";
        } else {
            $stmt = $conn->prepare("DELETE FROM content_block_types WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success_message = 'Content block type deleted successfully!';
                logActivity($_SESSION['user_id'], 'Deleted content block type', 'content_block_type', $id);
            } else {
                $error_message = 'Failed to delete content block type.';
            }
        }
        $action = 'list';
        if (!headers_sent()) {
            header('Location: content_blocks.php?action=list&tab=types&msg=' . urlencode($success_message ?: $error_message));
            ob_end_clean();
            exit();
        }
    }
    
    // Get block type for editing
    if ($action == 'edit' && $id) {
        $stmt = $conn->prepare("SELECT * FROM content_block_types WHERE id = ?");
        $stmt->execute([$id]);
        $block_type = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get all block types for listing
    if ($action == 'list') {
        $search_query = $_GET['search'] ?? '';
        $sql = "SELECT cbt.*, COUNT(cb.id) as usage_count FROM content_block_types cbt 
                LEFT JOIN content_blocks cb ON cbt.type_name = cb.type 
                WHERE 1=1";
        $params = [];
        
        if (!empty($search_query)) {
            $sql .= " AND (cbt.type_name LIKE ? OR cbt.display_name LIKE ? OR cbt.description LIKE ?)";
            $params[] = '%' . $search_query . '%';
            $params[] = '%' . $search_query . '%';
            $params[] = '%' . $search_query . '%';
        }
        
        $sql .= " GROUP BY cbt.id ORDER BY cbt.display_name";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $block_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle bulk actions for content blocks
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_action']) && $tab === 'blocks') {
    $bulk_action = $_POST['bulk_action'];
    $selected_blocks = $_POST['selected_blocks'] ?? [];
    if (!empty($selected_blocks)) {
        $placeholders = implode(',', array_fill(0, count($selected_blocks), '?'));
        $success_count = 0;
        $error_count = 0;
        
        if ($bulk_action == 'delete') {
            $stmt = $conn->prepare("DELETE FROM content_blocks WHERE id IN ($placeholders)");
            if ($stmt->execute($selected_blocks)) {
                $success_count = count($selected_blocks);
                foreach ($selected_blocks as $bid) {
                    logActivity($_SESSION['user_id'], 'Bulk deleted content block', 'content_block', $bid);
                }
            } else {
                $error_count = count($selected_blocks);
            }
        } elseif ($bulk_action == 'activate') {
            $stmt = $conn->prepare("UPDATE content_blocks SET status = 'active' WHERE id IN ($placeholders)");
            if ($stmt->execute($selected_blocks)) {
                $success_count = count($selected_blocks);
                foreach ($selected_blocks as $bid) {
                    logActivity($_SESSION['user_id'], 'Bulk activated content block', 'content_block', $bid);
                }
            } else {
                $error_count = count($selected_blocks);
            }
        } elseif ($bulk_action == 'deactivate') {
            $stmt = $conn->prepare("UPDATE content_blocks SET status = 'inactive' WHERE id IN ($placeholders)");
            if ($stmt->execute($selected_blocks)) {
                $success_count = count($selected_blocks);
                foreach ($selected_blocks as $bid) {
                    logActivity($_SESSION['user_id'], 'Bulk deactivated content block', 'content_block', $bid);
                }
            } else {
                $error_count = count($selected_blocks);
            }
        }
        
        if ($success_count > 0) {
            $success_message = "Successfully processed $success_count content block(s).";
        }
        if ($error_count > 0) {
            $error_message = "Failed to process $error_count content block(s).";
        }
        
        if (!headers_sent()) {
            header('Location: content_blocks.php?action=list&tab=blocks&msg=' . urlencode($success_message ?: $error_message));
            ob_end_clean();
            exit();
        }
    }
}

// Display success/error messages
if (isset($_GET['msg'])) {
    $success_message = urldecode($_GET['msg']);
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Content Blocks Management</h1>
                <div>
                    <?php if ($tab === 'blocks'): ?>
                        <a href="?action=new&tab=blocks" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Block
                        </a>
                    <?php elseif ($tab === 'types'): ?>
                        <a href="?action=new&tab=types" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Type
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo $tab === 'blocks' ? 'active' : ''; ?>" href="?action=list&tab=blocks">
                        <i class="fas fa-cubes"></i> Content Blocks
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $tab === 'types' ? 'active' : ''; ?>" href="?action=list&tab=types">
                        <i class="fas fa-tags"></i> Block Types
                    </a>
                </li>
            </ul>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'blocks' && $action == 'list'): ?>
                <!-- Search and Filter for Content Blocks -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <input type="hidden" name="action" value="list">
                            <input type="hidden" name="tab" value="blocks">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search" placeholder="Search blocks..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="type_filter">
                                    <option value="">All Types</option>
                                    <?php foreach ($content_block_types as $type): ?>
                                        <option value="<?php echo $type['type_name']; ?>" <?php echo ($_GET['type_filter'] ?? '') === $type['type_name'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['display_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="status_filter">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo ($_GET['status_filter'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($_GET['status_filter'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="page_filter">
                                    <option value="">All Pages</option>
                                    <?php foreach ($page_slugs as $slug): ?>
                                        <option value="<?php echo $slug; ?>" <?php echo ($_GET['page_filter'] ?? '') === $slug ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($slug); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                            <div class="col-md-1">
                                <a href="?action=list&tab=blocks" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Content Blocks List -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" id="bulk-form">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <select class="form-select me-2" name="bulk_action" style="width: auto;">
                                        <option value="">Bulk Actions</option>
                                        <option value="activate">Activate</option>
                                        <option value="deactivate">Deactivate</option>
                                        <option value="delete">Delete</option>
                                    </select>
                                    <button type="submit" class="btn btn-outline-primary" onclick="return confirm('Are you sure you want to perform this action?')">
                                        Apply
                                    </button>
                                </div>
                                <div class="text-muted">
                                    <?php echo count($content_blocks); ?> block(s) found
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="30">
                                                <input type="checkbox" id="select-all">
                                            </th>
                                            <th>Name</th>
                                            <th>Title</th>
                                            <th>Type</th>
                                            <th>Page</th>
                                            <th width="100">Order</th>
                                            <th width="100">Status</th>
                                            <th width="150">Created</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($content_blocks)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">
                                                    <i class="fas fa-cubes fa-2x mb-2"></i>
                                                    <p>No content blocks found</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($content_blocks as $block): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="selected_blocks[]" value="<?php echo $block['id']; ?>" class="block-checkbox">
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($block['name']); ?></div>
                                                        <?php if ($block['icon_class']): ?>
                                                            <small class="text-muted">
                                                                <i class="<?php echo htmlspecialchars($block['icon_class']); ?>"></i>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div><?php echo htmlspecialchars($block['title']); ?></div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($block['type_display_name'] ?? $block['type']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($block['page_slug']): ?>
                                                            <span class="badge bg-light text-dark"><?php echo htmlspecialchars($block['page_slug']); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark"><?php echo $block['display_order']; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($block['status'] == 'active'): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php echo date('M j, Y', strtotime($block['created_at'])); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="?action=edit&id=<?php echo $block['id']; ?>&tab=blocks" class="btn btn-outline-primary" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="?action=delete&id=<?php echo $block['id']; ?>&tab=blocks" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this content block?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>

            <?php elseif ($tab === 'types' && $action == 'list'): ?>
                <!-- Search for Block Types -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <input type="hidden" name="action" value="list">
                            <input type="hidden" name="tab" value="types">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Search types..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="?action=list&tab=types" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Block Types List -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div></div>
                            <div class="text-muted">
                                <?php echo count($block_types); ?> type(s) found
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Type Name</th>
                                        <th>Display Name</th>
                                        <th>Description</th>
                                        <th width="100">Usage</th>
                                        <th width="150">Created</th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($block_types)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-tags fa-2x mb-2"></i>
                                                <p>No block types found</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($block_types as $type): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-medium"><?php echo htmlspecialchars($type['type_name']); ?></div>
                                                </td>
                                                <td>
                                                    <div><?php echo htmlspecialchars($type['display_name']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="text-muted">
                                                        <?php echo htmlspecialchars($type['description']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark"><?php echo $type['usage_count']; ?></span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('M j, Y', strtotime($type['created_at'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="?action=edit&id=<?php echo $type['id']; ?>&tab=types" class="btn btn-outline-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?action=delete&id=<?php echo $type['id']; ?>&tab=types" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this block type?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
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

            <?php elseif ($tab === 'blocks' && ($action == 'new' || $action == 'edit')): ?>
                <!-- Content Block Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <?php echo $action == 'new' ? 'Add New Content Block' : 'Edit Content Block'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="form_type" value="content_block">
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($content_block['name'] ?? ''); ?>" required>
                                        <div class="form-text">Unique identifier for this block</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($content_block['title'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Content</label>
                                        <textarea class="form-control" id="content" name="content" rows="6"><?php echo htmlspecialchars($content_block['content'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="type" name="type" required>
                                            <option value="">Select Type</option>
                                            <?php foreach ($content_block_types as $type): ?>
                                                <option value="<?php echo $type['type_name']; ?>" <?php echo ($content_block['type'] ?? '') === $type['type_name'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($type['display_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="icon_class" class="form-label">Icon Class</label>
                                        <input type="text" class="form-control" id="icon_class" name="icon_class" value="<?php echo htmlspecialchars($content_block['icon_class'] ?? ''); ?>" placeholder="fas fa-icon">
                                        <div class="form-text">FontAwesome icon class</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="display_order" class="form-label">Display Order</label>
                                        <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $content_block['display_order'] ?? 0; ?>" min="0">
                                        <div class="form-text">Lower numbers appear first</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="page_slug" class="form-label">Page Slug</label>
                                        <input type="text" class="form-control" id="page_slug" name="page_slug" value="<?php echo htmlspecialchars($content_block['page_slug'] ?? ''); ?>" placeholder="contact, about, etc.">
                                        <div class="form-text">Leave empty for global blocks</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo ($content_block['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo ($content_block['status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="?action=list&tab=blocks" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> 
                                        <?php echo $action == 'new' ? 'Create Block' : 'Update Block'; ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            <?php elseif ($tab === 'types' && ($action == 'new' || $action == 'edit')): ?>
                <!-- Block Type Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <?php echo $action == 'new' ? 'Add New Block Type' : 'Edit Block Type'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="form_type" value="block_type">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="type_name" class="form-label">Type Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="type_name" name="type_name" value="<?php echo htmlspecialchars($block_type['type_name'] ?? ''); ?>" required>
                                        <div class="form-text">Lowercase letters and underscores only</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="display_name" name="display_name" value="<?php echo htmlspecialchars($block_type['display_name'] ?? ''); ?>" required>
                                        <div class="form-text">Human-readable name</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($block_type['description'] ?? ''); ?></textarea>
                                        <div class="form-text">Brief description of this block type</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="?action=list&tab=types" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> 
                                        <?php echo $action == 'new' ? 'Create Type' : 'Update Type'; ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality for content blocks
    const selectAll = document.getElementById('select-all');
    const blockCheckboxes = document.querySelectorAll('.block-checkbox');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            blockCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // Update select all when individual checkboxes change
    blockCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(blockCheckboxes).every(cb => cb.checked);
            const anyChecked = Array.from(blockCheckboxes).some(cb => cb.checked);
            
            if (selectAll) {
                selectAll.checked = allChecked;
                selectAll.indeterminate = anyChecked && !allChecked;
            }
        });
    });
    
    // Auto-resize textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
        
        // Trigger on load for existing content
        if (textarea.value) {
            textarea.dispatchEvent(new Event('input'));
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
