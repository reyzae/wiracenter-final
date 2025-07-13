-- -------------------------------------------------------------
-- Wiracenter Database Schema (Complete & Structured)
-- Version: 1.0
-- Created: 2025-07-06
-- Description: Complete database schema for Wiracenter CMS
-- -------------------------------------------------------------

-- Set character set and collation
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `wiracent_db2` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `wiracent_db2`;

-- ====================================================================
-- ====================== CORE TABLES =================================
-- ====================================================================

-- --------------------------
-- Users Table (Admin Authentication)
-- --------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'editor', 'viewer') DEFAULT 'viewer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------
-- Articles Table (Blog Posts)
-- --------------------------
CREATE TABLE IF NOT EXISTS `articles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `content` TEXT NOT NULL,
    `draft_content` TEXT,
    `excerpt` TEXT,
    `featured_image` VARCHAR(255),
    `status` ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
    `publish_date` DATETIME,
    `created_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    CONSTRAINT `fk_articles_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_articles_status` (`status`),
    INDEX `idx_articles_slug` (`slug`),
    INDEX `idx_articles_publish_date` (`publish_date`),
    INDEX `idx_articles_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------
-- Projects Table (Portfolio Projects)
-- --------------------------
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT NOT NULL,
    `content` TEXT NOT NULL,
    `draft_content` TEXT,
    `featured_image` VARCHAR(255),
    `project_url` VARCHAR(255),
    `github_url` VARCHAR(255),
    `technologies` JSON,
    `status` ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
    `publish_date` DATETIME,
    `created_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    CONSTRAINT `fk_projects_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_projects_status` (`status`),
    INDEX `idx_projects_slug` (`slug`),
    INDEX `idx_projects_publish_date` (`publish_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------
-- Tools Table (Tools & Utilities)
-- --------------------------
CREATE TABLE IF NOT EXISTS `tools` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT NOT NULL,
    `content` TEXT NOT NULL,
    `draft_content` TEXT,
    `featured_image` VARCHAR(255),
    `tool_url` VARCHAR(255),
    `category` VARCHAR(100),
    `status` ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
    `publish_date` DATETIME,
    `created_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    CONSTRAINT `fk_tools_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_tools_status` (`status`),
    INDEX `idx_tools_slug` (`slug`),
    INDEX `idx_tools_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------
-- Pages Table (Static Pages)
-- --------------------------
CREATE TABLE IF NOT EXISTS `pages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `content` TEXT NOT NULL,
    `status` ENUM('draft', 'published') DEFAULT 'draft',
    `created_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    CONSTRAINT `fk_pages_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_pages_status` (`status`),
    INDEX `idx_pages_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- ====================== CONTENT MANAGEMENT ==========================
-- ====================================================================

-- --------------------------
-- Navigation Items Table
-- --------------------------
CREATE TABLE IF NOT EXISTS `navigation_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    `display_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_navigation_status` (`status`),
    INDEX `idx_navigation_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------
-- FAQs Table
-- --------------------------
CREATE TABLE IF NOT EXISTS `faqs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `question` TEXT NOT NULL,
    `answer` TEXT NOT NULL,
    `display_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_faqs_status` (`status`),
    INDEX `idx_faqs_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------
-- Content Block Types Table
-- --------------------------------------
CREATE TABLE IF NOT EXISTS `content_block_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type_name` VARCHAR(50) NOT NULL UNIQUE,
    `display_name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_block_types_name` (`type_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------
-- Content Blocks Table
-- --------------------------------------
CREATE TABLE IF NOT EXISTS `content_blocks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `title` VARCHAR(255),
    `content` TEXT,
    `type` VARCHAR(50) NOT NULL,
    `icon_class` VARCHAR(100),
    `display_order` INT DEFAULT 0,
    `page_slug` VARCHAR(100),
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_content_blocks_type` FOREIGN KEY (`type`) REFERENCES `content_block_types`(`type_name`) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX `idx_content_blocks_status` (`status`),
    INDEX `idx_content_blocks_type` (`type`),
    INDEX `idx_content_blocks_page` (`page_slug`),
    INDEX `idx_content_blocks_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- ====================== MEDIA & FILES ===============================
-- ====================================================================

-- --------------------------
-- Files Table (Media Management)
-- --------------------------
CREATE TABLE IF NOT EXISTS `files` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `filename` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size` INT NOT NULL,
    `file_type` VARCHAR(100) NOT NULL,
    `uploaded_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    CONSTRAINT `fk_files_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_files_type` (`file_type`),
    INDEX `idx_files_uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- ====================== SYSTEM & SETTINGS ===========================
-- ====================================================================

-- --------------------------
-- Site Settings Table
-- --------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_type` ENUM('text', 'textarea', 'image', 'json') DEFAULT 'text',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------
-- Contact Messages Table
-- --------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_contact_status` (`status`),
    INDEX `idx_contact_email` (`email`),
    INDEX `idx_contact_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------
-- Activity Logs Table
-- --------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `action` VARCHAR(255) NOT NULL,
    `item_type` VARCHAR(50),
    `item_id` INT,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_activity_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_activity_user_id` (`user_id`),
    INDEX `idx_activity_item_type` (`item_type`),
    INDEX `idx_activity_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------
-- Notifications Table
-- --------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `message` TEXT NOT NULL,
    `link` VARCHAR(255),
    `is_read` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_notifications_user_id` (`user_id`),
    INDEX `idx_notifications_is_read` (`is_read`),
    INDEX `idx_notifications_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- ====================== DEFAULT DATA INSERTS ========================
-- ====================================================================

-- Default admin user (password: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES
('admin', 'admin@wiracenter.com', '$2y$12$kzOPjFx2eEJXVaWHw7Ny5uAyoPDdc0IJ7a74kHd5SDsLc56IK8pJ6', 'admin');

-- Default site settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('site_name', 'Wiracenter', 'text'),
('site_description', 'Showcase of projects, experiments, and stories in tech, and digital world.', 'textarea'),
('site_keywords', 'portfolio, web development, programming, tech projects, experiments', 'text'),
('site_logo', '', 'image'),
('site_favicon', '', 'image'),
('hero_title', 'Reyza\'s Digital Lab @ Wiracenter', 'text'),
('hero_subtitle', 'Projects, Stories, and Experiments in Tech', 'text'),
('about_title', 'About Me', 'text'),
('about_content', 'I am a passionate as tech enthusiast, constantly exploring new ideas and sharing knowledge through digital projects.', 'textarea'),
('contact_email', 'info@wiracenter.com', 'text'),
('contact_phone', '+6281313099914', 'text'),
('contact_address', 'Central Jakarta, Indonesia', 'text'),
('operating_hours', 'Mon-Fri: 9 AM - 5 PM', 'text'),
('social_media', '{"instagram": "https://instagram.com/reyzawira", "threads": "https://www.threads.com/@reyzawira", "linkedin": "https://linkedin.com/in/reyzawirakusuma", "github": "https://github.com/reyzae"}', 'json'),
('theme_mode', 'light', 'text'),
('debug_mode', '0', 'text'),
('google_analytics_id', '', 'text'),
('maintenance_mode', '0', 'text'),
('maintenance_message', 'Our website is currently undergoing scheduled maintenance. We will be back shortly!', 'textarea'),
('maintenance_countdown', '', 'text'),
('log_retention_days', '30', 'text');

-- Default navigation items
INSERT INTO `navigation_items` (`name`, `url`, `display_order`, `status`) VALUES
('Home', 'index.php', 1, 'active'),
('About', 'page.php?slug=about', 2, 'active'),
('My Spaces', 'my-spaces.php', 3, 'active'),
('Contact', 'contact.php', 4, 'active');

-- Default FAQs
INSERT INTO `faqs` (`question`, `answer`, `display_order`, `status`) VALUES
('What services are available?',
 'You can explore various digital projects, tutorials, and tech experiments here. I also enjoy sharing knowledge and insights about technology trends and digital solutions.',
 1, 'active'),

('How long does it take to launch a new project or update?',
 'The timeline depends on the size and complexity of each project. Some updates or experiments can be published in just a few days, while bigger initiatives might take a few weeks. Stay tuned for regular updates!',
 2, 'active'),

('Is there ongoing support or content updates?',
 'Yes, this platform is regularly updated with new projects, stories, and learning resources. Feel free to reach out or check back often for the latest updates and features!',
 3, 'active'),

('What topics and technologies are usually featured?',
 'I love exploring a wide range of topics — from web and digital tools to creative problem solving, tech tutorials, productivity hacks, and digital lifestyle. The content is designed to inspire and help fellow tech enthusiasts.',
 4, 'active');

-- Default content block types
INSERT INTO `content_block_types` (`type_name`, `display_name`, `description`) VALUES
('contact_info_card', 'Contact Info Card', 'Displays contact information as a card'),
('faq_item', 'FAQ Item', 'Displays a single FAQ item'),
('hero_section', 'Hero Section', 'Landing page hero section'),
('feature_card', 'Feature Card', 'Displays a feature or service as a card'),
('testimonial', 'Testimonial', 'Displays a customer or user testimonial'),
('stats_counter', 'Stats Counter', 'Displays statistics or numbers'),
('social_links', 'Social Links', 'Displays social media links');

-- Default content blocks for Contact page
INSERT INTO `content_blocks` (`name`, `title`, `content`, `type`, `icon_class`, `display_order`, `page_slug`, `status`) VALUES
('contact_email_card', 'Email', 'info@wiracenter.com', 'contact_info_card', 'fas fa-envelope', 1, 'contact', 'active'),
('contact_phone_card', 'Phone', '+6281313099914', 'contact_info_card', 'fas fa-phone', 2, 'contact', 'active'),
('contact_address_card', 'Address', 'Central Jakarta, Indonesia', 'contact_info_card', 'fas fa-map-marker-alt', 3, 'contact', 'active');

-- Default content blocks for FAQs on Contact page
INSERT INTO `content_blocks` (`name`, `title`, `content`, `type`, `display_order`, `page_slug`, `status`) VALUES
('faq_services', 'What services are available?',
 'You can explore various digital projects, tutorials, and tech experiments here. I also enjoy sharing knowledge and insights about technology trends and digital solutions.',
 'faq_item', 1, 'contact', 'active'),
('faq_project_time', 'How long does a typical project take?',
 'The timeline depends on the size and complexity of each project. Some updates or experiments can be published in just a few days, while bigger initiatives might take a few weeks. Stay tuned for regular updates!',
 'faq_item', 2, 'contact', 'active'),
('faq_maintenance', 'Is there ongoing support or content updates?',
 'Yes, this platform is regularly updated with new projects, stories, and learning resources. Feel free to reach out or check back often for the latest updates and features!',
 'faq_item', 3, 'contact', 'active'),
('faq_technologies', 'What topics and technologies are usually featured?',
 'I love exploring a wide range of topics — from web and digital tools to creative problem solving, tech tutorials, productivity hacks, and digital lifestyle. The content is designed to inspire and help fellow tech enthusiasts.',
 'faq_item', 4, 'contact', 'active');

-- Default About page content
INSERT INTO `pages` (`title`, `slug`, `content`, `status`, `created_by`) VALUES
('About Me', 'about', 
'<h2>Welcome to My Digital Space</h2>
<p>Hello! I\'m Reyza, a passionate tech enthusiast and digital creator. This space is where I share my journey through technology, experiments, and creative projects.</p>

<h3>What I Do</h3>
<p>I love exploring the intersection of technology and creativity. From web development to digital tools, I\'m constantly experimenting with new ideas and sharing what I learn along the way.</p>

<h3>My Approach</h3>
<p>I believe in learning by doing. Every project here represents a step in my learning journey, and I hope they inspire you to explore and create as well.</p>

<h3>Get in Touch</h3>
<p>Feel free to reach out if you\'d like to collaborate, have questions, or just want to say hello. I\'m always excited to connect with fellow tech enthusiasts!</p>', 
'published', 1);

-- Default My Spaces page content
INSERT INTO `pages` (`title`, `slug`, `content`, `status`, `created_by`) VALUES
('My Spaces', 'my-spaces', 
'<h2>Explore My Digital Spaces</h2>
<p>Welcome to my collection of projects, articles, and tools. Here you\'ll find everything from web development experiments to useful digital tools and insights.</p>

<h3>Projects</h3>
<p>Browse through my portfolio of web projects, experiments, and creative solutions. Each project represents a learning experience and a step forward in my tech journey.</p>

<h3>Articles</h3>
<p>Read about my experiences, tutorials, and insights into the world of technology and digital creation. I share what I learn to help others on their own journeys.</p>

<h3>Tools</h3>
<p>Discover useful tools and utilities I\'ve created or found helpful. These are designed to make digital work more efficient and enjoyable.</p>', 
'published', 1);

-- ====================================================================
-- ====================== INDEXES & OPTIMIZATION =======================
-- ====================================================================

-- Additional indexes for better performance
CREATE INDEX `idx_articles_deleted_at` ON `articles` (`deleted_at`);
CREATE INDEX `idx_projects_deleted_at` ON `projects` (`deleted_at`);
CREATE INDEX `idx_tools_deleted_at` ON `tools` (`deleted_at`);
CREATE INDEX `idx_pages_deleted_at` ON `pages` (`deleted_at`);
CREATE INDEX `idx_files_deleted_at` ON `files` (`deleted_at`);

-- Full-text search indexes for content
CREATE FULLTEXT INDEX `ft_articles_title_content` ON `articles` (`title`, `content`);
CREATE FULLTEXT INDEX `ft_projects_title_description` ON `projects` (`title`, `description`);
CREATE FULLTEXT INDEX `ft_tools_title_description` ON `tools` (`title`, `description`);

-- ====================================================================
-- ====================== FINAL SETUP =================================
-- ====================================================================

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Show completion message
SELECT 'Wiracenter Database Schema created successfully!' AS message;
