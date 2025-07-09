<?php
$page_title = 'Projects';
include 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null; // Inisialisasi $id di sini

// Pastikan $projects selalu terdefinisi
$projects = [];

require_once __DIR__ . '/../vendor/autoload.php'; // Pindahkan ke sini

// Global variables for TinyMCE
$pageContentType = 'project';
$pageContentId = ($id !== null) ? json_encode($id) : 'null'; // Pastikan $id terdefinisi

$db = new Database();
$conn = $db->connect();

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission here
    if (
        isset($_POST['bulk_action']) && 
        !empty($_POST['bulk_action']) && 
        isset($_POST['selected_projects']) && 
        is_array($_POST['selected_projects']) && 
        count($_POST['selected_projects']) > 0
    )
    {
        $bulk_action = $_POST['bulk_action'];
        $selected_projects = $_POST['selected_projects'];

        // Example: handle bulk delete
        if ($bulk_action === 'delete') {
            foreach ($selected_projects as $project_id) {
                // You should implement a proper delete function here
                $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
                $stmt->execute([$project_id]);
            }
            $success_message = "Selected projects have been deleted.";
        }
        // You can add more bulk actions here (publish, draft, archive, schedule)
        // ...
    }

    if (!empty($success_message)) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($success_message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
    if (!empty($error_message)) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($error_message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
    // Tutup blok PHP form submission
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'new') {
    $title = sanitize($_POST['title'] ?? '');
    $slug = generateSlug($_POST['slug'] ?: $title, 'projects');
    $description = $_POST['description'] ?? '';
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $publish_date = $_POST['publish_date'] ?? null;
    $errors = [];
    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($content)) $errors[] = 'Content is required.';
    if (!in_array($status, ['draft','published','scheduled','archived'])) $errors[] = 'Invalid status.';
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO projects (title, slug, description, content, status, publish_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$title, $slug, $description, $content, $status, $publish_date, $_SESSION['user_id']]);
        if ($result) {
            $success_message = 'Project created successfully!';
            $action = 'list';
        } else {
            $error_message = 'Failed to create project.';
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Handle delete action
if ($action == 'delete' && $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success_message = 'Project deleted successfully!';
        } else {
            $error_message = 'Failed to delete project.';
        }
    } catch (PDOException $e) {
        $error_message = 'Failed to delete project: ' . $e->getMessage();
    }
    header('Location: projects.php?action=list&msg=' . urlencode($success_message ?: $error_message));
    exit();
}

// Ambil semua projects untuk listing
if ($action == 'list') {
    $search_query = $_GET['search'] ?? '';
    $status_filter = $_GET['status_filter'] ?? '';
    $sql = "SELECT p.*, u.username FROM projects p LEFT JOIN users u ON p.created_by = u.id WHERE 1=1";
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
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
    <h1 class="h2 mb-4">Projects</h1>

<?php if ($action == 'list'): ?>
    <!-- Projects List -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">All Projects</h1>
        <a href="?action=new" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>New Project
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center mb-4">
                <input type="hidden" name="action" value="list">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search projects..." value="<?php echo $_GET['search'] ?? ''; ?>">
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
                                <th><input type="checkbox" id="select-all-projects"></th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Author</th>
                                <th>Publish Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($projects)): ?>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td><input type="checkbox" name="selected_projects[]" value="<?php echo $project['id']; ?>" class="project-checkbox"></td>
                                    <td>
                                        <strong><?php echo $project['title']; ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo $project['slug']; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            if ($project['status'] == 'published') echo 'success';
                                            else if ($project['status'] == 'draft') echo 'secondary';
                                            else if ($project['status'] == 'scheduled') echo 'info';
                                            else if ($project['status'] == 'archived') echo 'dark';
                                            else echo 'warning'; // Fallback for unknown status
                                        ?>">
                                            <?php echo ucfirst($project['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $project['username']; ?></td>
                                    <td><?php echo $project['publish_date'] ? formatDate($project['publish_date']) : '-'; ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?action=edit&id=<?php echo $project['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                            <a href="?action=delete&id=<?php echo $project['id']; ?>" class="btn btn-outline-danger delete-btn" data-item="project">Delete</a>
                                            <?php if ($project['status'] == 'published'): ?>
                                                <a href="../project.php?slug=<?php echo $project['slug']; ?>" class="btn btn-outline-success" target="_blank">View</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No projects found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($action == 'new' || $action == 'edit'): ?>
    <!-- Project Form -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><?php echo $action == 'new' ? 'New Project' : 'Edit Project'; ?></h4>
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
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $project['title'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="<?php echo $project['slug'] ?? ''; ?>">
                            <small class="form-text text-muted">Leave blank to auto-generate from title</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Short Description</label>
                            <textarea class="tinymce" id="description" name="description" rows="3"><?php echo $project['description'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Full Content *</label>
                            <textarea class="form-control tinymce" id="content" name="content" rows="15"><?php echo $project['content'] ?? ''; ?></textarea>
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
                        <h6 class="card-title mb-0">Project Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="draft" <?php echo ($project['status'] ?? '') == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($project['status'] ?? '') == 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="scheduled" <?php echo ($project['status'] ?? '') == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="archived" <?php echo ($project['status'] ?? '') == 'archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="publish_date" class="form-label">Publish Date</label>
                            <input type="datetime-local" class="form-control" id="publish_date" name="publish_date" value="<?php echo $project['publish_date'] ?? ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="featured_image" class="form-label">Featured Image</label>
                            <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                            <?php if (!empty($project['featured_image'])): ?>
                                <div class="mt-2">
                                    <img src="../<?php echo UPLOAD_PATH . $project['featured_image']; ?>" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="project_url" class="form-label">Project URL</label>
                            <input type="url" class="form-control" id="project_url" name="project_url" value="<?php echo $project['project_url'] ?? ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="github_url" class="form-label">GitHub URL</label>
                            <input type="url" class="form-control" id="github_url" name="github_url" value="<?php echo $project['github_url'] ?? ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="technologies" class="form-label">Technologies (comma-separated)</label>
                            <input type="text" class="form-control" id="technologies" name="technologies" value="<?php echo isset($project['technologies']) ? implode(',', json_decode($project['technologies'], true)) : ''; ?>">
                            <small class="form-text text-muted">e.g., PHP, MySQL, Bootstrap</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i><?php echo $action == 'new' ? 'Create Project' : 'Update Project'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

