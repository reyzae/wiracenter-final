-- -------------------------------------------------------------
-- Wiracenter Database Schema (Full SQL Script, Cleaned & Upgraded)
-- -------------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `wiracenter_db2`;
USE `wiracenter_db2`;

-- --------------------------
-- Users Table (Admin Auth)
-- --------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'editor', 'viewer') DEFAULT 'viewer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------
-- Articles Table
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
    CONSTRAINT `fk_articles_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------
-- Projects Table
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
    CONSTRAINT `fk_projects_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------
-- Tools Table
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
    CONSTRAINT `fk_tools_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------
-- Files Table (Media)
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
    CONSTRAINT `fk_files_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------
-- Pages Table (Static Content)
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
    CONSTRAINT `fk_pages_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

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
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

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
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------
-- Content Block Types Table (For blocks)
-- --------------------------------------
CREATE TABLE IF NOT EXISTS `content_block_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type_name` VARCHAR(50) NOT NULL UNIQUE, -- machine name
    `display_name` VARCHAR(100) NOT NULL,    -- human name
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------
-- Content Blocks Table (Dynamic Sections)
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
    CONSTRAINT `fk_content_blocks_type` FOREIGN KEY (`type`) REFERENCES `content_block_types`(`type_name`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- --------------------------
-- Site Settings Table
-- --------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_type` ENUM('text', 'textarea', 'image', 'json') DEFAULT 'text',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

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
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

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
    CONSTRAINT `fk_activity_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

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
    CONSTRAINT `fk_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ====================================================================
-- ====================== DEFAULT DATA INSERTS ========================
-- ====================================================================

-- Default admin user (password: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `role`)
VALUES
('admin', 'admin@wiracenter.com', '$2y$12$YoayHtO5NFpXNPkvfoI4aue4IvXokF5gUYnkxRyXsYz1jPpkRq4qu', 'admin');

-- Default site settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('site_name', 'Wiracenter', 'text'),
('site_description', 'Showcase of projects, experiments, and stories in tech, and digital world.', 'textarea'),
('site_keywords', 'portfolio, web development, programming, tech projects, experiments', 'text'),
('site_logo', '', 'image'),
('site_favicon', '', 'image'),
('hero_title', 'Reyza’s Digital Lab @ Wiracenter', 'text'),
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
('My Spaces', 'page.php?slug=my-spaces', 3, 'active'),
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

-- Default content_block_types
INSERT INTO `content_block_types` (`type_name`, `display_name`, `description`) VALUES
('contact_info_card', 'Contact Info Card', 'Displays contact information as a card'),
('faq_item', 'FAQ Item', 'Displays a single FAQ item'),
('hero_section', 'Hero Section', 'Landing page hero section');

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

-- Default About page content (for preview, customize as you need!)
INSERT INTO `pages` (`title`, `slug`, `content`, `status`, `created_by`) VALUES
('About Me', 'about', '<div class="container">
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <h1 class="section-title-custom text-center mb-5 animate-on-load">About Me: Your IT Support & Tech Explorer</h1>
            <div class="card p-4 mb-5 animate-on-load">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center mb-4 mb-md-0">
                        <div class="profile-photo-placeholder mx-auto">
                            <i class="fas fa-user-circle fa-5x text-muted"></i>
                            <p class="mt-2 text-muted">Your Photo Here</p>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <p class="lead">Hello! I\'m [Your Name], an enthusiastic IT Support professional...</p>
                        <p>As an IT Support specialist, I thrive on solving complex problems, optimizing systems, and empowering users with the knowledge they need...</p>
                    </div>
                </div>
            </div>
            <div class="card p-4 mb-5 animate-on-load">
                <h2 class="section-title-custom text-center mb-4">About Wiracenter</h2>
                <p>Wiracenter is the embodiment of my vision to create a platform that not only provides IT solutions...</p>
            </div>
            <div class="card p-4 mb-5 animate-on-load">
                <h2 class="section-title-custom text-center mb-4">My Core Expertise & Tech Stack</h2>
                <div class="row text-center">
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <i class="fab fa-windows fa-3x mb-2"></i>
                        <p>Windows Server & Client</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <i class="fab fa-linux fa-3x mb-2"></i>
                        <p>Linux (Ubuntu, CentOS)</p>
                    </div>
                    <!-- More tech stack here -->
                </div>
            </div>
            <div class="card p-4 mb-5 animate-on-load">
                <h2 class="section-title-custom text-center mb-4">My Journey as a Tech Explorer</h2>
                <p>My curiosity extends far beyond fixing immediate issues. I\'m constantly experimenting with new technologies, building small projects...</p>
                <ul>
                    <li><strong>Automation Scripting:</strong> Automating repetitive IT tasks using Python and PowerShell.</li>
                    <li><strong>Containerization:</strong> Exploring Docker and Kubernetes for efficient application deployment.</li>
                </ul>
            </div>
            <div class="card p-4 mb-5 animate-on-load text-center">
