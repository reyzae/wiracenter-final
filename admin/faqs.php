<?php
$page_title = 'FAQs';
include 'includes/header.php';

$db = new Database();
$conn = $db->connect();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question = sanitize($_POST['question']);
    $answer = $_POST['answer']; // TinyMCE content, handle carefully
    $display_order = (int)$_POST['display_order'];
    $status = $_POST['status'];

    $errors = [];

    // Validation
    if (empty($question)) {
        $errors[] = 'Question is required.';
    }
    if (empty($answer)) {
        $errors[] = 'Answer is required.';
    }
    if (!in_array($status, ['active', 'inactive'])) {
        $errors[] = 'Invalid status selected.';
    }

    if (empty($errors)) {
        if ($action == 'new') {
            // Create new FAQ
            $sql = "INSERT INTO faqs (question, answer, display_order, status) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute([$question, $answer, $display_order, $status])) {
                $success_message = 'FAQ created successfully!';
                $action = 'list';
                logActivity($_SESSION['user_id'], 'Created FAQ', 'faq', $conn->lastInsertId());
            } else {
                $error_message = 'Failed to create FAQ.';
            }
        } elseif ($action == 'edit' && $id) {
            // Update existing FAQ
            $sql = "UPDATE faqs SET question=?, answer=?, display_order=?, status=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute([$question, $answer, $display_order, $status, $id])) {
                $success_message = 'FAQ updated successfully!';
                $action = 'list';
                logActivity($_SESSION['user_id'], 'Updated FAQ', 'faq', $id);
            } else {
                $error_message = 'Failed to update FAQ.';
            }
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Handle delete action
if ($action == 'delete' && $id) {
    $stmt = $conn->prepare("DELETE FROM faqs WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success_message = 'FAQ deleted successfully!';
        logActivity($_SESSION['user_id'], 'Deleted FAQ', 'faq', $id);
    } else {
        $error_message = 'Failed to delete FAQ.';
    }
    $action = 'list';
}

// Get FAQ for editing
$faq = null;
if ($action == 'edit' && $id) {
    $stmt = $conn->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$id]);
    $faq = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all FAQs for listing
if ($action == 'list') {
    $search_query = $_GET['search'] ?? '';
    $status_filter = $_GET['status_filter'] ?? '';
    $sql = "SELECT * FROM faqs WHERE 1=1";
    $params = [];

    if (!empty($search_query)) {
        $sql .= " AND (question LIKE ? OR answer LIKE ?)";
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
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <!-- FAQs List -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">FAQs</h1>
        <a href="?action=new" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New FAQ
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center mb-4">
                <input type="hidden" name="action" value="list">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search FAQs..." value="<?php echo $_GET['search'] ?? ''; ?>">
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
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($faqs): ?>
                        <?php foreach ($faqs as $faq_item): ?>
                            <tr>
                                <td><?php echo $faq_item['question']; ?></td>
                                <td><?php echo $faq_item['display_order']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($faq_item['status'] == 'active') ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($faq_item['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?action=edit&id=<?php echo $faq_item['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                        <a href="?action=delete&id=<?php echo $faq_item['id']; ?>" class="btn btn-outline-danger delete-btn" data-item="FAQ"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No FAQs found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($action == 'new' || $action == 'edit'): ?>
    <!-- FAQ Form -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><?php echo $action == 'new' ? 'Add New FAQ' : 'Edit FAQ'; ?></h1>
        <a href="?action=list" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>
    
    <form method="POST">
        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label for="question" class="form-label">Question *</label>
                    <input type="text" class="form-control" id="question" name="question" value="<?php echo $faq['question'] ?? ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="answer" class="form-label">Answer *</label>
                    <textarea class="form-control tinymce" id="answer" name="answer" rows="10"><?php echo $faq['answer'] ?? ''; ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="display_order" class="form-label">Display Order</label>
                    <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $faq['display_order'] ?? 0; ?>">
                    <small class="form-text text-muted">FAQs with lower numbers appear first.</small>
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?php echo ($faq['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($faq['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i><?php echo $action == 'new' ? 'Add FAQ' : 'Update FAQ'; ?>
                </button>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>