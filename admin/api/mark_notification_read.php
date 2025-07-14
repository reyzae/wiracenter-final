<?php
require_once '../../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Protection
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid CSRF token. Please try again.';
        if (!headers_sent()) {
            header('Location: mark_notification_read.php?error=' . urlencode($error_message));
            exit();
        }
    }
    

    $db = new Database();
    $conn = $db->connect();

    $notification_id = $_POST['id'] ?? null;
    $mark_all = $_POST['mark_all'] ?? false;
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
        exit;
    }

    if ($mark_all) {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE");
        if ($stmt->execute([$user_id])) {
            echo json_encode(['success' => true, 'message' => 'All notifications marked as read.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark all notifications as read.']);
        }
    } elseif ($notification_id) {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$notification_id, $user_id])) {
            echo json_encode(['success' => true, 'message' => 'Notification marked as read.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>