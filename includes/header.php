<?php // require_once 'config/config.php'; // Removed as it's included in main files ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? getSetting('site_name', 'Wiracenter'); ?></title>
    <meta name="description" content="<?php echo $page_description ?? getSetting('site_description', 'Personal Portfolio and Tech Blog'); ?>">
    <?php if (getSetting('site_favicon')): ?>
    <link rel="icon" href="<?php echo getSetting('site_favicon'); ?>" type="image/x-icon">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Integrated Language & Theme Toggle Styles -->
    <style>
        .header-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-left: 16px;
        }
        
        .control-btn {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 50px;
            padding: 10px 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .control-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        
        .control-btn i {
            font-size: 16px;
        }
        
        .language-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            min-width: 140px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            margin-top: 5px;
        }
        
        .language-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .language-option {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 12px 16px;
            border: none;
            background: none;
            color: #333;
            text-align: left;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            border-radius: 8px;
            margin: 2px;
        }
        
        .language-option:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .language-option.active {
            background-color: #1e90ff;
            color: white;
        }
        
        .language-option i {
            font-size: 16px;
        }
        
        /* Dark mode styles */
        [data-theme="dark"] .control-btn {
            background: rgba(30, 30, 30, 0.9);
            border-color: rgba(255, 255, 255, 0.1);
            color: #e9ecef;
        }
        
        [data-theme="dark"] .language-dropdown {
            background: rgba(30, 30, 30, 0.95);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        [data-theme="dark"] .language-option {
            color: #e9ecef;
        }
        
        [data-theme="dark"] .language-option:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-controls {
                margin-left: 0;
                margin-top: 8px;
                gap: 8px;
            }
            .control-btn {
                padding: 8px 12px;
                font-size: 12px;
            }
            .control-btn i {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Integrated Language & Theme Controls -->
    <?php
    // Tambahkan fungsi renderHeaderControls untuk dipanggil di navbar.php
    if (!function_exists('renderHeaderControls')) {
        function renderHeaderControls() {
            ?>
            <div class="header-controls">
                <!-- Language Switcher -->
                <div class="language-switcher" style="position: relative;">
                    <button class="control-btn" id="languageBtn">
                        <i class="fas fa-globe"></i>
                        <span id="currentLang">EN</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="language-dropdown" id="languageDropdown">
                        <button class="language-option" data-lang="en" data-active="true">
                            <i class="fas fa-flag"></i>
                            <span>English</span>
                        </button>
                        <button class="language-option" data-lang="id">
                            <i class="fas fa-flag"></i>
                            <span>Indonesia</span>
                        </button>
                    </div>
                </div>
                <!-- Theme Toggle -->
                <button class="control-btn" id="themeToggle" title="Toggle Dark Mode">
                    <i class="fas fa-moon" id="themeIcon"></i>
                    <span id="themeText">Dark</span>
                </button>
            </div>
            <?php
        }
    }
    // Hapus div.header-controls dari luar navbar
    ?>

    <!-- Reading Progress Bar -->
    <div class="reading-progress" id="readingProgress"></div>
    
    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" title="Back to Top">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">

    <!-- Integrated Language & Theme Toggle Script -->
    <script>
        // Language translations
        const translations = {
            en: {
                'nav.home': 'Home',
                'nav.about': 'About',
                'nav.my_spaces': 'My Spaces',
                'nav.contact': 'Contact',
                'theme.dark': 'Dark',
                'theme.light': 'Light',
                'lang.english': 'English',
                'lang.indonesia': 'Indonesia',
                'lang.changed': 'Language changed to English',
                'theme.changed': 'Theme changed to Dark Mode',
                // Home page translations
                'home.welcome': 'Welcome to Wiracenter',
                'home.description': 'Explore my projects, articles, and professional journey',
                'home.view_projects': 'Learn More',
                'home.latest_articles': 'Latest Articles',
                'home.no_articles': 'No articles yet',
                'home.articles_coming': 'Articles will be displayed here once published.',
                'home.view_all_articles': 'View All Articles',
                'home.latest_projects': 'Latest Projects',
                'home.no_projects': 'No projects yet',
                'home.projects_coming': 'Projects will be displayed here once published.',
                'home.view_all_projects': 'View All Projects',
                'home.latest_tools': 'Latest Tools',
                'home.no_tools': 'No tools yet',
                'home.tools_coming': 'Tools will be displayed here once published.',
                'home.view_all_tools': 'View All Tools',
                'home.about_wiracenter': 'About Wiracenter',
                'home.learn_more': 'Learn More',
                'home.main_description': 'Wiracenter is a digital platform dedicated to supporting the growth of Indonesian entrepreneurs. We provide informative articles, inspiring project showcases, and practical tools to help you grow your business from idea to successful execution.',
                // Contact page translations
                'contact.title': 'Contact Me',
                'contact.subtitle': 'We are ready to help you. Please fill out the form below to contact us.',
                'contact.contact_info': 'Contact Information',
                'contact.email': 'Email',
                'contact.phone': 'Phone',
                'contact.location': 'Location',
                'contact.send_message': 'Send Me a Message',
                'contact.name': 'Name',
                'contact.subject': 'Subject',
                'contact.message': 'Message',
                'contact.send': 'Send Message',
                'contact.faq': 'Frequently Asked Questions',
                'contact.connect_with_me': 'Connect With Me',
                'contact.success': 'Thank you! Your message has been sent successfully. I\'ll get back to you soon.',
                'contact.error': 'Sorry, there was an error sending your message. Please try again.',
                'contact.required_name': 'Name is required',
                'contact.required_email': 'Email is required',
                'contact.invalid_email': 'Please enter a valid email address',
                'contact.required_subject': 'Subject is required',
                'contact.required_message': 'Message is required',
                'contact.placeholder': 'Tell me about your project, question, or just say hello...',
                // My Spaces translations
                'my_spaces.title': 'My Digital Spaces',
                'my_spaces.filter_by_type': 'Filter by Type:',
                'my_spaces.all': 'All',
                'my_spaces.articles': 'Articles',
                'my_spaces.projects': 'Projects',
                'my_spaces.tools': 'Tools',
                'my_spaces.filter_by_category': 'Filter by Category:',
                'my_spaces.all_categories': 'All Categories',
                'my_spaces.search_results': 'Search Results',
                'my_spaces.read_more': 'Read More',
                'my_spaces.no_results': 'No results found',
                'my_spaces.try_adjusting': 'Try adjusting your search terms or browse all content below.',
                'my_spaces.browse_all': 'Browse All',
                'my_spaces.latest_articles': 'Latest Articles',
                'my_spaces.read_article': 'Read Article',
                'my_spaces.featured_projects': 'Featured Projects',
                'my_spaces.view_project': 'View Project',
                'my_spaces.useful_tools': 'Useful Tools',
                'my_spaces.use_tool': 'Use Tool',
                'my_spaces.no_content': 'No content found',
                'my_spaces.try_adjusting_criteria': 'There\'s no content matching your current filters. Try adjusting your search criteria.',
                'my_spaces.view_all_content': 'View All Content'
            },
            id: {
                'nav.home': 'Beranda',
                'nav.about': 'Tentang',
                'nav.my_spaces': 'Ruang Saya',
                'nav.contact': 'Kontak',
                'theme.dark': 'Gelap',
                'theme.light': 'Terang',
                'lang.english': 'English',
                'lang.indonesia': 'Indonesia',
                'lang.changed': 'Bahasa diubah ke Indonesia',
                'theme.changed': 'Tema diubah ke Mode Gelap',
                // Home page translations
                'home.welcome': 'Selamat Datang di Wiracenter',
                'home.description': 'Jelajahi proyek, artikel, dan perjalanan profesional saya',
                'home.view_projects': 'Pelajari Lebih Lanjut',
                'home.latest_articles': 'Artikel Terbaru',
                'home.no_articles': 'Belum Ada Artikel',
                'home.articles_coming': 'Artikel akan ditampilkan di sini setelah dipublikasikan.',
                'home.view_all_articles': 'Lihat Semua Artikel',
                'home.latest_projects': 'Proyek Terbaru',
                'home.no_projects': 'Belum Ada Proyek',
                'home.projects_coming': 'Proyek akan ditampilkan di sini setelah dipublikasikan.',
                'home.view_all_projects': 'Lihat Semua Proyek',
                'home.latest_tools': 'Tools Terbaru',
                'home.no_tools': 'Belum Ada Tools',
                'home.tools_coming': 'Tools akan ditampilkan di sini setelah dipublikasikan.',
                'home.view_all_tools': 'Lihat Semua Tools',
                'home.about_wiracenter': 'Tentang Wiracenter',
                'home.learn_more': 'Pelajari Lebih Lanjut',
                'home.main_description': 'Wiracenter adalah platform digital yang didedikasikan untuk mendukung pertumbuhan wirausaha Indonesia. Kami menyediakan artikel informatif, showcase proyek inspiratif, dan tools praktis yang dapat membantu Anda mengembangkan bisnis dari ide hingga eksekusi yang sukses.',
                // Contact page translations
                'contact.title': 'Hubungi Saya',
                'contact.subtitle': 'Kami siap membantu Anda. Silakan isi form di bawah ini untuk menghubungi kami.',
                'contact.contact_info': 'Informasi Kontak',
                'contact.email': 'Email',
                'contact.phone': 'Telepon',
                'contact.location': 'Lokasi',
                'contact.send_message': 'Kirim Pesan ke Saya',
                'contact.name': 'Nama',
                'contact.subject': 'Subjek',
                'contact.message': 'Pesan',
                'contact.send': 'Kirim Pesan',
                'contact.faq': 'Pertanyaan yang Sering Diajukan',
                'contact.connect_with_me': 'Terhubung dengan Saya',
                'contact.success': 'Terima kasih! Pesan Anda telah berhasil dikirim. Saya akan segera menghubungi Anda.',
                'contact.error': 'Maaf, terjadi kesalahan saat mengirim pesan. Silakan coba lagi.',
                'contact.required_name': 'Nama wajib diisi',
                'contact.required_email': 'Email wajib diisi',
                'contact.invalid_email': 'Masukkan alamat email yang valid',
                'contact.required_subject': 'Subjek wajib diisi',
                'contact.required_message': 'Pesan wajib diisi',
                'contact.placeholder': 'Ceritakan tentang proyek Anda, pertanyaan, atau sekadar menyapa...',
                // My Spaces translations
                'my_spaces.title': 'Ruang Digital Saya',
                'my_spaces.filter_by_type': 'Filter berdasarkan Tipe:',
                'my_spaces.all': 'Semua',
                'my_spaces.articles': 'Artikel',
                'my_spaces.projects': 'Proyek',
                'my_spaces.tools': 'Tools',
                'my_spaces.filter_by_category': 'Filter berdasarkan Kategori:',
                'my_spaces.all_categories': 'Semua Kategori',
                'my_spaces.search_results': 'Hasil Pencarian',
                'my_spaces.read_more': 'Baca Selengkapnya',
                'my_spaces.no_results': 'Tidak ada hasil ditemukan',
                'my_spaces.try_adjusting': 'Coba ubah kata kunci pencarian atau telusuri semua konten di bawah ini.',
                'my_spaces.browse_all': 'Lihat Semua',
                'my_spaces.latest_articles': 'Artikel Terbaru',
                'my_spaces.read_article': 'Baca Artikel',
                'my_spaces.featured_projects': 'Proyek Unggulan',
                'my_spaces.view_project': 'Lihat Proyek',
                'my_spaces.useful_tools': 'Tools Bermanfaat',
                'my_spaces.use_tool': 'Gunakan Tool',
                'my_spaces.no_content': 'Tidak ada konten ditemukan',
                'my_spaces.try_adjusting_criteria': 'Tidak ada konten yang cocok dengan filter Anda. Coba ubah kriteria pencarian.',
                'my_spaces.view_all_content': 'Lihat Semua Konten'
            }
        };

        // Tambahkan key FAQ otomatis (langsung di object, bukan array_merge PHP)
        translations['en']['faq.q1'] = 'What services are available?';
        translations['en']['faq.a1'] = 'You can explore various digital projects, tutorials, and tech experiments here. I also enjoy sharing knowledge and insights about technology trends and digital solutions.';
        translations['en']['faq.q2'] = 'How long does a typical project take?';
        translations['en']['faq.a2'] = 'Project timelines vary depending on complexity and requirements. After discussing your needs, I will provide a clear estimate and schedule.';
        translations['en']['faq.q3'] = 'Is there ongoing support or content updates?';
        translations['en']['faq.a3'] = 'Yes, I offer ongoing support and regular content updates to ensure your project or learning journey continues smoothly.';
        translations['en']['faq.q4'] = 'What topics and technologies are usually featured?';
        translations['en']['faq.a4'] = 'Topics include web development, UI/UX design, automation, and the latest in digital technology. I also cover practical tips and case studies.';
        translations['id']['faq.q1'] = 'Layanan apa saja yang tersedia?';
        translations['id']['faq.a1'] = 'Anda dapat menjelajahi berbagai proyek digital, tutorial, dan eksperimen teknologi di sini. Saya juga senang berbagi wawasan dan pengetahuan seputar tren teknologi dan solusi digital.';
        translations['id']['faq.q2'] = 'Berapa lama rata-rata pengerjaan proyek?';
        translations['id']['faq.a2'] = 'Durasi proyek berbeda-beda tergantung kompleksitas dan kebutuhan. Setelah diskusi, saya akan memberikan estimasi waktu dan jadwal yang jelas.';
        translations['id']['faq.q3'] = 'Apakah ada dukungan berkelanjutan atau update konten?';
        translations['id']['faq.a3'] = 'Ya, saya menyediakan dukungan berkelanjutan dan update konten secara rutin agar proyek atau proses belajar Anda tetap berjalan lancar.';
        translations['id']['faq.q4'] = 'Topik dan teknologi apa saja yang biasanya dibahas?';
        translations['id']['faq.a4'] = 'Topik meliputi pengembangan web, desain UI/UX, otomasi, dan teknologi digital terbaru. Saya juga membahas tips praktis dan studi kasus.';

        // Tambahkan key footer.copyright
        translations['en']['footer.copyright'] = '@ 2025 Wiracenter. All rights reserved';
        translations['id']['footer.copyright'] = '@ 2025 Wiracenter. All rights reserved';

        // Translation function
        function translate(key, lang = 'en') {
            const langData = translations[lang] || translations['en'];
            if (langData[key]) {
                return langData[key];
            } else {
                // Fallback: tampilkan key aslinya dan warning di console
                if (window && window.console) {
                    console.warn('Translation key not found:', key, 'for lang:', lang);
                }
                return key;
            }
        }

        // Initialize controls when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initLanguageSwitcher();
            initThemeToggle();
            initPageTranslations();
        });

        // Language Switcher
        function initLanguageSwitcher() {
            const languageBtn = document.getElementById('languageBtn');
            const languageDropdown = document.getElementById('languageDropdown');
            const currentLang = document.getElementById('currentLang');
            const languageOptions = document.querySelectorAll('.language-option');
            
            if (!languageBtn || !languageDropdown || !currentLang) return;
            
            // Get saved language from localStorage
            const savedLang = localStorage.getItem('language') || 'en';
            updateLanguage(savedLang);
            
            // Toggle dropdown
            languageBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                languageDropdown.classList.toggle('show');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                languageDropdown.classList.remove('show');
            });
            
            // Handle language selection
            languageOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const lang = this.getAttribute('data-lang');
                    updateLanguage(lang);
                    languageDropdown.classList.remove('show');
                    
                    // Update active state
                    languageOptions.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        }

        function updateLanguage(lang) {
            const currentLang = document.getElementById('currentLang');
            const html = document.documentElement;
            
            // Update display
            if (currentLang) {
                currentLang.textContent = lang.toUpperCase();
            }
            
            // Update HTML lang attribute
            html.setAttribute('lang', lang);
            
            // Save to localStorage
            localStorage.setItem('language', lang);
            
            // Update active state in dropdown
            const languageOptions = document.querySelectorAll('.language-option');
            languageOptions.forEach(option => {
                if (option.getAttribute('data-lang') === lang) {
                    option.classList.add('active');
                } else {
                    option.classList.remove('active');
                }
            });
            
            // Update page content
            updatePageTranslations(lang);
            
            // Show notification
            showNotification('info', translate('lang.changed', lang));
        }

        // Theme Toggle
        function initThemeToggle() {
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            const html = document.documentElement;
            
            if (!themeToggle || !themeIcon || !themeText) return;
            
            // Get saved theme from localStorage
            const savedTheme = localStorage.getItem('theme') || 'light';
            setTheme(savedTheme);
            
            // Add event listener
            themeToggle.addEventListener('click', function() {
                const currentTheme = html.getAttribute('data-theme') || 'light';
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                setTheme(newTheme);
                
                // Add animation effect
                themeToggle.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    themeToggle.style.transform = 'scale(1)';
                }, 150);
            });
        }

        function setTheme(theme) {
            const html = document.documentElement;
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            
            html.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            
            // Update icon and text
            if (themeIcon) {
                if (theme === 'dark') {
                    themeIcon.className = 'fas fa-sun';
                    themeIcon.title = 'Switch to Light Mode';
                } else {
                    themeIcon.className = 'fas fa-moon';
                    themeIcon.title = 'Switch to Dark Mode';
                }
            }
            
            if (themeText) {
                themeText.textContent = translate(`theme.${theme}`, getCurrentLanguage());
            }
            
            // Show notification
            showNotification('info', translate('theme.changed', getCurrentLanguage()));
        }

        // Page Translations
        function initPageTranslations() {
            // Pastikan update setelah seluruh konten dimuat
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                const currentLang = localStorage.getItem('language') || 'en';
                updatePageTranslations(currentLang);
            } else {
                window.addEventListener('DOMContentLoaded', function() {
                    const currentLang = localStorage.getItem('language') || 'en';
                    updatePageTranslations(currentLang);
                });
            }
        }

        function updatePageTranslations(lang) {
            // Update navigation links
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && href.includes('index.php')) {
                    link.textContent = translate('nav.home', lang);
                } else if (href && href.includes('about')) {
                    link.textContent = translate('nav.about', lang);
                } else if (href && href.includes('my-spaces.php')) {
                    link.textContent = translate('nav.my_spaces', lang);
                } else if (href && href.includes('contact.php')) {
                    link.textContent = translate('nav.contact', lang);
                }
            });
            // Update all elements with data-i18n attribute
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (key) {
                    el.textContent = translate(key, lang);
                }
            });
        }

        function getCurrentLanguage() {
            return localStorage.getItem('language') || 'en';
        }

        // Notification system
        function showNotification(type, message) {
            const container = document.getElementById('notificationContainer');
            if (!container) return;
            
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <div class="notification-content">${message}</div>
                <button class="notification-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.appendChild(notification);
            
            // Show animation
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }, 3000);
        }
    </script>
