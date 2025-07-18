<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable autoload for HTMLPurifier and other dependencies
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load environment variables if .env exists
if (file_exists(__DIR__ . '/../.env')) {
    try {
        // Load .env file manually with better parsing
        $env_file = __DIR__ . '/../.env';
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments and empty lines
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            // Only process lines with = sign
            if (strpos($line, '=') !== false) {
                $parts = explode('=', $line, 2);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    
                    // Remove quotes if present
                    if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                        (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                        $value = substr($value, 1, -1);
                    }
                    
                    // Only set if key is not empty
                    if (!empty($key)) {
                        $_ENV[$key] = $value;
                        putenv("$key=$value");
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error loading .env file: " . $e->getMessage());
    }
}

// Database configuration
require_once 'database.php';

// Site configuration
define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://localhost:81');
define('ADMIN_URL', $_ENV['ADMIN_URL'] ?? SITE_URL . '/admin');
define('UPLOAD_PATH', $_ENV['UPLOAD_PATH'] ?? 'uploads/');
define('MAX_FILE_SIZE', $_ENV['MAX_FILE_SIZE'] ?? 5 * 1024 * 1024); // 5MB

// Timezone
$timezone = $_ENV['TIMEZONE'] ?? 'Asia/Jakarta';
date_default_timezone_set($timezone);

// Error reporting - Production safe
$debug_mode = $_ENV['DEBUG_MODE'] ?? getSetting('debug_mode', '0');
$environment = $_ENV['ENVIRONMENT'] ?? 'production';

if ($debug_mode == '1' || $environment == 'development') {
    // Development mode - show all errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../php_errors.log');
} else {
    // Production mode - hide errors from users, log them
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../php_errors.log');
    
    // Set custom error handler for production
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $error_message = date('Y-m-d H:i:s') . " Error [$errno]: $errstr in $errfile on line $errline\n";
        error_log($error_message);
        
        // Don't execute PHP internal error handler
        return true;
    });
}

// Security headers
if (!headers_sent()) {
    // Prevent XSS attacks
    header('X-Content-Type-Options: nosniff');
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
    // Strict transport security (HTTPS only)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Helper functions
function redirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    } else {
        echo "<script>window.location.href='$url';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=$url'></noscript>";
        exit();
    }
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateSlug($text, $table_name = null, $exclude_id = null) {
    global $db;

    $slug = strtolower($text);
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');

    if ($table_name && $db) {
        $original_slug = $slug;
        $counter = 1;
        while (true) {
            $sql = "SELECT COUNT(*) FROM " . $table_name . " WHERE slug = ?";
            $params = [$slug];

            if ($exclude_id) {
                $sql .= " AND id != ?";
                $params[] = $exclude_id;
            }

            try {
                $conn = $db->connect();
                if ($conn) {
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    $count = $stmt->fetchColumn();

                    if ($count == 0) {
                        break;
                    }

                    $slug = $original_slug . '-' . $counter;
                    $counter++;
                } else {
                    break;
                }
            } catch (Exception $e) {
                error_log("Error generating slug: " . $e->getMessage());
                break;
            }
        }
    }

    return $slug;
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
    
    try {
        $conn = $db->connect();
        if ($conn) {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, item_type, item_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $action, $item_type, $item_id, $ip_address, $user_agent]);
        }
    } catch (Exception $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}

function createNotification($user_id, $message, $link = null) {
    global $db;
    if (!$db) {
        $db = new Database();
    }
    
    try {
        $conn = $db->connect();
        if ($conn) {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $message, $link]);
        }
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
    }
}

function getSetting($key, $default = '') {
    global $db;
    if (!$db) {
        $db = new Database();
    }
    try {
        $conn = $db->connect();
        if ($conn) {
            $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['setting_value'] : $default;
        }
    } catch (Exception $e) {
        error_log("Error getting setting: " . $e->getMessage());
    }
    return $default;
}

function setSetting($key, $value) {
    global $db;
    if (!$db) {
        $db = new Database();
    }
    
    try {
        $conn = $db->connect();
        if ($conn) {
            $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
            $stmt->execute([$key, $value, $value]);
            return true;
        }
    } catch (Exception $e) {
        error_log("Error setting setting: " . $e->getMessage());
    }
    
    return false;
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// File upload security
function validateUploadedFile($file, $allowed_types = ['image/jpeg', 'image/png', 'image/gif'], $max_size = 5242880) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    
    if ($file['size'] > $max_size) {
        return false;
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    return in_array($mime_type, $allowed_types);
}

// Structured logging (audit improvement) - DISABLED SEMENTARA
// function logStructured($level, $message, $context = []) {
//     $log_entry = [
//         'timestamp' => date('Y-m-d H:i:s'),
//         'level' => $level,
//         'message' => $message,
//         'context' => $context,
//         'user_id' => $_SESSION['user_id'] ?? null,
//         'ip' => $_SERVER['REMOTE_ADDR'] ?? null
//     ];
//     error_log(json_encode($log_entry) . "\n", 3, __DIR__ . '/../logs/app.log');
// }
?>