<?php
$page_title = 'Content Blocks';
include 'includes/header.php';

$db = new Database();
$conn = $db->connect();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $title = sanitize($_POST['title']);
    $content = $_POST['content']; // Allow HTML content
    $type = sanitize($_POST['type']);
    $icon_class = sanitize($_POST['icon_class']);
    $display_order = (int)$_POST['display_order'];
    $page_slug = sanitize($_POST['page_slug']);
    $status = sanitize($_POST['status']);

    $errors = [];

    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (empty($type)) {
        $errors[] = 'Type is required.';
    }
    if (!in_array($status, ['active', 'inactive'])) {
        $errors[] = 'Invalid status selected.';
    }

    if (empty($errors)) {
        if ($action == 'new') {
            // Create new content block
            $sql = "INSERT INTO content_blocks (name, title, content, type, icon_class, display_order, page_slug, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute([$name, $title, $content, $type, $icon_class, $display_order, $page_slug, $status])) {
                $success_message = 'Content block created successfully!';
                $action = 'list';
                logActivity($_SESSION['user_id'], 'Created content block', 'content_block', $conn->lastInsertId());
            } else {
                $error_message = 'Failed to create content block.';
            }
        } elseif ($action == 'edit' && $id) {
            // Update existing content block
            $sql = "UPDATE content_blocks SET name=?, title=?, content=?, type=?, icon_class=?, display_order=?, page_slug=?, status=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute([$name, $title, $content, $type, $icon_class, $display_order, $page_slug, $status, $id])) {
                $success_message = 'Content block updated successfully!';
                $action = 'list';
                logActivity($_SESSION['user_id'], 'Updated content block', 'content_block', $id);
            } else {
                $error_message = 'Failed to update content block.';
            }
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Handle actions for content block types
if ($action == 'manage_types') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type_action'])) {
        $type_action = sanitize($_POST['type_action']);
        $type_name = sanitize($_POST['type_name']);
        $display_name = sanitize($_POST['display_name']);
        $description = sanitize($_POST['description']);
        $type_id = $_POST['type_id'] ?? null;

        $type_errors = [];

        if (empty($type_name)) {
            $type_errors[] = 'Type Name is required.';
        }
        if (empty($display_name)) {
            $type_errors[] = 'Display Name is required.';
        }

        if (empty($type_errors)) {
            if ($type_action == 'add_type') {
                // Check for uniqueness
                $stmt_check = $conn->prepare("SELECT COUNT(*) FROM content_block_types WHERE type_name = ?");
                $stmt_check->execute([$type_name]);
                if ($stmt_check->fetchColumn() > 0) {
                    $error_message = 'Type Name already exists.';
                } else {
                    $sql = "INSERT INTO content_block_types (type_name, display_name, description) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    if ($stmt->execute([$type_name, $display_name, $description])) {
                        $success_message = 'Content block type added successfully!';
                        logActivity($_SESSION['user_id'], 'Added content block type', 'content_block_type', $conn->lastInsertId());
                    } else {
                        $error_message = 'Failed to add content block type.';
                    }
                }
            } elseif ($type_action == 'edit_type' && $type_id) {
                // Check for uniqueness, excluding current type_id
                $stmt_check = $conn->prepare("SELECT COUNT(*) FROM content_block_types WHERE type_name = ? AND id != ?");
                $stmt_check->execute([$type_name, $type_id]);
                if ($stmt_check->fetchColumn() > 0) {
                    $error_message = 'Type Name already exists.';
                } else {
                    $sql = "UPDATE content_block_types SET type_name=?, display_name=?, description=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    if ($stmt->execute([$type_name, $display_name, $description, $type_id])) {
                        $success_message = 'Content block type updated successfully!';
                        logActivity($_SESSION['user_id'], 'Updated content block type', 'content_block_type', $type_id);
                    } else {
                        $error_message = 'Failed to update content block type.';
                    }
                }
            }
        } else {
            $error_message = implode('<br>', $type_errors);
        }
    } elseif ($action == 'manage_types' && isset($_GET['sub_action']) && $_GET['sub_action'] == 'delete_type' && isset($_GET['type_id'])) {
        $type_id = $_GET['type_id'];
        $stmt = $conn->prepare("DELETE FROM content_block_types WHERE id = ?");
        if ($stmt->execute([$type_id])) {
            $success_message = 'Content block type deleted successfully!';
            logActivity($_SESSION['user_id'], 'Deleted content block type', 'content_block_type', $type_id);
        } else {
            $error_message = 'Failed to delete content block type. Make sure no content blocks are using this type.';
        }
    }

    // Fetch all content block types for display
    $stmt_types = $conn->query("SELECT * FROM content_block_types ORDER BY display_name ASC");
    $all_content_block_types = $stmt_types->fetchAll(PDO::FETCH_ASSOC);

    // Fetch a single type for editing if sub_action is edit_type
    $editing_type = null;
    if (isset($_GET['sub_action']) && $_GET['sub_action'] == 'edit_type' && isset($_GET['type_id'])) {
        $stmt_edit_type = $conn->prepare("SELECT * FROM content_block_types WHERE id = ?");
        $stmt_edit_type->execute([$_GET['type_id']]);
        $editing_type = $stmt_edit_type->fetch(PDO::FETCH_ASSOC);
    }
}

// Handle delete action
if ($action == 'delete' && $id) {
    $stmt = $conn->prepare("DELETE FROM content_blocks WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success_message = 'Content block deleted successfully!';
        logActivity($_SESSION['user_id'], 'Deleted content block', 'content_block', $id);
    } else {
        $error_message = 'Failed to delete content block.';
    }
    $action = 'list';
}

// Get content block for editing
$content_block = null;
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
    $page_slug_filter = $_GET['page_slug_filter'] ?? '';

    $sql = "SELECT * FROM content_blocks WHERE 1=1";
    $params = [];

    if (!empty($search_query)) {
        $sql .= " AND (name LIKE ? OR title LIKE ? OR content LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }

    if (!empty($type_filter)) {
        $sql .= " AND type = ?";
        $params[] = $type_filter;
    }
    if (!empty($status_filter)) {
        $sql .= " AND status = ?";
        $params[] = $status_filter;
    }
    if (!empty($page_slug_filter)) {
        $sql .= " AND page_slug = ?";
        $params[] = $page_slug_filter;
    }

    $sql .= " ORDER BY page_slug ASC, display_order ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $content_blocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <!-- Content Blocks List -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Content Blocks</h1>
        <a href="?action=manage_types" class="btn btn-info">
            <i class="fas fa-cogs me-2"></i>Manage Block Types
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center mb-4">
                <input type="hidden" name="action" value="list">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search blocks..." value="<?php echo $_GET['search'] ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <select name="type_filter" class="form-select">
                        <option value="">All Types</option>
                        <?php
                            $stmt_types = $conn->query("SELECT type_name, display_name FROM content_block_types ORDER BY display_name ASC");
                            $content_block_types = $stmt_types->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($content_block_types as $type_option) {
                                $selected = (($_GET['type_filter'] ?? '') == $type_option['type_name']) ? 'selected' : '';
                                echo '<option value="' . $type_option['type_name'] . '" ' . $selected . '>' . $type_option['display_name'] . '</option>';
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="page_slug_filter" class="form-select">
                        <option value="">All Pages</option>
                        <option value="contact" <?php echo (($_GET['page_slug_filter'] ?? '') == 'contact') ? 'selected' : ''; ?>>Contact Page</option>
                        <option value="home" <?php echo (($_GET['page_slug_filter'] ?? '') == 'home') ? 'selected' : ''; ?>>Home Page</option>
                        <!-- Add more page slugs as needed -->
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
                <div class="col-md-auto ms-2">
                    <a href="?action=new" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Add New Block
                    </a>
                </div>
            </form>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Page</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($content_blocks): ?>
                        <?php foreach ($content_blocks as $block): ?>
                            <tr>
                                <td><?php echo $block['name']; ?></td>
                                <td><?php echo $block['title']; ?></td>
                                <td><?php echo $block['type']; ?></td>
                                <td><?php echo $block['page_slug'] ?: 'Global'; ?></td>
                                <td><?php echo $block['display_order']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($block['status'] == 'active') ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($block['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?action=edit&id=<?php echo $block['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                        <a href="?action=delete&id=<?php echo $block['id']; ?>" class="btn btn-outline-danger delete-btn" data-item="content block"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No content blocks found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($action == 'new' || $action == 'edit'): ?>
    <!-- Content Block Form -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><?php echo $action == 'new' ? 'Add New Content Block' : 'Edit Content Block'; ?></h1>
        <a href="?action=list" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>
    
    <form method="POST">
        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Name *</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $content_block['name'] ?? ''; ?>" required>
                    <small class="form-text text-muted">Unique internal name (e.g., contact_email_card)</small>
                </div>
                
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo $content_block['title'] ?? ''; ?>">
                    <small class="form-text text-muted">Displayed title for the block (e.g., Email, Address)</small>
                </div>
                
                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <textarea class="form-control tinymce" id="content" name="content" rows="10"><?php echo $content_block['content'] ?? ''; ?></textarea>
                    <small class="form-text text-muted">Main content for the block (can be HTML)</small>
                </div>
                
                <div class="mb-3">
                    <label for="type" class="form-label">Type *</label>
                    <select class="form-select" id="type" name="type" required>
                        <option value="">Select Type</option>
                        <?php
                            foreach ($content_block_types as $type_option) {
                                $selected = ((isset($content_block['type']) && $content_block['type'] == $type_option['type_name'])) ? 'selected' : '';
                                echo '<option value="' . $type_option['type_name'] . '" ' . $selected . '>' . $type_option['display_name'] . '</option>';
                            }
                        ?>
                    </select>
                    <small class="form-text text-muted">Defines how the block is rendered in the frontend.</small>
                </div>
                
                <div class="mb-3">
                    <label for="icon_class" class="form-label">Icon Class (Font Awesome)</label>
                    <input type="text" class="form-control" id="icon_class" name="icon_class" value="<?php echo $content_block['icon_class'] ?? ''; ?>">
                    <small class="form-text text-muted">e.g., fas fa-envelope, fas fa-map-marker-alt</small>
                </div>
                
                <div class="mb-3">
                    <label for="display_order" class="form-label">Display Order</label>
                    <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $content_block['display_order'] ?? 0; ?>">
                    <small class="form-text text-muted">Blocks with lower numbers appear first.</small>
                </div>
                
                <div class="mb-3">
                    <label for="page_slug" class="form-label">Page Slug (Optional)</label>
                    <input type="text" class="form-control" id="page_slug" name="page_slug" value="<?php echo $content_block['page_slug'] ?? ''; ?>">
                    <small class="form-text text-muted">e.g., contact, home. Leave empty for global blocks.</small>
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?php echo (isset($content_block['status']) && $content_block['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo (isset($content_block['status']) && $content_block['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i><?php echo $action == 'new' ? 'Add Block' : 'Update Block'; ?>
                </button>
            </div>
        </div>
    </form>
<?php elseif ($action == 'manage_types'): ?>
    <!-- Manage Content Block Types -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Manage Content Block Types</h1>
        <a href="?action=list" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Content Blocks
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $editing_type ? 'Edit Block Type' : 'Add New Block Type'; ?></h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="type_action" value="<?php echo $editing_type ? 'edit_type' : 'add_type'; ?>">
                <?php if ($editing_type): ?>
                    <input type="hidden" name="type_id" value="<?php echo $editing_type['id']; ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label for="type_name" class="form-label">Type Name (Machine-readable) *</label>
                    <input type="text" class="form-control" id="type_name" name="type_name" value="<?php echo $editing_type['type_name'] ?? ''; ?>" required <?php echo $editing_type ? 'readonly' : ''; ?>>
                    <small class="form-text text-muted">Unique internal name (e.g., image_gallery, testimonial_slider)</small>
                </div>
                <div class="mb-3">
                    <label for="display_name" class="form-label">Display Name (Human-readable) *</label>
                    <input type="text" class="form-control" id="display_name" name="display_name" value="<?php echo $editing_type['display_name'] ?? ''; ?>" required>
                    <small class="form-text text-muted">Name shown in dropdowns (e.g., Image Gallery, Testimonial Slider)</small>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo $editing_type['description'] ?? ''; ?></textarea>
                    <small class="form-text text-muted">Brief description of this block type's purpose.</small>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i><?php echo $editing_type ? 'Update Type' : 'Add Type'; ?>
                </button>
                <?php if ($editing_type): ?>
                    <a href="?action=manage_types" class="btn btn-outline-secondary ms-2">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Existing Content Block Types</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Type Name</th>
                            <th>Display Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($all_content_block_types): ?>
                            <?php foreach ($all_content_block_types as $type): ?>
                                <tr>
                                    <td><?php echo $type['type_name']; ?></td>
                                    <td><?php echo $type['display_name']; ?></td>
                                    <td><?php echo $type['description']; ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?action=manage_types&sub_action=edit_type&type_id=<?php echo $type['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                            <a href="?action=manage_types&sub_action=delete_type&type_id=<?php echo $type['id']; ?>" class="btn btn-outline-danger delete-btn" data-item="content block type"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No content block types found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>