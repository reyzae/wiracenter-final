<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Tambahkan logging untuk debug double insert
error_log('Contact API called at ' . date('Y-m-d H:i:s') . ' from IP: ' . ($_SERVER['REMOTE_ADDR'] ?? ''));

// CSRF Protection
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

try {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    // Save to database
    // $db = new Database();
    // $conn = $db->connect();
    // $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    // if ($stmt->execute([$name, $email, $subject, $message])) {
    //     // Send email notification (optional)
    //     $to = getSetting('contact_email', 'admin@wiracenter.com');
    //     $email_subject = "New Contact Form Message: $subject";
    //     $email_body = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";
    //     $headers = "From: $email\r\nReply-To: $email\r\n";
    //     @mail($to, $email_subject, $email_body, $headers);
    //     echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    // } else {
    //     echo json_encode(['success' => false, 'message' => 'Failed to save message']);
    // }
    // Migrasi: hanya kirim email, tidak simpan ke database
    $to = 'support@wiracenter.com';
    $email_subject = "New Contact Form Message: $subject";
    $email_body = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";
    $headers = "From: $email\r\nReply-To: $email\r\n";
    if (@mail($to, $email_subject, $email_body, $headers)) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>