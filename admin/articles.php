<?php
// Articles Admin Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Articles';
include 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Pastikan $articles selalu terdefinisi
$articles = [];

// Pastikan autoload HTMLPurifier jika digunakan
if (!class_exists('HTMLPurifier_Config')) {
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }
}

// Global variables for TinyMCE
$pageContentType = 'article';
$pageContentId = json_encode($id);

$db = new Database();
$conn = $db->connect();

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $slug = generateSlug($_POST['slug'] ?: $title);
    $content = $_POST['content']; // TinyMCE content, handle carefully
    $excerpt = sanitize($_POST['excerpt']);
    $status = $_POST['status'];
    $publish_date = $_POST['publish_date'] ?: null;

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
            $imageName = uniqid() . '_' . $_FILES['featured_image']['name'];
            $imagePath = $uploadDir . $imageName;
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $imagePath)) {
                $featured_image = $imageName;
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
            // Handle featured image upload (again for edit)
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

            if ($action == 'new') {
                // Create new article
                $sql = "INSERT INTO articles (title, slug, content, excerpt, featured_image, status, publish_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $status, $publish_date, $_SESSION['user_id']])) {
                    $success_message = 'Article created successfully!';
                    $action = 'list';
                    logActivity($_SESSION['user_id'], 'Created article', 'article', $conn->lastInsertId());
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
}

// Get article for editing
$article = null;
if ($action == 'edit' && $id) {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all articles for listing
if ($action == 'list') {
    $search_query = $_GET['search'] ?? '';
    $status_filter = $_GET['status_filter'] ?? '';
    $sql = "SELECT a.*, u.username FROM articles a LEFT JOIN users u ON a.created_by = u.id WHERE a.deleted_at IS NULL";
    $params = [];
    if (!empty($search_query)) {
        $sql .= " AND (a.title LIKE ? OR a.content LIKE ?)";
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
    $bulk_action = $_POST['bulk_action'];
    $selected_articles = $_POST['selected_articles'] ?? [];
    if (!empty($selected_articles)) {
        $placeholders = implode(',', array_fill(0, count($selected_articles), '?'));
        $success_count = 0;
        $error_count = 0;
        if ($bulk_action == 'delete') {
            $stmt = $conn->prepare("UPDATE articles SET deleted_at = NOW() WHERE id IN ($placeholders)");
            if ($stmt->execute($selected_articles)) {
                $success_count = $stmt->rowCount();
                logActivity($_SESSION['user_id'], 'Bulk moved articles to trash', 'article', implode(',', $selected_articles));
            } else {
                $error_count = count($selected_articles);
            }
        } elseif (in_array($bulk_action, ['publish', 'draft', 'archive', 'schedule'])) {
            $new_status = str_replace(['publish', 'archive', 'schedule'], ['published', 'archived', 'scheduled'], $bulk_action);
            $stmt = $conn->prepare("UPDATE articles SET status = ? WHERE id IN ($placeholders)");
            $bulk_params = array_merge([$new_status], $selected_articles);
            if ($stmt->execute($bulk_params)) {
                $success_count = $stmt->rowCount();
                logActivity($_SESSION['user_id'], 'Bulk updated article status to ' . $new_status, 'article', implode(',', $selected_articles));
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
    redirect(ADMIN_URL . '/articles.php?action=list');
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
    <!-- Articles List -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">All Articles</h1>
        <a href="?action=new" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>New Article
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center mb-4">
                <input type="hidden" name="action" value="list">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search articles..." value="<?php echo $_GET['search'] ?? ''; ?>">
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
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="select-all-articles">
                        <label class="form-check-label" for="select-all-articles">
                            Select All
                        </label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all-articles"></th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Author</th>
                                <th>Publish Date</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($articles) && is_array($articles)): ?>
                            <?php foreach ($articles as $article): ?>
                                <tr>
                                    <td><input type="checkbox" name="selected_articles[]" value="<?php echo $article['id']; ?>" class="article-checkbox"></td>
                                    <td>
                                        <strong><?php echo $article['title']; ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo $article['slug']; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            if ($article['status'] == 'published') echo 'success';
                                            else if ($article['status'] == 'draft') echo 'secondary';
                                            else if ($article['status'] == 'scheduled') echo 'info';
                                            else if ($article['status'] == 'archived') echo 'dark';
                                            else echo 'warning'; // Fallback for unknown status
                                        ?>">
                                            <?php echo ucfirst($article['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $article['username']; ?></td>
                                    <td><?php echo $article['publish_date'] ? formatDate($article['publish_date']) : '-'; ?></td>
                                    <td><?php echo formatDate($article['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?action=edit&id=<?php echo $article['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                            <a href="?action=delete&id=<?php echo $article['id']; ?>" class="btn btn-outline-danger delete-btn" data-item="article">Delete</a>
                                            <?php if ($article['status'] == 'published'): ?>
                                                <a href="../article.php?slug=<?php echo $article['slug']; ?>" class="btn btn-outline-success" target="_blank">View</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No articles found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($action == 'new' || $action == 'edit'): ?>
    <!-- Article Form -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><?php echo $action == 'new' ? 'New Article' : 'Edit Article'; ?></h1>
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
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $article['title'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="<?php echo $article['slug'] ?? ''; ?>">
                            <small class="form-text text-muted">Leave blank to auto-generate from title</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?php echo $article['excerpt'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Content *</label>
                            <textarea class="tinymce" id="content" name="content" rows="20"><?php echo $article['content'] ?? ''; ?></textarea>
                            <div class="text-muted mt-2">
                                Word Count: <span id="word-count">0</span> | Read Time: <span id="read-time">0 min</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Article Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="draft" <?php echo ($article['status'] ?? '') == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($article['status'] ?? '') == 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="scheduled" <?php echo ($article['status'] ?? '') == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="archived" <?php echo ($article['status'] ?? '') == 'archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="publish_date" class="form-label">Publish Date</label>
                            <input type="datetime-local" class="form-control" id="publish_date" name="publish_date" value="<?php echo $article['publish_date'] ?? ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="featured_image" class="form-label">Featured Image</label>
                            <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                            <?php if (!empty($article['featured_image'])): ?>
                                <div class="mt-2">
                                    <img src="../<?php echo UPLOAD_PATH . $article['featured_image']; ?>" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i><?php echo $action == 'new' ? 'Create Article' : 'Update Article'; ?>
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