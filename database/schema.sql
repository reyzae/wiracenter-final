CREATE DATABASE IF NOT EXISTS wiracenter_db2;
USE wiracenter_db2;

-- Users table for admin authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'viewer') DEFAULT 'viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Articles table
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    status ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
    publish_date DATETIME,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Projects table
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    content TEXT NOT NULL,
    featured_image VARCHAR(255),
    project_url VARCHAR(255),
    github_url VARCHAR(255),
    technologies JSON,
    status ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
    publish_date DATETIME,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Tools table
CREATE TABLE tools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    content TEXT NOT NULL,
    featured_image VARCHAR(255),
    tool_url VARCHAR(255),
    category VARCHAR(100),
    status ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
    publish_date DATETIME,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Files table for media management
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Pages table for static content
CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Navigation Items table
CREATE TABLE navigation_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- FAQs table
CREATE TABLE content_block_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) UNIQUE NOT NULL, -- Internal machine-readable name (e.g., 'contact_info_card')
    display_name VARCHAR(100) NOT NULL, -- Human-readable name (e.g., 'Contact Info Card')
    description TEXT, -- Optional: description of what this block type is for
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Content Block Types table for dynamic block types
CREATE TABLE content_block_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) UNIQUE NOT NULL, -- Internal machine-readable name (e.g., 'contact_info_card')
    display_name VARCHAR(100) NOT NULL, -- Human-readable name (e.g., 'Contact Info Card')
    description TEXT, -- Optional: description of what this block type is for
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Content Blocks table for flexible content sections
CREATE TABLE content_blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL, -- Internal name for identification (e.g., contact_email_card)
    title VARCHAR(255), -- Title displayed on the block
    content TEXT, -- Main content of the block (can be HTML, text, or JSON)
    type VARCHAR(50) NOT NULL, -- Type of block (e.g., contact_info_card, faq_item, hero_section)
    icon_class VARCHAR(100), -- Font Awesome icon class (e.g., fas fa-envelope)
    display_order INT DEFAULT 0,
    page_slug VARCHAR(100), -- Optional: slug of the page this block belongs to (e.g., contact, home)
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (type) REFERENCES content_block_types(type_name) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Site settings table
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'image', 'json') DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Activity Logs table
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    item_type VARCHAR(50),
    item_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message TEXT NOT NULL,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@wiracenter.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES 
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
('contact_address', '123 Main Street, Anytown, USA', 'text'),
('operating_hours', 'Mon-Fri: 9 AM - 5 PM', 'text'),
('social_media', '{"instagram": "https://instagram.com/reyzawira", "threads": "https://www.threads.com/@reyzawira", "linkedin": "https://linkedin.com/in/reyzawirakusuma", "github": "https://github.com/reyzae"}', 'json');

INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES 
('theme_mode', 'light', 'text');

INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES 
('debug_mode', '0', 'text');

INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES 
('google_analytics_id', '', 'text');

INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES 
('maintenance_mode', '0', 'text'),
('maintenance_message', 'Our website is currently undergoing scheduled maintenance. We will be back shortly!', 'textarea'),
('maintenance_countdown', '', 'text');

INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES 
('log_retention_days', '30', 'text');

-- Insert default navigation items
INSERT INTO navigation_items (name, url, display_order, status) VALUES
('Home', 'index.php', 1, 'active'),
('About', 'page.php?slug=about', 2, 'active'),
('My Spaces', 'page.php?slug=my-spaces', 3, 'active'),
('Contact', 'contact.php', 4, 'active');

-- Insert default FAQs
INSERT INTO faqs (question, answer, display_order, status) VALUES
('What services do you offer?', 'I offer web development services including custom website development, web applications, API integration, and consultation on web technologies.', 1, 'active'),
('How long does a typical project take?', 'Project timelines vary depending on complexity and requirements. A simple website might take 2-4 weeks, while complex applications can take 2-6 months.', 2, 'active'),
('Do you provide ongoing maintenance?', 'Yes, I offer ongoing maintenance and support services to ensure your website remains secure, updated, and functioning optimally.', 3, 'active'),
('What technologies do you work with?', 'I primarily work with PHP, JavaScript, HTML5, CSS3, MySQL, and various frameworks like Bootstrap. I\'m also experienced with cloud platforms and modern development tools.', 4, 'active');

-- Insert default content blocks for Contact page
INSERT INTO content_blocks (name, title, content, type, icon_class, display_order, page_slug, status) VALUES
('contact_email_card', 'Email', 'info@wiracenter.com', 'contact_info_card', 'fas fa-envelope', 1, 'contact', 'active'),
('contact_phone_card', 'Phone', '+6281313099914', 'contact_info_card', 'fas fa-phone', 2, 'contact', 'active'),
('contact_address_card', 'Address', '123 Main Street, Anytown, USA', 'contact_info_card', 'fas fa-map-marker-alt', 3, 'contact', 'active'),
('contact_hours_card', 'Operating Hours', 'Mon-Fri: 9 AM - 5 PM', 'contact_info_card', 'fas fa-clock', 4, 'contact', 'active');

-- Insert default content blocks for FAQs on Contact page
INSERT INTO content_blocks (name, title, content, type, display_order, page_slug, status) VALUES
('faq_services', 'What services do you offer?', 'I offer web development services including custom website development, web applications, API integration, and consultation on web technologies.', 'faq_item', 1, 'contact', 'active'),
('faq_project_time', 'How long does a typical project take?', 'Project timelines vary depending on complexity and requirements. A simple website might take 2-4 weeks, while complex applications can take 2-6 months.', 'faq_item', 2, 'contact', 'active'),
('faq_maintenance', 'Do you provide ongoing maintenance?', 'Yes, I offer ongoing maintenance and support services to ensure your website remains secure, updated, and functioning optimally.', 'faq_item', 3, 'contact', 'active'),
('faq_technologies', 'What technologies do you work with?', 'I primarily work with PHP, JavaScript, HTML5, CSS3, MySQL, and various frameworks like Bootstrap. I\'m also experienced with cloud platforms and modern development tools.', 'faq_item', 4, 'contact', 'active');