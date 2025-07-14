---
**Timestamp:** 2025-07-13 20:00:00
**Catatan Troubleshooting:**
Jika terjadi error fatal terkait kolom JSON (misal: 'Invalid JSON text' atau 'SQLSTATE[22032]') pada MySQL/PHP:

1. Jalankan query SQL berikut untuk membersihkan data tidak valid:
   UPDATE nama_tabel SET nama_kolom_json = '[]' WHERE JSON_VALID(nama_kolom_json) = 0 OR nama_kolom_json IS NULL OR nama_kolom_json = '' OR nama_kolom_json = 'Invalid value.';
   (Ganti nama_tabel dan nama_kolom_json sesuai kebutuhan)
2. Di PHP, sebelum insert/update ke kolom JSON, lakukan validasi dan encode array ke JSON. Saat membaca, jika gagal decode, fallback ke array kosong.
3. Untuk settings bertipe JSON (misal: site_settings.setting_value), validasi sudah otomatis di admin/settings.php dan tidak menyebabkan fatal error, hanya warning di UI.
---
---
**Timestamp:** 2025-07-13 19:00:00
**Deskripsi:** IMPLEMENTASI FITUR MODERN CONTENT PAGES - Breadcrumb, Reading Time, Author Info, Next/Prev Navigation, Print Button, dan Social Share untuk Tool Detail Page.
**Perubahan Utama:**
1. **Breadcrumb Navigation:**
   - Breadcrumb trail: Home > Tools > [Tool Title]
   - Styling konsisten dengan tema website
   - Responsive design untuk mobile
   - Dark mode support

2. **Reading Time Estimation:**
   - Kalkulasi otomatis berdasarkan jumlah kata (200 kata/menit)
   - Display dengan icon jam dan format "X min read"
   - Integrasi dengan tool meta section

3. **Enhanced Tool Meta:**
   - Publish date dengan format yang konsisten
   - Reading time estimation
   - Author information (jika tersedia)
   - Last updated date (jika berbeda dari created date)
   - Live tool link dengan icon external link
   - Icon-based layout yang modern

4. **Next/Previous Navigation:**
   - Query database untuk tool sebelum/sesudah
   - Support bilingual content (slug_en, slug_id)
   - Hover effects dengan transform dan shadow
   - Responsive layout dengan arrow indicators

5. **Action Buttons:**
   - Print button dengan window.print() functionality
   - Copy link button dengan clipboard API
   - Visit Tool button dengan icon external link
   - Success feedback dengan visual indicators
   - Modern button styling

6. **Social Share Integration:**
   - Facebook, Twitter, LinkedIn, WhatsApp sharing
   - Dynamic URL encoding untuk current page
   - Title integration untuk social media
   - Responsive circular button design

7. **Reading Progress Bar:**
   - Fixed position progress bar di top
   - Real-time update berdasarkan scroll position
   - Gradient styling yang menarik
   - High z-index untuk visibility

8. **CSS Enhancements:**
   - Tool-specific styling dengan modern typography
   - Dark mode support untuk semua komponen baru
   - Responsive design untuk mobile devices
   - Smooth transitions dan hover effects

**File Terpengaruh:**
- `tool.php` (REFACTOR - tambah breadcrumb, reading time, meta info, navigation, action buttons, social share)

**Alat Digunakan:** `edit_file`

---
**Timestamp:** 2025-07-13 18:30:00
**Deskripsi:** IMPLEMENTASI FITUR MODERN CONTENT PAGES - Breadcrumb, Reading Time, Author Info, Next/Prev Navigation, Print Button, dan Social Share untuk Project Detail Page.
**Perubahan Utama:**
1. **Breadcrumb Navigation:**
   - Breadcrumb trail: Home > Projects > [Project Title]
   - Styling konsisten dengan tema website
   - Responsive design untuk mobile
   - Dark mode support

2. **Reading Time Estimation:**
   - Kalkulasi otomatis berdasarkan jumlah kata (200 kata/menit)
   - Display dengan icon jam dan format "X min read"
   - Integrasi dengan project meta section

3. **Enhanced Project Meta:**
   - Publish date dengan format yang konsisten
   - Reading time estimation
   - Author information (jika tersedia)
   - Last updated date (jika berbeda dari created date)
   - Live project link dengan icon external link
   - Icon-based layout yang modern

4. **Next/Previous Navigation:**
   - Query database untuk project sebelum/sesudah
   - Support bilingual content (slug_en, slug_id)
   - Hover effects dengan transform dan shadow
   - Responsive layout dengan arrow indicators

5. **Action Buttons:**
   - Print button dengan window.print() functionality
   - Copy link button dengan clipboard API
   - Visit Project button dengan icon external link
   - Success feedback dengan visual indicators
   - Modern button styling

6. **Social Share Integration:**
   - Facebook, Twitter, LinkedIn, WhatsApp sharing
   - Dynamic URL encoding untuk current page
   - Title integration untuk social media
   - Responsive circular button design

7. **Reading Progress Bar:**
   - Fixed position progress bar di top
   - Real-time update berdasarkan scroll position
   - Gradient styling yang menarik
   - High z-index untuk visibility

8. **CSS Enhancements:**
   - Project-specific styling dengan modern typography
   - Dark mode support untuk semua komponen baru
   - Responsive design untuk mobile devices
   - Smooth transitions dan hover effects

**File Terpengaruh:**
- `project.php` (REFACTOR - tambah breadcrumb, reading time, meta info, navigation, action buttons, social share)

**Alat Digunakan:** `edit_file`

---
**Timestamp:** 2025-07-13 18:00:00
**Deskripsi:** IMPLEMENTASI FITUR MODERN CONTENT PAGES - Breadcrumb, Reading Time, Author Info, Next/Prev Navigation, Print Button, dan Social Share untuk Article Detail Page.
**Perubahan Utama:**
1. **Breadcrumb Navigation:**
   - Breadcrumb trail: Home > Articles > [Article Title]
   - Styling konsisten dengan tema website
   - Responsive design untuk mobile
   - Dark mode support

2. **Reading Time Estimation:**
   - Kalkulasi otomatis berdasarkan jumlah kata (200 kata/menit)
   - Display dengan icon jam dan format "X min read"
   - Integrasi dengan article meta section

3. **Enhanced Article Meta:**
   - Publish date dengan format yang konsisten
   - Reading time estimation
   - Author information (jika tersedia)
   - Last updated date (jika berbeda dari created date)
   - Icon-based layout yang modern

4. **Next/Previous Navigation:**
   - Query database untuk artikel sebelum/sesudah
   - Support bilingual content (slug_en, slug_id)
   - Hover effects dengan transform dan shadow
   - Responsive layout dengan arrow indicators

5. **Action Buttons:**
   - Print button dengan window.print() functionality
   - Copy link button dengan clipboard API
   - Success feedback dengan visual indicators
   - Modern button styling

6. **Social Share Integration:**
   - Facebook, Twitter, LinkedIn, WhatsApp sharing
   - Dynamic URL encoding untuk current page
   - Title integration untuk social media
   - Responsive circular button design

7. **Reading Progress Bar:**
   - Fixed position progress bar di top
   - Real-time update berdasarkan scroll position
   - Gradient styling yang menarik
   - High z-index untuk visibility

8. **CSS Enhancements:**
   - Article-specific styling dengan modern typography
   - Dark mode support untuk semua komponen baru
   - Responsive design untuk mobile devices
   - Smooth transitions dan hover effects

**File Terpengaruh:**
- `article.php` (REFACTOR - tambah breadcrumb, reading time, meta info, navigation, action buttons, social share)
- `assets/css/style.css` (ADD - article styles, breadcrumb, navigation, dark mode support)

**Alat Digunakan:** `edit_file`, `search_replace`

---
**Timestamp:** 2025-07-13
**Deskripsi:** Integrasi auto-translate konten bilingual (EN/ID) via API (DeepL/LibreTranslate) sudah disiapkan di skrip `translate_articles_deepl.php`, namun belum aktif karena kendala API key/akses (HTTP 403 Forbidden dari DeepL).

**Instruksi Aktivasi:**
1. Pastikan API key valid dan akun DeepL sudah aktif.
2. Jalankan skrip `php translate_articles_deepl.php` untuk mengisi kolom EN di database.
3. Cek hasil di frontend dengan toggle bahasa.
4. Jika ingin pakai API lain (misal LibreTranslate), ganti endpoint dan parameter di skrip.

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

## [2024-12-XX] - Performance & Security Audit - Phase 1

### 🔧 **AUDIT & OPTIMIZATION IMPROVEMENTS**

#### **project.php**
- ✅ **Input Sanitization**: Added proper sanitization for slug parameter using `filter_var()`
- ✅ **Database Query Optimization**: Optimized from 3 separate queries to 1 optimized query with subqueries for next/prev projects
- ✅ **Social Share URLs**: Fixed social sharing URLs to use proper HTTPS detection instead of hardcoded `$_SERVER['HTTP_HOST']`
- ✅ **Performance**: Added lazy loading for featured images with error handling
- ✅ **Security**: Improved copy link functionality to use server-generated URLs

#### **page.php**
- ✅ **Input Sanitization**: Added proper sanitization for slug parameter
- ✅ **Database Error Handling**: Improved error handling to prevent exposing database errors to users
- ✅ **Bilingual Support**: Added proper multi-language support for title, content, and excerpt
- ✅ **Performance**: Added lazy loading for profile images with fallback handling
- ✅ **Code Organization**: Moved large inline CSS to external file `assets/css/page-styles.css`
- ✅ **Security**: Added `deleted_at IS NULL` checks to prevent showing deleted content

#### **tool.php**
- ✅ **Input Sanitization**: Added proper sanitization for slug parameter
- ✅ **Database Query Optimization**: Optimized from 3 separate queries to 1 optimized query with subqueries for next/prev tools
- ✅ **Social Share URLs**: Fixed social sharing URLs to use proper HTTPS detection
- ✅ **Performance**: Added lazy loading for featured images with error handling
- ✅ **Security**: Improved copy link functionality to use server-generated URLs

#### **my-spaces.php**
- ✅ **Input Sanitization**: Added proper sanitization for search, category, and type filter parameters
- ✅ **Database Connection**: Fixed database connection method from `getConnection()` to `connect()`
- ✅ **Performance**: Added LIMIT clauses to search queries to prevent performance issues
- ✅ **Code Organization**: Moved large inline CSS to external file `assets/css/my-spaces.css`
- ✅ **JavaScript Organization**: Moved inline JavaScript to external file `assets/js/my-spaces.js`
- ✅ **URL Consistency**: Fixed inconsistent URL structure for article links
- ✅ **Security**: Added `deleted_at IS NULL` checks to prevent showing deleted content
- ✅ **Performance**: Added lazy loading for card images with intersection observer

### 📁 **NEW FILES CREATED**
- `assets/css/page-styles.css` - External CSS for page.php styles
- `assets/css/my-spaces.css` - External CSS for my-spaces.php styles  
- `assets/js/my-spaces.js` - External JavaScript for my-spaces.php functionality

### 🔒 **SECURITY IMPROVEMENTS**
- Input sanitization for all user inputs using `filter_var()`
- Proper error handling without exposing sensitive information
- Added `deleted_at IS NULL` checks to prevent showing deleted content
- Fixed social sharing URLs to prevent potential security issues

### ⚡ **PERFORMANCE IMPROVEMENTS**
- Database query optimization (reduced from 3 queries to 1 optimized query)
- Lazy loading for images with error handling
- External CSS and JavaScript files for better caching
- Added LIMIT clauses to prevent large result sets
- Intersection Observer for smooth animations

### 🎨 **UI/UX IMPROVEMENTS**
- Consistent URL structure across all content types
- Better error handling with user-friendly messages
- Improved image loading with fallback handling
- Enhanced animations and transitions

---

## [2024-12-XX] - CSRF Protection Implementation

### 🔒 **SECURITY ENHANCEMENTS**

#### **CSRF Token System**
- ✅ **Token Generation**: Implemented secure CSRF token generation using `random_bytes()`
- ✅ **Token Validation**: Added server-side validation for all form submissions
- ✅ **Session Management**: Integrated CSRF tokens with PHP sessions
- ✅ **Form Integration**: Added CSRF tokens to all admin forms and API endpoints

#### **Files Updated**
- `admin/articles.php` - Added CSRF protection to create/edit/delete forms
- `admin/projects.php` - Added CSRF protection to create/edit/delete forms
- `admin/tools.php` - Added CSRF protection to create/edit/delete forms
- `admin/pages.php` - Added CSRF protection to create/edit/delete forms
- `admin/content_blocks.php` - Added CSRF protection to create/edit/delete forms
- `admin/faqs.php` - Added CSRF protection to create/edit/delete forms
- `admin/users.php` - Added CSRF protection to user management forms
- `admin/settings.php` - Added CSRF protection to settings forms
- `admin/login.php` - Added CSRF protection to login form
- `admin/profile.php` - Added CSRF protection to profile update form
- `admin/force_change_password.php` - Added CSRF protection to password change form
- `api/contact.php` - Added CSRF protection to contact form
- `contact.php` - Added CSRF protection to contact form
- `admin/api/upload.php` - Added CSRF protection to file upload
- `admin/api/upload_image.php` - Added CSRF protection to image upload
- `admin/api/save_draft.php` - Added CSRF protection to draft saving
- `admin/api/insert_notification.php` - Added CSRF protection to notifications
- `admin/api/mark_notification_read.php` - Added CSRF protection to notification actions
- `admin/api/notification_actions.php` - Added CSRF protection to notification actions

#### **New Files Created**
- `admin/includes/csrf.php` - CSRF token management functions
- `apply_csrf_protection.php` - Automated script to apply CSRF protection (temporary)

### 🔧 **IMPLEMENTATION DETAILS**
- **Token Generation**: Uses `random_bytes(32)` for cryptographically secure tokens
- **Token Storage**: Tokens stored in PHP sessions with automatic cleanup
- **Validation**: Server-side validation with proper error handling
- **Form Integration**: Hidden input fields with `name="csrf_token"`
- **Error Handling**: Graceful fallback with user-friendly error messages

---

## [2024-12-XX] - URL Security & Clean URLs Implementation

### 🔒 **URL SECURITY ENHANCEMENTS**

#### **Clean URL Implementation**
- ✅ **URL Rewriting**: Implemented .htaccess rules for clean URLs
- ✅ **Extension Hiding**: Removed .php extensions from URLs
- ✅ **Parameter Hiding**: Clean URLs for articles, projects, tools, and pages
- ✅ **Admin Panel**: Clean URLs for admin panel routes
- ✅ **Redirects**: Proper redirects for old URLs to maintain compatibility

#### **URL Structure Changes**
- **Articles**: `/article/slug` instead of `article.php?slug=slug`
- **Projects**: `/project/slug` instead of `project.php?slug=slug`
- **Tools**: `/tool/slug` instead of `tool.php?slug=slug`
- **Pages**: `/page/slug` instead of `page.php?slug=slug`
- **Admin**: `/admin/login` instead of `admin/login.php`

#### **Files Updated**
- `.htaccess` - Added comprehensive URL rewriting rules
- `article.php` - Updated to handle clean URL structure
- `project.php` - Updated to handle clean URL structure
- `tool.php` - Updated to handle clean URL structure
- `page.php` - Updated to handle clean URL structure
- All internal links updated to use new URL format

### 🔧 **IMPLEMENTATION DETAILS**
- **Apache Rewrite Rules**: Comprehensive .htaccess configuration
- **Fallback Handling**: Proper 404 handling for invalid URLs
- **SEO Benefits**: Cleaner, more user-friendly URLs
- **Security**: Reduced exposure of server technology and file structure

---

## [2024-12-XX] - HTML Entity Display Fix

### 🐛 **BUG FIXES**

#### **HTML Entity Display Issue**
- ✅ **Problem**: HTML entities (e.g., `&#039;`) showing in article titles instead of proper characters
- ✅ **Solution**: Added `htmlspecialchars_decode()` to properly decode HTML entities
- ✅ **Files Updated**: 
  - `admin/dashboard.php`
  - `index.php`
  - All content display files

### 🔧 **IMPLEMENTATION DETAILS**
- **Root Cause**: HTML entities were being double-encoded
- **Solution**: Used `htmlspecialchars_decode()` to properly decode entities
- **Testing**: Verified proper display of apostrophes and special characters

---

## [2024-12-XX] - Language & Label Updates

### 🌐 **CONTENT IMPROVEMENTS**

#### **Label Standardization**
- ✅ **Language Consistency**: Changed Indonesian labels to English for better international accessibility
- ✅ **Updated Labels**:
  - "Baca Selengkapnya" → "Read More"
  - "Lihat Detail" → "View Details" 
  - "Gunakan Tool" → "Use Tool"
  - "Baca Artikel" → "Read Article"

#### **Files Updated**
- `index.php` - Updated all content link labels
- `my-spaces.php` - Updated content link labels
- All admin content management files

### 🎯 **BENEFITS**
- **International Audience**: Better accessibility for non-Indonesian speakers
- **Consistency**: Uniform labeling across the entire application
- **Professional Appearance**: More polished and professional user interface

---

## [2024-12-XX] - Initial Security Audit

### 🔒 **SECURITY ASSESSMENT**

#### **URL Security Best Practices**
- ✅ **HTTPS Implementation**: Proper SSL/TLS configuration
- ✅ **Input Validation**: Comprehensive input sanitization and validation
- ✅ **XSS Prevention**: Proper output encoding using `htmlspecialchars()`
- ✅ **SQL Injection Prevention**: Prepared statements throughout the application
- ✅ **File Access Security**: Proper file upload validation and storage
- ✅ **Error Handling**: Secure error handling without information disclosure

#### **Missing Security Features**
- ⚠️ **CSRF Protection**: Forms lacked CSRF token protection
- ⚠️ **Rate Limiting**: No rate limiting on forms and API endpoints
- ⚠️ **Content Security Policy**: Missing CSP headers

### 📋 **RECOMMENDATIONS IMPLEMENTED**
- CSRF token system for all forms
- Input sanitization improvements
- Enhanced error handling
- Security headers implementation

---

## [2024-12-XX] - Project Initialization

### 🚀 **INITIAL SETUP**

#### **Core Features**
- ✅ **Content Management**: Articles, projects, tools, and pages
- ✅ **Admin Panel**: Comprehensive admin interface
- ✅ **User Management**: User authentication and authorization
- ✅ **File Management**: Secure file upload and storage
- ✅ **Search Functionality**: Advanced search across content
- ✅ **Multi-language Support**: Bilingual content support
- ✅ **Responsive Design**: Mobile-friendly interface

#### **Technical Stack**
- **Backend**: PHP 8.0+ with PDO
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Security**: Prepared statements, input validation, XSS protection
- **Performance**: Optimized queries, caching, lazy loading

### 📁 **PROJECT STRUCTURE**
- `admin/` - Admin panel files
- `api/` - API endpoints
- `assets/` - CSS, JS, and image files
- `config/` - Configuration files
- `includes/` - Shared PHP includes
- `uploads/` - User uploaded files
- `vendor/` - Composer dependencies

---
Audit & improvement admin/projects.php (2024-07-13):
  - Tambah validasi tipe dan ukuran file upload (featured_image): hanya jpg, png, gif, webp, max 2MB.
  - Konversi input technologies (string) ke array (explode koma) jika bukan array/JSON.
  - Pastikan output judul dan deskripsi pakai htmlspecialchars_decode agar entity HTML tidak tampil di UI.
  - Konsistensi font-family 'Fira Sans', Arial, Helvetica, sans-serif di seluruh input, textarea, dan TinyMCE.
  - Tambahkan fallback error jika upload gambar gagal.
Audit & improvement admin/pages.php (2024-07-13):
  - Tambah validasi CSRF token pada backend untuk semua form POST (page & navigation).
  - Tambah input CSRF token pada form navigation.
  - Setelah aksi hapus (page/navigation), redirect ke list dan tampilkan pesan sukses/gagal.
  - Terapkan font-family 'Fira Sans', Arial, Helvetica, sans-serif pada input, textarea, dan TinyMCE.
  - Tambahkan validasi ukuran maksimal (2MB) untuk data base64 profile image (about page).
Audit & improvement admin/tools.php (2024-07-13):
  - Tambah validasi tipe dan ukuran file upload (featured_image): hanya jpg, png, gif, webp, max 2MB.
  - Pastikan output judul dan deskripsi pakai htmlspecialchars_decode agar entity HTML tidak tampil di UI.
  - Konsistensi font-family 'Fira Sans', Arial, Helvetica, sans-serif di seluruh input, textarea, dan TinyMCE.
  - Tambahkan fallback error jika upload gambar gagal.
Audit & improvement admin/content_blocks.php (2024-07-13):
  - Tambah validasi CSRF token pada backend untuk semua form POST (content block & block type).
  - Tambah input CSRF token pada semua form.
  - Terapkan font-family 'Fira Sans', Arial, Helvetica, sans-serif pada input, textarea, dan TinyMCE.
Audit & improvement admin/faqs.php (2024-07-13):
  - Tambah validasi CSRF token pada backend untuk semua form POST (FAQ & bulk action).
  - Tambah input CSRF token pada semua form.
  - Terapkan font-family 'Fira Sans', Arial, Helvetica, sans-serif pada input, textarea, dan TinyMCE.
Audit & improvement admin/contact_messages.php (2024-07-13):
  - Tambah validasi CSRF token pada backend untuk semua form POST (bulk action, reply).
  - Tambah input CSRF token pada semua form.
  - Tampilkan pesan sukses/gagal di UI untuk bulk action dan reply.
  - Terapkan font-family 'Fira Sans', Arial, Helvetica, sans-serif pada input, textarea, tabel, dsb.
Audit & improvement admin_upload_about_image.php (2024-07-13):
  - Tambah validasi CSRF token pada upload.
  - Tambah validasi ukuran file (max 2MB).
  - Tambah feedback error/sukses via redirect dengan pesan.
Audit & improvement admin/export_data.php (2024-07-13):
  - Tambah validasi CSRF pada aksi custom query (POST).
  - Terapkan font-family 'Fira Sans', Arial, Helvetica, sans-serif pada form, tabel, dsb.
Audit & improvement admin/activity_logs.php (2024-07-13):
  - Terapkan font-family 'Fira Sans', Arial, Helvetica, sans-serif pada form, tabel, dsb.
Audit & improvement admin/force_change_password.php (2024-07-13):
  - Terapkan font-family 'Fira Sans', Arial, Helvetica, sans-serif pada input/password.