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