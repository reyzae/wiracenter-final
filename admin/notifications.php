<?php
require_once '../config/config.php';
requireLogin();
$page_title = 'Notifications';
include 'includes/header.php';

$db = new Database();
$conn = $db->connect();
$user_id = $_SESSION['user_id'];

// Handle mark as read
if (isset($_GET['action']) && $_GET['action'] === 'read' && isset($_GET['id'])) {
    $notif_id = intval($_GET['id']);
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $user_id]);
    header('Location: notifications.php');
    exit();
}
// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $notif_id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $user_id]);
    header('Location: notifications.php');
    exit();
}
// Ambil semua notifikasi user
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="h2 mb-4">Notifications</h1>
<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Link</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($notifications): ?>
                        <?php foreach ($notifications as $notif): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($notif['message']); ?></td>
                                <td>
                                    <?php if (!empty($notif['link'])): ?>
                                        <a href="<?php echo htmlspecialchars($notif['link']); ?>" target="_blank">Open</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $notif['is_read'] ? 'secondary' : 'success'; ?>">
                                        <?php echo $notif['is_read'] ? 'Read' : 'Unread'; ?>
                                    </span>
                                </td>
                                <td><?php echo formatDateTime($notif['created_at']); ?></td>
                                <td>
                                    <?php if (!$notif['is_read']): ?>
                                        <a href="?action=read&id=<?php echo $notif['id']; ?>" class="btn btn-sm btn-success">Mark as Read</a>
                                    <?php endif; ?>
                                    <a href="?action=delete&id=<?php echo $notif['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this notification?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No notifications found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 