<?php
/**
 * Script untuk membuat file .env untuk konfigurasi XAMPP
 * Jalankan script ini di browser atau command line
 */

// Fungsi untuk membuat file .env
function createEnvFile() {
    $env_content = <<<ENV
# Database Configuration for XAMPP
DB_HOST=localhost
DB_NAME=wiracenter_db2
DB_USER=root
DB_PASS=

# Alternative configurations (uncomment if needed)
# For 127.0.0.1:
# DB_HOST=127.0.0.1

# For specific port (if MySQL runs on different port):
# DB_HOST=localhost:3306

# Site Configuration
SITE_URL=http://localhost:8000
ADMIN_URL=http://localhost:8000/admin

# Debug Mode (set to 1 for debugging, 0 for production)
DEBUG_MODE=1

# File Upload Settings
MAX_FILE_SIZE=5242880
UPLOAD_PATH=uploads/

# Timezone
TIMEZONE=Asia/Jakarta

# Session Configuration
SESSION_LIFETIME=3600
SESSION_SECURE=false

# Security Settings
CSRF_TOKEN_LIFETIME=3600
PASSWORD_MIN_LENGTH=8

# Email Configuration (if needed later)
# SMTP_HOST=smtp.gmail.com
# SMTP_PORT=587
# SMTP_USER=your-email@gmail.com
# SMTP_PASS=your-app-password
# SMTP_SECURE=tls
ENV;

    $env_file = __DIR__ . '/.env';
    
    if (file_exists($env_file)) {
        return [
            'success' => false,
            'message' => 'File .env sudah ada. Silakan edit manual atau hapus file yang ada terlebih dahulu.'
        ];
    }
    
    $result = file_put_contents($env_file, $env_content);
    
    if ($result !== false) {
        return [
            'success' => true,
            'message' => 'File .env berhasil dibuat dengan konfigurasi untuk XAMPP!'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Gagal membuat file .env. Periksa permission folder.'
        ];
    }
}

// Fungsi untuk test koneksi database
function testDatabaseConnection() {
    require_once 'config/database.php';
    
    $db = new Database();
    $result = $db->testConnection();
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Koneksi database berhasil!'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Koneksi database gagal. Periksa konfigurasi MySQL di XAMPP.'
        ];
    }
}

// Handle form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_env'])) {
        $result = createEnvFile();
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    } elseif (isset($_POST['test_connection'])) {
        $result = testDatabaseConnection();
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    }
}

// Check if .env file exists
$env_exists = file_exists(__DIR__ . '/.env');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Environment - WiraCenter</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
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
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
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
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .steps {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .steps ol {
            margin: 0;
            padding-left: 20px;
        }
        .steps li {
            margin-bottom: 10px;
        }
        .file-content {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            white-space: pre-wrap;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Setup Environment WiraCenter</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="steps">
            <h3>📋 Langkah-langkah Setup:</h3>
            <ol>
                <li><strong>Pastikan XAMPP sudah berjalan</strong> - Apache dan MySQL harus aktif</li>
                <li><strong>Buat file .env</strong> - Klik tombol di bawah untuk membuat file konfigurasi</li>
                <li><strong>Test koneksi database</strong> - Pastikan aplikasi bisa terhubung ke MySQL</li>
                <li><strong>Setup database</strong> - Jalankan script setup_database.php jika belum ada database</li>
            </ol>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <?php if (!$env_exists): ?>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="create_env" class="btn btn-success">
                        📄 Buat File .env
                    </button>
                </form>
            <?php else: ?>
                <div class="message info">
                    ✅ File .env sudah ada. Anda bisa edit manual atau test koneksi database.
                </div>
            <?php endif; ?>

            <form method="POST" style="display: inline;">
                <button type="submit" name="test_connection" class="btn">
                    🔌 Test Koneksi Database
                </button>
            </form>

            <a href="setup_database.php" class="btn btn-warning">
                🗄️ Setup Database
            </a>
        </div>

        <?php if ($env_exists): ?>
            <div class="message info">
                <h4>📝 Isi file .env saat ini:</h4>
                <div class="file-content"><?php echo htmlspecialchars(file_get_contents(__DIR__ . '/.env')); ?></div>
            </div>
        <?php endif; ?>

        <div class="steps">
            <h3>🔧 Konfigurasi XAMPP:</h3>
            <ol>
                <li>Buka XAMPP Control Panel</li>
                <li>Start Apache dan MySQL</li>
                <li>Pastikan port 3306 tidak digunakan aplikasi lain</li>
                <li>Test koneksi via phpMyAdmin: <code>http://localhost/phpmyadmin</code></li>
                <li>Jika perlu, buat database <code>wiracenter_db2</code> di phpMyAdmin</li>
            </ol>
        </div>

        <div class="steps">
            <h3>🚨 Troubleshooting:</h3>
            <ul>
                <li><strong>Access denied:</strong> Periksa username/password MySQL</li>
                <li><strong>Connection refused:</strong> Pastikan MySQL berjalan di XAMPP</li>
                <li><strong>Unknown database:</strong> Jalankan setup_database.php</li>
                <li><strong>Permission denied:</strong> Periksa permission folder project</li>
            </ul>
        </div>
    </div>
</body>
</html> 