<?php
ob_start();
// Articles Admin Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Articles';
include 'includes/header.php';

// Add Fira Sans font for admin articles page
?>
<style>
body, .card, .btn, .form-control, .table, .list-group-item, .modal-content, .nav-link, .dropdown-item {
    font-family: 'Fira Sans', Arial, Helvetica, sans-serif !important;
}
</style>
<?php
// Initialize variables to prevent undefined variable errors
$action = isset($_GET['action']) ? preg_replace('/[^a-z_]/', '', $_GET['action']) : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$articles = [];
$article = null;
$success_message = '';
$error_message = '';
$errors = [];
$tab = isset($_GET['tab']) ? preg_replace('/[^a-z_]/', '', $_GET['tab']) : 'active';

// Ensure HTMLPurifier is available
if (!class_exists('HTMLPurifier_Config')) {
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }
    // If HTMLPurifier is still not available, create a simple fallback
    if (!class_exists('HTMLPurifier_Config')) {
        class HTMLPurifier_Config {
            public static function createDefault() {
                return new self();
            }
        }
        class HTMLPurifier {
            public static function purify($html, $config = null) {
                // Simple HTML sanitization fallback
                return strip_tags($html, '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><blockquote><code><pre>');
            }
        }
    }
}

// Global variables for TinyMCE
$pageContentType = 'article';
$pageContentId = json_encode($id);

$db = new Database();
$conn = $db->connect();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Protection
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid CSRF token. Please try again.';
        $action = 'list';
        if (!headers_sent()) {
            header('Location: articles.php?action=list&tab=active&error=' . urlencode($error_message));
            ob_end_clean();
            exit();
        }
    }
    
    $title = sanitize($_POST['title'] ?? '');
    $slug = generateSlug($_POST['slug'] ?? $title, 'articles', $id);
    $content = $_POST['content'] ?? ''; // TinyMCE content, handle carefully
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $publish_date = $_POST['publish_date'] ?? null;

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
    if (!in_array($status, ['draft', 'published', 'scheduled', 'archived'])) {
        $errors[] = 'Invalid status selected.';
    }
    if (!empty($publish_date) && !strtotime($publish_date)) {
        $errors[] = 'Invalid publish date format.';
    }

    if (empty($errors)) {
        // Handle featured image upload
        $featured_image = '';
        if (!empty($_FILES['featured_image']['name'])) {
            $uploadDir = '../' . UPLOAD_PATH;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['featured_image']['type'];
            $file_size = $_FILES['featured_image']['size'];
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.';
            } elseif ($file_size > 2 * 1024 * 1024) { // 2MB max
                $errors[] = 'Image file size must be less than 2MB.';
            } else {
                $imageName = uniqid() . '_' . time() . '_' . basename($_FILES['featured_image']['name']);
                $imagePath = $uploadDir . $imageName;
                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $imagePath)) {
                    $featured_image = $imageName;
                } else {
                    $errors[] = 'Failed to upload image.';
                }
            }
        }

        // Auto-generate excerpt if empty
        if (empty($excerpt)) {
            $plain_content = strip_tags($content);
            $excerpt = substr($plain_content, 0, 160);
            if (strlen($plain_content) > 160) {
                $excerpt .= '...';
            }
        }

        // Auto-fill publish date if new article and date is empty
        if ($action == 'new' && empty($publish_date)) {
            $publish_date = date('Y-m-d H:i:s');
        }

        if ($action == 'new' || $action == 'edit') {
            if ($action == 'new') {
                // Create new article
                $sql = "INSERT INTO articles (title, slug, content, excerpt, featured_image, status, publish_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $status, $publish_date, $_SESSION['user_id']])) {
                    $success_message = 'Article created successfully!';
                    $action = 'list';
                    logActivity($_SESSION['user_id'], 'Created article', 'article', $conn->lastInsertId());
                    if (!headers_sent()) {
                        header('Location: articles.php?action=list&tab=active&msg=' . urlencode($success_message));
                        ob_end_clean();
                        exit();
                    }
                } else {
                    $error_message = 'Failed to create article.';
                }
            } elseif ($action == 'edit' && $id) {
                // Update existing article
                if ($featured_image) {
                    $sql = "UPDATE articles SET title=?, slug=?, content=?, excerpt=?, featured_image=?, status=?, publish_date=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $result = $stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $status, $publish_date, $id]);
                } else {
                    $sql = "UPDATE articles SET title=?, slug=?, content=?, excerpt=?, status=?, publish_date=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $result = $stmt->execute([$title, $slug, $content, $excerpt, $status, $publish_date, $id]);
                }
                if ($result) {
                    $success_message = 'Article updated successfully!';
                    $action = 'list';
                    logActivity($_SESSION['user_id'], 'Updated article', 'article', $id);
                    createNotification($_SESSION['user_id'], 'Article "' . $title . '" has been updated.', 'articles.php?action=edit&id=' . $id);
                    if (!headers_sent()) {
                        header('Location: articles.php?action=list&tab=active&msg=' . urlencode($success_message));
                        ob_end_clean();
                        exit();
                    }
                } else {
                    $error_message = 'Failed to update article.';
                }
            }
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Handle delete action
if ($action == 'delete' && $id) {
    $stmt = $conn->prepare("UPDATE articles SET deleted_at = NOW() WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success_message = 'Article moved to trash successfully!';
        logActivity($_SESSION['user_id'], 'Moved article to trash', 'article', $id);
    } else {
        $error_message = 'Failed to delete article.';
    }
    $action = 'list';
    if (!headers_sent()) {
        header('Location: articles.php?action=list&tab=active&msg=' . urlencode($success_message ?: $error_message));
        ob_end_clean();
        exit();
    }
}

// Get article for editing
if ($action == 'edit' && $id) {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all articles for listing
if ($action == 'list') {
    $search_query = sanitize($_GET['search'] ?? '');
    $status_filter = sanitize($_GET['status_filter'] ?? '');
    $sql = "SELECT a.*, u.username FROM articles a LEFT JOIN users u ON a.created_by = u.id WHERE a.deleted_at IS NULL";
    $params = [];
    if (!empty($search_query)) {
        $sql .= " AND (a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }
    if (!empty($status_filter)) {
        $sql .= " AND a.status = ?";
        $params[] = $status_filter;
    }
    $sql .= " ORDER BY a.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_action'])) {
    // CSRF Protection for bulk actions
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid CSRF token. Please try again.';
        if (!headers_sent()) {
            header('Location: articles.php?action=list&tab=active&error=' . urlencode($error_message));
            ob_end_clean();
            exit();
        }
    }
    
    $bulk_action = sanitize($_POST['bulk_action']);
    $selected_articles = $_POST['selected_articles'] ?? [];
    if (!empty($selected_articles)) {
        $placeholders = implode(',', array_fill(0, count($selected_articles), '?'));
        $success_count = 0;
        $error_count = 0;
        if ($bulk_action == 'delete') {
            $stmt = $conn->prepare("UPDATE articles SET deleted_at = NOW() WHERE id IN ($placeholders)");
            if ($stmt->execute($selected_articles)) {
                $success_count = count($selected_articles);
                foreach ($selected_articles as $aid) {
                    logActivity($_SESSION['user_id'], 'Bulk moved article to trash', 'article', $aid);
                }
            } else {
                $error_count = count($selected_articles);
            }
        } elseif (in_array($bulk_action, ['publish', 'draft', 'archive', 'schedule'])) {
            $new_status = str_replace(['publish', 'archive', 'schedule'], ['published', 'archived', 'scheduled'], $bulk_action);
            $stmt = $conn->prepare("UPDATE articles SET status = ? WHERE id IN ($placeholders)");
            $bulk_params = array_merge([$new_status], $selected_articles);
            if ($stmt->execute($bulk_params)) {
                $success_count = count($selected_articles);
                foreach ($selected_articles as $aid) {
                    logActivity($_SESSION['user_id'], 'Bulk updated article status to ' . $new_status, 'article', $aid);
                }
            } else {
                $error_count = count($selected_articles);
            }
        }
        if ($success_count > 0) {
            $success_message = $success_count . ' article(s) ' . str_replace(['publish', 'draft', 'archive', 'schedule'], ['published', 'drafted', 'archived', 'scheduled'], $bulk_action) . ' successfully!';
        }
        if ($error_count > 0) {
            $error_message = $error_count . ' article(s) failed to ' . $bulk_action . '.';
        }
    }
    // Redirect to clear POST data and show updated list
    if (!headers_sent()) {
        header('Location: articles.php?action=list&tab=active');
        ob_end_clean();
        exit();
    }
}

// Display success/error messages
if (isset($_GET['msg'])) {
    $success_message = urldecode($_GET['msg']);
}
?>

<!-- Page Content -->
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-newspaper me-2"></i>Articles Management
        </h1>
        <a href="?action=new" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add New Article
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
        <!-- Articles List -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">All Articles</h6>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <div class="dropdown-menu p-3" style="width: 300px;">
                        <form method="GET" action="">
                            <input type="hidden" name="action" value="list">
                            <div class="mb-3">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search articles...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status_filter">
                                    <option value="">All Status</option>
                                    <option value="draft" <?php echo ($status_filter == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo ($status_filter == 'published') ? 'selected' : ''; ?>>Published</option>
                                    <option value="scheduled" <?php echo ($status_filter == 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                                    <option value="archived" <?php echo ($status_filter == 'archived') ? 'selected' : ''; ?>>Archived</option>
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
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="articlesTable" width="100%" cellspacing="0">
                            <thead class="table-primary">
                                <tr>
                                    <th style="width: 36px;" class="text-center">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th style="width: 22%;">Title</th>
                                    <th style="width: 28%;">Excerpt</th>
                                    <th style="width: 8%;" class="text-center text-nowrap">Status</th>
                                    <th style="width: 13%;" class="text-center text-nowrap">Publish Date</th>
                                    <th style="width: 12%;" class="text-center text-nowrap">Created By</th>
                                    <th style="width: 12%;" class="text-center text-nowrap">Created Date</th>
                                    <th style="width: 8%;" class="text-center text-nowrap">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($articles)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No articles found.</p>
                                            <a href="?action=new" class="btn btn-primary">Create Your First Article</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($articles as $article_item): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_articles[]" value="<?php echo $article_item['id']; ?>" class="article-checkbox">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($article_item['featured_image']): ?>
                                                        <img src="../<?php echo UPLOAD_PATH . $article_item['featured_image']; ?>" alt="<?php echo htmlspecialchars($article_item['title']); ?>" class="me-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars_decode($article_item['title']); ?></strong>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($article_item['slug']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($article_item['excerpt']): ?>
                                                    <?php
                                                    $excerpt_clean = str_replace('\xC2\xA0', ' ', html_entity_decode($article_item['excerpt']));
                                                    $excerpt_clean = str_replace('&nbsp;', ' ', $excerpt_clean);
                                                    echo htmlspecialchars_decode(substr($excerpt_clean, 0, 100));
                                                    echo strlen($excerpt_clean) > 100 ? '...' : '';
                                                    ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No excerpt</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center text-nowrap">
    <span class="badge" style="border-radius:0.35rem;padding:0.5em 1em;display:inline-block;font-size:1em;background-color:<?php
        switch($article_item['status']) {
            case 'published': echo '#198754'; break;
            case 'draft': echo '#6c757d'; break;
            case 'scheduled': echo '#ffc107'; break;
            case 'archived': echo '#343a40'; break;
            default: echo '#6c757d';
        }
    ?>;color:#fff;">
        <?php echo ucfirst($article_item['status']); ?>
    </span>
</td>
                                            <td class="text-center text-nowrap">
    <?php if ($article_item['status'] == 'published' && !empty($article_item['publish_date'])): ?>
        Published on <?php echo formatDate($article_item['publish_date']); ?>
    <?php else: ?>
        <?php echo ucfirst($article_item['status']); ?> on <?php echo formatDate($article_item['created_at']); ?>
    <?php endif; ?>
</td>
                                            <td><?php echo htmlspecialchars($article_item['username'] ?? 'Unknown'); ?></td>
                                            <td><?php echo formatDateTime($article_item['created_at']); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="?action=edit&id=<?php echo $article_item['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="../article/<?php echo $article_item['slug']; ?>" target="_blank" class="btn btn-sm btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $article_item['id']; ?>, '<?php echo htmlspecialchars($article_item['title']); ?>')" title="Delete">
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

                    <?php if (!empty($articles)): ?>
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
                                <?php echo count($articles); ?> article(s) found
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    <?php elseif ($action == 'new' || $action == 'edit'): ?>
        <!-- Article Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-<?php echo $action == 'new' ? 'plus' : 'edit'; ?> me-2"></i>
                    <?php echo $action == 'new' ? 'Add New Article' : 'Edit Article'; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="articleForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($article['title']) ? htmlspecialchars($article['title']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="form-label">Slug</label>
                                <input type="text" class="form-control" id="slug" name="slug" value="<?php echo isset($article['slug']) ? htmlspecialchars($article['slug']) : ''; ?>" placeholder="auto-generated">
                                <small class="form-text text-muted">Leave empty to auto-generate from title</small>
                            </div>

                            <div class="mb-3">
                                <label for="excerpt" class="form-label">Excerpt</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="3" placeholder="Brief summary of the article..."><?php echo isset($article['excerpt']) ? htmlspecialchars_decode($article['excerpt']) : ''; ?></textarea>
                                <small class="form-text text-muted">Leave empty to auto-generate from content</small>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                                <textarea name="content" id="content" class="form-control tinymce" rows="12"><?php echo isset($article['content']) ? htmlspecialchars_decode($article['content']) : ''; ?></textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Article Settings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?php echo (isset($article['status']) && $article['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                            <option value="published" <?php echo (isset($article['status']) && $article['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                                            <option value="scheduled" <?php echo (isset($article['status']) && $article['status'] == 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                                            <option value="archived" <?php echo (isset($article['status']) && $article['status'] == 'archived') ? 'selected' : ''; ?>>Archived</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="publish_date" class="form-label">Publish Date</label>
                                        <input type="datetime-local" class="form-control" id="publish_date" name="publish_date" value="<?php echo isset($article['publish_date']) && $article['publish_date'] ? date('Y-m-d\TH:i', strtotime($article['publish_date'])) : ''; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="featured_image" class="form-label">Featured Image</label>
                                        <?php if (!empty($article['featured_image'])): ?>
                                            <div class="mb-2">
                                                <img src="../<?php echo UPLOAD_PATH . $article['featured_image']; ?>" alt="Current featured image" class="img-thumbnail" style="max-width: 200px;">
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
                                    <?php echo $action == 'new' ? 'Create Article' : 'Update Article'; ?>
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
    const checkboxes = document.querySelectorAll('.article-checkbox');
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('articleForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
                var content = tinymce.get('content').getContent({ format: 'text' }).trim();
                if (!content) {
                    alert('Content is required.');
                    tinymce.get('content').focus();
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
