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
- **File Management**: Upload and manage media files
- **Site Settings**: Dynamic site configuration
- **User Management**: Role-based access control (Admin/Editor/Viewer)
- **Contact Messages**: View and manage contact form submissions

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
git clone https://github.com/yourusername/wiracenter-portfolio.git
cd wiracenter-portfolio
```

### Step 2: Database Setup
1. Create a MySQL database named `wiracenter_portfolio`
2. Import the database schema:
```bash
mysql -u username -p wiracenter_portfolio < database/schema.sql
```

### Step 3: Configuration
1. Update database credentials in `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'wiracenter_portfolio';
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
- **Password**: admin123

> **Important**: Change the default password immediately after first login!

## 📁 Project Structure

```
wiracenter-portfolio/
├── admin/                  # Admin dashboard
│   ├── api/               # API endpoints
│   ├── includes/          # Header and footer includes
│   ├── dashboard.php      # Main dashboard
│   ├── articles.php       # Articles management
│   ├── projects.php       # Projects management
│   ├── tools.php          # Tools management
│   ├── files.php          # File management
│   ├── settings.php       # Site settings
│   ├── users.php          # User management
│   └── login.php          # Admin login
├── api/                   # Frontend API endpoints
├── assets/                # Static assets
│   ├── css/              # Stylesheets
│   └── js/               # JavaScript files
├── config/                # Configuration files
│   ├── database.php      # Database connection
│   └── config.php        # Main configuration
├── database/              # Database schema
│   └── schema.sql        # Database structure
├── uploads/               # File uploads directory
├── index.php             # Homepage
├── about.php             # About page
├── my-spaces.php         # Projects/Articles/Tools page
├── contact.php           # Contact page
└── README.md             # This file
```

## 🎨 Customization

### Adding New Content Types
1. Create database table in `database/schema.sql`
2. Add navigation link in `admin/includes/header.php`
3. Create management page in `admin/`
4. Add API endpoints in `admin/api/`

### Styling
- Custom CSS is in `assets/css/style.css`
- CSS variables are defined in `:root` for easy theme customization
- Bootstrap classes can be overridden

### Site Settings
All site settings can be managed through the admin dashboard:
- Site name and description
- Contact information
- Hero section content
- About page content
- Social media links

## 🔧 Features in Detail

### Articles Management
- Rich text editor (TinyMCE)
- Featured images
- Excerpt and SEO fields
- Draft/Published status
- Publish date scheduling

### Projects Management
- Project descriptions and content
- Technology tags (JSON array)
- Project and GitHub URLs
- Featured images
- Status management

### Tools Management
- Tool categories
- Tool URLs
- Descriptions and content
- Featured images

### File Management
- Drag-and-drop upload
- File type validation
- File size limits
- Database tracking

### User Management
- Three user roles: Admin, Editor, Viewer
- Role-based permissions
- User creation and management
- Password hashing

## 🛡️ Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- Session-based authentication
- Role-based access control
- File upload validation

## 📱 Responsive Design

The website is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- Various screen sizes

## 🎯 SEO Optimized

- Clean URL structure
- Meta tags and descriptions
- Semantic HTML structure
- Fast loading times
- Mobile-friendly design

## 🔄 Updates and Maintenance

### Regular Updates
- Keep PHP and MySQL updated
- Update Bootstrap and other dependencies
- Regular security patches

### Backup
- Regular database backups
- File system backups
- Version control with Git

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Create an issue on GitHub
- Email: support@wiracenter.com
- Documentation: Check the code comments

## 🚀 Deployment

### Apache Configuration
```apache
<Directory /path/to/wiracenter-portfolio>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/wiracenter-portfolio;
    index index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🎉 Credits

- **Bootstrap**: Frontend framework
- **Font Awesome**: Icons
- **TinyMCE**: Rich text editor
- **PHP**: Server-side scripting
- **MySQL**: Database management

---

**Made with ❤️ by Wiracenter**