<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Pages & Navigation';
include 'includes/header.php';

// --- Setup ---
$db = new Database();
$conn = $db->connect();

$tab = $_GET['tab'] ?? 'pages';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$success_message = '';
$error_message = '';
$errors = [];

// --- Handle Tabs ---
if (isset($_GET['tab']) && in_array($_GET['tab'], ['pages', 'navigation'])) {
    $tab = $_GET['tab'];
}

// --- Pages Management Logic ---
$pages = [];
$page = null;
if ($tab === 'pages') {
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form_type'] ?? '') === 'page') {
        $title = sanitize($_POST['title'] ?? '');
        $slug = generateSlug($_POST['slug'] ?? $title, 'pages', $id);
        $content = $_POST['content'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        $profile_image = $page['profile_image'] ?? '';
        // Handle profile image upload (only for About page)
        if ($slug === 'about' && !empty($_POST['cropped_profile_image'])) {
            $data = $_POST['cropped_profile_image'];
            if (preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $data, $type)) {
                $data = substr($data, strpos($data, ',') + 1);
                $data = base64_decode($data);
                $ext = $type[1] === 'jpeg' ? 'jpg' : $type[1];
                $filename = 'about_profile_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                $filepath = '../uploads/' . $filename;
                // Remove old image if exists and different
                if (!empty($profile_image) && file_exists('../uploads/' . $profile_image)) {
                    @unlink('../uploads/' . $profile_image);
                }
                file_put_contents($filepath, $data);
                $profile_image = $filename;
            }
        }
        // Validation
        if (empty($title)) $errors[] = 'Title is required.';
        if (strlen($title) > 255) $errors[] = 'Title cannot exceed 255 characters.';
        if (empty($content)) $errors[] = 'Content is required.';
        if (!empty($slug) && !preg_match('/^[a-z0-9-]+$/', $slug)) $errors[] = 'Slug can only contain lowercase letters, numbers, and hyphens.';
        if (!in_array($status, ['draft', 'published'])) $errors[] = 'Invalid status selected.';

        if (empty($errors)) {
            if ($action == 'new') {
                $sql = "INSERT INTO pages (title, slug, content, status, created_by) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$title, $slug, $content, $status, $_SESSION['user_id']])) {
                    $success_message = 'Page created successfully!';
                    $action = 'list';
                    logActivity($_SESSION['user_id'], 'Created page', 'page', $conn->lastInsertId());
                } else {
                    $error_message = 'Failed to create page.';
                }
            } elseif ($action == 'edit' && $id) {
                if ($slug === 'about') {
                    $sql = "UPDATE pages SET title=?, slug=?, content=?, status=?, profile_image=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    if ($stmt->execute([$title, $slug, $content, $status, $profile_image, $id])) {
                        $success_message = 'Page updated successfully!';
                        $action = 'list';
                        logActivity($_SESSION['user_id'], 'Updated page', 'page', $id);
                    } else {
                        $error_message = 'Failed to update page.';
                    }
                } else {
                    $sql = "UPDATE pages SET title=?, slug=?, content=?, status=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    if ($stmt->execute([$title, $slug, $content, $status, $id])) {
                        $success_message = 'Page updated successfully!';
                        $action = 'list';
                        logActivity($_SESSION['user_id'], 'Updated page', 'page', $id);
                    } else {
                        $error_message = 'Failed to update page.';
                    }
                }
            }
        } else {
            $error_message = implode('<br>', $errors);
        }
    }
    // Handle delete action
    if ($action == 'delete' && $id) {
        $stmt = $conn->prepare("DELETE FROM pages WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success_message = 'Page deleted successfully!';
            logActivity($_SESSION['user_id'], 'Deleted page', 'page', $id);
        } else {
            $error_message = 'Failed to delete page.';
        }
        $action = 'list';
    }
    // Get page for editing
    if ($action == 'edit' && $id) {
        $stmt = $conn->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$id]);
        $page = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Get all pages for listing
    if ($action == 'list') {
        $search_query = $_GET['search'] ?? '';
        $status_filter = $_GET['status_filter'] ?? '';
        $sql = "SELECT * FROM pages WHERE deleted_at IS NULL";
        $params = [];
        if (!empty($search_query)) {
            $sql .= " AND (title LIKE ? OR slug LIKE ?)";
            $params[] = '%' . $search_query . '%';
            $params[] = '%' . $search_query . '%';
        }
        if (!empty($status_filter)) {
            $sql .= " AND status = ?";
            $params[] = $status_filter;
        }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// --- Navigation Management Logic ---
$navigation_items = [];
$navigation_item = null;
if ($tab === 'navigation') {
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form_type'] ?? '') === 'navigation') {
        $name = sanitize($_POST['name'] ?? '');
        $url = sanitize($_POST['url'] ?? '');
        $display_order = (int)($_POST['display_order'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        $errors = [];
        if (empty($name)) $errors[] = 'Name is required.';
        if (empty($url)) $errors[] = 'URL is required.';
        if (!filter_var($url, FILTER_VALIDATE_URL) && !preg_match('/^[a-zA-Z0-9_\-]+\.php(\?.*)?$/', $url) && !preg_match('/^page\.php\?slug=[a-zA-Z0-9_\-]+$/', $url)) $errors[] = 'Invalid URL format. Must be a valid URL or a relative path to a .php file (e.g., index.php, page.php?slug=about).';
        if (!in_array($status, ['active', 'inactive'])) $errors[] = 'Invalid status selected.';
        if (empty($errors)) {
            if ($action == 'new') {
                $sql = "INSERT INTO navigation_items (name, url, display_order, status) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$name, $url, $display_order, $status])) {
                    $success_message = 'Navigation item created successfully!';
                    $action = 'list';
                    logActivity($_SESSION['user_id'], 'Created navigation item', 'navigation_item', $conn->lastInsertId());
                } else {
                    $error_message = 'Failed to create navigation item.';
                }
            } elseif ($action == 'edit' && $id) {
                $sql = "UPDATE navigation_items SET name=?, url=?, display_order=?, status=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$name, $url, $display_order, $status, $id])) {
                    $success_message = 'Navigation item updated successfully!';
                    $action = 'list';
                    logActivity($_SESSION['user_id'], 'Updated navigation item', 'navigation_item', $id);
                } else {
                    $error_message = 'Failed to update navigation item.';
                }
            }
        } else {
            $error_message = implode('<br>', $errors);
        }
    }
    // Handle delete action
    if ($action == 'delete' && $id) {
        $stmt = $conn->prepare("DELETE FROM navigation_items WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success_message = 'Navigation item deleted successfully!';
            logActivity($_SESSION['user_id'], 'Deleted navigation item', 'navigation_item', $id);
        } else {
            $error_message = 'Failed to delete navigation item.';
        }
        $action = 'list';
    }
    // Get navigation item for editing
    if ($action == 'edit' && $id) {
        $stmt = $conn->prepare("SELECT * FROM navigation_items WHERE id = ?");
        $stmt->execute([$id]);
        $navigation_item = $stmt->fetch(PDO::FETCH_ASSOC);
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
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $navigation_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="container-fluid">
    <ul class="nav nav-tabs mb-4" id="pagesNavTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link<?php echo $tab === 'pages' ? ' active' : ''; ?>" href="?tab=pages">Pages</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link<?php echo $tab === 'navigation' ? ' active' : ''; ?>" href="?tab=navigation">Navigation/Menu</a>
        </li>
    </ul>

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

    <?php if ($tab === 'pages'): ?>
        <?php if ($action == 'list'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">Pages Management</h1>
                <a href="?tab=pages&action=new" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Page
                </a>
            </div>
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-center mb-4">
                        <input type="hidden" name="tab" value="pages">
                        <input type="hidden" name="action" value="list">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search pages..." value="<?php echo $_GET['search'] ?? ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="status_filter" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="published" <?php echo (($_GET['status_filter'] ?? '') == 'published') ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo (($_GET['status_filter'] ?? '') == 'draft') ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Title</th>
                                    <th>Slug</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($pages): ?>
                                    <?php foreach ($pages as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                                            <td><?php echo htmlspecialchars($item['slug']); ?></td>
                                            <td><span class="badge bg-<?php echo $item['status'] == 'published' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($item['status']); ?></span></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="?tab=pages&action=edit&id=<?php echo $item['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                                    <a href="?tab=pages&action=delete&id=<?php echo $item['id']; ?>" class="btn btn-outline-danger delete-btn" data-item="page">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No pages found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($action == 'new' || $action == 'edit'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2"><?php echo $action == 'new' ? 'Add New Page' : 'Edit Page'; ?></h1>
                <a href="?tab=pages&action=list" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
            <form method="POST" enctype="multipart/form-data" id="editPageForm">
                <input type="hidden" name="form_type" value="page">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($page['title'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug *</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($page['slug'] ?? ''); ?>" required>
                            <small class="form-text text-muted">e.g., about, contact, faq</small>
                        </div>
                        <?php if (($page['slug'] ?? '') === 'about'): ?>
                        <div class="mb-3">
                            <label class="form-label">Profile Image</label><br>
                            <?php if (!empty($page['profile_image'])): ?>
                                <img id="profileImagePreview" src="../uploads/<?php echo htmlspecialchars($page['profile_image']); ?>" alt="Profile Image" style="max-width:120px;max-height:120px;border-radius:50%;margin-bottom:10px;display:block;">
                            <?php else: ?>
                                <img id="profileImagePreview" src="https://via.placeholder.com/120x120?text=No+Image" alt="Profile Image" style="max-width:120px;max-height:120px;border-radius:50%;margin-bottom:10px;display:block;">
                            <?php endif; ?>
                            <input type="file" class="form-control" id="profileImageInput" name="profile_image" accept="image/*">
                            <input type="hidden" name="cropped_profile_image" id="croppedProfileImage">
                            <div id="cropperContainer" style="width:400px;height:400px;overflow:hidden;margin-top:15px;display:none;">
                                <img id="cropperImage" style="max-width:100%;max-height:100%;display:block;margin:auto;">
                            </div>
                            <button type="button" class="btn btn-outline-secondary mt-2" id="cropProfileImageBtn" style="display:none;">Crop & Preview</button>
                        </div>
                        <?php endif; ?>
                        <?php if ($action == 'edit' && $page && $page['slug'] === 'about'): ?>
                            <!-- Blok duplikat upload foto profil About dihapus sesuai permintaan user -->
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="content" class="form-label">Content *</label>
                            <textarea class="form-control tinymce" id="content" name="content" rows="10" required><?php echo htmlspecialchars($page['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="published" <?php echo ($page['status'] ?? '') == 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo ($page['status'] ?? '') == 'draft' ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Page
                        </button>
                    </div>
                </div>
            </form>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet"/>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
            <script>
            let cropper;
            const cropperImage = document.getElementById('cropperImage');
            const cropperContainer = document.getElementById('cropperContainer');
            const cropBtn = document.getElementById('cropProfileImageBtn');
            const profileImageInput = document.getElementById('profileImageInput');
            const profileImagePreview = document.getElementById('profileImagePreview');

            profileImageInput?.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        cropperImage.src = event.target.result;
                        cropperContainer.style.display = 'block';
                        cropBtn.style.display = 'inline-block';
                        if (cropper) {
                            cropper.destroy();
                            cropper = null;
                        }
                        cropper = new Cropper(cropperImage, {
                            aspectRatio: 1,
                            viewMode: 1,
                            autoCropArea: 1,
                            dragMode: 'move',
                            responsive: false,
                            background: false
                        });
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });

            cropBtn?.addEventListener('click', function() {
                if (cropper) {
                    cropper.getCroppedCanvas({width: 400, height: 400}).toBlob(function(blob) {
                        const reader = new FileReader();
                        reader.onloadend = function() {
                            document.getElementById('croppedProfileImage').value = reader.result;
                            profileImagePreview.src = reader.result;
                        };
                        reader.readAsDataURL(blob);
                    }, 'image/png');
                }
            });
            </script>
        <?php endif; ?>
    <?php elseif ($tab === 'navigation'): ?>
        <?php if ($action == 'list'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">Navigation/Menu Management</h1>
                <a href="?tab=navigation&action=new" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Item
                </a>
            </div>
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-center mb-4">
                        <input type="hidden" name="tab" value="navigation">
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
                            <thead class="table-primary">
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
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['url']); ?></td>
                                            <td><?php echo (int)$item['display_order']; ?></td>
                                            <td><span class="badge bg-<?php echo $item['status'] == 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($item['status']); ?></span></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="?tab=navigation&action=edit&id=<?php echo $item['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                                    <a href="?tab=navigation&action=delete&id=<?php echo $item['id']; ?>" class="btn btn-outline-danger delete-btn" data-item="navigation item">Delete</a>
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
            </div>
        <?php elseif ($action == 'new' || $action == 'edit'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2"><?php echo $action == 'new' ? 'Add New Navigation Item' : 'Edit Navigation Item'; ?></h1>
                <a href="?tab=navigation&action=list" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
            <form method="POST">
                <input type="hidden" name="form_type" value="navigation">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($navigation_item['name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="url" class="form-label">URL *</label>
                            <input type="text" class="form-control" id="url" name="url" value="<?php echo htmlspecialchars($navigation_item['url'] ?? ''); ?>" required>
                            <small class="form-text text-muted">e.g., index.php, page.php?slug=about, https://external.com</small>
                        </div>
                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo (int)($navigation_item['display_order'] ?? 0); ?>">
                            <small class="form-text text-muted">Lower numbers appear first.</small>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?php echo ($navigation_item['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($navigation_item['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Item
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.tiny.cloud/1/7t4ysw5ibpvf6otxc72fed05syoih8onsdc91gce3e4sqi3a/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: 'textarea.tinymce',
        plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        height: 400,
        menubar: false
    });
</script>
<?php include 'includes/footer.php'; ?>
<?php ob_end_flush(); ?>
