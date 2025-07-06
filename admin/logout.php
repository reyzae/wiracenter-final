<?php
require_once '../config/config.php';

// Log logout activity
if (isLoggedIn()) {
    logActivity($_SESSION['user_id'], 'User logged out');
}

// Destroy the session
session_destroy();

// Redirect to login page
redirect(ADMIN_URL . '/login.php');
?>