<?php
// Initialize variables to prevent undefined variable errors
$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? 'list';
$tools = [];
$tool = null;
$success_message = '';
$error_message = '';
$errors = [];
$tab = $_GET['tab'] ?? 'active';

ob_start();
require_once __DIR__ . '/../config/config.php';
$page_title = 'Tools';

// Ensure HTMLPurifier is available
if (!class_exists('HTMLPurifier_Config')) {
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }
}

$pageContentType = 'tool';
$pageContentId = json_encode($id);

$db = new Database();
$conn = $db->connect();

// Handle form submissions (tambah/edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($action == 'new' || $action == 'edit')) {
    $title = sanitize($_POST['title'] ?? '');
    $slug = generateSlug($_POST['slug'] ?? $title, 'tools', $id);
    $content = $_POST['content'] ?? '';
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $publish_date = $_POST['publish_date'] ?? null;
    $tool_url = $_POST['tool_url'] ?? '';
    $github_url = $_POST['github_url'] ?? '';
    $technologies = $_POST['technologies'] ?? '';

    // Validasi
    if (empty($title)) $errors[] = 'Title is required.';
    if (strlen($title) > 255) $errors[] = 'Title cannot exceed 255 characters.';
    if (empty($content)) $errors[] = 'Content is required.';
    if (!empty($slug) && !preg_match('/^[a-z0-9-]+$/', $slug)) $errors[] = 'Slug can only contain lowercase letters, numbers, and hyphens.';
    if (!in_array($status, ['draft', 'published', 'scheduled', 'archived'])) $errors[] = 'Invalid status selected.';
    if (!empty($publish_date) && !strtotime($publish_date)) $errors[] = 'Invalid publish date format.';

    // Upload featured image
    $featured_image = '';
    if (!empty($_FILES['featured_image']['name'])) {
        $uploadDir = '../' . UPLOAD_PATH;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $imageName = uniqid() . '_' . $_FILES['featured_image']['name'];
        $imagePath = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $imagePath)) {
            $featured_image = $imageName;
        }
    }

    // Auto-generate excerpt jika kosong
    if (empty($excerpt)) {
        $plain_content = strip_tags($content);
        $excerpt = substr($plain_content, 0, 160);
        if (strlen($plain_content) > 160) {
            $excerpt .= '...';
        }
    }

    // Auto-fill publish date jika tambah baru dan kosong
    if ($action == 'new' && empty($publish_date)) {
        $publish_date = date('Y-m-d H:i:s');
    }

    // Format technologies ke JSON
    $technologies_json = $technologies ? json_encode(array_map('trim', explode(',', $technologies))) : null;

    if (empty($errors)) {
        if ($action == 'new') {
            $sql = "INSERT INTO tools (title, slug, content, excerpt, featured_image, status, publish_date, created_by, tool_url, github_url, technologies) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $status, $publish_date, $_SESSION['user_id'], $tool_url, $github_url, $technologies_json])) {
                $success_message = 'Tool created successfully!';
                $action = 'list';
                logActivity($_SESSION['user_id'], 'Created tool', 'tool', $conn->lastInsertId());
                if (!headers_sent()) {
                    header('Location: tools.php?action=list&tab=active&msg=' . urlencode($success_message));
                    ob_end_clean();
                    exit();
                }
            } else {
                $error_message = 'Failed to create tool.';
            }
        } elseif ($action == 'edit' && $id) {
            if ($featured_image) {
                $sql = "UPDATE tools SET title=?, slug=?, content=?, excerpt=?, featured_image=?, status=?, publish_date=?, tool_url=?, github_url=?, technologies=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $status, $publish_date, $tool_url, $github_url, $technologies_json, $id]);
            } else {
                $sql = "UPDATE tools SET title=?, slug=?, content=?, excerpt=?, status=?, publish_date=?, tool_url=?, github_url=?, technologies=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([$title, $slug, $content, $excerpt, $status, $publish_date, $tool_url, $github_url, $technologies_json, $id]);
            }
            if ($result) {
                $success_message = 'Tool updated successfully!';
                $action = 'list';
                logActivity($_SESSION['user_id'], 'Updated tool', 'tool', $id);
                createNotification($_SESSION['user_id'], 'Tool "' . $title . '" has been updated.', 'tools.php?action=edit&id=' . $id);
            } else {
                $error_message = 'Failed to update tool.';
            }
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Handle delete action (soft delete)
if ($action == 'delete' && $id) {
    $stmt = $conn->prepare("UPDATE tools SET deleted_at = NOW() WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success_message = 'Tool moved to trash successfully!';
        logActivity($_SESSION['user_id'], 'Moved tool to trash', 'tool', $id);
    } else {
        $error_message = 'Failed to delete tool.';
    }
    $action = 'list';
    if (!headers_sent()) {
        header('Location: tools.php?action=list&tab=active&msg=' . urlencode($success_message ?: $error_message));
        ob_end_clean();
        exit();
    }
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_action'])) {
    $bulk_action = $_POST['bulk_action'];
    $selected_tools = $_POST['selected_tools'] ?? [];
    if (!empty($selected_tools)) {
        $placeholders = implode(',', array_fill(0, count($selected_tools), '?'));
        $success_count = 0;
        $error_count = 0;
        if ($bulk_action == 'delete') {
            $stmt = $conn->prepare("UPDATE tools SET deleted_at = NOW() WHERE id IN ($placeholders)");
            if ($stmt->execute($selected_tools)) {
                $success_count = count($selected_tools);
                foreach ($selected_tools as $tid) {
                    logActivity($_SESSION['user_id'], 'Bulk moved tool to trash', 'tool', $tid);
                }
            } else {
                $error_count = count($selected_tools);
            }
        } elseif (in_array($bulk_action, ['publish', 'draft', 'archive', 'schedule'])) {
            $new_status = str_replace(['publish', 'archive', 'schedule'], ['published', 'archived', 'scheduled'], $bulk_action);
            $stmt = $conn->prepare("UPDATE tools SET status = ? WHERE id IN ($placeholders)");
            $bulk_params = array_merge([$new_status], $selected_tools);
            if ($stmt->execute($bulk_params)) {
                $success_count = count($selected_tools);
                foreach ($selected_tools as $tid) {
                    logActivity($_SESSION['user_id'], 'Bulk updated tool status to ' . $new_status, 'tool', $tid);
                }
            } else {
                $error_count = count($selected_tools);
            }
        }
        if ($success_count > 0) {
            $success_message = $success_count . ' tool(s) ' . str_replace(['publish', 'draft', 'archive', 'schedule'], ['published', 'drafted', 'archived', 'scheduled'], $bulk_action) . ' successfully!';
        }
        if ($error_count > 0) {
            $error_message = $error_count . ' tool(s) failed to ' . $bulk_action . '.';
        }
    }
    // Redirect to clear POST data and show updated list
    if (!headers_sent()) {
        header('Location: tools.php?action=list&tab=active');
        ob_end_clean();
        exit();
    }
}

// Get tool for editing
if ($action == 'edit' && $id) {
    $stmt = $conn->prepare("SELECT * FROM tools WHERE id = ?");
    $stmt->execute([$id]);
    $tool = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all tools for listing
if ($action == 'list') {
    $search_query = $_GET['search'] ?? '';
    $status_filter = $_GET['status_filter'] ?? '';
    $sql = "SELECT t.*, u.username FROM tools t LEFT JOIN users u ON t.created_by = u.id WHERE t.deleted_at IS NULL";
    $params = [];
    if (!empty($search_query)) {
        $sql .= " AND (t.title LIKE ? OR t.content LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }
    if (!empty($status_filter)) {
        $sql .= " AND t.status = ?";
        $params[] = $status_filter;
    }
    $sql .= " ORDER BY t.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle success/error messages from URL parameters
if (isset($_GET['msg'])) {
    $success_message = $_GET['msg'];
}

include 'includes/header.php';
// include 'includes/navigation.php';
?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($tab == 'active') ? 'active' : ''; ?>" href="?action=list&tab=active">
                            <i class="fas fa-list me-2"></i>Active Tools
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($tab == 'draft') ? 'active' : ''; ?>" href="?action=list&tab=draft">
                            <i class="fas fa-file-alt me-2"></i>Draft Tools
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($tab == 'scheduled') ? 'active' : ''; ?>" href="?action=list&tab=scheduled">
                            <i class="fas fa-calendar-alt me-2"></i>Scheduled Tools
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($tab == 'archived') ? 'active' : ''; ?>" href="?action=list&tab=archived">
                            <i class="fas fa-archive me-2"></i>Archived Tools
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($tab == 'trash') ? 'active' : ''; ?>" href="?action=list&tab=trash">
                            <i class="fas fa-trash me-2"></i>Trash
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2"><?php echo $action == 'list' ? 'All Tools' : ($action == 'new' ? 'New Tool' : 'Edit Tool'); ?></h1>
                <a href="?action=list" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>

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
                <!-- Tools List -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2">All Tools</h1>
                    <a href="?action=new" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Tool
                    </a>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-center mb-4">
                            <input type="hidden" name="action" value="list">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search tools..." value="<?php echo $_GET['search'] ?? ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="status_filter" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="draft" <?php echo (($_GET['status_filter'] ?? '') == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo (($_GET['status_filter'] ?? '') == 'published') ? 'selected' : ''; ?>>Published</option>
                                    <option value="scheduled" <?php echo (($_GET['status_filter'] ?? '') == 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                                    <option value="archived" <?php echo (($_GET['status_filter'] ?? '') == 'archived') ? 'selected' : ''; ?>>Archived</option>
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
                                        <option value="archive">Change Status to Archived</option>
                                        <option value="schedule">Change Status to Scheduled</option>
                                    </select>
                                    <button type="submit" class="btn btn-info" onclick="return confirm('Are you sure you want to apply this bulk action?');">Apply</button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all-tools"></th>
                                            <th>Title</th>
                                            <th>Status</th>
                                            <th>Author</th>
                                            <th>Publish Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!empty($tools)): ?>
                                        <?php foreach ($tools as $tool): ?>
                                            <tr>
                                                <td><input type="checkbox" name="selected_tools[]" value="<?php echo $tool['id']; ?>" class="tool-checkbox"></td>
                                                <td>
                                                    <strong><?php echo $tool['title']; ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo $tool['slug']; ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        if ($tool['status'] == 'published') echo 'success';
                                                        else if ($tool['status'] == 'draft') echo 'secondary';
                                                        else if ($tool['status'] == 'scheduled') echo 'info';
                                                        else if ($tool['status'] == 'archived') echo 'dark';
                                                        else echo 'warning'; // Fallback for unknown status
                                                    ?>">
                                                        <?php echo ucfirst($tool['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $tool['username']; ?></td>
                                                <td><?php echo $tool['publish_date'] ? formatDate($tool['publish_date']) : '-'; ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="?action=edit&id=<?php echo $tool['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                                        <a href="?action=delete&id=<?php echo $tool['id']; ?>" class="btn btn-outline-danger delete-btn" data-item="tool">Delete</a>
                                                        <?php if ($tool['status'] == 'published'): ?>
                                                            <a href="../tool.php?slug=<?php echo $tool['slug']; ?>" class="btn btn-outline-success" target="_blank">View</a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No tools found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($action == 'new' || $action == 'edit'): ?>
                <!-- Tool Form -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4><?php echo $action == 'new' ? 'New Tool' : 'Edit Tool'; ?></h4>
                    <a href="?action=list" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Content</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" value="<?php echo $tool['title'] ?? ''; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="slug" class="form-label">Slug</label>
                                        <input type="text" class="form-control" id="slug" name="slug" value="<?php echo $tool['slug'] ?? ''; ?>">
                                        <small class="form-text text-muted">Leave blank to auto-generate from title</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="excerpt" class="form-label">Excerpt</label>
                                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?php echo $tool['excerpt'] ?? ''; ?></textarea>
                                        <small class="form-text text-muted">Leave blank to auto-generate from content.</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Full Content *</label>
                                        <textarea class="form-control tinymce" id="content" name="content" rows="15"><?php echo $tool['content'] ?? ''; ?></textarea>
                                    </div>
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
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Tool Settings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?php echo ($tool['status'] ?? '') == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                            <option value="published" <?php echo ($tool['status'] ?? '') == 'published' ? 'selected' : ''; ?>>Published</option>
                                            <option value="scheduled" <?php echo ($tool['status'] ?? '') == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                            <option value="archived" <?php echo ($tool['status'] ?? '') == 'archived' ? 'selected' : ''; ?>>Archived</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="publish_date" class="form-label">Publish Date</label>
                                        <input type="datetime-local" class="form-control" id="publish_date" name="publish_date" value="<?php echo $tool['publish_date'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="featured_image" class="form-label">Featured Image</label>
                                        <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                                        <?php if (!empty($tool['featured_image'])): ?>
                                            <div class="mt-2">
                                                <img src="../<?php echo UPLOAD_PATH . $tool['featured_image']; ?>" class="img-thumbnail" style="max-width: 200px;">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="tool_url" class="form-label">Tool URL</label>
                                        <input type="url" class="form-control" id="tool_url" name="tool_url" value="<?php echo $tool['tool_url'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="github_url" class="form-label">GitHub URL</label>
                                        <input type="url" class="form-control" id="github_url" name="github_url" value="<?php echo $tool['github_url'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="technologies" class="form-label">Technologies (comma-separated)</label>
                                        <input type="text" class="form-control" id="technologies" name="technologies" value="<?php echo isset($tool['technologies']) ? implode(',', json_decode($tool['technologies'], true)) : ''; ?>">
                                        <small class="form-text text-muted">e.g., PHP, MySQL, Bootstrap</small>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-save me-2"></i><?php echo $action == 'new' ? 'Create Tool' : 'Update Tool'; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
