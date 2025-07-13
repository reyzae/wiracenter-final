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

// Error reporting
$debug_mode = $_ENV['DEBUG_MODE'] ?? getSetting('debug_mode', '0');
if ($debug_mode == '1') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../php_errors.log');
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
    return htmlspecialchars(trim($data));
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

// Site settings cache
$site_settings = [];
function getSetting($key, $default = '') {
    global $site_settings;
    
    if (empty($site_settings)) {
        try {
            $db = new Database();
            $conn = $db->connect();
            if ($conn) {
                $stmt = $conn->prepare("SELECT setting_key, setting_value FROM site_settings");
                $stmt->execute();
                $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                $site_settings = $settings;
            }
        } catch (Exception $e) {
            error_log("Error getting settings: " . $e->getMessage());
            return $default;
        }
    }
    
    return isset($site_settings[$key]) ? $site_settings[$key] : $default;
}

function setSetting($key, $value) {
    try {
        $db = new Database();
        $conn = $db->connect();
        if ($conn) {
            $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
            
            // Update cache
            global $site_settings;
            $site_settings[$key] = $value;
        }
    } catch (Exception $e) {
        error_log("Error setting setting: " . $e->getMessage());
    }
}
?>