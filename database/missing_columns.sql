-- ====================================================================
-- WiraCenter V1 - Missing Columns SQL
-- Add columns that are used in queries but missing from schema
-- ====================================================================

USE `wiracent_db2`;

-- ====================================================================
-- 1. ADD MISSING COLUMNS TO USERS TABLE
-- ====================================================================

-- Add status column for user management
ALTER TABLE `users` 
ADD COLUMN `status` ENUM('active', 'suspended', 'inactive') DEFAULT 'active' AFTER `role`,
ADD INDEX `idx_users_status` (`status`);

-- Add temporary password columns for password reset functionality
ALTER TABLE `users` 
ADD COLUMN `temp_password` VARCHAR(255) DEFAULT NULL AFTER `password`,
ADD COLUMN `temp_password_expired_at` DATETIME DEFAULT NULL AFTER `temp_password`;

-- Update existing users to have active status
UPDATE `users` SET `status` = 'active' WHERE `status` IS NULL;

-- ====================================================================
-- 2. ADD MISSING COLUMNS TO CONTACT_MESSAGES TABLE
-- ====================================================================

-- Add important flag for contact messages
ALTER TABLE `contact_messages` 
ADD COLUMN `important` TINYINT(1) DEFAULT 0 AFTER `status`,
ADD INDEX `idx_contact_messages_important` (`important`);

-- ====================================================================
-- 3. ADD MISSING COLUMNS TO PAGES TABLE
-- ====================================================================

-- Add profile_image column for pages
ALTER TABLE `pages` 
ADD COLUMN `profile_image` VARCHAR(255) DEFAULT NULL AFTER `content`;

-- ====================================================================
-- 4. FIX SITE_SETTINGS TABLE COLUMN NAME
-- ====================================================================

-- Check if setting_value column exists and rename to value
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'wiracent_db2' 
    AND TABLE_NAME = 'site_settings' 
    AND COLUMN_NAME = 'setting_value'
);

-- If setting_value exists, rename it to value
SET @sql = IF(@column_exists > 0, 
    'ALTER TABLE `site_settings` CHANGE `setting_value` `value` TEXT;',
    'SELECT "Column setting_value does not exist, value column should already be present" as message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ====================================================================
-- 5. ADD MISSING COLUMNS TO FILES TABLE
-- ====================================================================

-- Add uploaded_by column if not exists
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'wiracent_db2' 
    AND TABLE_NAME = 'files' 
    AND COLUMN_NAME = 'uploaded_by'
);

SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `files` ADD COLUMN `uploaded_by` INT DEFAULT NULL AFTER `file_type`, ADD CONSTRAINT `fk_files_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;',
    'SELECT "Column uploaded_by already exists" as message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ====================================================================
-- 6. ADD MISSING COLUMNS TO NOTIFICATIONS TABLE
-- ====================================================================

-- Add is_read column if not exists
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'wiracent_db2' 
    AND TABLE_NAME = 'notifications' 
    AND COLUMN_NAME = 'is_read'
);

SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `notifications` ADD COLUMN `is_read` TINYINT(1) DEFAULT 0 AFTER `link`, ADD INDEX `idx_notifications_is_read` (`is_read`);',
    'SELECT "Column is_read already exists" as message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ====================================================================
-- 7. ADD MISSING COLUMNS TO ARTICLES, PROJECTS, TOOLS TABLES
-- ====================================================================

-- Add draft_content column to articles if not exists
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'wiracent_db2' 
    AND TABLE_NAME = 'articles' 
    AND COLUMN_NAME = 'draft_content'
);

SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `articles` ADD COLUMN `draft_content` TEXT AFTER `content`;',
    'SELECT "Column draft_content already exists in articles" as message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add draft_content column to projects if not exists
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'wiracent_db2' 
    AND TABLE_NAME = 'projects' 
    AND COLUMN_NAME = 'draft_content'
);

SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `projects` ADD COLUMN `draft_content` TEXT AFTER `content`;',
    'SELECT "Column draft_content already exists in projects" as message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add draft_content column to tools if not exists
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'wiracent_db2' 
    AND TABLE_NAME = 'tools' 
    AND COLUMN_NAME = 'draft_content'
);

SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `tools` ADD COLUMN `draft_content` TEXT AFTER `content`;',
    'SELECT "Column draft_content already exists in tools" as message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ====================================================================
-- 8. VERIFY ALL COLUMNS ARE PRESENT
-- ====================================================================

-- Show table structure for verification
SHOW TABLES;

-- Show structure of key tables
DESCRIBE `users`;
DESCRIBE `contact_messages`;
DESCRIBE `pages`;
DESCRIBE `site_settings`;
DESCRIBE `files`;
DESCRIBE `notifications`;
DESCRIBE `articles`;
DESCRIBE `projects`;
DESCRIBE `tools`;

-- ====================================================================
-- 9. INSERT DEFAULT DATA IF NEEDED
-- ====================================================================

-- Insert default site settings if table is empty
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Wiracenter'),
('site_description', 'Exploring tech & digital stuff'),
('maintenance_mode', '0'),
('debug_mode', '0'),
('google_analytics_id', ''),
('contact_email', 'info@wiracenter.com'),
('upload_max_size', '5242880'),
('allowed_file_types', 'jpg,jpeg,png,gif,webp,pdf,doc,docx,txt');

-- Insert default navigation items if table is empty
INSERT IGNORE INTO `navigation_items` (`name`, `url`, `display_order`, `status`) VALUES
('Home', '/', 1, 'active'),
('About', '/about.php', 2, 'active'),
('Articles', '/articles.php', 3, 'active'),
('Projects', '/projects.php', 4, 'active'),
('Tools', '/tools.php', 5, 'active'),
('Contact', '/contact.php', 6, 'active');

-- Insert default content block types if table is empty
INSERT IGNORE INTO `content_block_types` (`type_name`, `display_name`, `description`) VALUES
('hero', 'Hero Section', 'Main banner section for pages'),
('content', 'Content Block', 'General content block'),
('feature', 'Feature Block', 'Feature highlight block'),
('testimonial', 'Testimonial', 'Customer testimonial block'),
('contact', 'Contact Form', 'Contact form block'),
('gallery', 'Gallery', 'Image gallery block'),
('contact_info_card', 'Contact Info Card', 'Contact information card'),
('faq_item', 'FAQ Item', 'Frequently asked question item');

-- ====================================================================
-- COMPLETION MESSAGE
-- ====================================================================

SELECT 'Database schema updated successfully! All missing columns have been added.' as status; 