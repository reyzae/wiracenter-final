<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['about_photo'])) {
    $upload_dir = 'uploads/';
    $target_file = $upload_dir . 'about_profile.png';
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $file_type = $_FILES['about_photo']['type'];
    $tmp_name = $_FILES['about_photo']['tmp_name'];
    if (in_array($file_type, $allowed_types)) {
        // Optional: compress/resize image here
        move_uploaded_file($tmp_name, $target_file);
    }
}
header('Location: page.php?slug=about');
exit; 