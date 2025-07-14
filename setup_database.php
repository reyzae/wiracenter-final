<?php
// Database Setup Script for WiraCenter V1
// This script creates all necessary tables and initial data

require_once 'config/config.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    die("Failed to connect to database. Please check your configuration.");
}

echo "<h2>WiraCenter Database Setup</h2>";

// Create missing tables
$tables_to_create = [
    'pages' => "
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
            INDEX `idx_pages_status` (`status`),
            INDEX `idx_pages_slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'navigation_items' => "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'faqs' => "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'content_block_types' => "
        CREATE TABLE IF NOT EXISTS `content_block_types` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `type_name` VARCHAR(50) NOT NULL UNIQUE,
            `display_name` VARCHAR(100) NOT NULL,
            `description` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_block_types_name` (`type_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'content_blocks' => "
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
            INDEX `idx_content_blocks_status` (`status`),
            INDEX `idx_content_blocks_type` (`type`),
            INDEX `idx_content_blocks_page` (`page_slug`),
            INDEX `idx_content_blocks_order` (`display_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
];

// Create tables
foreach ($tables_to_create as $table_name => $sql) {
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        echo "<p style='color: green;'>✓ Table '$table_name' created successfully.</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error creating table '$table_name': " . $e->getMessage() . "</p>";
    }
}

// Insert default content block types
$default_block_types = [
    ['hero', 'Hero Section', 'Main banner section for pages'],
    ['content', 'Content Block', 'General content block'],
    ['feature', 'Feature Block', 'Feature highlight block'],
    ['testimonial', 'Testimonial', 'Customer testimonial block'],
    ['contact', 'Contact Form', 'Contact form block'],
    ['gallery', 'Gallery', 'Image gallery block']
];

try {
    $stmt = $conn->prepare("INSERT IGNORE INTO content_block_types (type_name, display_name, description) VALUES (?, ?, ?)");
    foreach ($default_block_types as $type) {
        $stmt->execute($type);
    }
    echo "<p style='color: green;'>✓ Default content block types inserted.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error inserting content block types: " . $e->getMessage() . "</p>";
}

// Insert default navigation items
$default_navigation = [
    ['Home', '/', 1],
    ['About', '/about.php', 2],
    ['Articles', '/articles.php', 3],
    ['Projects', '/projects.php', 4],
    ['Tools', '/tools.php', 5],
    ['Contact', '/contact.php', 6]
];

try {
    $stmt = $conn->prepare("INSERT IGNORE INTO navigation_items (name, url, display_order) VALUES (?, ?, ?)");
    foreach ($default_navigation as $nav) {
        $stmt->execute($nav);
    }
    echo "<p style='color: green;'>✓ Default navigation items inserted.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error inserting navigation items: " . $e->getMessage() . "</p>";
}

// Insert sample FAQ
$sample_faqs = [
    ['What is WiraCenter?', 'WiraCenter is a digital platform for entrepreneurs providing articles, projects, and tools to help businesses grow.', 1],
    ['How can I contact support?', 'You can contact us through the contact form on our website or email us directly.', 2],
    ['Are the tools free to use?', 'Most of our tools are free to use, but some premium features may require registration.', 3]
];

try {
    $stmt = $conn->prepare("INSERT IGNORE INTO faqs (question, answer, display_order) VALUES (?, ?, ?)");
    foreach ($sample_faqs as $faq) {
        $stmt->execute($faq);
    }
    echo "<p style='color: green;'>✓ Sample FAQs inserted.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error inserting FAQs: " . $e->getMessage() . "</p>";
}

// Create default pages
$default_pages = [
    ['About Us', 'about', '<h1>About WiraCenter</h1><p>WiraCenter is dedicated to helping entrepreneurs succeed in the digital age.</p>', 'published'],
    ['Privacy Policy', 'privacy', '<h1>Privacy Policy</h1><p>Your privacy is important to us. This policy explains how we collect and use your information.</p>', 'published'],
    ['Terms of Service', 'terms', '<h1>Terms of Service</h1><p>By using our services, you agree to these terms and conditions.</p>', 'published']
];

try {
    $stmt = $conn->prepare("INSERT IGNORE INTO pages (title, slug, content, status) VALUES (?, ?, ?, ?)");
    foreach ($default_pages as $page) {
        $stmt->execute($page);
    }
    echo "<p style='color: green;'>✓ Default pages created.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error creating default pages: " . $e->getMessage() . "</p>";
}

// Add deleted_at column to existing tables if not exists
$tables_to_check = ['articles', 'projects', 'tools', 'files'];
foreach ($tables_to_check as $table) {
    try {
        $stmt = $conn->prepare("SHOW COLUMNS FROM $table LIKE 'deleted_at'");
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            $stmt = $conn->prepare("ALTER TABLE $table ADD COLUMN deleted_at DATETIME DEFAULT NULL");
            $stmt->execute();
            echo "<p style='color: green;'>✓ Added deleted_at column to '$table' table.</p>";
        } else {
            echo "<p style='color: blue;'>ℹ Column deleted_at already exists in '$table' table.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error checking/adding deleted_at column to '$table': " . $e->getMessage() . "</p>";
    }
}

echo "<h3 style='color: green;'>Database setup completed!</h3>";
echo "<p><a href='admin/login.php'>Go to Admin Panel</a></p>";
?> 