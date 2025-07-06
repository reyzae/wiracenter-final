<?php
require_once '../../config/config.php';

// This script is for testing purposes only. In a real application, notifications should be triggered by events.

// Ensure a user is logged in for the notification to be associated with someone
// For testing, we'll assume user_id 1 exists (e.g., the default admin user)
$user_id = 1; // You can change this to a different user ID if needed

$message = "Ini adalah notifikasi uji coba baru.";
$link = "messages.php"; // Link to the messages page

$db = new Database();
$conn = $db->connect();

if ($conn) {
    try {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link, is_read) VALUES (?, ?, ?, FALSE)");
        $stmt->execute([$user_id, $message, $link]);

        if ($stmt->rowCount() > 0) {
            echo "Notifikasi berhasil ditambahkan untuk user ID: " . $user_id . "\n";
            echo "Pesan: " . $message . "\n";
        } else {
            echo "Gagal menambahkan notifikasi.\n";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Gagal terhubung ke database.\n";
}
?>