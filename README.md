# Wiracenter Portfolio - Personal Portfolio Website with CMS

A complete personal portfolio website with a powerful Content Management System (CMS) built using PHP and MySQL.

## 🌟 Features

### Frontend Portfolio Website
- **Modern Design**: Responsive design with Bootstrap 5
- **Home Page**: Hero section with featured projects and articles
- **About Page**: Professional profile with skills, experience, and education
- **My Spaces**: Showcase of projects, articles, and tools with filtering
- **Contact Page**: Contact form with FAQ section

### Admin Dashboard CMS
- **Real-time Dashboard**: Live statistics and quick actions
- **Articles Management**: Full CRUD operations with TinyMCE editor
- **Projects Management**: Project portfolio with technologies and links
- **Tools Management**: Tools showcase with categories
- **Pages Management**: Custom pages with rich content
- **Content Blocks**: Reusable content sections
- **FAQs Management**: Manage frequently asked questions
- **File Management**: Upload and manage media files
- **Site Settings**: Dynamic site configuration
- **User Management**: Role-based access control (Admin/Editor/Viewer)
- **Contact Messages**: View and manage contact form submissions
- **Notifications**: In-app notification system with history
- **Activity Logs**: Track all admin actions
- **Trash**: Soft delete and restore content

## 🚀 Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6)
- **Framework**: Bootstrap 5
- **Icons**: Font Awesome 6
- **Editor**: TinyMCE 6
- **Session Management**: PHP Sessions
- **Security**: Password hashing, SQL injection prevention

## 📦 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache or Nginx web server
- mod_rewrite enabled (for Apache)

### Step 1: Clone the Repository
```bash
git clone https://github.com/yourusername/wiracenter-final.git
cd wiracenter-final
```

### Step 2: Database Setup
1. Create a MySQL database named `wiracenter_db2`
2. Import the database schema:
```bash
mysql -u username -p wiracenter_db2 < database/schema.sql
```

### Step 3: Configuration
1. Update database credentials in `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'wiracenter_db2';
private $username = 'your_db_username';
private $password = 'your_db_password';
```

2. Update site URL in `config/config.php`:
```php
define('SITE_URL', 'http://your-domain.com');
```

### Step 4: File Permissions
Make sure the uploads directory is writable:
```bash
chmod 755 uploads/
```

### Step 5: Access the Website
- **Frontend**: `http://your-domain.com/`
- **Admin Dashboard**: `http://your-domain.com/admin/`

## 🔐 Default Login Credentials

- **Username**: admin
- **Password**: wiracenter!

> **Important**: Change the default password immediately after first login!

## 📁 Project Structure (2025)

```
wiracenter-v1-main/
├── admin/
│   ├── api/                  # API endpoints (upload, notifications, stats, etc)
│   ├── assets/
│   │   ├── css/              # Admin styles (admin-style.css)
│   │   └── js/               # Admin scripts (tinymce-init.js, admin-script.js)
│   ├── includes/             # Header, footer, sidebar
│   ├── activity_logs.php     # Activity log
│   ├── articles.php          # Articles management
│   ├── content_blocks.php    # Content blocks management
│   ├── dashboard.php         # Admin dashboard
│   ├── export_data.php       # Export data
│   ├── faqs.php              # FAQs management
│   ├── files.php             # File management
│   ├── help.php              # Help & docs
│   ├── login.php             # Admin login
│   ├── logout.php            # Logout
│   ├── messages.php          # Contact messages
│   ├── navigation.php        # Navigation management
│   ├── notifications.php     # Notification history
│   ├── pages.php             # Pages management
│   ├── profile.php           # User profile
│   ├── projects.php          # Projects management
│   ├── settings.php          # Site settings
│   ├── tools.php             # Tools management
│   ├── trash.php             # Trash bin
│   ├── users.php             # User management
│   └── backup.php            # Backup & restore
├── api/
│   └── contact.php           # Public contact API
├── assets/
│   ├── css/                  # Frontend styles (style.css)
│   └── js/                   # Frontend scripts (script.js)
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
├── index.php                 # Homepage
├── about.php                 # About page
├── my-spaces.php             # Projects/Articles/Tools
├── contact.php               # Contact page
├── project.php               # Project detail
├── article.php               # Article detail
├── tool.php                  # Tool detail
├── maintenance.php           # Maintenance page
├── README.md                 # This file
├── change_log.md             # Changelog (auto updated)
└── ...
```

## 🗂️ Admin Modules & Features

- **Articles**: CRUD, TinyMCE, featured image, excerpt, SEO, status, schedule
- **Projects**: CRUD, description, content, tech tags, URLs, featured image, status
- **Tools**: CRUD, description, content, URLs, featured image, status
- **Pages**: CRUD, custom content, status
- **Content Blocks**: CRUD, reusable sections
- **FAQs**: CRUD, question/answer
- **Files**: Upload, manage, delete
- **Navigation**: Menu management
- **Users**: Role-based management
- **Settings**: Site-wide config
- **Notifications**: In-app, mark as read/delete, history
- **Activity Logs**: Track admin actions
- **Trash**: Soft delete/restore
- **Backup/Export**: DB backup, export data
- **Profile**: Update user info
- **Help**: Docs & support

## 🛠️ API Endpoints (Admin)

- `admin/api/upload_image.php` — Upload image for editor
- `admin/api/save_draft.php` — Autosave draft content
- `admin/api/notification_actions.php` — Mark/delete notifications
- `admin/api/mark_notification_read.php` — Mark all as read
- `admin/api/insert_notification.php` — Insert notification (test/dev)
- `admin/api/stats.php` — Dashboard stats
- `admin/api/upload.php` — General file upload

## 🧩 Custom Scripts & Styles

- **Admin JS**: `admin/assets/js/tinymce-init.js`, `admin/assets/js/admin-script.js`
- **Admin CSS**: `admin/assets/css/admin-style.css`
- **Frontend JS**: `assets/js/script.js`
- **Frontend CSS**: `assets/css/style.css`

## 🛡️ Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- Session-based authentication
- Role-based access control
- File upload validation

## 📱 Responsive Design

- Fully responsive (Bootstrap 5)
- Desktop, tablet, mobile support

## 🎯 SEO Optimized

- Clean URL structure
- Meta tags and descriptions
- Semantic HTML
- Fast loading
- Mobile-friendly

## 📝 Changelog

See `change_log.md` for detailed update history and troubleshooting log.

---

## ⚠️ Notes, Plans & Unsolved Troubleshoots

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