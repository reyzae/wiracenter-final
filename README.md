# Wiracenter Portfolio & CMS

A modern personal portfolio and content management system (CMS) built with PHP & MySQL, featuring a robust admin dashboard, bilingual content, dark mode, and advanced security.

---

## 🌟 Fitur Utama

### Website Portfolio (Frontend)
- **Desain Modern & Responsif**: Menggunakan Bootstrap 5, mendukung dark mode & font 'Fira Sans'.
- **Home**: Hero slider, highlight artikel/proyek/tools terbaru.
- **About**: Profil profesional, skills, pengalaman, dan edukasi.
- **My Spaces**: Filter & pencarian lintas artikel, proyek, tools.
- **Contact**: Form kontak dengan validasi & FAQ.
- **Bilingual**: Toggle bahasa (ID/EN) untuk konten utama (artikel, proyek, tools, pages).
- **Dark Mode**: Toggle tema terang/gelap, otomatis simpan preferensi.

### Admin Dashboard (CMS)
- **Dashboard Real-time**: Statistik, notifikasi, quick actions.
- **Manajemen Artikel**: CRUD, TinyMCE, autosave draft, SEO, featured image, bilingual.
- **Manajemen Proyek**: CRUD, deskripsi, konten, tech tags (JSON), URL, bilingual.
- **Manajemen Tools**: CRUD, deskripsi, konten, kategori, URL, bilingual.
- **Pages**: CRUD, konten custom, bilingual.
- **Content Blocks**: CRUD, reusable section, tipe dinamis.
- **FAQs**: CRUD, urutan tampil, status.
- **File Management**: Upload, hapus, kelola media.
- **Navigation**: Menu dinamis, urutan & status.
- **User Management**: Role-based (admin/editor/viewer), soft delete, suspend, reset password, audit log.
- **Settings**: Konfigurasi dinamis (site, theme, maintenance, dsb).
- **Contact Messages**: Lihat, kelola pesan kontak.
- **Notifications**: In-app, mark as read/delete, riwayat.
- **Activity Logs**: Semua aksi penting dicatat.
- **Trash**: Soft delete & restore konten.
- **Backup/Export**: Backup DB, export data.
- **Profile**: Update profil & password.
- **Help**: Dokumentasi & support.

---

## 🚀 Teknologi
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JS (ES6), Bootstrap 5
- **Icons**: Font Awesome 6
- **Editor**: TinyMCE 6 (autosave, image upload)
- **Session**: PHP Sessions
- **Security**: Password hashing, SQL injection prevention, XSS protection, CSRF, security headers

---

## 📦 Instalasi

### Prasyarat
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx (mod_rewrite aktif untuk Apache)

### 1. Clone Repo
```bash
git clone https://github.com/yourusername/wiracenter-final.git
cd wiracenter-final
```

### 2. Setup Database
1. Buat database MySQL: `wiracenter_db2`
2. Import schema:
```bash
mysql -u username -p wiracenter_db2 < database/schema.sql
```

### 3. Konfigurasi
- Edit `config/database.php` untuk kredensial DB
- Edit `config/config.php` untuk SITE_URL, dsb

### 4. Permission
```bash
chmod 755 uploads/
```

### 5. Akses Website
- **Frontend**: `http://your-domain.com/`
- **Admin**: `http://your-domain.com/admin/`

---

## 🔐 Default Login
- **Username**: admin
- **Password**: wiracenter!
> Ganti password default setelah login!

---

## 📁 Struktur Project (2025)

```
wiracenter-v1-main/
├── admin/
│   ├── api/                  # API endpoints (upload, notifications, stats, dsb)
│   ├── assets/
│   │   ├── css/              # admin-style.css
│   │   └── js/               # tinymce-init.js, admin-script.js
│   ├── includes/             # Header, footer, sidebar
│   ├── ...                   # Halaman admin (articles, projects, tools, pages, users, dsb)
├── api/
│   └── contact.php           # Public contact API
├── assets/
│   ├── css/                  # style.css, page-styles.css, dsb
│   └── js/                   # script.js, my-spaces.js, contact.js
├── config/
│   ├── config.php            # Main config
│   └── database.php          # DB connection
├── database/
│   └── schema.sql            # DB schema
├── includes/
│   ├── footer.php
│   ├── header.php
│   └── sidebar.php
├── uploads/                  # Uploaded files
├── ...                       # index.php, about.php, my-spaces.php, contact.php, project.php, article.php, tool.php, maintenance.php
├── change_log.md             # Changelog & troubleshooting log
└── README.md                 # This file
```

---

## 🗂️ Modul & Fitur Admin
- **Articles/Projects/Tools/Pages**: CRUD, bilingual, autosave draft, featured image, SEO, status, schedule
- **Content Blocks**: CRUD, tipe dinamis, reusable
- **FAQs**: CRUD, urutan, status
- **Files**: Upload, kelola, hapus
- **Navigation**: Menu dinamis
- **Users**: Role-based, soft delete, suspend, reset password, audit log, filter/search/pagination
- **Settings**: Konfigurasi site/theme/maintenance
- **Notifications**: In-app, mark as read/delete, riwayat
- **Activity Logs**: Semua aksi penting dicatat
- **Trash**: Soft delete/restore
- **Backup/Export**: Backup DB, export data
- **Profile**: Update profil & password
- **Help**: Dokumentasi & support

---

## 🛠️ API Endpoints (Admin)
- `admin/api/upload_image.php` — Upload image untuk editor
- `admin/api/save_draft.php` — Autosave draft konten
- `admin/api/notification_actions.php` — Mark/delete notifications
- `admin/api/mark_notification_read.php` — Mark all as read
- `admin/api/insert_notification.php` — Insert notification (test/dev)
- `admin/api/stats.php` — Dashboard stats
- `admin/api/upload.php` — General file upload

---

## 🧩 Custom Scripts & Styles
- **Admin JS**: `admin/assets/js/tinymce-init.js`, `admin/assets/js/admin-script.js`
- **Admin CSS**: `admin/assets/css/admin-style.css`
- **Frontend JS**: `assets/js/script.js`, `assets/js/my-spaces.js`, `assets/js/contact.js`
- **Frontend CSS**: `assets/css/style.css`, `assets/css/page-styles.css`, dsb

---

## 🛡️ Security & Best Practices
- Password hashing (`password_hash()`)
- SQL injection prevention (prepared statements)
- XSS protection (`htmlspecialchars()`, HTMLPurifier)
- CSRF protection (token di semua form penting)
- Session-based authentication
- Role-based access control
- File upload validation
- Security headers (.htaccess & PHP)
- Error log terpisah (php_errors.log)

---

## 🌐 Bilingual & Auto-Translate
- Semua konten utama (artikel, proyek, tools, pages) mendukung field bilingual (ID/EN)
- Toggle bahasa di frontend (slug, judul, konten, dsb)
- Script auto-translate (`translate_articles_deepl.php`) siap pakai (butuh API key valid)
- Toggle slug otomatis (`assets/js/script.js`)

---

## 🌙 Dark Mode & Theme
- Toggle dark/light mode di frontend & admin
- Preferensi user disimpan (localStorage/cookie)
- CSS variabel untuk theme (`[data-theme="dark"]` di style.css)
- Admin: class `theme-dark` pada body, frontend: `[data-theme]` pada html

---

## 📝 Changelog & Troubleshooting
- Semua troubleshooting & patch dicatat di `change_log.md`
- Error JSON kolom: lihat instruksi di `change_log.md` (SQL & PHP handling)

---

## ⚠️ Notes, Rencana & Troubleshoot Belum Selesai

1. **Pengembangan Live Preview**
   - Rencana: Opsi posisi panel preview (kanan, bawah, tab, dsb) dan style lebih fleksibel.
   - Status: Belum dikembangkan, masih basic.
2. **Aksi action=delete blank page**
   - Masih ada kasus redirect gagal/blank pada beberapa halaman admin setelah delete (selain articles.php).
   - Rencana: Refactor blok delete ke paling atas file, pastikan tidak ada output sebelum header, dan konsisten redirect ke list.
3. **Testing & Konsistensi CRUD**
   - Pastikan semua halaman konten admin (projects, tools, pages, content_blocks, faqs) CRUD-nya konsisten seperti articles.php.
4. **Troubleshoot lain**
   - Lihat `change_log.md` untuk log detail troubleshooting dan patch.

---

# Wiracenter User Management Features

## 1. Soft Delete & Restore User
- **Delete user**: User tidak dihapus permanen, hanya kolom `deleted_at` yang diisi. User tidak bisa login dan tidak muncul di list utama.
- **Restore user**: Di tab "Trashed Users", admin bisa mengembalikan user (set `deleted_at` ke NULL).

## 2. Reset Password (Admin Panel)
- Admin bisa reset password user via tombol di tabel user.
- Password otomatis digenerate (format `#user-xxxx`), readonly, background abu-abu, ada toggle show/hide dan tombol copy.
- Admin bisa generate ulang password random di modal.
- Setelah reset, password user diupdate (hash), dan temporary password serta expired_at disimpan (berlaku 1 jam).

## 3. Temporary Password Flow
- Password temporary berlaku 1 jam (`temp_password`, `temp_password_expired_at`).
- User login dengan password ini **wajib ganti password** sebelum akses dashboard.
- Jika expired, login ditolak dan muncul pesan: "Your temporary password has expired. Please contact the administrator to get a new password."
- Setelah password diganti, kolom `temp_password` dan `temp_password_expired_at` dihapus.

## 4. Admin Panel: Lihat/Generate Ulang Temporary Password
- Admin bisa melihat temporary password (jika masih berlaku) di tabel user.
- Jika expired, admin bisa generate ulang password baru (langsung bisa di-copy, expired 1 jam).

## 5. Filter, Search, Pagination
- Tersedia filter role/status, search user (username/email), dan pagination di tabel user (tab Active & Trashed).

## 6. Audit Log
- Semua aksi penting (edit, suspend, delete, restore, reset password, force change) dicatat di log aktivitas.

## 7. Notifikasi Login
- Pesan khusus untuk user suspended, deleted, expired, dan wajib ganti password:
  - Suspended: "Your account has been suspended. Please contact the administrator for more information."
  - Deleted: "Your account has been deleted. Please contact the administrator if you believe this is a mistake."
  - Temporary password expired: "Your temporary password has expired. Please contact the administrator to get a new password."
  - Force change: "You must change your password before accessing the dashboard."

## 8. Flow Admin & User
### Admin:
1. Bisa create user, reset password, suspend, restore, soft delete, lihat/generate temp password.
2. Semua aksi penting tercatat di audit log.

### User:
1. Jika login dengan temp password, wajib ganti password.
2. Jika password expired, hubungi admin untuk dapat password baru.
3. Jika suspended/deleted, tidak bisa login dan dapat notifikasi.

---

**Seluruh fitur di atas sudah terintegrasi dan siap digunakan.**

## Catatan Integrasi Auto-Translate Konten
- Fitur auto-translate konten bilingual (EN/ID) via API (DeepL/LibreTranslate) sudah disiapkan di skrip `translate_articles_deepl.php`.
- Saat ini belum aktif karena kendala API key/akses (HTTP 403 Forbidden dari DeepL).
- Untuk mengaktifkan kembali:
  1. Pastikan API key valid dan akun DeepL sudah aktif.
  2. Jalankan skrip `php translate_articles_deepl.php` untuk mengisi kolom EN di database.
  3. Cek hasil di frontend dengan toggle bahasa.
- Jika ingin pakai API lain (misal LibreTranslate), tinggal ganti endpoint dan parameter di skrip.