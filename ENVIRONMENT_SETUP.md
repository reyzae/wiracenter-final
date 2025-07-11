# Environment Setup Guide - WiraCenter

## 📋 Overview
Panduan lengkap untuk setup environment WiraCenter di XAMPP dengan konfigurasi localhost.

## 🚀 Quick Start

### 1. Prerequisites
- XAMPP (Apache + MySQL + PHP)
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Composer (untuk dependencies)

### 2. Setup Otomatis
Jalankan script setup otomatis:
```bash
# 1. Buat file .env
php create_env.php

# 2. Test koneksi database
php test_connection.php

# 3. Setup database
php setup_database.php
```

## 🔧 Konfigurasi XAMPP

### Step 1: Start XAMPP Services
1. Buka XAMPP Control Panel
2. Start Apache dan MySQL
3. Pastikan kedua service berjalan (status hijau)

### Step 2: Test phpMyAdmin
1. Buka browser dan akses: `http://localhost/phpmyadmin`
2. Login dengan user `root` tanpa password
3. Pastikan dapat mengakses MySQL

### Step 3: Test Koneksi
1. Jalankan `test_connection.php` untuk verifikasi
2. Pastikan aplikasi dapat terhubung ke database

## 📄 File .env Configuration

### Template .env untuk XAMPP
```env
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
```

### Cara Membuat File .env
1. **Otomatis**: Jalankan `create_env.php` di browser
2. **Manual**: Buat file `.env` di root folder project
3. **Copy**: Copy template di atas dan sesuaikan konfigurasi

## 🗄️ Database Setup

### Method 1: Otomatis (Recommended)
```bash
php setup_database.php
```

### Method 2: Manual via phpMyAdmin
1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Buat database baru: `wiracenter_db2`
3. Import file: `database/wiracenter_db2.sql`
4. Atau jalankan schema: `database/schema.sql`

### Default Admin Credentials
- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@wiracenter.com`

**⚠️ Penting**: Ganti password default setelah login pertama!

## 🔍 Troubleshooting

### Masalah Koneksi Database

#### Error: "Access denied for user 'root'@'localhost'"
**Solusi:**
1. Buka phpMyAdmin
2. Pastikan user `root` ada dan dapat login
3. Reset password root jika perlu:
   ```sql
   ALTER USER 'root'@'localhost' IDENTIFIED BY '';
   FLUSH PRIVILEGES;
   ```

#### Error: "Connection refused"
**Solusi:**
1. Pastikan MySQL berjalan di XAMPP
2. Periksa port 3306 tidak digunakan aplikasi lain
3. Restart MySQL di XAMPP Control Panel

#### Error: "Unknown database"
**Solusi:**
1. Jalankan `setup_database.php`
2. Atau buat database manual di phpMyAdmin
3. Import schema dari folder `database/`

#### Error: "Can't connect to MySQL server"
**Solusi:**
1. Pastikan XAMPP MySQL service berjalan
2. Restart MySQL di XAMPP Control Panel
3. Coba akses phpMyAdmin untuk test koneksi manual

### Masalah Permission

#### Error: "Permission denied"
**Solusi:**
1. Periksa permission folder project
2. Pastikan web server dapat menulis ke folder `uploads/`
3. Set permission folder:
   ```bash
   chmod 755 uploads/
   chmod 644 .env
   ```

#### Error: "Headers already sent"
**Solusi:**
1. Pastikan tidak ada whitespace sebelum `<?php`
2. Periksa file encoding (gunakan UTF-8 without BOM)
3. Pastikan output buffering aktif

### Masalah Session

#### Error: "Session cannot be started"
**Solusi:**
1. Periksa session path dapat ditulis
2. Pastikan session_start() dipanggil di awal
3. Periksa konfigurasi session di php.ini

## 🧪 Testing

### Test Koneksi Database
```bash
php test_connection.php
```

### Test Setup Lengkap
1. Jalankan `create_env.php` - Buat file .env
2. Jalankan `test_connection.php` - Test koneksi
3. Jalankan `setup_database.php` - Setup database
4. Akses `http://localhost:8000/admin` - Test admin panel

### Test Manual
1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Test login dengan user `root`
3. Buat database test
4. Test koneksi dari aplikasi

## 📁 File Structure
```
wiracenter-v1-main/
├── .env                          # Environment variables
├── config/
│   ├── config.php               # Main configuration
│   └── database.php             # Database connection
├── create_env.php               # Script buat .env
├── test_connection.php          # Script test koneksi
├── setup_database.php           # Script setup database
├── database/
│   ├── schema.sql               # Database schema
│   └── wiracenter_db2.sql       # Full database dump
└── ENVIRONMENT_SETUP.md         # This file
```

## 🔒 Security Considerations

### Production Setup
1. Ganti password default admin
2. Set `DEBUG_MODE=0`
3. Gunakan strong password untuk database
4. Aktifkan HTTPS
5. Set proper file permissions

### Development Setup
1. `DEBUG_MODE=1` untuk debugging
2. Password kosong untuk database (XAMPP default)
3. Akses localhost only

## 📞 Support

Jika mengalami masalah:
1. Periksa error log: `php_errors.log`
2. Jalankan `test_connection.php` untuk diagnosis
3. Periksa dokumentasi ini
4. Pastikan XAMPP berjalan dengan benar

## 🔄 Update Log

### v1.2 - Localhost Configuration
- ✅ Fixed IP 0.0.0.0 connection issues
- ✅ Improved .env file parsing
- ✅ Enhanced error handling for XAMPP
- ✅ Better troubleshooting guide
- ✅ Simplified configuration for localhost

### v1.1 - IP 0.0.0.0 Support
- ✅ Dukungan IP 0.0.0.0 untuk XAMPP
- ✅ Multiple connection fallback methods
- ✅ Improved error handling
- ✅ Better troubleshooting guide
- ✅ Automatic .env creation script

### v1.0 - Initial Setup
- ✅ Basic XAMPP integration
- ✅ Database setup automation
- ✅ Environment configuration
- ✅ Admin panel setup 