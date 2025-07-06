<?php
require_once '../../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

if (empty($_FILES['file'])) {
    echo json_encode(['error' => 'No file uploaded.']);
    exit;
}

$file = $_FILES['file'];

// Basic validation
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Upload error: ' . $file['error']]);
    exit;
}

if ($file['size'] > MAX_FILE_SIZE) {
    echo json_encode(['error' => 'File size exceeds limit.']);
    exit;
}

$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['error' => 'Invalid file type. Only JPEG, PNG, GIF, WEBP are allowed.']);
    exit;
}

$uploadDir = '../../' . UPLOAD_PATH;
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '_' . time() . '.' . $extension;
$filepath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    $db = new Database();
    $conn = $db->connect();

    // Save file info to database (optional, but good for media management)
    $stmt = $conn->prepare("INSERT INTO files (filename, original_name, file_path, file_size, file_type, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$filename, $file['name'], UPLOAD_PATH . $filename, $file['size'], $file['type'], $_SESSION['user_id'] ?? null])) {
        $file_id = $conn->lastInsertId();
        logActivity($_SESSION['user_id'] ?? null, 'Uploaded image via TinyMCE', 'file', $file_id);
    }

    echo json_encode(['location' => SITE_URL . '/' . UPLOAD_PATH . $filename]);
} else {
    echo json_encode(['error' => 'Failed to move uploaded file.']);
}
?>