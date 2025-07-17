# WiraCenter API Documentation

## Public API
- `POST /api/contact.php` — Kirim pesan kontak
  - Params: name, email, subject, message, csrf_token
  - Response: { success, message }

## Admin API
- `POST /admin/api/upload_image.php` — Upload image untuk editor
- `POST /admin/api/save_draft.php` — Autosave draft konten
- `POST /admin/api/notification_actions.php` — Mark/delete notifications
- `POST /admin/api/mark_notification_read.php` — Mark all as read
- `POST /admin/api/insert_notification.php` — Insert notification (test/dev)
- `GET /admin/api/stats.php` — Dashboard stats
- `POST /admin/api/upload.php` — General file upload

> Untuk detail parameter dan response, tambahkan sesuai kebutuhan. Semua endpoint admin membutuhkan autentikasi session dan CSRF token. 