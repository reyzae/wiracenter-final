<?php
$page_title = 'Tools';
include 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null; // Inisialisasi $id di sini

// Pastikan $tools selalu terdefinisi
$tools = [];

require_once __DIR__ . '/../vendor/autoload.php'; // Pindahkan ke sini

// Global variables for TinyMCE
$pageContentType = 'tool';
$pageContentId = ($id !== null) ? json_encode($id) : 'null'; // Pastikan $id terdefinisi

$db = new Database();
$conn = $db->connect();

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'new') {
    $title = sanitize($_POST['title'] ?? '');
    $slug = generateSlug($_POST['slug'] ?: $title, 'tools');
    $description = $_POST['description'] ?? '';
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $publish_date = $_POST['publish_date'] ?? null;
    $errors = [];
    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($content)) $errors[] = 'Content is required.';
    if (!in_array($status, ['draft','published','scheduled','archived'])) $errors[] = 'Invalid status.';
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO tools (title, slug, description, content, status, publish_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$title, $slug, $description, $content, $status, $publish_date, $_SESSION['user_id']]);
        if ($result) {
            $success_message = 'Tool created successfully!';
            $action = 'list';
        } else {
            $error_message = 'Failed to create tool.';
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Handle delete action
if ($action == 'delete' && $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM tools WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success_message = 'Tool deleted successfully!';
        } else {
            $error_message = 'Failed to delete tool.';
        }
    } catch (PDOException $e) {
        $error_message = 'Failed to delete tool: ' . $e->getMessage();
    }
    header('Location: tools.php?action=list&msg=' . urlencode($success_message ?: $error_message));
    exit();
}

// Ambil semua tools untuk listing
if ($action == 'list') {
    $search_query = $_GET['search'] ?? '';
    $status_filter = $_GET['status_filter'] ?? '';
    $sql = "SELECT t.*, u.username FROM tools t LEFT JOIN users u ON t.created_by = u.id WHERE 1=1";
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

<h1 class="h2 mb-4">Tools</h1>

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
                            <label for="description" class="form-label">Short Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo $tool['description'] ?? ''; ?></textarea>
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

<?php include 'includes/footer.php'; ?>
