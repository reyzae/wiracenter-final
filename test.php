<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>WiraCenter Environment Test</h2>";
echo "<p>Testing environment untuk mencari penyebab error 500...</p>";

// Test 1: PHP Version
echo "<h3>1. PHP Version</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PHP Extensions: " . implode(", ", get_loaded_extensions()) . "<br>";

// Test 2: File .env
echo "<h3>2. File .env Check</h3>";
echo "File .env exists: " . (file_exists('.env') ? 'YES' : 'NO') . "<br>";
echo "File .env readable: " . (is_readable('.env') ? 'YES' : 'NO') . "<br>";

if (file_exists('.env')) {
    $env_content = file_get_contents('.env');
    echo "ENV content length: " . strlen($env_content) . " characters<br>";
    
    // Parse .env
    $lines = explode("\n", $env_content);
    echo "<strong>ENV Variables:</strong><br>";
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) && strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            $parts = explode('=', $line, 2);
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            echo "&nbsp;&nbsp;$key = $value<br>";
        }
    }
}

// Test 3: Vendor autoload
echo "<h3>3. Vendor Dependencies</h3>";
echo "Vendor autoload exists: " . (file_exists('vendor/autoload.php') ? 'YES' : 'NO') . "<br>";
echo "Vendor folder exists: " . (is_dir('vendor') ? 'YES' : 'NO') . "<br>";

if (file_exists('vendor/autoload.php')) {
    echo "Vendor autoload readable: " . (is_readable('vendor/autoload.php') ? 'YES' : 'NO') . "<br>";
}

// Test 4: Config files
echo "<h3>4. Config Files</h3>";
echo "Config file exists: " . (file_exists('config/config.php') ? 'YES' : 'NO') . "<br>";
echo "Database file exists: " . (file_exists('config/database.php') ? 'YES' : 'NO') . "<br>";

// Test 5: Database connection
echo "<h3>5. Database Connection</h3>";
try {
    // Load .env manually for database test
    if (file_exists('.env')) {
        $env_file = '.env';
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        $db_host = 'localhost';
        $db_name = 'wiracenter_db2';
        $db_user = 'root';
        $db_pass = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            if (strpos($line, '=') !== false) {
                $parts = explode('=', $line, 2);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    
                    if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                        (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                        $value = substr($value, 1, -1);
                    }
                    
                    if (!empty($key)) {
                        switch ($key) {
                            case 'DB_HOST':
                                $db_host = $value;
                                break;
                            case 'DB_NAME':
                                $db_name = $value;
                                break;
                            case 'DB_USER':
                                $db_user = $value;
                                break;
                            case 'DB_PASS':
                                $db_pass = $value;
                                break;
                        }
                    }
                }
            }
        }
        
        echo "DB Host: $db_host<br>";
        echo "DB Name: $db_name<br>";
        echo "DB User: $db_user<br>";
        echo "DB Pass: " . (empty($db_pass) ? '(empty)' : '(set)') . "<br>";
        
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        echo "<strong style='color: green;'>Database connection: SUCCESS</strong><br>";
        
        // Test query
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables found: " . count($tables) . "<br>";
        echo "Tables: " . implode(", ", $tables) . "<br>";
        
    } else {
        echo "<strong style='color: red;'>Cannot test database - .env file not found</strong><br>";
    }
} catch (Exception $e) {
    echo "<strong style='color: red;'>Database connection: FAILED</strong><br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test 6: Load config
echo "<h3>6. Config Loading</h3>";
try {
    require_once 'config/config.php';
    echo "<strong style='color: green;'>Config loaded: SUCCESS</strong><br>";
    
    // Test if functions exist
    echo "Function generateSlug exists: " . (function_exists('generateSlug') ? 'YES' : 'NO') . "<br>";
    echo "Function sanitize exists: " . (function_exists('sanitize') ? 'YES' : 'NO') . "<br>";
    
} catch (Exception $e) {
    echo "<strong style='color: red;'>Config loaded: FAILED</strong><br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test 7: File permissions
echo "<h3>7. File Permissions</h3>";
echo "Uploads folder exists: " . (is_dir('uploads') ? 'YES' : 'NO') . "<br>";
echo "Uploads folder writable: " . (is_writable('uploads') ? 'YES' : 'NO') . "<br>";
echo "Config folder readable: " . (is_readable('config') ? 'YES' : 'NO') . "<br>";

// Test 8: Session
echo "<h3>8. Session Test</h3>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session started: " . (session_status() === PHP_SESSION_ACTIVE ? 'YES' : 'NO') . "<br>";

echo "<h3>Test Selesai</h3>";
echo "<p>Jika ada error di atas, itu yang menyebabkan error 500. Jika semua SUCCESS, kemungkinan ada error lain.</p>";
?> 