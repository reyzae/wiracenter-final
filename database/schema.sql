-- ==============================
-- Wiracenter DB Schema (FIXED)
-- ==============================

CREATE DATABASE IF NOT EXISTS wiracenter_db2;
USE wiracenter_db2;

-- ==============================
-- Users table (admin auth)
-- ==============================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'viewer') DEFAULT 'viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL
) ENGINE=InnoDB;

-- ==============================
-- Articles table
-- ==============================
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    draft_content TEXT,
    excerpt TEXT,
    featured_image VARCHAR(255),
    status ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
    publish_date DATETIME,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_articles_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==============================
-- Projects table
-- ==============================
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    content TEXT NOT NULL,
    draft_content TEXT,
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
    CONSTRAINT fk_projects_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==============================
-- Tools table
-- ==============================
CREATE TABLE IF NOT EXISTS tools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    content TEXT NOT NULL,
    draft_content TEXT,
    featured_image VARCHAR(255),
    tool_url VARCHAR(255),
    category VARCHAR(100),
    status ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
    publish_date DATETIME,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_tools_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==============================
-- Files table (media management)
-- ==============================
CREATE TABLE IF NOT EXISTS files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_files_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==============================
-- Pages table (static content)
-- ==============================
CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_pages_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==============================
-- Navigation Items table
-- ==============================
CREATE TABLE IF NOT EXISTS navigation_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==============================
-- FAQs table
-- ==============================
CREATE TABLE IF NOT EXISTS faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==============================
-- Content Block Types table
-- ==============================
CREATE TABLE IF NOT EXISTS content_block_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==============================
-- Content Blocks table
-- ==============================
CREATE TABLE IF NOT EXISTS content_blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(255),
    content TEXT,
    type VARCHAR(50) NOT NULL,
    icon_class VARCHAR(100),
    display_order INT DEFAULT 0,
    page_slug VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_content_blocks_type FOREIGN KEY (type) REFERENCES content_block_types(type_name) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ==============================
-- Site settings table
-- ==============================
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'image', 'json') DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==============================
-- Contact messages table
-- ==============================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==============================
-- Activity Logs table
-- ==============================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    item_type VARCHAR(50),
    item_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_logs_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==============================
-- Notifications table
-- ==============================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message TEXT NOT NULL,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ===========================================================
-- ================== DEFAULT DATA SEED ======================
-- ===========================================================

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@wiracenter.com', '$2y$12$4A36BQT5echsejxNFenwqOKT5AFgB/Lw92SLCyKlPV5x5tMYCqpMe', 'admin');

-- Insert jenis block dulu sebelum insert ke content_blocks!
INSERT INTO content_block_types (type_name, display_name, description) VALUES
  ('contact_info_card', 'Contact Info Card', 'Menampilkan informasi kontak'),
  ('faq_item', 'FAQ Item', 'Item untuk FAQ'),
  ('hero_section', 'Hero Section', 'Bagian hero di landing page');

-- Insert default site settings (gabung sekalian biar clean)
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

-- Insert default navigation items
INSERT INTO navigation_items (name, url, display_order, status) VALUES
('Home', 'index.php', 1, 'active'),
('About', 'page.php?slug=about', 2, 'active'),
('My Spaces', 'page.php?slug=my-spaces', 3, 'active'),
('Contact', 'contact.php', 4, 'active');

-- Insert default FAQs
INSERT INTO faqs (question, answer, display_order, status) VALUES
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

-- Insert default content blocks for Contact page
INSERT INTO content_blocks (name, title, content, type, icon_class, display_order, page_slug, status) VALUES
('contact_email_card', 'Email', 'info@wiracenter.com', 'contact_info_card', 'fas fa-envelope', 1, 'contact', 'active'),
('contact_phone_card', 'Phone', '+6281313099914', 'contact_info_card', 'fas fa-phone', 2, 'contact', 'active'),
('contact_address_card', 'Address', 'Central Jakarta, Indonesia', 'contact_info_card', 'fas fa-map-marker-alt', 3, 'contact', 'active');

-- Insert default content blocks for FAQs on Contact page
INSERT INTO content_blocks (name, title, content, type, display_order, page_slug, status) VALUES
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

-- Insert default About page content
INSERT INTO pages (title, slug, content, status, created_by) VALUES
('About Me', 'about', '<div class="container"> 
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card p-4 mb-5 animate-on-load">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center mb-4 mb-md-0">
                        <div class="profile-photo-placeholder mx-auto">
                            <i class="fas fa-user-circle fa-5x text-muted"></i>
                            <p class="mt-2 text-muted">Your Photo Here</p>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <p class="lead">Hello! I\'m Reyza, an enthusiastic IT Support professional with a deep-seated passion for technology and a relentless drive to explore its ever-evolving landscape. My journey in IT began with a fascination for how things work, quickly evolving into a commitment to ensure they work seamlessly for others.</p>
                        <p>As an IT Support specialist, I thrive on solving complex problems, optimizing systems, and empowering users with the knowledge they need. Beyond the day-to-day, I\'m a dedicated tech explorer, constantly diving into new frameworks, tools, and concepts to stay ahead of the curve and discover innovative solutions.</p>
                    </div>
                </div>
            </div>
            <div class="card p-4 mb-5 animate-on-load">
                <h2 class="section-title-custom text-center mb-4">About Wiracenter</h2>
                <p>Wiracenter is the embodiment of my vision to create a platform that not only provides IT solutions but also serves as a hub for technological exploration and innovation. Here, we are dedicated to helping individuals and businesses navigate the complexities of the digital world, from day-to-day technical support to the implementation of cutting-edge technology solutions.</p>
                <p>We believe that technology should be accessible and optimally utilized by everyone. Therefore, Wiracenter is here to bridge the gap between technical challenges and effective solutions, with a focus on reliability, efficiency, and continuous learning.</p>
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
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <i class="fab fa-apple fa-3x mb-2"></i>
                        <p>macOS Support</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <i class="fas fa-network-wired fa-3x mb-2"></i>
                        <p>Network Troubleshooting</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <i class="fas fa-database fa-3x mb-2"></i>
                        <p>Database Management (SQL)</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <i class="fas fa-shield-alt fa-3x mb-2"></i>
                        <p>Cybersecurity Practices</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <i class="fab fa-php fa-3x mb-2"></i>
                        <p>PHP Development</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <i class="fab fa-js-square fa-3x mb-2"></i>
                        <p>JavaScript</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <i class="fab fa-html5 fa-3x mb-2"></i>
                        <p>HTML5</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <i class="fab fa-css3-alt fa-3x mb-2"></i>
                        <p>CSS3</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <i class="fab fa-git-alt fa-3x mb-2"></i>
                        <p>Git Version Control</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <i class="fas fa-cloud fa-3x mb-2"></i>
                        <p>Cloud Fundamentals</p>
                    </div>
                </div>
            </div>
            <div class="card p-4 mb-5 animate-on-load">
                <h2 class="section-title-custom text-center mb-4">My Journey as a Tech Explorer</h2>
                <p>My curiosity extends far beyond fixing immediate issues. I\'m constantly experimenting with new technologies, building small projects, and contributing to the open-source community when I can. Whether it\'s delving into the latest AI trends, setting up a home lab for network simulations, or learning a new programming language, I believe continuous learning is key to navigating the digital world.</p>
                <p>Currently, I\'m particularly interested in:</p>
                <ul>
                    <li><strong>Automation Scripting:</strong> Automating repetitive IT tasks using Python and PowerShell.</li>
                    <li><strong>Containerization:</strong> Exploring Docker and Kubernetes for efficient application deployment.</li>
                    <li><strong>Data Analytics:</strong> Understanding how data can drive better IT decisions.</li>
                    <li><strong>IoT & Smart Devices:</strong> Tinkering with smart home tech and understanding their underlying protocols.</li>
                </ul>
            </div>
            <div class="card p-4 mb-5 animate-on-load text-center">
                <h2 class="section-title-custom mb-4">Let\'s Connect!</h2>
                <p>I\'m always open to discussing new technologies, collaborating on projects, or simply sharing insights about the IT world. Feel free to reach out!</p>
                <a href="contact.php" class="btn btn-primary btn-lg mt-3">Get in Touch</a>
            </div>
        </div>
    </div>
</div>', 'published', 1);

-- END OF SCRIPT --