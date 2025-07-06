<?php
require_once '../../config/config.php';
requireLogin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $notification_id = $_POST['id'] ?? null;
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        $response = ['success' => false, 'message' => 'User not logged in.'];
        echo json_encode($response);
        exit();
    }

    $db = new Database();
    $conn = $db->connect();

    try {
        if ($action === 'mark_read' && $notification_id !== null) {
            $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
            $stmt->execute([$notification_id, $user_id]);
            if ($stmt->rowCount() > 0) {
                $response = ['success' => true, 'message' => 'Notification marked as read.'];
            } else {
                $response = ['success' => false, 'message' => 'Notification not found or already read.'];
            }
        } elseif ($action === 'delete' && $notification_id !== null) {
            $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $stmt->execute([$notification_id, $user_id]);
            if ($stmt->rowCount() > 0) {
                $response = ['success' => true, 'message' => 'Notification deleted.'];
            } else {
                $response = ['success' => false, 'message' => 'Notification not found.'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Invalid action or missing ID.'];
        }
    } catch (PDOException $e) {
        $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

echo json_encode($response);
?>