<?php
require_once '../../config/config.php';
requireLogin();

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Get statistics
    $stats = [];
    
    // Count published articles
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM articles WHERE status = 'published'");
    $stmt->execute();
    $stats['articles'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count published projects
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM projects WHERE status = 'published'");
    $stmt->execute();
    $stats['projects'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count published tools
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tools WHERE status = 'published'");
    $stmt->execute();
    $stats['tools'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count unread messages
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
    $stmt->execute();
    $stats['messages'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count total files
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM files");
    $stmt->execute();
    $stats['files'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count total users
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching statistics: ' . $e->getMessage()
    ]);
}
?>