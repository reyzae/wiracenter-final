# WiraCenter V1 - Deployment Guide

## 🚀 Pre-Deployment Checklist

### ✅ Completed Audit & Fixes
- [x] Fixed undefined variable errors in admin files
- [x] Fixed missing database tables (pages, navigation_items, faqs, content_block_types)
- [x] Fixed column not found errors in trash.php
- [x] Fixed HTMLPurifier class loading issues
- [x] Fixed headers already sent errors
- [x] Removed hardcoded database credentials
- [x] Improved error reporting for production
- [x] Enhanced file upload security
- [x] Added security headers
- [x] Optimized maintenance mode
- [x] Created proper error pages
- [x] Removed development/debug files

## 📋 Deployment Requirements

### Server Requirements
- **PHP**: 7.4 or higher (8.0+ recommended)
- **MySQL**: 5.7 or higher (8.0+ recommended)
- **Apache**: 2.4+ with mod_rewrite enabled
- **Extensions**: PDO, PDO_MySQL, GD, mbstring, curl

### Disk Space
- Minimum: 100MB
- Recommended: 500MB+ for uploads and backups

## 🔧 Installation Steps

### 1. Upload Files
```bash
# Upload all files to your web server
# Ensure proper file permissions:
chmod 755 /path/to/wiracenter
chmod 644 /path/to/wiracenter/.htaccess
chmod 755 /path/to/wiracenter/uploads
chmod 755 /path/to/wiracenter/backups
```

### 2. Create Database
```sql
-- Create database
CREATE DATABASE wiracent_db2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (optional)
CREATE USER 'wiracent_admin'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON wiracent_db2.* TO 'wiracent_admin'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Configure Environment
```bash
# Copy environment example
cp .env.example .env

# Edit .env file with your settings
nano .env
```

**Required .env settings:**
```env
DB_HOST=localhost
DB_NAME=wiracent_db2
DB_USER=wiracent_admin
DB_PASS=your_secure_password
SITE_URL=https://yourdomain.com
ENVIRONMENT=production
DEBUG_MODE=0
```

### 4. Install Dependencies
```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader
```

### 5. Setup Database
```bash
# Run database setup script
php setup_database.php
```

### 6. Create Admin User
```sql
-- Insert default admin user
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@yourdomain.com', '$2y$10$your_hashed_password', 'admin');
```

## 🔒 Security Configuration

### 1. File Permissions
```bash
# Secure sensitive directories
chmod 700 config/
chmod 700 database/
chmod 700 backups/
chmod 700 vendor/

# Secure sensitive files
chmod 600 .env
chmod 600 php_errors.log
```

### 2. Apache Configuration
```apache
# Add to your Apache virtual host
<Directory "/path/to/wiracenter">
    AllowOverride All
    Require all granted
</Directory>
```

### 3. SSL Certificate
```bash
# Install SSL certificate (recommended)
# Update SITE_URL in .env to use HTTPS
SITE_URL=https://yourdomain.com
```

## 🛠️ Maintenance Mode

### Enable Maintenance Mode
1. **Via Admin Panel**: Go to Settings > Maintenance Mode
2. **Via .env file**: Set `MAINTENANCE_MODE=1`
3. **Via .htaccess**: Uncomment maintenance rules

### Customize Maintenance Page
```env
MAINTENANCE_MESSAGE=Custom maintenance message
MAINTENANCE_COUNTDOWN=2025-01-15 10:00:00
```

## 📊 Performance Optimization

### 1. Enable Caching
```env
CACHE_ENABLED=1
CACHE_DURATION=3600
```

### 2. Optimize Images
- Use WebP format when possible
- Compress images before upload
- Set appropriate max file sizes

### 3. Database Optimization
```sql
-- Run periodically
OPTIMIZE TABLE articles, projects, tools, pages;
ANALYZE TABLE articles, projects, tools, pages;
```

## 🔄 Backup Strategy

### 1. Database Backup
```bash
# Create backup script
#!/bin/bash
mysqldump -u wiracent_admin -p wiracent_db2 > backups/db_backup_$(date +%Y%m%d_%H%M%S).sql
```

### 2. File Backup
```bash
# Backup uploads directory
tar -czf backups/uploads_$(date +%Y%m%d_%H%M%S).tar.gz uploads/
```

### 3. Automated Backups
```bash
# Add to crontab
0 2 * * * /path/to/backup_script.sh
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Database Connection Error
```bash
# Check database credentials in .env
# Verify database exists and user has permissions
mysql -u wiracent_admin -p wiracent_db2
```

#### 2. 500 Internal Server Error
```bash
# Check error logs
tail -f php_errors.log

# Verify .htaccess syntax
apache2ctl -t
```

#### 3. File Upload Issues
```bash
# Check upload directory permissions
ls -la uploads/

# Verify PHP upload settings
php -i | grep upload
```

#### 4. Maintenance Mode Not Working
```bash
# Check .htaccess maintenance rules
# Verify maintenance.php exists
# Check file permissions
```

## 📈 Monitoring

### 1. Error Monitoring
- Monitor `php_errors.log`
- Set up log rotation
- Configure error notifications

### 2. Performance Monitoring
- Monitor database performance
- Track page load times
- Monitor disk space usage

### 3. Security Monitoring
- Monitor failed login attempts
- Check for suspicious activity
- Regular security updates

## 🔄 Updates

### 1. Backup Before Update
```bash
# Always backup before updating
./backup_script.sh
```

### 2. Update Process
```bash
# Download new version
# Compare and merge custom changes
# Run database migrations if any
# Test thoroughly
```

## 📞 Support

### Contact Information
- **Email**: support@yourdomain.com
- **Documentation**: /docs/
- **Issues**: GitHub Issues

### Emergency Contacts
- **Server Admin**: admin@yourdomain.com
- **Database Admin**: dba@yourdomain.com

---

## ✅ Post-Deployment Checklist

- [ ] Website loads without errors
- [ ] Admin panel accessible
- [ ] File uploads working
- [ ] Database backups configured
- [ ] SSL certificate installed
- [ ] Error monitoring active
- [ ] Performance optimized
- [ ] Security measures in place
- [ ] Documentation updated
- [ ] Team trained on maintenance procedures

**Deployment Date**: _______________
**Deployed By**: _______________
**Version**: WiraCenter V1.0 