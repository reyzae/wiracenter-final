<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Database configuration
require_once 'database.php';

// Site configuration
define('SITE_URL', 'http://localhost:8000');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting
$debug_mode = getSetting('debug_mode', '0');
if ($debug_mode == '1') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../php_errors.log'); // Log errors to a file outside web root

    // Set a custom error handler for production
    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            // This error code is not included in error_reporting
            return;
        }
        error_log(sprintf("PHP Error: %s in %s on line %d", $message, $file, $line));
        // For production, you might want to redirect to a generic error page
        // header('Location: /error.php');
        // exit();
    });

    // Set a custom exception handler for production
    set_exception_handler(function ($exception) {
        error_log(sprintf("PHP Exception: %s in %s on line %d", $exception->getMessage(), $exception->getFile(), $exception->getLine()));
        // For production, you might want to redirect to a generic error page
        // header('Location: /error.php');
        // exit();
    });
}

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(trim($data));
}

function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatDateTime($date) {
    return date('F j, Y g:i A', strtotime($date));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(ADMIN_URL . '/login.php');
    }
}

function hasPermission($required_role) {
    if (!isLoggedIn() || !isset($_SESSION['user_role'])) {
        return false;
    }
    
    $user_role = $_SESSION['user_role'];
    $roles = ['viewer' => 1, 'editor' => 2, 'admin' => 3];
    
    return isset($roles[$user_role]) && $roles[$user_role] >= $roles[$required_role];
}

function logActivity($user_id, $action, $item_type = null, $item_id = null) {
    global $db;
    if (!$db) {
        $db = new Database();
    }
    $conn = $db->connect();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, item_type, item_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $item_type, $item_id, $ip_address, $user_agent]);
}

function createNotification($user_id, $message, $link = null) {
    global $db;
    if (!$db) {
        $db = new Database();
    }
    $conn = $db->connect();
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $message, $link]);
}

// Site settings cache
$site_settings = [];
function getSetting($key, $default = '') {
    global $site_settings;
    
    if (empty($site_settings)) {
        $db = new Database();
        $conn = $db->connect();
        if (!$conn) {
            die('Database connection failed in getSetting().');
        }
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM site_settings");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $site_settings = $settings;
    }
    
    return isset($site_settings[$key]) ? $site_settings[$key] : $default;
}

function setSetting($key, $value) {
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$key, $value, $value]);
    
    // Update cache
    global $site_settings;
    $site_settings[$key] = $value;
}
?>