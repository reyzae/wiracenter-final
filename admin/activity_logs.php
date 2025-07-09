<?php
$page_title = 'Activity Logs';
include 'includes/header.php';

requireLogin();
if (!hasPermission('admin')) {
    redirect(ADMIN_URL . '/dashboard.php');
}

$db = new Database();
$conn = $db->connect();

// Get all activity logs
$logs = [];
try {
    $stmt = $conn->prepare("SELECT al.*, u.username FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $logs = [];
    $error_message = 'Table activity_logs not found in database.';
}
?>

<h1 class="h2 mb-4">Activity Logs</h1>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">All Activities</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Item Type</th>
                        <th>Item ID</th>
                        <th>IP Address</th>
                        <th>User Agent</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo $log['username'] ?? 'N/A'; ?></td>
                                <td><?php echo $log['action']; ?></td>
                                <td><?php echo $log['item_type'] ?? 'N/A'; ?></td>
                                <td><?php echo $log['item_id'] ?? 'N/A'; ?></td>
                                <td><?php echo $log['ip_address'] ?? 'N/A'; ?></td>
                                <td><?php echo $log['user_agent'] ? substr($log['user_agent'], 0, 50) . '...' : 'N/A'; ?></td>
                                <td><?php echo formatDateTime($log['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No activity logs found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>