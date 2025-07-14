<?php
$page_title = 'Contact Messages';
require_once '../config/config.php';
requireLogin();
if (!hasPermission('admin')) {
    redirect(ADMIN_URL . '/dashboard.php');
}
include 'includes/header.php';

$db = new Database();
$conn = $db->connect();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$success_message = '';
$error_message = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'status' && isset($_POST['id']) && isset($_POST['new_status'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid CSRF token. Please try again.';
    } else {
        $id = $_POST['id'];
        $new_status = sanitize($_POST['new_status']);
        try {
            $stmt = $conn->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
            if ($stmt->execute([$new_status, $id])) {
                $success_message = 'Message status updated successfully!';
            } else {
                $error_message = 'Failed to update message status.';
            }
        } catch (PDOException $e) {
            $error_message = 'Table contact_messages not found in database.';
        }
        $action = 'list';
    }
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid CSRF token. Please try again.';
    } else {
        $id = $_POST['id'];
        try {
            $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success_message = 'Message deleted successfully!';
            } else {
                $error_message = 'Failed to delete message.';
            }
        } catch (PDOException $e) {
            $error_message = 'Table contact_messages not found in database.';
        }
        $action = 'list';
    }
}

// Get message for viewing
$message = null;
if ($action == 'view' && $id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Mark as read if it was unread
        if ($message && $message['status'] == 'unread') {
            $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
            $stmt->execute([$id]);
            $message['status'] = 'read'; // Update status in current view
        }
    } catch (PDOException $e) {
        $message = null;
        $error_message = 'Table contact_messages not found in database.';
    }
}

// Get all messages for listing
if ($action == 'list') {
    try {
        $stmt = $conn->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC");
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $messages = [];
        $error_message = 'Table contact_messages not found in database.';
    }
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

<h1 class="h2 mb-4">Contact Messages</h1>

<?php if ($action == 'list'): ?>
    <!-- Messages List -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($messages): ?>
                            <?php foreach ($messages as $msg): ?>
                                <tr>
                                    <td><?php echo $msg['name']; ?></td>
                                    <td><?php echo $msg['email']; ?></td>
                                    <td><?php echo $msg['subject']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $msg['status'] == 'unread' ? 'danger' : ($msg['status'] == 'read' ? 'warning' : 'success'); ?>">
                                            <?php echo ucfirst($msg['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDateTime($msg['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?action=view&id=<?php echo $msg['id']; ?>" class="btn btn-outline-primary" style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">View</a>
                                            <form method="POST" action="" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <button type="submit" class="btn btn-outline-danger delete-btn" data-item="message" style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No contact messages found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($action == 'view' && $message): ?>
    <!-- View Message -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Message from <?php echo $message['name']; ?></h5>
            <a href="?action=list" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
        <div class="card-body">
            <p><strong>From:</strong> <?php echo $message['name']; ?> &lt;<?php echo $message['email']; ?>&gt;</p>
            <p><strong>Subject:</strong> <?php echo $message['subject']; ?></p>
            <p><strong>Date:</strong> <?php echo formatDateTime($message['created_at']); ?></p>
            <p><strong>Status:</strong> 
                <span class="badge bg-<?php echo $message['status'] == 'unread' ? 'danger' : ($message['status'] == 'read' ? 'warning' : 'success'); ?>">
                    <?php echo ucfirst($message['status']); ?>
                </span>
            </p>
            <hr>
            <p><strong>Message:</strong></p>
            <div class="alert alert-info" style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">
                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
            </div>
            
            <div class="mt-4">
                <h6>Change Status:</h6>
                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                    <input type="hidden" name="new_status" value="read">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <button type="submit" class="btn btn-sm btn-warning <?php echo $message['status'] == 'read' ? 'active' : ''; ?>" style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">Mark as Read</button>
                </form>
                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                    <input type="hidden" name="new_status" value="replied">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <button type="submit" class="btn btn-sm btn-success <?php echo $message['status'] == 'replied' ? 'active' : ''; ?>" style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">Mark as Replied</button>
                </form>
                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                    <input type="hidden" name="new_status" value="unread">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <button type="submit" class="btn btn-sm btn-danger <?php echo $message['status'] == 'unread' ? 'active' : ''; ?>" style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">Mark as Unread</button>
                </form>
                <form method="POST" action="" style="display:inline; float:right;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger delete-btn" data-item="message" style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">Delete Message</button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>