<?php
// Script untuk setup database otomatis di XAMPP
require_once 'config/config.php';

echo "<h2>Setup Database Otomatis untuk XAMPP</h2>";

// Cek apakah form sudah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_database') {
        echo "<h3>Membuat Database...</h3>";
        
        try {
            // Koneksi ke MySQL tanpa memilih database
            $pdo = new PDO("mysql:host=localhost", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // Buat database jika belum ada
            $pdo->exec("CREATE DATABASE IF NOT EXISTS wiracenter_db2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "✅ Database 'wiracenter_db2' berhasil dibuat<br>";
            
            // Pilih database
            $pdo->exec("USE wiracenter_db2");
            
            // Import schema
            $schema_file = __DIR__ . '/database/schema.sql';
            if (file_exists($schema_file)) {
                $sql = file_get_contents($schema_file);
                
                // Split SQL statements
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $pdo->exec($statement);
                    }
                }
                
                echo "✅ Schema database berhasil diimport<br>";
            } else {
                echo "❌ File schema.sql tidak ditemukan<br>";
            }
            
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage() . "<br>";
        }
        
    } elseif ($action === 'create_admin') {
        echo "<h3>Membuat Admin User...</h3>";
        
        try {
            $db = new Database();
            $conn = $db->connect();
            
            if ($conn) {
                // Cek apakah admin sudah ada
                $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
                $stmt->execute();
                $admin_exists = $stmt->fetchColumn() > 0;
                
                if (!$admin_exists) {
                    // Buat admin user
                    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->execute(['admin', 'admin@wiracenter.com', $password_hash, 'admin', 'active']);
                    
                    echo "✅ Admin user berhasil dibuat<br>";
                    echo "📧 Username: admin<br>";
                    echo "🔑 Password: admin123<br>";
                } else {
                    echo "⚠️ Admin user sudah ada<br>";
                }
            } else {
                echo "❌ Tidak dapat koneksi ke database<br>";
            }
            
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<hr>";
}

// Form untuk setup
?>
<form method="POST" style="max-width: 600px; margin: 20px 0;">
    <div class="card">
        <div class="card-header">
            <h4>Setup Database</h4>
        </div>
        <div class="card-body">
            <p>Script ini akan:</p>
            <ul>
                <li>Membuat database 'wiracenter_db2'</li>
                <li>Import schema dari database/schema.sql</li>
                <li>Membuat admin user pertama</li>
            </ul>
            
            <div class="mb-3">
                <button type="submit" name="action" value="create_database" class="btn btn-primary">
                    🗄️ Buat Database & Import Schema
                </button>
            </div>
            
            <div class="mb-3">
                <button type="submit" name="action" value="create_admin" class="btn btn-success">
                    👤 Buat Admin User
                </button>
            </div>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <h4>Langkah Selanjutnya</h4>
    </div>
    <div class="card-body">
        <p>Setelah setup selesai:</p>
        <ol>
            <li><a href="test_connection.php">Test koneksi database</a></li>
            <li><a href="index.php">Akses website</a></li>
            <li><a href="admin/login.php">Login ke admin panel</a></li>
        </ol>
        
        <div class="alert alert-info">
            <strong>Default Admin Credentials:</strong><br>
            Username: <code>admin</code><br>
            Password: <code>admin123</code>
        </div>
    </div>
</div>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.card { border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px; }
.card-header { background: #f8f9fa; padding: 15px; border-bottom: 1px solid #ddd; }
.card-body { padding: 15px; }
.btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px; }
.btn-primary { background: #007bff; color: white; }
.btn-success { background: #28a745; color: white; }
.alert { padding: 15px; border-radius: 5px; margin: 15px 0; }
.alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
</style> 