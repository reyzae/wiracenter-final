<?php
/**
 * Script sederhana untuk membuat file .env secara manual
 * Jalankan script ini untuk membuat file .env dengan konfigurasi yang benar
 */

echo "🔧 Membuat file .env untuk WiraCenter...\n\n";

// Konten file .env
$env_content = "# Database Configuration for XAMPP
DB_HOST=localhost
DB_NAME=wiracenter_db2
DB_USER=root
DB_PASS=

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
PASSWORD_MIN_LENGTH=8";

// Path file .env
$env_file = __DIR__ . '/.env';

// Cek apakah file sudah ada
if (file_exists($env_file)) {
    echo "⚠️  File .env sudah ada!\n";
    echo "Apakah Anda ingin menimpa file yang ada? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) !== 'y') {
        echo "❌ Operasi dibatalkan.\n";
        exit;
    }
}

// Tulis file .env
$result = file_put_contents($env_file, $env_content);

if ($result !== false) {
    echo "✅ File .env berhasil dibuat!\n";
    echo "📁 Lokasi: " . $env_file . "\n\n";
    
    echo "📋 Langkah selanjutnya:\n";
    echo "1. Pastikan XAMPP berjalan (Apache + MySQL)\n";
    echo "2. Jalankan: php test_connection.php\n";
    echo "3. Jalankan: php setup_database.php\n";
    echo "4. Akses: http://localhost:8000/admin\n\n";
    
    echo "🔧 Konfigurasi default:\n";
    echo "- Host: localhost\n";
    echo "- Database: wiracenter_db2\n";
    echo "- User: root\n";
    echo "- Password: (kosong)\n\n";
    
    echo "📖 Untuk informasi lebih lanjut, lihat ENVIRONMENT_SETUP.md\n";
} else {
    echo "❌ Gagal membuat file .env!\n";
    echo "Periksa permission folder: " . __DIR__ . "\n";
}
?> 