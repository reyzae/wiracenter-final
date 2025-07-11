<?php
/**
 * Script untuk test koneksi database dengan berbagai metode
 * Mendukung localhost dan 127.0.0.1 untuk XAMPP
 */

// Load environment variables manually if .env exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments and empty lines
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
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
                }
            }
        }
    }
}

// Get configuration
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'wiracenter_db2';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

// Test multiple connection methods
$connection_tests = [
    'Direct connection with current host' => function() use ($host, $dbname, $username, $password) {
        return testConnection($host, $dbname, $username, $password);
    },
    'Localhost fallback' => function() use ($dbname, $username, $password) {
        return testConnection('localhost', $dbname, $username, $password);
    },
    '127.0.0.1 fallback' => function() use ($dbname, $username, $password) {
        return testConnection('127.0.0.1', $dbname, $username, $password);
    },
    'Connection without database (server only)' => function() use ($host, $username, $password) {
        return testConnection($host, null, $username, $password);
    }
];

function testConnection($host, $dbname, $username, $password) {
    try {
        if ($dbname) {
            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        } else {
            $dsn = "mysql:host={$host};charset=utf8mb4";
        }
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        $pdo = new PDO($dsn, $username, $password, $options);
        
        // Test if we can execute a simple query
        $stmt = $pdo->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        return [
            'success' => true,
            'message' => "Koneksi berhasil ke {$host}" . ($dbname ? " dengan database {$dbname}" : " (tanpa database)"),
            'connection' => $pdo
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => "Koneksi gagal ke {$host}: " . $e->getMessage(),
            'error' => $e->getMessage()
        ];
    }
}

// Run tests
$results = [];
$successful_connection = null;

foreach ($connection_tests as $test_name => $test_function) {
    $result = $test_function();
    $results[$test_name] = $result;
    
    if ($result['success'] && !$successful_connection) {
        $successful_connection = $result['connection'];
    }
}

// Test database creation if we have a successful connection
$db_created = false;
if ($successful_connection && !isset($_ENV['DB_NAME'])) {
    try {
        $successful_connection->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $db_created = true;
    } catch (Exception $e) {
        $db_created = false;
    }
}

// Check XAMPP status
$xampp_status = [
    'mysql_running' => false,
    'apache_running' => false,
    'ports' => []
];

// Check if MySQL port is open
$mysql_port = @fsockopen('localhost', 3306, $errno, $errstr, 5);
if ($mysql_port) {
    $xampp_status['mysql_running'] = true;
    fclose($mysql_port);
}

// Check if Apache port is open
$apache_port = @fsockopen('localhost', 80, $errno, $errstr, 5);
if ($apache_port) {
    $xampp_status['apache_running'] = true;
    fclose($apache_port);
}

// Check common XAMPP ports
$common_ports = [80, 443, 3306, 8080, 8000];
foreach ($common_ports as $port) {
    $connection = @fsockopen('localhost', $port, $errno, $errstr, 2);
    if ($connection) {
        $xampp_status['ports'][$port] = true;
        fclose($connection);
    } else {
        $xampp_status['ports'][$port] = false;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Koneksi Database - WiraCenter</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .config-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .test-result {
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-left-color: #17a2b8;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            border-left-color: #ffc107;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .status-item {
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
        .status-running {
            background-color: #d4edda;
            color: #155724;
        }
        .status-stopped {
            background-color: #f8d7da;
            color: #721c24;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .config-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .config-table th,
        .config-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .config-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔌 Test Koneksi Database WiraCenter</h1>
        
        <div class="config-section">
            <h3>📋 Konfigurasi Saat Ini:</h3>
            <table class="config-table">
                <tr>
                    <th>Parameter</th>
                    <th>Nilai</th>
                    <th>Sumber</th>
                </tr>
                <tr>
                    <td>DB_HOST</td>
                    <td><?php echo htmlspecialchars($host); ?></td>
                    <td><?php echo isset($_ENV['DB_HOST']) ? '.env' : 'Default'; ?></td>
                </tr>
                <tr>
                    <td>DB_NAME</td>
                    <td><?php echo htmlspecialchars($dbname); ?></td>
                    <td><?php echo isset($_ENV['DB_NAME']) ? '.env' : 'Default'; ?></td>
                </tr>
                <tr>
                    <td>DB_USER</td>
                    <td><?php echo htmlspecialchars($username); ?></td>
                    <td><?php echo isset($_ENV['DB_USER']) ? '.env' : 'Default'; ?></td>
                </tr>
                <tr>
                    <td>DB_PASS</td>
                    <td><?php echo $password ? '***' : '(kosong)'; ?></td>
                    <td><?php echo isset($_ENV['DB_PASS']) ? '.env' : 'Default'; ?></td>
                </tr>
            </table>
        </div>

        <div class="config-section">
            <h3>🖥️ Status XAMPP:</h3>
            <div class="status-grid">
                <div class="status-item <?php echo $xampp_status['mysql_running'] ? 'status-running' : 'status-stopped'; ?>">
                    MySQL: <?php echo $xampp_status['mysql_running'] ? '✅ Berjalan' : '❌ Berhenti'; ?>
                </div>
                <div class="status-item <?php echo $xampp_status['apache_running'] ? 'status-running' : 'status-stopped'; ?>">
                    Apache: <?php echo $xampp_status['apache_running'] ? '✅ Berjalan' : '❌ Berhenti'; ?>
                </div>
            </div>
            
            <h4>Port Status:</h4>
            <div class="status-grid">
                <?php foreach ($xampp_status['ports'] as $port => $status): ?>
                    <div class="status-item <?php echo $status ? 'status-running' : 'status-stopped'; ?>">
                        Port <?php echo $port; ?>: <?php echo $status ? '✅ Terbuka' : '❌ Tertutup'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="config-section">
            <h3>🔍 Hasil Test Koneksi:</h3>
            <?php foreach ($results as $test_name => $result): ?>
                <div class="test-result <?php echo $result['success'] ? 'success' : 'error'; ?>">
                    <strong><?php echo htmlspecialchars($test_name); ?>:</strong><br>
                    <?php echo htmlspecialchars($result['message']); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($successful_connection): ?>
            <div class="test-result success">
                <h4>✅ Koneksi Database Berhasil!</h4>
                <p>Aplikasi dapat terhubung ke database MySQL. Anda dapat melanjutkan ke langkah berikutnya.</p>
                
                <?php if ($db_created): ?>
                    <div class="test-result info">
                        <strong>Database '<?php echo htmlspecialchars($dbname); ?>' berhasil dibuat!</strong>
                    </div>
                <?php endif; ?>
                
                <div style="text-align: center; margin: 20px 0;">
                    <a href="setup_database.php" class="btn btn-success">🗄️ Setup Database Schema</a>
                    <a href="admin/" class="btn">🔧 Masuk ke Admin Panel</a>
                </div>
            </div>
        <?php else: ?>
            <div class="test-result error">
                <h4>❌ Semua Test Koneksi Gagal</h4>
                <p>Berikut adalah langkah troubleshooting yang dapat Anda coba:</p>
                <ol>
                    <li><strong>Pastikan XAMPP berjalan:</strong> Buka XAMPP Control Panel dan start MySQL</li>
                    <li><strong>Periksa konfigurasi MySQL:</strong> Pastikan MySQL dapat diakses via phpMyAdmin</li>
                    <li><strong>Restart MySQL:</strong> Stop dan start ulang MySQL di XAMPP</li>
                    <li><strong>Periksa port:</strong> Pastikan port 3306 tidak digunakan aplikasi lain</li>
                    <li><strong>Test manual:</strong> Coba koneksi via phpMyAdmin di http://localhost/phpmyadmin</li>
                </ol>
                
                <div style="text-align: center; margin: 20px 0;">
                    <a href="create_env.php" class="btn">📄 Buat/Edit File .env</a>
                    <a href="ENVIRONMENT_SETUP.md" class="btn">📖 Baca Dokumentasi</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="config-section">
            <h3>🔧 Langkah Selanjutnya:</h3>
            <ol>
                <li>Jika koneksi berhasil, jalankan <code>setup_database.php</code> untuk membuat tabel</li>
                <li>Jika koneksi gagal, periksa konfigurasi XAMPP dan file <code>.env</code></li>
                <li>Pastikan MySQL berjalan dan dapat diakses dari aplikasi</li>
                <li>Test koneksi via phpMyAdmin untuk memastikan MySQL berfungsi</li>
            </ol>
        </div>
    </div>
</body>
</html> 