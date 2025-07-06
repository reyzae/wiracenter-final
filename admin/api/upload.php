<?php
require_once '../../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    if (empty($_FILES['files']['name'][0])) {
        echo json_encode(['success' => false, 'message' => 'No files uploaded']);
        exit;
    }
    
    $uploadDir = '../../' . UPLOAD_PATH;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $db = new Database();
    $conn = $db->connect();
    
    $uploadedFiles = [];
    $errors = [];
    
    foreach ($_FILES['files']['name'] as $key => $name) {
        $tmpName = $_FILES['files']['tmp_name'][$key];
        $size = $_FILES['files']['size'][$key];
        $error = $_FILES['files']['error'][$key];
        $type = $_FILES['files']['type'][$key];
        
        // Check for upload errors
        if ($error !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading $name";
            continue;
        }
        
        // Check file size
        if ($size > MAX_FILE_SIZE) {
            $errors[] = "$name is too large (max " . (MAX_FILE_SIZE / 1024 / 1024) . "MB)";
            continue;
        }
        
        // Generate unique filename
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($tmpName, $filepath)) {
            // Save to database
            $stmt = $conn->prepare("INSERT INTO files (filename, original_name, file_path, file_size, file_type, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$filename, $name, UPLOAD_PATH . $filename, $size, $type, $_SESSION['user_id']])) {
                $file_id = $conn->lastInsertId();
                logActivity($_SESSION['user_id'], 'Uploaded file', 'file', $file_id);
                $uploadedFiles[] = [
                    'filename' => $filename,
                    'original_name' => $name,
                    'file_path' => UPLOAD_PATH . $filename,
                    'file_size' => $size,
                    'file_type' => $type
                ];
            } else {
                $errors[] = "Failed to save $name to database";
                unlink($filepath); // Remove file if database save failed
            }
        } else {
            $errors[] = "Failed to move $name";
        }
    }
    
    if (empty($uploadedFiles) && !empty($errors)) {
        echo json_encode(['success' => false, 'message' => 'All uploads failed', 'errors' => $errors]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => count($uploadedFiles) . ' file(s) uploaded successfully',
            'files' => $uploadedFiles,
            'errors' => $errors
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>