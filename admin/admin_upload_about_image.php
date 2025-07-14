<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['about_photo'])) {
    require_once '../config/config.php';
    $redirect = $_POST['redirect'] ?? 'pages.php?action=edit&slug=about';
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        header('Location: ' . $redirect . '&error=' . urlencode('Invalid CSRF token.'));
        exit;
    }
    $upload_dir = '../uploads/';
    $target_file = $upload_dir . 'about_profile.png';
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $file_type = $_FILES['about_photo']['type'];
    $tmp_name = $_FILES['about_photo']['tmp_name'];
    $file_size = $_FILES['about_photo']['size'];
    $max_size = 2 * 1024 * 1024; // 2MB
    if (!in_array($file_type, $allowed_types)) {
        header('Location: ' . $redirect . '&error=' . urlencode('Invalid image type.'));
        exit;
    }
    if ($file_size > $max_size) {
        header('Location: ' . $redirect . '&error=' . urlencode('Image size must be less than 2MB.'));
        exit;
    }
    if (move_uploaded_file($tmp_name, $target_file)) {
        header('Location: ' . $redirect . '&msg=' . urlencode('Profile image uploaded successfully.'));
    } else {
        header('Location: ' . $redirect . '&error=' . urlencode('Failed to upload image.'));
    }
    exit;
}
header('Location: ' . $redirect);
exit; 