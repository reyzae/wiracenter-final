<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'FAQs';
include 'includes/header.php';

// --- Setup ---
$db = new Database();
$conn = $db->connect();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$success_message = '';
$error_message = '';
$errors = [];

// --- FAQs Management Logic ---
$faqs = [];
$faq = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form_type'] ?? '') === 'faq') {
    $question = sanitize($_POST['question'] ?? '');
    $answer = $_POST['answer'] ?? '';
    $display_order = (int)($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($question)) $errors[] = 'Question is required.';
    if (empty($answer)) $errors[] = 'Answer is required.';
    if (strlen($question) > 65535) $errors[] = 'Question is too long.';
    if (strlen($answer) > 65535) $errors[] = 'Answer is too long.';
    if (!in_array($status, ['active', 'inactive'])) $errors[] = 'Invalid status selected.';
    if ($display_order < 0) $errors[] = 'Display order must be 0 or greater.';

    if (empty($errors)) {
        if ($action == 'new') {
            $sql = "INSERT INTO faqs (question, answer, display_order, status) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$question, $answer, $display_order, $status])) {
                $success_message = 'FAQ created successfully!';
                $action = 'list';
                logActivity($_SESSION['user_id'], 'Created FAQ', 'faq', $conn->lastInsertId());
                if (!headers_sent()) {
                    header('Location: faqs.php?action=list&msg=' . urlencode($success_message));
                    ob_end_clean();
                    exit();
                }
            } else {
                $error_message = 'Failed to create FAQ.';
            }
        } elseif ($action == 'edit' && $id) {
            $sql = "UPDATE faqs SET question=?, answer=?, display_order=?, status=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$question, $answer, $display_order, $status, $id])) {
                $success_message = 'FAQ updated successfully!';
                $action = 'list';
                logActivity($_SESSION['user_id'], 'Updated FAQ', 'faq', $id);
                if (!headers_sent()) {
                    header('Location: faqs.php?action=list&msg=' . urlencode($success_message));
                    ob_end_clean();
                    exit();
                }
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
    if (!headers_sent()) {
        header('Location: faqs.php?action=list&msg=' . urlencode($success_message ?: $error_message));
        ob_end_clean();
        exit();
    }
}

// Get FAQ for editing
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
    $sql .= " ORDER BY display_order ASC, created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_action'])) {
    $bulk_action = $_POST['bulk_action'];
    $selected_faqs = $_POST['selected_faqs'] ?? [];
    if (!empty($selected_faqs)) {
        $placeholders = implode(',', array_fill(0, count($selected_faqs), '?'));
        $success_count = 0;
        $error_count = 0;
        
        if ($bulk_action == 'delete') {
            $stmt = $conn->prepare("DELETE FROM faqs WHERE id IN ($placeholders)");
            if ($stmt->execute($selected_faqs)) {
                $success_count = count($selected_faqs);
                foreach ($selected_faqs as $fid) {
                    logActivity($_SESSION['user_id'], 'Bulk deleted FAQ', 'faq', $fid);
                }
            } else {
                $error_count = count($selected_faqs);
            }
        } elseif ($bulk_action == 'activate') {
            $stmt = $conn->prepare("UPDATE faqs SET status = 'active' WHERE id IN ($placeholders)");
            if ($stmt->execute($selected_faqs)) {
                $success_count = count($selected_faqs);
                foreach ($selected_faqs as $fid) {
                    logActivity($_SESSION['user_id'], 'Bulk activated FAQ', 'faq', $fid);
                }
            } else {
                $error_count = count($selected_faqs);
            }
        } elseif ($bulk_action == 'deactivate') {
            $stmt = $conn->prepare("UPDATE faqs SET status = 'inactive' WHERE id IN ($placeholders)");
            if ($stmt->execute($selected_faqs)) {
                $success_count = count($selected_faqs);
                foreach ($selected_faqs as $fid) {
                    logActivity($_SESSION['user_id'], 'Bulk deactivated FAQ', 'faq', $fid);
                }
            } else {
                $error_count = count($selected_faqs);
            }
        }
        
        if ($success_count > 0) {
            $success_message = "Successfully processed $success_count FAQ(s).";
        }
        if ($error_count > 0) {
            $error_message = "Failed to process $error_count FAQ(s).";
        }
        
        if (!headers_sent()) {
            header('Location: faqs.php?action=list&msg=' . urlencode($success_message ?: $error_message));
            ob_end_clean();
            exit();
        }
    }
}

// Display success/error messages
if (isset($_GET['msg'])) {
    $success_message = urldecode($_GET['msg']);
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">FAQs Management</h1>
                <a href="?action=new" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New FAQ
                </a>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($action == 'list'): ?>
                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <input type="hidden" name="action" value="list">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Search questions or answers..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status_filter">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo ($_GET['status_filter'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($_GET['status_filter'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="?action=list" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- FAQs List -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" id="bulk-form">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <select class="form-select me-2" name="bulk_action" style="width: auto;">
                                        <option value="">Bulk Actions</option>
                                        <option value="activate">Activate</option>
                                        <option value="deactivate">Deactivate</option>
                                        <option value="delete">Delete</option>
                                    </select>
                                    <button type="submit" class="btn btn-outline-primary" onclick="return confirm('Are you sure you want to perform this action?')">
                                        Apply
                                    </button>
                                </div>
                                <div class="text-muted">
                                    <?php echo count($faqs); ?> FAQ(s) found
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="30">
                                                <input type="checkbox" id="select-all">
                                            </th>
                                            <th>Question</th>
                                            <th>Answer</th>
                                            <th width="100">Order</th>
                                            <th width="100">Status</th>
                                            <th width="150">Created</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($faqs)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p>No FAQs found</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($faqs as $faq_item): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="selected_faqs[]" value="<?php echo $faq_item['id']; ?>" class="faq-checkbox">
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($faq_item['question']); ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="text-muted">
                                                            <?php 
                                                            $answer_preview = strip_tags($faq_item['answer']);
                                                            echo strlen($answer_preview) > 100 ? substr($answer_preview, 0, 100) . '...' : $answer_preview;
                                                            ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark"><?php echo $faq_item['display_order']; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($faq_item['status'] == 'active'): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php echo date('M j, Y', strtotime($faq_item['created_at'])); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="?action=edit&id=<?php echo $faq_item['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="?action=delete&id=<?php echo $faq_item['id']; ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this FAQ?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>

            <?php elseif ($action == 'new' || $action == 'edit'): ?>
                <!-- FAQ Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <?php echo $action == 'new' ? 'Add New FAQ' : 'Edit FAQ'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="form_type" value="faq">
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="question" class="form-label">Question <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="question" name="question" rows="3" required><?php echo htmlspecialchars($faq['question'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="answer" class="form-label">Answer <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="answer" name="answer" rows="8" required><?php echo htmlspecialchars($faq['answer'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="display_order" class="form-label">Display Order</label>
                                        <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $faq['display_order'] ?? 0; ?>" min="0">
                                        <div class="form-text">Lower numbers appear first</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo ($faq['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo ($faq['status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="?action=list" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> 
                                        <?php echo $action == 'new' ? 'Create FAQ' : 'Update FAQ'; ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    const selectAll = document.getElementById('select-all');
    const faqCheckboxes = document.querySelectorAll('.faq-checkbox');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            faqCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // Update select all when individual checkboxes change
    faqCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(faqCheckboxes).every(cb => cb.checked);
            const anyChecked = Array.from(faqCheckboxes).some(cb => cb.checked);
            
            if (selectAll) {
                selectAll.checked = allChecked;
                selectAll.indeterminate = anyChecked && !allChecked;
            }
        });
    });
    
    // Auto-resize textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
        
        // Trigger on load for existing content
        if (textarea.value) {
            textarea.dispatchEvent(new Event('input'));
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
