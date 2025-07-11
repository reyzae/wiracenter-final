---
**Timestamp:** 2025-07-09 08:00:00
**Deskripsi:** Perbaikan Komprehensif untuk Editor Konten dan Halaman Admin.
**Perubahan Utama:**
1.  **Perbaikan Halaman Admin:** Memperbaiki halaman `admin/projects.php` dan `admin/tools.php` yang tidak dapat diakses dengan memulihkan logika PHP yang hilang untuk operasi CRUD (Create, Read, Update, Delete).
2.  **Perbaikan Toolbar Editor:** Mengatasi masalah hilangnya toolbar editor TinyMCE di semua halaman konten dengan:
    *   Membuat skrip inisialisasi `admin/assets/js/tinymce-init.js` lebih tangguh agar tidak gagal pada halaman yang tidak memiliki editor.
    *   Memperbaiki urutan inisialisasi variabel di `admin/pages.php` untuk memastikan fungsionalitas *autosave*.
    *   Menambahkan variabel yang diperlukan untuk *autosave* di `admin/content_blocks.php` agar konsisten dengan halaman lain.
    *   Memastikan `admin/includes/header.php` memuat skrip inisialisasi editor dengan benar.
**File Terpengaruh:**
- `admin/projects.php`
- `admin/tools.php`
- `admin/assets/js/tinymce-init.js`
- `admin/pages.php`
- `admin/content_blocks.php`
- `admin/includes/header.php`
**Alat Digunakan:** `replace`, `write_file`

---
# Log Perubahan Kode oleh Gemini CLI

File ini mencatat semua modifikasi yang dilakukan oleh agen Gemini CLI pada codebase.
Setiap entri mencakup timestamp, deskripsi perubahan, file yang terpengaruh, dan alat yang digunakan.

---
**Timestamp:** 2025-07-09 07:00:00
**Deskripsi:** Mengimplementasikan fitur Autosave/Draft untuk editor TinyMCE, memungkinkan penyimpanan otomatis konten yang sedang dikerjakan.
**Perubahan Utama:**
1.  **Database Schema:** Menambahkan kolom `draft_content` (TEXT) ke tabel `articles`, `projects`, dan `tools` untuk menyimpan konten draft.
2.  **API Endpoint:** Membuat `admin/api/save_draft.php` untuk menerima dan menyimpan konten draft ke database.
3.  **TinyMCE Configuration:** Mengkonfigurasi `admin/assets/js/tinymce-init.js` untuk mengaktifkan plugin `autosave` dan mengarahkan penyimpanan draft ke `save_draft.php`.
4.  **PHP Logic:** Memodifikasi `admin/articles.php`, `admin/projects.php`, dan `admin/tools.php` untuk:
    *   Memuat `draft_content` ke editor jika ada saat halaman dimuat (mode edit).
    *   Menghapus `draft_content` dari database setelah konten utama berhasil disimpan atau diperbarui.
**File Terpengaruh:** 
- `database/schema.sql`
- `admin/api/save_draft.php`
- `admin/assets/js/tinymce-init.js`
- `admin/articles.php`
- `admin/projects.php`
- `admin/tools.php`
**Alat Digunakan:** `replace`, `write_file`

---
**Timestamp:** 2025-07-09 06:35:00
**Deskripsi:** Memperbaiki error fatal 'Class "HTMLPurifier_Config" not found' yang menyebabkan halaman creator blank. Akar masalahnya adalah `ezyang/htmlpurifier` tidak terdaftar di `composer.json`.
**Perubahan Utama:**
1.  Menambahkan `"ezyang/htmlpurifier": "^5.6",
        "ezyang/htmlpurifier": "^4.14"` ke bagian `require` di `composer.json`.
2.  Menjalankan `composer update` untuk menginstal dependensi yang hilang dan membangun ulang autoloader.
3.  Memindahkan `require_once __DIR__ . '/../vendor/autoload.php';` ke bagian atas file `articles.php`, `projects.php`, dan `tools.php` untuk memastikan autoloader dimuat lebih awal.
**File Terpengaruh:** 
- `composer.json`
- `admin/articles.php`
- `admin/projects.php`
- `admin/tools.php`
**Alat Digunakan:** `replace`, `run_shell_command`

---
**Timestamp:** 2025-07-08 11:00:00
**Deskripsi:** Melakukan upgrade sistem editor dari TinyMCE ke CKEditor 5, meningkatkan keamanan dengan HTMLPurifier, dan mengintegrasikan upload gambar.
**Perubahan Utama:**
1.  **Editor Upgrade:** Mengganti library TinyMCE dengan CKEditor 5 untuk pengalaman pengguna yang lebih modern.
2.  **Peningkatan Keamanan:** Mengimplementasikan `HTMLPurifier` pada proses penyimpanan konten (Artikel, Proyek, Tools) untuk mencegah serangan XSS.
3.  **Fitur Upload Gambar:** Mengkonfigurasi CKEditor 5 agar terintegrasi dengan skrip upload gambar backend, memungkinkan upload langsung dari editor.
**File Terpengaruh:** 
- `admin/includes/header.php` (Mengganti script editor)
- `admin/includes/footer.php` (Memperbarui script inisialisasi)
- `admin/assets/js/ckeditor-init.js` (File inisialisasi baru untuk CKEditor)
- `admin/articles.php` (Implementasi HTMLPurifier)
- `admin/projects.php` (Implementasi HTMLPurifier)
- `admin/tools.php` (Implementasi HTMLPurifier)
- `admin/api/upload_image.php` (Penyesuaian untuk kompatibilitas dengan CKEditor)
- `admin/assets/js/tinymce-init.js` (Dihapus)
- `admin/assets/js/tinymce-functions.js` (Dihapus)
**Alat Digunakan:** `replace`, `write_file`, `run_shell_command`

---
**Timestamp:** 2025-07-08 10:00:00
**Deskripsi:** Memperbaiki duplikasi definisi tabel dan menambahkan definisi tabel `faqs` yang benar di `database/schema.sql`.
**File Terpengaruh:** `database/schema.sql`
**Alat Digunakan:** `replace`

---
**Timestamp:** 2025-07-08 10:05:00
**Deskripsi:** Menghapus baris `INSERT` untuk 'Operating Hours' dari `database/schema.sql` sesuai permintaan pengguna.
**File Terpengaruh:** `database/schema.sql`
**Alat Digunakan:** `replace`

---
**Timestamp:** 2025-07-08 10:10:00
**Deskripsi:** Memperbarui nilai `contact_address` di `site_settings` dan `contact_address_card` di `content_blocks` dalam `database/schema.sql` menjadi 'Central Jakarta, Indonesia'.
**File Terpengaruh:** `database/schema.sql`
**Alat Digunakan:** `replace`

---
**Timestamp:** 2025-07-08 10:15:00
**Deskripsi:** Menyamakan konten `content_blocks` untuk FAQ di halaman kontak dengan `default FAQs` di `database/schema.sql`. Ini melibatkan pembaruan `faq_services`, `faq_project_time`, `faq_maintenance`, dan `faq_technologies`.
**File Terpengaruh:** `database/schema.sql`
**Alat Digunakan:** `replace`

---
**Timestamp:** 2025-07-08 10:20:00
**Deskripsi:** Menimpa seluruh file `database/schema.sql` dengan versi yang sudah dirapikan dan diperiksa.
**File Terpengaruh:** `database/schema.sql`
**Alat Digunakan:** `write_file`

---
**Timestamp:** 2025-07-08 10:30:00
**Deskripsi:** Menambahkan `INSERT` statement untuk konten halaman "About Me" ke tabel `pages` di `database/schema.sql`.
**File Terpengaruh:** `database/schema.sql`
**Alat Digunakan:** `replace`

---
**Timestamp:** 2025-07-08 10:35:00
**Deskripsi:** Mengubah `about.php` untuk mengarahkan ulang ke `page.php?slug=about`, menjadikan halaman "About" dinamis dan dapat dikelola melalui admin dashboard.
**File Terpengaruh:** `about.php`
**Alat Digunakan:** `write_file`

---
**Timestamp:** 2025-07-09 07:00:00
**Deskripsi:** Memperbarui dan merapikan `database/schema.sql` dengan versi yang lebih robust dari `schema_gpt.sql`. Perubahan meliputi penambahan `IF NOT EXISTS`, penggunaan backticks, penentuan `ENGINE=InnoDB`, penamaan foreign key, dan konsolidasi `INSERT` statements.
**File Terpengaruh:** `database/schema.sql`
**Alat Digunakan:** `write_file`

---
**Timestamp:** 2025-07-09 07:00:00
**Deskripsi:** Memastikan konten halaman "About Me" rata tengah dengan menambahkan kelas `text-center` pada div utama konten di `database/schema.sql`.
**File Terpengaruh:** `database/schema.sql`
**Alat Digunakan:** `replace`

---
**Timestamp:** 2025-07-09 09:30:00
**Deskripsi:** Troubleshooting & Perbaikan Live Preview Gambar di Editor Admin
**Perubahan Utama:**
1. Diagnosis masalah gambar tidak tampil di panel preview editor admin (TinyMCE), baik dari upload maupun paste clipboard.
2. Analisis path gambar yang salah (relatif, tanpa slash, atau mengarah ke /admin/uploads/).
3. Patch bertahap pada `admin/assets/js/tinymce-init.js`:
    * Menambahkan logika agar semua path gambar di preview selalu `/uploads/namafile.png`.
    * Menangani gambar base64 hasil paste clipboard.
    * Memastikan attribute `src` pada tag `<img>` di preview di-set dengan benar.
    * Menambahkan log debug untuk menampilkan path gambar sebelum dan sesudah di-set.
4. Diagnosis dan verifikasi file gambar benar-benar ada di folder `/uploads/` dan bisa diakses langsung via browser.
5. Konfirmasi perbaikan: gambar dari upload dan base64 kini tampil di panel preview editor admin.
**File Terpengaruh:**
- `admin/assets/js/tinymce-init.js`
- (Diagnosis: folder `/uploads/` dan path gambar di HTML editor)
**Alat Digunakan:** `replace`, `write_file`, `debug_console`

---
## 2025-07-10
- **BUGFIX:** Mengatasi masalah halaman blank setelah aksi suspend/unsuspend/delete pada halaman admin/users.php.
- **Penyebab:**
  - Ada output (HTML/spasi) dari file lain (misal: includes/header.php) sebelum fungsi header('Location: ...') dipanggil, sehingga redirect gagal dan halaman menjadi blank.
  - PHP tidak bisa melakukan redirect jika sudah ada output yang terkirim ke browser.
- **Solusi:**
  - Menambahkan output buffering (`ob_start()` di baris 1 dan `ob_end_clean()` sebelum setiap `exit();`) pada file admin/users.php agar semua output dibersihkan sebelum redirect.
  - Sekarang, setelah aksi konfirmasi, user akan selalu kembali ke halaman users.php?tab=active dan status user langsung berubah tanpa halaman blank.

---
## [Perbaikan] 10 Juli 2025
- Menambahkan kolom `excerpt` pada tabel `projects` dan `tools` di database melalui phpMyAdmin untuk mengatasi error SQLSTATE[42S22] (Unknown column 'excerpt' in 'field list') yang menyebabkan halaman admin/projects.php dan admin/tools.php blank.

---
# Change Log - WiraCenter

## [2024-12-XX] - Fixed IP 0.0.0.0 Issues & Improved .env Parsing

### 🔧 Critical Database Connection Fixes
- **config/database.php**: 
  - ✅ Fixed IP 0.0.0.0 connection issues (not valid for database connections)
  - ✅ Changed default host from '0.0.0.0' to 'localhost'
  - ✅ Improved .env file parsing with better error handling
  - ✅ Enhanced comment and whitespace handling in .env parsing
  - ✅ Added validation for empty keys in environment variables
  - ✅ Improved connection fallback methods (localhost, 127.0.0.1)
  - ✅ Better error logging for troubleshooting

### 🆕 Updated Setup Scripts
- **create_env.php**: 
  - ✅ Updated template to use 'localhost' instead of '0.0.0.0'
  - ✅ Improved configuration for standard XAMPP setup
  - ✅ Enhanced troubleshooting instructions
  - ✅ Better error handling and user feedback

- **test_connection.php**: 
  - ✅ Fixed .env parsing to handle comments and whitespace properly
  - ✅ Updated default host configuration
  - ✅ Improved error messages for XAMPP troubleshooting
  - ✅ Enhanced connection testing methods

### 📚 Updated Documentation
- **ENVIRONMENT_SETUP.md**: 
  - ✅ Removed references to problematic IP 0.0.0.0 configuration
  - ✅ Updated to use standard localhost configuration
  - ✅ Simplified XAMPP setup instructions
  - ✅ Enhanced troubleshooting section
  - ✅ Added v1.2 update log entry

### 🐛 Bug Fixes
- **IP 0.0.0.0 Issue**: 
  - ❌ Fixed: "The requested address is not valid in its context" error
  - ✅ Solution: Use 'localhost' as default host for database connections
  - ✅ Added fallback to 127.0.0.1 if localhost fails

- **.env Parsing Issue**: 
  - ❌ Fixed: "Failed to parse dotenv file. Encountered unexpected whitespace" error
  - ✅ Solution: Improved parsing logic with proper comment and whitespace handling
  - ✅ Added validation for empty keys and malformed lines

### 🔍 Connection Method Improvements
- **Primary Method**: Direct connection with configured host
- **Fallback 1**: localhost if current host is not localhost
- **Fallback 2**: 127.0.0.1 for alternative local connection
- **Fallback 3**: Server-only connection (without database) for troubleshooting

### 🛠️ XAMPP Integration
- **Standard Configuration**: Use localhost for database connections
- **Service Detection**: Automatic detection of MySQL and Apache services
- **Port Testing**: Verification of common XAMPP ports (80, 443, 3306, 8080, 8000)
- **Error Handling**: Better error messages for common XAMPP issues

### 🚀 Quick Setup Process (Updated)
1. Run `create_env.php` to generate .env file with localhost configuration
2. Run `test_connection.php` to verify database connectivity
3. Run `setup_database.php` to create database schema
4. Access admin panel at `http://localhost:8000/admin`

### 🔒 Security & Performance
- **Connection Reliability**: Multiple fallback methods for better reliability
- **Error Logging**: Enhanced logging for debugging and troubleshooting
- **Configuration Validation**: Better validation of environment variables
- **Performance**: Optimized connection attempts to reduce timeout issues

---

## [2024-12-XX] - IP 0.0.0.0 Support & Database Connection Improvements

### 🔧 Enhanced Database Connection
- **config/database.php**: 
  - ✅ Added support for IP 0.0.0.0 configuration
  - ✅ Implemented multiple connection fallback methods (localhost, 127.0.0.1, 0.0.0.0)
  - ✅ Added automatic database creation if not exists
  - ✅ Improved environment variable loading with quote handling
  - ✅ Enhanced error logging for XAMPP troubleshooting
  - ✅ Added connection testing with multiple methods

### 🆕 New Setup Scripts
- **create_env.php**: 
  - ✅ Interactive web interface for .env file creation
  - ✅ Automatic configuration for XAMPP with IP 0.0.0.0
  - ✅ Database connection testing functionality
  - ✅ Step-by-step setup guidance
  - ✅ Troubleshooting tips and XAMPP configuration help

- **test_connection.php**: 
  - ✅ Comprehensive database connection testing
  - ✅ Multiple connection method testing
  - ✅ XAMPP service status checking
  - ✅ Port availability verification
  - ✅ Detailed error reporting and troubleshooting

### 📚 Updated Documentation
- **ENVIRONMENT_SETUP.md**: 
  - ✅ Added IP 0.0.0.0 configuration guide
  - ✅ Enhanced XAMPP setup instructions
  - ✅ Improved troubleshooting section
  - ✅ Added security considerations
  - ✅ Updated file structure documentation

### 🔍 Configuration Improvements
- **Default Host**: Changed from 'localhost' to '0.0.0.0' for better XAMPP compatibility
- **Connection Methods**: Added fallback to localhost and 127.0.0.1 if 0.0.0.0 fails
- **Error Handling**: Better error messages for common XAMPP issues
- **Environment Loading**: Improved .env file parsing with quote handling

### 🛠️ XAMPP Integration
- **Bind Address**: Support for MySQL bind-address = 0.0.0.0
- **Port Testing**: Automatic detection of MySQL and Apache ports
- **Service Status**: Real-time checking of XAMPP services
- **Firewall Considerations**: Added guidance for Windows firewall configuration

### 🚀 Quick Setup Process
1. Run `create_env.php` to generate .env file
2. Run `test_connection.php` to verify database connectivity
3. Run `setup_database.php` to create database schema
4. Access admin panel at `http://localhost:8000/admin`

### 🔒 Security & Performance
- **Connection Pooling**: Improved database connection management
- **Error Logging**: Enhanced logging for debugging
- **Fallback Methods**: Multiple connection attempts for reliability
- **Configuration Validation**: Better validation of environment variables

---

## [2024-12-XX] - Comprehensive QA Check and Automatic Fixes

### 🔧 Database Configuration
- **config/config.php**: 
  - ✅ Enabled autoload for HTMLPurifier and dependencies
  - ✅ Added environment variable loading with error handling
  - ✅ Improved error reporting configuration
  - ✅ Enhanced redirect function with headers_sent check
  - ✅ Added proper session management

- **config/database.php**: 
  - ✅ Added backward compatibility for existing code
  - ✅ Improved error logging and debugging
  - ✅ Added helpful error messages for XAMPP issues
  - ✅ Enhanced connection testing functionality

### 🛠️ Admin Panel Fixes
- **admin/articles.php**: 
  - ✅ Fixed undefined variable $id initialization
  - ✅ Added proper error handling for database operations
  - ✅ Improved bulk action handling with row counting
  - ✅ Enhanced activity logging

- **admin/projects.php**: 
  - ✅ Fixed undefined variable issues
  - ✅ Added proper initialization of arrays and variables
  - ✅ Improved error handling and user feedback
  - ✅ Enhanced bulk operations

- **admin/tools.php**: 
  - ✅ Fixed undefined variable $id
  - ✅ Added proper error handling
  - ✅ Improved form validation
  - ✅ Enhanced user experience

- **admin/pages.php**: 
  - ✅ Fixed undefined variable issues
  - ✅ Added proper error handling
  - ✅ Improved bulk operations
  - ✅ Enhanced activity logging

- **admin/users.php**: 
  - ✅ Fixed undefined variable $tab
  - ✅ Added proper initialization of variables
  - ✅ Improved error handling
  - ✅ Enhanced user management

- **admin/trash.php**: 
  - ✅ Fixed missing deleted_at column handling
  - ✅ Added proper error handling for database operations
  - ✅ Improved trash management functionality
  - ✅ Enhanced user feedback

### 🔐 Authentication & Session
- **admin/login.php**: 
  - ✅ Fixed session regeneration issues
  - ✅ Added proper session validation
  - ✅ Improved security measures
  - ✅ Enhanced error handling

### 🎨 Frontend Improvements
- **admin/includes/footer.php**: 
  - ✅ Added missing JavaScript setup
  - ✅ Fixed undefined variable issues
  - ✅ Improved admin panel functionality
  - ✅ Enhanced user experience

### 🌐 Public Pages
- **index.php**: 
  - ✅ Added database connection error handling
  - ✅ Improved content sanitization
  - ✅ Enhanced error reporting
  - ✅ Better user experience

- **article.php**: 
  - ✅ Added proper error handling
  - ✅ Improved content display
  - ✅ Enhanced security measures
  - ✅ Better user experience

- **project.php**: 
  - ✅ Added database error handling
  - ✅ Improved content sanitization
  - ✅ Enhanced security
  - ✅ Better user experience

- **tool.php**: 
  - ✅ Added proper error handling
  - ✅ Improved content display
  - ✅ Enhanced security measures
  - ✅ Better user experience

### 📚 Documentation & Setup
- **ENVIRONMENT_SETUP.md**: 
  - ✅ Created comprehensive setup guide
  - ✅ Added XAMPP integration instructions
  - ✅ Included troubleshooting steps
  - ✅ Added security considerations

- **setup_database.php**: 
  - ✅ Created automated database setup script
  - ✅ Added schema import functionality
  - ✅ Included admin user creation
  - ✅ Enhanced error handling

- **test_connection.php**: 
  - ✅ Created database connection testing script
  - ✅ Added comprehensive error reporting
  - ✅ Included configuration validation
  - ✅ Enhanced debugging capabilities

### 🔍 Error Handling Improvements
- **Global**: 
  - ✅ Added try-catch blocks around database operations
  - ✅ Improved error logging and reporting
  - ✅ Enhanced user feedback for errors
  - ✅ Better exception handling

- **Headers**: 
  - ✅ Fixed "headers already sent" warnings
  - ✅ Added proper header checking before redirects
  - ✅ Improved session management
  - ✅ Enhanced security measures

### 📊 Activity Logging
- **Global**: 
  - ✅ Improved activity logging functionality
  - ✅ Enhanced error tracking
  - ✅ Better user action monitoring
  - ✅ Comprehensive audit trail

### 🚀 Performance Optimizations
- **Database**: 
  - ✅ Improved connection management
  - ✅ Enhanced query optimization
  - ✅ Better resource utilization
  - ✅ Reduced memory usage

### 🔒 Security Enhancements
- **Input Validation**: 
  - ✅ Enhanced input sanitization
  - ✅ Improved SQL injection prevention
  - ✅ Better XSS protection
  - ✅ Enhanced CSRF protection

- **Session Management**: 
  - ✅ Improved session security
  - ✅ Enhanced authentication
  - ✅ Better authorization checks
  - ✅ Comprehensive security measures

### 📝 Code Quality
- **Standards**: 
  - ✅ Improved code consistency
  - ✅ Enhanced readability
  - ✅ Better documentation
  - ✅ Comprehensive error handling

### 🛠️ Maintenance
- **Logging**: 
  - ✅ Enhanced error logging
  - ✅ Improved debugging capabilities
  - ✅ Better monitoring
  - ✅ Comprehensive troubleshooting

---

## [Previous Entries]
- Initial project setup and configuration
- Basic functionality implementation
- User authentication system
- Content management features
- File upload system
- Admin panel development