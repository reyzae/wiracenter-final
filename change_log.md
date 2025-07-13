---
**Timestamp:** 2025-07-10 17:00:00
**Deskripsi:** Menambahkan Fitur Recent Tools di Admin Dashboard - Menampilkan 5 Tools Terbaru dengan Status dan Link Edit.
**Perubahan Utama:**
1. **Database Query:** Menambahkan query untuk mengambil 5 tools terbaru dari database dengan informasi id, title, status, dan publish_date.
2. **Error Handling:** Implementasi try-catch block untuk menangani error jika tabel tools tidak ditemukan.
3. **UI Section:** Menambahkan card section "Recent Tools" dengan layout yang konsisten dengan Recent Articles dan Recent Projects.
4. **Layout Adjustment:** Mengubah layout dari 2 kolom (col-md-6) menjadi 3 kolom (col-md-4) untuk menampung Articles, Projects, dan Tools secara seimbang.
5. **Interactive Elements:** Setiap tool menampilkan title, publish date, status badge, dan link ke halaman edit tools.php.
6. **Empty State:** Menampilkan icon tools dan pesan "No tools yet" ketika belum ada tools yang dibuat.

**File Terpengaruh:**
- `admin/dashboard.php` (menambahkan query recent tools, section HTML, dan layout adjustment)

**Alat Digunakan:** `search_replace`

---
**Timestamp:** 2025-07-10 16:30:00
**Deskripsi:** PENERAPAN FITUR TOGGLE LANGUAGE DAN DARK MODE UNTUK HALAMAN MY-SPACES.PHP DAN ABOUT - Integrasi Lengkap dengan Header.php.
**Perubahan Utama:**
1. **Halaman My Spaces (my-spaces.php):**
   - **REFACTOR** struktur HTML untuk menggunakan header.php yang terintegrasi
   - **HAPUS** DOCTYPE, head, dan body tags yang duplikat
   - **TAMBAHKAN** variabel $page_title dan $page_description untuk header.php
   - **PERTAHANKAN** semua fitur existing (search, filter, content grid, animations)
   - **INTEGRASI** dengan sistem toggle language dan dark mode yang sudah ada

2. **Halaman About (page.php):**
   - **REFACTOR** struktur HTML untuk menggunakan header.php yang terintegrasi
   - **PERBAIKI** halaman 404 dengan layout yang konsisten
   - **TAMBAHKAN** variabel $page_title dan $page_description untuk header.php
   - **PERTAHANKAN** layout custom about page dengan cards dan styling
   - **INTEGRASI** dengan sistem toggle language dan dark mode yang sudah ada

3. **Fitur yang Aktif:**
   - ✅ **Floating Controls** - Toggle language dan dark mode di pojok kanan atas
   - ✅ **Language Persistence** - Menyimpan preferensi bahasa di localStorage
   - ✅ **Theme Persistence** - Menyimpan preferensi tema di localStorage
   - ✅ **Real-time Translation** - Update konten berdasarkan bahasa yang dipilih
   - ✅ **Smooth Transitions** - Animasi halus saat mengubah tema
   - ✅ **Notification System** - Feedback visual saat mengubah language/theme

4. **Optimasi:**
   - **Reduced Code Duplication** - Menghilangkan struktur HTML yang duplikat
   - **Consistent Experience** - Fitur toggle aktif di semua halaman public
   - **Better Maintainability** - Menggunakan header.php yang terintegrasi
   - **Performance Improvement** - Mengurangi HTTP requests

**File Terpengaruh:**
- `my-spaces.php` (REFACTOR - integrasi dengan header.php, hapus struktur HTML duplikat)
- `page.php` (REFACTOR - integrasi dengan header.php, perbaiki halaman 404)

**Alat Digunakan:** `edit_file`

---
**Timestamp:** 2025-07-10 16:00:00
**Deskripsi:** AUDIT & REFACTORING FITUR TOGGLE LANGUAGE DAN DARK MODE - Implementasi Metode Baru Terintegrasi di Header.php.
**Perubahan Utama:**
1. **Audit Fitur Existing:**
   - Menemukan fitur toggle language dan dark mode tersebar di 4 file berbeda
   - Language switcher di navbar.php (baris 25-40)
   - Theme toggle di navbar.php (baris 42-46)
   - JavaScript logic di script.js (baris 20-150)
   - CSS styling di style.css (baris 60-150)
   - File translations.js terpisah (368 baris)

2. **Refactoring Komprehensif:**
   - **HAPUS** semua fitur existing dari navbar.php, script.js, style.css, dan translations.js
   - **BUAT ULANG** dengan metode baru yang terintegrasi di satu file header.php
   - **EMBED** CSS, HTML, dan JavaScript dalam satu file untuk konsistensi

3. **Implementasi Baru di Header.php:**
   - **Floating Controls:** Toggle language dan dark mode sebagai floating buttons di pojok kanan atas
   - **Modern UI:** Glassmorphism design dengan backdrop-filter blur
   - **Responsive:** Adaptif untuk mobile dan desktop
   - **Integrated Script:** JavaScript terintegrasi dengan translations built-in
   - **Notification System:** Feedback visual saat mengubah language/theme

4. **Fitur Baru:**
   - **Fixed Position:** Controls selalu terlihat di semua halaman
   - **Smooth Animations:** Hover effects dan transitions yang halus
   - **Dark Mode Support:** Controls beradaptasi dengan tema yang dipilih
   - **Language Persistence:** Menyimpan preferensi di localStorage
   - **Theme Persistence:** Menyimpan preferensi tema di localStorage

5. **Optimasi:**
   - **Reduced Dependencies:** Menghilangkan ketergantungan pada file terpisah
   - **Better Performance:** Script terintegrasi mengurangi HTTP requests
   - **Consistent Experience:** Fitur aktif di semua halaman public
   - **Clean Codebase:** Menghapus kode yang tidak diperlukan

**File Terpengaruh:**
- `includes/header.php` (REFACTOR - integrasi lengkap fitur toggle language dan dark mode)
- `includes/navbar.php` (REMOVE - hapus language switcher dan theme toggle)
- `assets/js/script.js` (REMOVE - hapus initThemeToggle dan initLanguageSwitcher)
- `assets/css/style.css` (REMOVE - hapus CSS untuk theme toggle dan language switcher)
- `assets/js/translations.js` (DELETE - file dihapus karena sudah terintegrasi)

**Alat Digunakan:** `edit_file`, `search_replace`, `delete_file`

---
**Timestamp:** 2025-07-10 15:00:00
**Deskripsi:** Implementasi Fitur Back to Top Button, Reading Progress Bar, dan Social Share Buttons untuk Frontend Public.
**Perubahan Utama:**
1. **Back to Top Button:**
   - Button floating di pojok kanan bawah
   - Muncul saat scroll > 300px
   - Smooth scroll animation ke atas
   - Hover effects dan responsive design
   - Icon arrow-up yang konsisten

2. **Reading Progress Bar:**
   - Progress bar di bagian atas halaman
   - Gradient warna yang menarik
   - Update real-time berdasarkan scroll position
   - Z-index tinggi untuk visibility

3. **Social Share Buttons:**
   - Implementasi di halaman article.php, project.php, dan tool.php
   - Support 7 platform: Facebook, Twitter, LinkedIn, WhatsApp, Telegram, Email, Copy Link
   - Responsive design dengan flexbox
   - Modern UI dengan hover effects
   - Copy to clipboard functionality dengan fallback

4. **Enhanced Functionality:**
   - Modern clipboard API dengan fallback untuk browser lama
   - Auto-detection halaman konten untuk social share
   - Translation support untuk semua fitur baru
   - Notification system integration

**File Terpengaruh:**
- `assets/css/style.css` (back to top styles, reading progress, social share buttons)
- `assets/js/script.js` (back to top logic, reading progress, social share functionality)
- `assets/js/translations.js` (translation keys untuk fitur baru)
- `includes/header.php` (HTML elements untuk back to top dan reading progress)
- `article.php` (social share buttons)
- `project.php` (social share buttons)
- `tool.php` (social share buttons)

**Alat Digunakan:** `edit_file`, `search_replace`

---
**Timestamp:** 2025-07-10 14:00:00
**Deskripsi:** Implementasi Fitur Dark Mode Toggle dan Language Switcher (ENG | ID) untuk Frontend Public.
**Perubahan Utama:**
1. **Dark Mode Toggle:**
   - Menambahkan CSS variables untuk light dan dark themes dengan transisi smooth
   - Implementasi toggle button dengan icon yang berubah (moon/sun)
   - Penyimpanan preferensi theme di localStorage
   - Animasi hover dan click effects
   - Support untuk semua komponen UI (navbar, cards, forms, footer)

2. **Language Switcher (ENG | ID):**
   - Dropdown language selector dengan flag icons
   - Sistem translation lengkap dengan file `assets/js/translations.js`
   - Penyimpanan preferensi bahasa di localStorage
   - Update dinamis konten halaman berdasarkan bahasa yang dipilih
   - Support untuk navigation, form labels, dan konten utama

3. **Notification System:**
   - Sistem notifikasi modern dengan animasi slide-in
   - Support untuk berbagai tipe (success, error, info, warning)
   - Auto-dismiss setelah 5 detik
   - Close button manual
   - Responsive design

4. **UI/UX Improvements:**
   - Konsistensi desain dengan font Fira Sans
   - Smooth transitions untuk semua interaksi
   - Responsive design untuk mobile devices
   - Accessibility improvements dengan proper ARIA labels

**File Terpengaruh:**
- `assets/css/style.css` (dark mode variables, theme toggle styles, language switcher styles, notification system)
- `assets/js/script.js` (theme toggle logic, language switcher logic, notification system)
- `assets/js/translations.js` (dibuat baru - sistem translation lengkap)
- `includes/header.php` (menambahkan data-theme attribute, notification container, translations.js)
- `includes/navbar.php` (menambahkan theme toggle button dan language switcher)

**Alat Digunakan:** `edit_file`, `search_replace`

---
**Timestamp:** 2025-07-10 13:00:00
**Deskripsi:** Perbaikan Error Database di index.php - Mengatasi Fatal Error PDOException untuk kolom 'image' yang tidak ditemukan.
**Perubahan Utama:**
1. **Perbaikan Query Database:** Mengubah semua referensi kolom 'image' menjadi 'featured_image' sesuai dengan struktur database yang ada di schema.sql.
2. **Error Handling:** Menambahkan try-catch blocks untuk semua query database untuk mencegah fatal error dan memberikan fallback yang graceful.
3. **Fallback Content:** Menambahkan pengecekan array kosong dan menampilkan pesan "Belum Ada [Konten]" ketika database kosong atau query gagal.
4. **Optimasi Slider:** Memperbaiki logika slider navigation dots dan auto-slide untuk menangani kasus ketika tidak ada konten dinamis.
5. **Struktur Data:** Menginisialisasi array kosong di awal untuk menghindari undefined variable errors.
**File Terpengaruh:**
- `index.php` (perbaikan query dan error handling)
**Alat Digunakan:** `search_replace`

---
**Timestamp:** 2025-07-10 12:00:00
**Deskripsi:** Membuat Database Schema SQL yang Lengkap dan Terstruktur untuk Wiracenter CMS.
**Perubahan Utama:**
1.  **Schema Database Lengkap:** Membuat file `database/schema.sql` yang komprehensif dengan semua tabel yang diperlukan untuk sistem CMS Wiracenter.
2.  **Tabel yang Diimplementasikan:**
    *   **Core Tables:** `users`, `articles`, `projects`, `tools`, `pages`
    *   **Content Management:** `navigation_items`, `faqs`, `content_block_types`, `content_blocks`
    *   **Media & Files:** `files`
    *   **System & Settings:** `site_settings`, `contact_messages`, `activity_logs`, `notifications`
3.  **Fitur Database:**
    *   Foreign key constraints untuk integritas data
    *   Indexes untuk optimasi performa query
    *   Full-text search indexes untuk pencarian konten
    *   Soft delete dengan kolom `deleted_at`
    *   Timestamp tracking (`created_at`, `updated_at`)
    *   Enum constraints untuk status dan roles
4.  **Default Data:**
    *   Admin user default dengan password yang aman
    *   Site settings lengkap untuk konfigurasi website
    *   Navigation items default
    *   FAQs default untuk halaman contact
    *   Content block types dan blocks default
    *   Halaman About dan My Spaces default
5.  **Optimasi:**
    *   Character set UTF8MB4 untuk support emoji dan karakter khusus
    *   Collation unicode_ci untuk pencarian yang case-insensitive
    *   Engine InnoDB untuk support foreign keys dan transactions
**File Terpengaruh:**
- `database/schema.sql` (dibuat baru dengan struktur lengkap)
**Alat Digunakan:** `edit_file`

---
**Timestamp:** 2025-07-10 11:00:00
**Deskripsi:** Implementasi Lengkap Halaman Admin Articles.php dengan Konsistensi Desain dan Logika.
**Perubahan Utama:**
1.  **Halaman Articles Admin:** Membuat halaman `admin/articles.php` yang lengkap dengan operasi CRUD (Create, Read, Update, Delete) mengikuti pola yang konsisten dengan halaman admin lainnya.
2.  **Fitur Utama:**
    *   Daftar articles dengan fitur pencarian dan filter status
    *   Form tambah/edit article dengan TinyMCE editor
    *   Upload gambar featured image
    *   Auto-generate excerpt dari content
    *   Bulk actions (publish, draft, archive, delete)
    *   Auto-generate slug dari title
    *   Validasi form yang komprehensif
    *   Soft delete (move to trash)
    *   Activity logging dan notifications
3.  **Integrasi Konsisten:**
    *   Menggunakan struktur database yang sudah ada (tabel `articles`)
    *   Mengikuti pola desain dan logika yang sama dengan `tools.php` dan `projects.php`
    *   Integrasi dengan sistem autosave TinyMCE
    *   Menggunakan komponen UI yang konsisten (cards, tables, forms)
    *   Table header menggunakan `table-primary` untuk visibility yang lebih baik
**File Terpengaruh:**
- `admin/articles.php` (dibuat baru)
**Alat Digunakan:** `edit_file`

---
**Timestamp:** 2025-07-10 10:00:00
**Deskripsi:** Implementasi Lengkap Halaman Admin Tools.php dengan Konsistensi Desain dan Logika.
**Perubahan Utama:**
1.  **Halaman Tools Admin:** Membuat halaman `admin/tools.php` yang lengkap dengan operasi CRUD (Create, Read, Update, Delete) mengikuti pola yang konsisten dengan halaman admin lainnya.
2.  **Fitur Utama:**
    *   Daftar tools dengan fitur pencarian, filter status, dan filter kategori
    *   Form tambah/edit tool dengan TinyMCE editor
    *   Upload gambar featured image
    *   Bulk actions (publish, draft, archive, delete)
    *   Auto-generate slug dari title
    *   Validasi form yang komprehensif
    *   Soft delete (move to trash)
    *   Activity logging dan notifications
3.  **Integrasi Konsisten:**
    *   Menggunakan struktur database yang sudah ada (tabel `tools`)
    *   Mengikuti pola desain dan logika yang sama dengan `articles.php` dan `projects.php`
    *   Integrasi dengan sistem autosave TinyMCE
    *   Menggunakan komponen UI yang konsisten (cards, tables, forms)
**File Terpengaruh:**
- `admin/tools.php` (dibuat baru)
**Alat Digunakan:** `edit_file`

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

---
**Timestamp:** 2025-07-10 10:00:00
**Deskripsi:** Troubleshooting & Fix: Sidebar Admin Collapse/Expand Tidak Berfungsi
**Perubahan & Solusi:**
1. Diagnosis penyebab menu sidebar collapse/expand tidak berjalan di halaman admin:
   - Kode JavaScript toggle sidebar (`menu-toggle`/`wrapper`) hilang di `admin/assets/js/admin-script.js`.
   - Urutan dan jenis script Bootstrap JS tidak sesuai (harus pakai `bootstrap.bundle.min.js`).
   - Tidak ada error pada struktur HTML, namun script JS dan CSS perlu penyesuaian.
2. Solusi yang dilakukan:
   - Menambahkan kembali kode JS toggle sidebar di `admin/assets/js/admin-script.js`.
   - Memastikan hanya ada satu script Bootstrap JS (`bootstrap.bundle.min.js`) dan diletakkan sebelum `</body>` di `admin/includes/header.php`.
   - Menghapus menu "TEST SUBMENU" dari sidebar admin.
3. Hasil: Fitur collapse/expand sidebar admin kini berjalan normal.
**File Terpengaruh:**
- `admin/assets/js/admin-script.js`
- `admin/includes/header.php`
**Alat Digunakan:** `replace`, `write_file`

---
# Change Log - Wiracenter CMS

## [2025-01-07] - Implementasi Status Inactive Users

### Added
- **Tab "Inactive Users"** di admin/users.php untuk menampilkan user dengan status 'inactive'
- **Tombol "Set Inactive"** untuk mengubah status user dari active ke inactive
- **Tombol "Activate"** untuk mengaktifkan kembali user dari status inactive
- **Status 'inactive'** ke dropdown create user
- **Aksi suspend/unsuspend** untuk mengelola status user (kecuali admin)
- **Filter status** yang hanya muncul di tab "Active Users"

### Modified
- **Query database** untuk memisahkan user berdasarkan tab (active/inactive)
- **Modal konfirmasi** untuk aksi set_inactive dan activate
- **Logging** untuk semua aksi user management

### Database Changes
- **File: add_inactive_status.sql** - SQL untuk menambahkan status 'inactive' ke kolom status di tabel users
- **ENUM values**: 'active', 'suspended', 'inactive'

### Security
- **Admin user protection**: Tombol suspend/unsuspend tidak tersedia untuk user dengan username 'admin'
- **Confirmation modals**: Semua aksi berbahaya memerlukan konfirmasi

### Files Modified
- `admin/users.php` - Implementasi lengkap status inactive users
- `add_inactive_status.sql` - SQL untuk update database

---

## [2025-01-06] - Admin Panel Enhancements

### Added
- **Complete Settings Page** (`admin/settings.php`) dengan kategori dan field types
- **Files Management** (`admin/files.php`) dengan upload, list, filter, dan soft delete
- **Trash Management** (`admin/trash.php`) untuk restore dan permanent delete
- **Navigation submenu removal** dari sidebar

### Modified
- **Admin header** untuk menghilangkan submenu navigation
- **Settings page** dengan modern UI dan categorized sections
- **Success/error notifications** untuk semua admin actions

### Database Fixes
- **Missing columns** di tabel users: status, temp_password, temp_password_expired_at
- **Missing columns** di tabel pages: display_order, deleted_at

### Files Modified
- `admin/settings.php` - Complete settings management
- `admin/files.php` - File upload and management
- `admin/trash.php` - Soft delete management
- `admin/includes/header.php` - Remove navigation submenu
- `admin/users.php` - Fix missing columns
- `admin/pages.php` - Fix missing columns

---

## [2025-01-05] - Database & Login Issues Resolution

### Fixed
- **Database connection issues** - Mismatch between application and phpMyAdmin databases
- **Login errors** - Password hash verification and temporary password handling
- **Missing database columns** - Added status, temp_password, temp_password_expired_at to users table
- **Table structure inconsistencies** - Fixed missing columns in various tables

### Added
- **Database verification scripts** untuk debugging connection issues
- **Password reset functionality** dengan temporary password system
- **Activity logging** untuk login attempts

### Files Modified
- `admin/login.php` - Enhanced login logic with proper error handling
- `admin/users.php` - Added missing columns support
- `test_connection.php` - Database connection verification
- `cek_env_db.php` - Environment and database checking

---

## [2025-01-04] - About Page & Navigation Updates

### Added
- **Modern About Page** dengan responsive design dan improved content
- **Enhanced page.php template** untuk better content display
- **Navbar highlighting** untuk about page

### Modified
- **About page content** di database dengan modern layout
- **Page template** untuk better styling dan responsiveness
- **Navigation logic** untuk proper page highlighting

### Files Modified
- `page.php` - Enhanced template for better content display
- `includes/navbar.php` - Updated navigation highlighting
- Database content for about page

---

## [2025-01-03] - Initial Setup & Configuration

### Added
- **Complete Wiracenter CMS** dengan admin panel
- **Database schema** dengan semua tabel yang diperlukan
- **User authentication** system
- **Content management** untuk articles, projects, tools, pages
- **File upload** system
- **Settings management**
- **Contact form** handling
- **Activity logging**

### Features
- **Admin Dashboard** dengan statistics
- **User Management** dengan roles (admin, editor, viewer)
- **Content Management** untuk semua content types
- **Media Management** untuk file uploads
- **Settings Management** untuk site configuration
- **Contact Management** untuk message handling
- **Security Features** dengan login protection dan role-based access

### Files Created
- Complete admin panel structure
- Database schema and initial data
- Configuration files
- Frontend templates
- API endpoints