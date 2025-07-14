<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';

// Initialize database connection
$db = new Database();
$conn = $db->getConnection();

// Initialize empty arrays
$latest_articles = [];
$latest_projects = [];
$latest_tools = [];
$featured_articles = [];
$featured_projects = [];
$featured_tools = [];

try {
    // Get latest articles (3)
    $stmt = $conn->prepare("SELECT id, title, excerpt, featured_image, created_at, slug FROM articles WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $latest_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error or handle gracefully
    error_log("Error fetching articles: " . $e->getMessage());
}

try {
    // Get latest projects (3)
    $stmt = $conn->prepare("SELECT id, title, description, featured_image, created_at, slug FROM projects WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $latest_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching projects: " . $e->getMessage());
}

try {
    // Get latest tools (3)
    $stmt = $conn->prepare("SELECT id, title, description, featured_image, created_at, slug FROM tools WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $latest_tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching tools: " . $e->getMessage());
}

try {
    // Get featured content for slider (note: featured column doesn't exist, so we'll use latest published)
    $stmt = $conn->prepare("SELECT id, title, excerpt, featured_image, 'article' as type, slug FROM articles WHERE status = 'published' ORDER BY created_at DESC LIMIT 2");
    $stmt->execute();
    $featured_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT id, title, description, featured_image, 'project' as type, slug FROM projects WHERE status = 'published' ORDER BY created_at DESC LIMIT 2");
    $stmt->execute();
    $featured_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT id, title, description, featured_image, 'tool' as type, slug FROM tools WHERE status = 'published' ORDER BY created_at DESC LIMIT 1");
    $stmt->execute();
    $featured_tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching featured content: " . $e->getMessage());
}

$slider_items = array_merge($featured_articles, $featured_projects, $featured_tools);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? html_entity_decode($page_title, ENT_QUOTES | ENT_HTML5, 'UTF-8') : getSetting('site_name', 'Wiracenter'); ?></title>
    <meta name="description" content="Wiracenter adalah platform digital yang menyediakan artikel, proyek, dan tools untuk membantu wirausaha berkembang.">
    <meta name="keywords" content="wirausaha, bisnis, artikel, proyek, tools, digital">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Custom CSS for Homepage -->
    <style>
        :root {
            --primary-cyan: #00BCD4;
            --secondary-cyan: #0097A7;
            --accent-cyan: #26C6DA;
            --light-cyan: #B2EBF2;
            --dark-cyan: #00695C;
        }

        /* Hero Section & Slider */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-cyan), var(--secondary-cyan));
            color: white;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-slider {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
            height: 400px;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
            display: flex;
            align-items: center;
            padding: 0 20px;
        }

        .slide.active {
            opacity: 1;
        }

        .slide-content {
            max-width: 600px;
            z-index: 3; /* dinaikkan agar di atas tombol */
            padding-left: 60px;
            padding-right: 60px;
        }

        .slide-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .slide-description {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .slide-bg {
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0.3;
            border-radius: 20px 0 0 20px;
        }

        /* Slider Navigation */
        .slider-nav {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 3;
        }

        .slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .slider-dot.active {
            background: white;
            transform: scale(1.2);
        }

        .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 1.5rem;
            padding: 15px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 3;
        }

        .slider-arrow:hover {
            background: rgba(255,255,255,0.3);
        }

        .slider-arrow.prev {
            left: -30px;
        }

        .slider-arrow.next {
            right: -30px;
        }

        /* Sections */
        .section {
            padding: 80px 0;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            color: var(--dark-cyan);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-cyan), var(--secondary-cyan));
            border-radius: 2px;
        }

        /* Cards */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .card-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card-icon {
            width: 100%;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--light-cyan), var(--accent-cyan));
            font-size: 4rem;
            color: white;
        }

        .card-content {
            padding: 25px;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-cyan);
        }

        .card-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: #888;
        }

        .card-date {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .read-more {
            color: var(--primary-cyan);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .read-more:hover {
            color: var(--secondary-cyan);
        }

        /* About Section */
        .about-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        .about-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }

        .about-description {
            font-size: 1.2rem;
            line-height: 1.8;
            color: #555;
            margin-bottom: 30px;
        }

        /* CTA Button */
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary-cyan), var(--secondary-cyan));
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,188,212,0.3);
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .slide-title {
                font-size: 2rem;
            }
            
            .slide-description {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .card-grid {
                grid-template-columns: 1fr;
            }
            
            .slider-arrow {
                display: none;
            }
            .slide-content {
                padding-left: 20px;
                padding-right: 20px;
            }
        }

        /* Loading Animation */
        .loading {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section with Slider -->
    <section class="hero-section">
        <div class="hero-slider">
            <!-- Slide 1: Welcome -->
            <div class="slide active" data-slide="0">
                <div class="slide-content">
                    <h1 data-i18n="home.welcome">Selamat Datang di Wiracenter</h1>
                    <p class="slide-description" data-i18n="home.description">Platform digital terdepan untuk wirausaha Indonesia. Temukan inspirasi, pengetahuan, dan tools yang Anda butuhkan untuk mengembangkan bisnis.</p>
                    <a href="#about" class="cta-button" data-i18n="home.view_projects">Pelajari Lebih Lanjut</a>
                    <!-- [SLIDER NAV INSIDE .slide-content - COMMENTED OUT] -->
                    <?php /*
                    if ($slider_items): ?>
                    <div class="slider-nav">
                        <?php 
                        $total_slides = 1 + count($slider_items);
                        for ($i = 0; $i < $total_slides; $i++): 
                        ?>
                        <div class="slider-dot <?php echo $i === 0 ? 'active' : ''; ?>" onclick="goToSlide(<?php echo $i; ?>)"></div>
                        <?php endfor; ?>
                    </div>
                    <?php endif; */ ?>
                </div>
                <div class="slide-bg" style="background-image: url('assets/images/hero-bg.jpg')"></div>
            </div>

            <!-- Dynamic Slides from Database -->
            <?php if (!empty($slider_items)): ?>
                <?php foreach ($slider_items as $index => $item): ?>
                <div class="slide" data-slide="<?php echo $index + 1; ?>">
                    <div class="slide-content">
                        <h2 class="slide-title"><?php echo html_entity_decode($item['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></h2>
                        <p class="slide-description">
                            <?php 
                            $description = isset($item['excerpt']) ? $item['excerpt'] : $item['description'];
                            echo html_entity_decode(substr($description, 0, 150)) . '...';
                            ?>
                        </p>
                        <a href="<?php echo $item['type']; ?>.php?slug=<?php echo $item['slug']; ?>" class="cta-button">Read More</a>
                        <!-- [SLIDER NAV INSIDE .slide-content - COMMENTED OUT] -->
                        <?php /*
                        if ($slider_items): ?>
                        <div class="slider-nav">
                            <?php 
                            $total_slides = 1 + count($slider_items);
                            for ($i = 0; $i < $total_slides; $i++): 
                            ?>
                            <div class="slider-dot <?php echo $i === ($index + 1) ? 'active' : ''; ?>" onclick="goToSlide(<?php echo $i; ?>)"></div>
                            <?php endfor; ?>
                        </div>
                        <?php endif; */ ?>
                    </div>
                    <div class="slide-bg" style="background-image: url('<?php echo isset($item['featured_image']) ? 'uploads/' . $item['featured_image'] : 'assets/images/default-' . $item['type'] . '.jpg'; ?>')"></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Navigation -->
            <button class="slider-arrow prev" onclick="changeSlide(-1)">‹</button>
            <button class="slider-arrow next" onclick="changeSlide(1)">›</button>
            <!-- [SLIDER NAV OUTSIDE .slide-content - RESTORED] -->
            <div class="slider-nav">
                <?php 
                $total_slides = 1 + count($slider_items); // 1 for welcome slide + dynamic slides
                for ($i = 0; $i < $total_slides; $i++): 
                ?>
                <div class="slider-dot <?php echo $i === 0 ? 'active' : ''; ?>" onclick="goToSlide(<?php echo $i; ?>)"></div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- Latest Articles Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title" data-i18n="home.latest_articles">Artikel Terbaru</h2>
            <div class="card-grid">
                <?php if (!empty($latest_articles)): ?>
                    <?php foreach ($latest_articles as $article): ?>
                    <div class="card loading">
                        <?php if ($article['featured_image']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($article['featured_image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="card-image">
                        <?php else: ?>
                        <div class="card-icon">📄</div>
                        <?php endif; ?>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo html_entity_decode($article['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></h3>
                            <p class="card-description"><?php echo html_entity_decode($article['excerpt'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></p>
                            <div class="card-meta">
                                <span class="card-date">
                                    📅 <?php echo date('d M Y', strtotime($article['created_at'])); ?>
                                </span>
                                <a href="article.php?slug=<?php echo $article['slug']; ?>" class="read-more">Read More →</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card loading" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                        <div class="card-icon">📄</div>
                        <div class="card-content">
                            <h3 class="card-title" data-i18n="home.no_articles">Belum Ada Artikel</h3>
                            <p class="card-description" data-i18n="home.articles_coming">Artikel akan ditampilkan di sini setelah dipublikasikan.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div style="text-align: center; margin-top: 40px;">
                <a href="articles.php" class="cta-button" data-i18n="home.view_all_articles">Lihat Semua Artikel</a>
            </div>
        </div>
    </section>

    <!-- Latest Projects Section -->
    <section class="section" style="background: #f8f9fa;">
        <div class="container">
            <h2 class="section-title" data-i18n="home.latest_projects">Proyek Terbaru</h2>
            <div class="card-grid">
                <?php if (!empty($latest_projects)): ?>
                    <?php foreach ($latest_projects as $project): ?>
                    <div class="card loading">
                        <?php if ($project['featured_image']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($project['featured_image']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="card-image">
                        <?php else: ?>
                        <div class="card-icon">🚀</div>
                        <?php endif; ?>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo htmlspecialchars_decode($project['title']); ?></h3>
                            <p class="card-description"><?php echo htmlspecialchars_decode(substr($project['description'], 0, 120)) . '...'; ?></p>
                            <div class="card-meta">
                                <span class="card-date">
                                    📅 <?php echo date('d M Y', strtotime($project['created_at'])); ?>
                                </span>
                                <a href="project.php?slug=<?php echo $project['slug']; ?>" class="read-more">View Details →</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card loading" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                        <div class="card-icon">🚀</div>
                        <div class="card-content">
                            <h3 class="card-title" data-i18n="home.no_projects">Belum Ada Proyek</h3>
                            <p class="card-description" data-i18n="home.projects_coming">Proyek akan ditampilkan di sini setelah dipublikasikan.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div style="text-align: center; margin-top: 40px;">
                <a href="projects.php" class="cta-button" data-i18n="home.view_all_projects">Lihat Semua Proyek</a>
            </div>
        </div>
    </section>

    <!-- Latest Tools Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title" data-i18n="home.latest_tools">Tools Terbaru</h2>
            <div class="card-grid">
                <?php if (!empty($latest_tools)): ?>
                    <?php foreach ($latest_tools as $tool): ?>
                    <div class="card loading">
                        <?php if ($tool['featured_image']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($tool['featured_image']); ?>" alt="<?php echo htmlspecialchars($tool['title']); ?>" class="card-image">
                        <?php else: ?>
                        <div class="card-icon">🛠️</div>
                        <?php endif; ?>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo htmlspecialchars_decode($tool['title']); ?></h3>
                            <p class="card-description"><?php echo htmlspecialchars_decode(substr($tool['description'], 0, 120)) . '...'; ?></p>
                            <div class="card-meta">
                                <span class="card-date">
                                    📅 <?php echo date('d M Y', strtotime($tool['created_at'])); ?>
                                </span>
                                <a href="tool.php?slug=<?php echo $tool['slug']; ?>" class="read-more">Use Tool →</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card loading" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                        <div class="card-icon">🛠️</div>
                        <div class="card-content">
                            <h3 class="card-title" data-i18n="home.no_tools">Belum Ada Tools</h3>
                            <p class="card-description" data-i18n="home.tools_coming">Tools akan ditampilkan di sini setelah dipublikasikan.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div style="text-align: center; margin-top: 40px;">
                <a href="tools.php" class="cta-button" data-i18n="home.view_all_tools">Lihat Semua Tools</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section about-section" id="about">
        <div class="container">
            <div class="about-content">
                <h2 class="section-title" data-i18n="home.about_wiracenter">About Wiracenter</h2>
                <p class="lead" data-i18n="home.main_description"><?php echo getSetting('site_description', 'Wiracenter is a digital playground for tech enthusiasts, learners, and makers. Here, you\'ll find hands-on experiments, practical guides, and real project showcases—built to inspire curiosity, share knowledge, and connect with fellow explorers in the world of technology.'); ?></p>
                <a href="about.php" class="cta-button" data-i18n="home.learn_more">Learn More</a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="assets/js/script.js"></script>
    <script>
        // Slider functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.slider-dot');
        const totalSlides = slides.length;

        function showSlide(n) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            currentSlide = (n + totalSlides) % totalSlides;
            
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        }

        function changeSlide(direction) {
            showSlide(currentSlide + direction);
        }

        function goToSlide(n) {
            showSlide(n);
        }

        // Auto slide every 5 seconds (only if there are multiple slides)
        if (totalSlides > 1) {
            setInterval(() => {
                changeSlide(1);
            }, 5000);
        }

        // Loading animation
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationDelay = Math.random() * 0.3 + 's';
                    entry.target.classList.add('loading');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.card').forEach(card => {
            observer.observe(card);
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
