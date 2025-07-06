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
if (isset($_GET['action']) && $_GET['action'] == 'status' && isset($_GET['id']) && isset($_GET['new_status'])) {
    $id = $_GET['id'];
    $new_status = sanitize($_GET['new_status']);
    
    $stmt = $conn->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $id])) {
        $success_message = 'Message status updated successfully!';
    } else {
        $error_message = 'Failed to update message status.';
    }
    $action = 'list';
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success_message = 'Message deleted successfully!';
    } else {
        $error_message = 'Failed to delete message.';
    }
    $action = 'list';
}

// Get message for viewing
$message = null;
if ($action == 'view' && $id) {
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Mark as read if it was unread
    if ($message && $message['status'] == 'unread') {
        $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
        $stmt->execute([$id]);
        $message['status'] = 'read'; // Update status in current view
    }
}

// Get all messages for listing
if ($action == 'list') {
    $stmt = $conn->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <table class="table table-hover mb-0">
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
                                            <a href="?action=view&id=<?php echo $msg['id']; ?>" class="btn btn-outline-primary">View</a>
                                            <a href="?action=delete&id=<?php echo $msg['id']; ?>" class="btn btn-outline-danger delete-btn" data-item="message">Delete</a>
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
            <div class="alert alert-info">
                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
            </div>
            
            <div class="mt-4">
                <h6>Change Status:</h6>
                <div class="btn-group">
                    <a href="?action=status&id=<?php echo $message['id']; ?>&new_status=read" class="btn btn-sm btn-warning <?php echo $message['status'] == 'read' ? 'active' : ''; ?>">Mark as Read</a>
                    <a href="?action=status&id=<?php echo $message['id']; ?>&new_status=replied" class="btn btn-sm btn-success <?php echo $message['status'] == 'replied' ? 'active' : ''; ?>">Mark as Replied</a>
                    <a href="?action=status&id=<?php echo $message['id']; ?>&new_status=unread" class="btn btn-sm btn-danger <?php echo $message['status'] == 'unread' ? 'active' : ''; ?>">Mark as Unread</a>
                </div>
                <a href="?action=delete&id=<?php echo $message['id']; ?>" class="btn btn-sm btn-outline-danger float-end delete-btn" data-item="message">Delete Message</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>