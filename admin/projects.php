<?php
ob_start();
// Projects Admin Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Projects';
include 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$projects = [];
$project = null;
$success_message = '';
$error_message = '';
$errors = [];
$tab = $_GET['tab'] ?? 'active';

if (!class_exists('HTMLPurifier_Config')) {
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }
}

$pageContentType = 'project';
$pageContentId = json_encode($id);

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Protection
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid CSRF token. Please try again.';
        if (!headers_sent()) {
            header('Location: projects.php?error=' . urlencode($error_message));
            exit();
        }
    }
    

    $title = sanitize($_POST['title'] ?? '');
    $slug = generateSlug($_POST['slug'] ?? $title, 'projects', $id);
    $description = sanitize($_POST['description'] ?? '');
    $content = $_POST['content'] ?? '';
    $project_url = sanitize($_POST['project_url'] ?? '');
    $github_url = sanitize($_POST['github_url'] ?? '');
    $technologies = $_POST['technologies'] ?? [];
    if (!is_array($technologies)) {
        // Jika input dari form adalah string (misal dari textarea), coba decode, jika gagal, fallback ke array kosong
        $decoded = json_decode($technologies, true);
        $technologies = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
    }
    // Technologies: ensure array
    if (!is_array($technologies)) {
        // Try to decode JSON, if not, split by comma
        $decoded = json_decode($technologies, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $technologies = $decoded;
        } else {
            $technologies = array_map('trim', explode(',', $technologies));
        }
    }
    $technologies_json = json_encode($technologies);
    $status = $_POST['status'] ?? 'draft';
    $publish_date = $_POST['publish_date'] ?? null;

    if (empty($title)) $errors[] = 'Title is required.';
    if (strlen($title) > 255) $errors[] = 'Title cannot exceed 255 characters.';
    if (empty($description)) $errors[] = 'Description is required.';
    if (empty($content)) $errors[] = 'Content is required.';
    if (!empty($slug) && !preg_match('/^[a-z0-9-]+$/', $slug)) $errors[] = 'Slug can only contain lowercase letters, numbers, and hyphens.';
    if (!in_array($status, ['draft', 'published', 'scheduled', 'archived'])) $errors[] = 'Invalid status selected.';
    if (!empty($publish_date) && !strtotime($publish_date)) $errors[] = 'Invalid publish date format.';
    if (!empty($project_url) && !filter_var($project_url, FILTER_VALIDATE_URL)) $errors[] = 'Invalid project URL format.';
    if (!empty($github_url) && !filter_var($github_url, FILTER_VALIDATE_URL)) $errors[] = 'Invalid GitHub URL format.';

    $featured_image = '';
    if (!empty($_FILES['featured_image']['name'])) {
        $uploadDir = '../' . UPLOAD_PATH;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $file_type = mime_content_type($_FILES['featured_image']['tmp_name']);
        $file_size = $_FILES['featured_image']['size'];
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = 'Invalid image type. Only JPG, PNG, GIF, WEBP allowed.';
        } elseif ($file_size > $max_size) {
            $errors[] = 'Image size must be less than 2MB.';
        } else {
            $imageName = uniqid() . '_' . basename($_FILES['featured_image']['name']);
            $imagePath = $uploadDir . $imageName;
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $imagePath)) {
                $featured_image = $imageName;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        }
    }

    if ($action == 'new' && empty($publish_date)) {
        $publish_date = date('Y-m-d H:i:s');
    }

    if (empty($errors)) {
        if ($action == 'new') {
            $sql = "INSERT INTO projects (title, slug, description, content, featured_image, project_url, github_url, technologies, status, publish_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$title, $slug, $description, $content, $featured_image, $project_url, $github_url, $technologies_json, $status, $publish_date, $_SESSION['user_id']])) {
                $success_message = 'Project created successfully!';
                $action = 'list';
                logActivity($_SESSION['user_id'], 'Created project', 'project', $conn->lastInsertId());
                if (!headers_sent()) {
                    header('Location: projects.php?action=list&tab=active&msg=' . urlencode($success_message));
                    ob_end_clean();
                    exit();
                }
            } else {
                $error_message = 'Failed to create project.';
            }
        } elseif ($action == 'edit' && $id) {
            if ($featured_image) {
                $sql = "UPDATE projects SET title=?, slug=?, description=?, content=?, featured_image=?, project_url=?, github_url=?, technologies=?, status=?, publish_date=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([$title, $slug, $description, $content, $featured_image, $project_url, $github_url, $technologies_json, $status, $publish_date, $id]);
            } else {
                $sql = "UPDATE projects SET title=?, slug=?, description=?, content=?, project_url=?, github_url=?, technologies=?, status=?, publish_date=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([$title, $slug, $description, $content, $project_url, $github_url, $technologies_json, $status, $publish_date, $id]);
            }
            if ($result) {
                $success_message = 'Project updated successfully!';
                $action = 'list';
                logActivity($_SESSION['user_id'], 'Updated project', 'project', $id);
                createNotification($_SESSION['user_id'], 'Project "' . $title . '" has been updated.', 'projects.php?action=edit&id=' . $id);
                if (!headers_sent()) {
                    header('Location: projects.php?action=list&tab=active&msg=' . urlencode($success_message));
                    ob_end_clean();
                    exit();
                }
            } else {
                $error_message = 'Failed to update project.';
            }
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

if ($action == 'delete' && $id) {
    $stmt = $conn->prepare("UPDATE projects SET deleted_at = NOW() WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success_message = 'Project moved to trash successfully!';
        logActivity($_SESSION['user_id'], 'Moved project to trash', 'project', $id);
    } else {
        $error_message = 'Failed to delete project.';
    }
    $action = 'list';
    if (!headers_sent()) {
        header('Location: projects.php?action=list&tab=active&msg=' . urlencode($success_message ?: $error_message));
        ob_end_clean();
        exit();
    }
}

if ($action == 'edit' && $id) {
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($action == 'list') {
    $search_query = $_GET['search'] ?? '';
    $status_filter = $_GET['status_filter'] ?? '';
    $sql = "SELECT p.*, u.username FROM projects p LEFT JOIN users u ON p.created_by = u.id WHERE p.deleted_at IS NULL";
    $params = [];
    if (!empty($search_query)) {
        $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.content LIKE ?)";
        $params[] = '%' . $search_query . '%';
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_action'])) {
    $bulk_action = $_POST['bulk_action'];
    $selected_projects = $_POST['selected_projects'] ?? [];
    if (!empty($selected_projects)) {
        $placeholders = implode(',', array_fill(0, count($selected_projects), '?'));
        $success_count = 0;
        $error_count = 0;
        if ($bulk_action == 'delete') {
            $stmt = $conn->prepare("UPDATE projects SET deleted_at = NOW() WHERE id IN ($placeholders)");
            if ($stmt->execute($selected_projects)) {
                $success_count = count($selected_projects);
                foreach ($selected_projects as $pid) {
                    logActivity($_SESSION['user_id'], 'Bulk moved project to trash', 'project', $pid);
                }
            } else {
                $error_count = count($selected_projects);
            }
        } elseif (in_array($bulk_action, ['publish', 'draft', 'archive', 'schedule'])) {
            $new_status = str_replace(['publish', 'archive', 'schedule'], ['published', 'archived', 'scheduled'], $bulk_action);
            $stmt = $conn->prepare("UPDATE projects SET status = ? WHERE id IN ($placeholders)");
            $bulk_params = array_merge([$new_status], $selected_projects);
            if ($stmt->execute($bulk_params)) {
                $success_count = count($selected_projects);
                foreach ($selected_projects as $pid) {
                    logActivity($_SESSION['user_id'], 'Bulk updated project status to ' . $new_status, 'project', $pid);
                }
            } else {
                $error_count = count($selected_projects);
            }
        }
        if ($success_count > 0) {
            $success_message = $success_count . ' project(s) ' . str_replace(['publish', 'draft', 'archive', 'schedule'], ['published', 'drafted', 'archived', 'scheduled'], $bulk_action) . ' successfully!';
        }
        if ($error_count > 0) {
            $error_message = $error_count . ' project(s) failed to ' . $bulk_action . '.';
        }
    }
    if (!headers_sent()) {
        header('Location: projects.php?action=list&tab=active');
        ob_end_clean();
        exit();
    }
}

if (isset($_GET['msg'])) {
    $success_message = urldecode($_GET['msg']);
}
?>

<!-- Page Content -->
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-code me-2"></i>Projects Management
        </h1>
        <a href="?action=new" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add New Project
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
        <!-- Projects List -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">All Projects</h6>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <div class="dropdown-menu p-3" style="width: 300px;">
                        <form method="GET" action="">
                            <input type="hidden" name="action" value="list">
                            <div class="mb-3">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Search projects...">
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
                        <table class="table table-bordered table-hover" id="projectsTable" width="100%" cellspacing="0">
                            <thead class="table-primary">
                                <tr>
                                    <th style="width: 36px;" class="text-center">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th style="width: 20%;">Title</th>
                                    <th style="width: 22%;">Description</th>
                                    <th style="width: 8%;" class="text-center text-nowrap">Status</th>
                                    <th style="width: 13%;" class="text-center text-nowrap">Publish Date</th>
                                    <th style="width: 12%;" class="text-center text-nowrap">Created By</th>
                                    <th style="width: 12%;" class="text-center text-nowrap">Created Date</th>
                                    <th style="width: 8%;" class="text-center text-nowrap">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($projects)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-code fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No projects found.</p>
                                            <a href="?action=new" class="btn btn-primary">Create Your First Project</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($projects as $project_item): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_projects[]" value="<?php echo $project_item['id']; ?>" class="project-checkbox">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($project_item['featured_image']): ?>
                                                        <img src="../<?php echo UPLOAD_PATH . $project_item['featured_image']; ?>" alt="<?php echo htmlspecialchars($project_item['title']); ?>" class="me-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars_decode($project_item['title']); ?></strong>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($project_item['slug']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $desc_clean = str_replace('\xC2\xA0', ' ', html_entity_decode($project_item['description']));
                                                $desc_clean = str_replace('&nbsp;', ' ', $desc_clean);
                                                echo htmlspecialchars_decode(substr($desc_clean, 0, 100));
                                                echo strlen($desc_clean) > 100 ? '...' : '';
                                                ?>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <span class="badge" style="border-radius:0.35rem;padding:0.5em 1em;display:inline-block;font-size:1em;background-color:<?php
                                                    switch($project_item['status']) {
                                                        case 'published': echo '#198754'; break;
                                                        case 'draft': echo '#6c757d'; break;
                                                        case 'scheduled': echo '#ffc107'; break;
                                                        case 'archived': echo '#343a40'; break;
                                                        default: echo '#6c757d';
                                                    }
                                                ?>;color:#fff;">
                                                    <?php echo ucfirst($project_item['status']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <?php if ($project_item['status'] == 'published' && !empty($project_item['publish_date'])): ?>
                                                    Published on <?php echo formatDate($project_item['publish_date']); ?>
                                                <?php else: ?>
                                                    <?php echo ucfirst($project_item['status']); ?> on <?php echo formatDate($project_item['created_at']); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?php echo htmlspecialchars($project_item['username'] ?? 'Unknown'); ?></td>
                                            <td class="text-center"><?php echo formatDateTime($project_item['created_at']); ?></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="?action=edit&id=<?php echo $project_item['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="../project.php?slug=<?php echo $project_item['slug']; ?>" target="_blank" class="btn btn-sm btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $project_item['id']; ?>, '<?php echo htmlspecialchars($project_item['title']); ?>')" title="Delete">
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

                    <?php if (!empty($projects)): ?>
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
                                <?php echo count($projects); ?> project(s) found
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    <?php elseif ($action == 'new' || $action == 'edit'): ?>
        <!-- Project Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-<?php echo $action == 'new' ? 'plus' : 'edit'; ?> me-2"></i>
                    <?php echo $action == 'new' ? 'Add New Project' : 'Edit Project'; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="projectForm">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($project['title'] ?? ''); ?>" required style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="form-label">Slug</label>
                                <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($project['slug'] ?? ''); ?>" placeholder="auto-generated" style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">
                                <small class="form-text text-muted">Leave empty to auto-generate from title</small>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="3" required style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;"><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="excerpt" class="form-label">Excerpt</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="3" placeholder="Brief summary of the project..." style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;"><?php echo isset($project['excerpt']) ? htmlspecialchars_decode($project['excerpt']) : ''; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                                <textarea name="content" id="content" class="form-control tinymce" rows="12" style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;"><?php echo isset($project['content']) ? htmlspecialchars_decode($project['content']) : ''; ?></textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Project Settings</h6>
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
                                        <input type="datetime-local" class="form-control" id="publish_date" name="publish_date" value="<?php echo $project['publish_date'] ? date('Y-m-d\TH:i', strtotime($project['publish_date'])) : ''; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="project_url" class="form-label">Project URL</label>
                                        <input type="url" class="form-control" id="project_url" name="project_url" value="<?php echo htmlspecialchars($project['project_url'] ?? ''); ?>" placeholder="https://example.com/project">
                                    </div>

                                    <div class="mb-3">
                                        <label for="github_url" class="form-label">GitHub URL</label>
                                        <input type="url" class="form-control" id="github_url" name="github_url" value="<?php echo htmlspecialchars($project['github_url'] ?? ''); ?>" placeholder="https://github.com/username/repo">
                                    </div>

                                    <div class="mb-3">
                                        <label for="technologies" class="form-label">Technologies</label>
                                        <input type="text" class="form-control" id="technologies" name="technologies" value="<?php echo htmlspecialchars(json_encode($project['technologies'] ?? [])); ?>" placeholder="e.g., PHP, MySQL, Bootstrap">
                                        <small class="form-text text-muted">Comma separated</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="featured_image" class="form-label">Featured Image</label>
                                        <?php if (!empty($project['featured_image'])): ?>
                                            <div class="mb-2">
                                                <img src="../<?php echo UPLOAD_PATH . $project['featured_image']; ?>" alt="Current featured image" class="img-thumbnail" style="max-width: 200px;">
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
                                    <?php echo $action == 'new' ? 'Create Project' : 'Update Project'; ?>
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
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('projectForm');
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

document.getElementById('title').addEventListener('input', function() {
    const title = this.value;
    const slug = title.toLowerCase()
        .replace(/[^a-z0-9 -]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
    document.getElementById('slug').value = slug;
});

document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.project-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

function confirmDelete(id, title) {
    if (confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
        window.location.href = `?action=delete&id=${id}`;
    }
}

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
        content_style: 'body { font-family: \'Fira Sans\', Arial, Helvetica, sans-serif; font-size: 14px; }',
        setup: function(editor) {
            editor.on('change', function() {
                const content = editor.getContent();
                const contentType = pageContentType;
                const contentId = pageContentId;
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
