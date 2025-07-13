<?php
ob_start();
// Tools Admin Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Tools';
include 'includes/header.php';

// Initialize variables to prevent undefined variable errors
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$tools = [];
$tool = null;
$success_message = '';
$error_message = '';
$errors = [];
$tab = $_GET['tab'] ?? 'active';

// Ensure HTMLPurifier is available
if (!class_exists('HTMLPurifier_Config')) {
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }
}

// Global variables for TinyMCE
$pageContentType = 'tool';
$pageContentId = json_encode($id);

$db = new Database();
$conn = $db->connect();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $slug = generateSlug($_POST['slug'] ?? $title, 'tools', $id);
    $description = sanitize($_POST['description'] ?? '');
    $content = $_POST['content'] ?? ''; // TinyMCE content, handle carefully
    $tool_url = sanitize($_POST['tool_url'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $publish_date = $_POST['publish_date'] ?? null;

    // Validation
    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    if (strlen($title) > 255) {
        $errors[] = 'Title cannot exceed 255 characters.';
    }
    if (empty($description)) {
        $errors[] = 'Description is required.';
    }
    if (empty($content)) {
        $errors[] = 'Content is required.';
    }
    if (!empty($slug) && !preg_match('/^[a-z0-9-]+$/', $slug)) {
        $errors[] = 'Slug can only contain lowercase letters, numbers, and hyphens.';
    }
    if (!in_array($status, ['draft', 'published', 'scheduled', 'archived'])) {
        $errors[] = 'Invalid status selected.';
    }
    if (!empty($publish_date) && !strtotime($publish_date)) {
        $errors[] = 'Invalid publish date format.';
    }
    if (!empty($tool_url) && !filter_var($tool_url, FILTER_VALIDATE_URL)) {
        $errors[] = 'Invalid tool URL format.';
    }

    if (empty($errors)) {
        // Handle featured image upload
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

        // Auto-fill publish date if new tool and date is empty
        if ($action == 'new' && empty($publish_date)) {
            $publish_date = date('Y-m-d H:i:s');
        }

        if ($action == 'new' || $action == 'edit') {
            if ($action == 'new') {
                // Create new tool
                $sql = "INSERT INTO tools (title, slug, description, content, featured_image, tool_url, category, status, publish_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$title, $slug, $description, $content, $featured_image, $tool_url, $category, $status, $publish_date, $_SESSION['user_id']])) {
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
                // Update existing tool
                if ($featured_image) {
                    $sql = "UPDATE tools SET title=?, slug=?, description=?, content=?, featured_image=?, tool_url=?, category=?, status=?, publish_date=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $result = $stmt->execute([$title, $slug, $description, $content, $featured_image, $tool_url, $category, $status, $publish_date, $id]);
                } else {
                    $sql = "UPDATE tools SET title=?, slug=?, description=?, content=?, tool_url=?, category=?, status=?, publish_date=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $result = $stmt->execute([$title, $slug, $description, $content, $tool_url, $category, $status, $publish_date, $id]);
                }
                if ($result) {
                    $success_message = 'Tool updated successfully!';
                    $action = 'list';
                    logActivity($_SESSION['user_id'], 'Updated tool', 'tool', $id);
                    createNotification($_SESSION['user_id'], 'Tool "' . $title . '" has been updated.', 'tools.php?action=edit&id=' . $id);
                    if (!headers_sent()) {
                        header('Location: tools.php?action=list&tab=active&msg=' . urlencode($success_message));
                        ob_end_clean();
                        exit();
                    }
                } else {
                    $error_message = 'Failed to update tool.';
                }
            }
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Handle delete action
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
    $category_filter = $_GET['category_filter'] ?? '';
    $sql = "SELECT t.*, u.username FROM tools t LEFT JOIN users u ON t.created_by = u.id WHERE t.deleted_at IS NULL";
    $params = [];
    if (!empty($search_query)) {
        $sql .= " AND (t.title LIKE ? OR t.description LIKE ? OR t.content LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }
    if (!empty($status_filter)) {
        $sql .= " AND t.status = ?";
        $params[] = $status_filter;
    }
    if (!empty($category_filter)) {
        $sql .= " AND t.category = ?";
        $params[] = $category_filter;
    }
    $sql .= " ORDER BY t.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

// Get unique categories for filter
$categories = [];
$stmt = $conn->prepare("SELECT DISTINCT category FROM tools WHERE category IS NOT NULL AND category != '' AND deleted_at IS NULL ORDER BY category");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Display success/error messages
if (isset($_GET['msg'])) {
    $success_message = urldecode($_GET['msg']);
}
?>

<!-- Page Content -->
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tools me-2"></i>Tools Management
        </h1>
        <a href="?action=new" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add New Tool
        </a>
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

    <?php if ($action == 'list'): ?>
        <!-- Tools List -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">All Tools</h6>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <div class="dropdown-menu p-3" style="width: 300px;">
                        <form method="GET" action="">
                            <input type="hidden" name="action" value="list">
                            <div class="mb-3">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Search tools...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status_filter">
                                    <option value="">All Status</option>
                                    <option value="draft" <?php echo ($_GET['status_filter'] ?? '') == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo ($_GET['status_filter'] ?? '') == 'published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="scheduled" <?php echo ($_GET['status_filter'] ?? '') == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                    <option value="archived" <?php echo ($_GET['status_filter'] ?? '') == 'archived' ? 'selected' : ''; ?>>Archived</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category_filter">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($_GET['category_filter'] ?? '') == $category ? 'selected' : ''; ?>><?php echo htmlspecialchars($category); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">Apply Filter</button>
                                <a href="?action=list" class="btn btn-outline-secondary btn-sm">Clear</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" id="bulkForm">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="toolsTable" width="100%" cellspacing="0">
                            <thead class="table-primary">
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Tool URL</th>
                                    <th>Created By</th>
                                    <th>Created Date</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tools)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No tools found.</p>
                                            <a href="?action=new" class="btn btn-primary">Create Your First Tool</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($tools as $tool_item): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_tools[]" value="<?php echo $tool_item['id']; ?>" class="tool-checkbox">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($tool_item['featured_image']): ?>
                                                        <img src="../<?php echo UPLOAD_PATH . $tool_item['featured_image']; ?>" alt="<?php echo htmlspecialchars($tool_item['title']); ?>" class="me-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($tool_item['title']); ?></strong>
                                                        <?php if ($tool_item['description']): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($tool_item['description'], 0, 100)); ?><?php echo strlen($tool_item['description']) > 100 ? '...' : ''; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($tool_item['category']): ?>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($tool_item['category']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_badges = [
                                                    'draft' => 'bg-secondary',
                                                    'published' => 'bg-success',
                                                    'scheduled' => 'bg-warning',
                                                    'archived' => 'bg-dark'
                                                ];
                                                $status_class = $status_badges[$tool_item['status']] ?? 'bg-secondary';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($tool_item['status']); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($tool_item['tool_url']): ?>
                                                    <a href="<?php echo htmlspecialchars($tool_item['tool_url']); ?>" target="_blank" class="text-decoration-none">
                                                        <i class="fas fa-external-link-alt me-1"></i>View Tool
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($tool_item['username'] ?? 'Unknown'); ?></td>
                                            <td><?php echo formatDateTime($tool_item['created_at']); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="?action=edit&id=<?php echo $tool_item['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="../tool.php?slug=<?php echo $tool_item['slug']; ?>" target="_blank" class="btn btn-sm btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $tool_item['id']; ?>, '<?php echo htmlspecialchars($tool_item['title']); ?>')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($tools)): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex align-items-center gap-2">
                                <select name="bulk_action" class="form-select form-select-sm" style="width: auto;">
                                    <option value="">Bulk Actions</option>
                                    <option value="publish">Publish</option>
                                    <option value="draft">Move to Draft</option>
                                    <option value="archive">Archive</option>
                                    <option value="delete">Delete</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Are you sure you want to perform this action?')">
                                    Apply
                                </button>
                            </div>
                            <div class="text-muted">
                                <?php echo count($tools); ?> tool(s) found
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    <?php elseif ($action == 'new' || $action == 'edit'): ?>
        <!-- Tool Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-<?php echo $action == 'new' ? 'plus' : 'edit'; ?> me-2"></i>
                    <?php echo $action == 'new' ? 'Add New Tool' : 'Edit Tool'; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="toolForm">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($tool['title'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="form-label">Slug</label>
                                <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($tool['slug'] ?? ''); ?>" placeholder="auto-generated">
                                <small class="form-text text-muted">Leave empty to auto-generate from title</small>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($tool['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="content" name="content" rows="15"><?php echo htmlspecialchars($tool['content'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Tool Settings</h6>
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
                                        <input type="datetime-local" class="form-control" id="publish_date" name="publish_date" value="<?php echo $tool['publish_date'] ? date('Y-m-d\TH:i', strtotime($tool['publish_date'])) : ''; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="category" class="form-label">Category</label>
                                        <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($tool['category'] ?? ''); ?>" placeholder="e.g., Development, Design, Utility">
                                    </div>

                                    <div class="mb-3">
                                        <label for="tool_url" class="form-label">Tool URL</label>
                                        <input type="url" class="form-control" id="tool_url" name="tool_url" value="<?php echo htmlspecialchars($tool['tool_url'] ?? ''); ?>" placeholder="https://example.com/tool">
                                    </div>

                                    <div class="mb-3">
                                        <label for="featured_image" class="form-label">Featured Image</label>
                                        <?php if (!empty($tool['featured_image'])): ?>
                                            <div class="mb-2">
                                                <img src="../<?php echo UPLOAD_PATH . $tool['featured_image']; ?>" alt="Current featured image" class="img-thumbnail" style="max-width: 200px;">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                                        <small class="form-text text-muted">Recommended size: 800x600px</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    <?php echo $action == 'new' ? 'Create Tool' : 'Update Tool'; ?>
                                </button>
                                <a href="?action=list" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Auto-generate slug from title
document.getElementById('title').addEventListener('input', function() {
    const title = this.value;
    const slug = title.toLowerCase()
        .replace(/[^a-z0-9 -]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
    document.getElementById('slug').value = slug;
});

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.tool-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Confirm delete
function confirmDelete(id, title) {
    if (confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
        window.location.href = `?action=delete&id=${id}`;
    }
}

// Initialize TinyMCE
if (typeof tinymce !== 'undefined') {
    tinymce.init({
        selector: '#content',
        height: 400,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; }',
        setup: function(editor) {
            // Auto-save functionality
            editor.on('change', function() {
                const content = editor.getContent();
                const contentType = pageContentType;
                const contentId = pageContentId;
                
                // Save draft content
                fetch('api/save_draft.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        content: content,
                        content_type: contentType,
                        content_id: contentId
                    })
                }).catch(error => console.log('Auto-save failed:', error));
            });
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
