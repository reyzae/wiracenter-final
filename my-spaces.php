<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';

// Initialize database connection
$db = new Database();
$conn = $db->getConnection();

// Initialize empty arrays
$articles = [];
$projects = [];
$tools = [];
$categories = [];
$search_results = [];

// Get filter parameters
$category_filter = $_GET['category'] ?? '';
$type_filter = $_GET['type'] ?? '';
$search_query = $_GET['search'] ?? '';

try {
    // Get all categories from tools
    $stmt = $conn->prepare("SELECT DISTINCT category FROM tools WHERE status = 'published' AND category IS NOT NULL AND category != '' ORDER BY category");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}

try {
    // Build search query if search parameter is provided
    if (!empty($search_query)) {
        $search_term = "%{$search_query}%";
        
        // Search in articles
        $stmt = $conn->prepare("SELECT id, title, excerpt, featured_image, created_at, slug, 'article' as type FROM articles 
                               WHERE status = 'published' AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?) 
                               ORDER BY created_at DESC");
        $stmt->execute([$search_term, $search_term, $search_term]);
        $search_results = array_merge($search_results, $stmt->fetchAll(PDO::FETCH_ASSOC));
        
        // Search in projects
        $stmt = $conn->prepare("SELECT id, title, description, featured_image, created_at, slug, 'project' as type FROM projects 
                               WHERE status = 'published' AND (title LIKE ? OR description LIKE ? OR content LIKE ?) 
                               ORDER BY created_at DESC");
        $stmt->execute([$search_term, $search_term, $search_term]);
        $search_results = array_merge($search_results, $stmt->fetchAll(PDO::FETCH_ASSOC));
        
        // Search in tools
        $stmt = $conn->prepare("SELECT id, title, description, featured_image, created_at, slug, 'tool' as type FROM tools 
                               WHERE status = 'published' AND (title LIKE ? OR description LIKE ? OR content LIKE ?) 
                               ORDER BY created_at DESC");
        $stmt->execute([$search_term, $search_term, $search_term]);
        $search_results = array_merge($search_results, $stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        // Get articles based on filters
        $article_sql = "SELECT id, title, excerpt, featured_image, created_at, slug FROM articles WHERE status = 'published'";
        $article_params = [];
        
        if (!empty($type_filter) && $type_filter !== 'article') {
            $article_sql .= " AND 1=0"; // Don't show articles if type filter is not article
        }
        
        $article_sql .= " ORDER BY created_at DESC LIMIT 12";
        
        $stmt = $conn->prepare($article_sql);
        $stmt->execute($article_params);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get projects based on filters
        $project_sql = "SELECT id, title, description, featured_image, created_at, slug FROM projects WHERE status = 'published'";
        $project_params = [];
        
        if (!empty($type_filter) && $type_filter !== 'project') {
            $project_sql .= " AND 1=0"; // Don't show projects if type filter is not project
        }
        
        $project_sql .= " ORDER BY created_at DESC LIMIT 12";
        
        $stmt = $conn->prepare($project_sql);
        $stmt->execute($project_params);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get tools based on filters
        $tool_sql = "SELECT id, title, description, featured_image, created_at, slug, category FROM tools WHERE status = 'published'";
        $tool_params = [];
        
        if (!empty($type_filter) && $type_filter !== 'tool') {
            $tool_sql .= " AND 1=0"; // Don't show tools if type filter is not tool
        }
        
        if (!empty($category_filter)) {
            $tool_sql .= " AND category = ?";
            $tool_params[] = $category_filter;
        }
        
        $tool_sql .= " ORDER BY created_at DESC LIMIT 12";
        
        $stmt = $conn->prepare($tool_sql);
        $stmt->execute($tool_params);
        $tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error fetching content: " . $e->getMessage());
}

// Get site settings
$site_name = getSetting('site_name', 'Wiracenter');
$site_description = getSetting('site_description', 'Showcase of projects, experiments, and stories in tech, and digital world.');

// Set page variables for header.php
$page_title = "My Spaces - " . $site_name;
$page_description = "Explore my digital spaces - articles, projects, and tools for tech enthusiasts and digital creators.";
?>

<?php include 'includes/header.php'; ?>

<!-- Custom CSS for My Spaces -->
<style>
    :root {
        --primary-cyan: #00BCD4;
        --secondary-cyan: #0097A7;
        --accent-cyan: #26C6DA;
        --light-cyan: #B2EBF2;
        --dark-cyan: #00695C;
        --gradient-primary: linear-gradient(135deg, var(--primary-cyan), var(--secondary-cyan));
        --gradient-secondary: linear-gradient(135deg, var(--accent-cyan), var(--light-cyan));
    }

    body {
        font-family: 'Fira Sans', Arial, Helvetica, sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    .hero-section {
        background: var(--gradient-primary);
        color: white;
        padding: 80px 0 60px;
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.3;
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .hero-subtitle {
        font-size: 1.3rem;
        margin-bottom: 2rem;
        opacity: 0.9;
    }

    .search-section {
        background: white;
        padding: 40px 0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    .search-form {
        max-width: 600px;
        margin: 0 auto;
    }

    .search-input {
        border: 2px solid #e9ecef;
        border-radius: 50px;
        padding: 15px 25px;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        border-color: var(--primary-cyan);
        box-shadow: 0 0 0 0.2rem rgba(0, 188, 212, 0.25);
    }

    .filter-section {
        background: white;
        padding: 30px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .filter-btn {
        border: 2px solid var(--primary-cyan);
        color: var(--primary-cyan);
        background: transparent;
        border-radius: 25px;
        padding: 8px 20px;
        margin: 5px;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: var(--primary-cyan);
        color: white;
        transform: translateY(-2px);
    }

    .content-section {
        padding: 60px 0;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        text-align: center;
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
        background: var(--gradient-primary);
        border-radius: 2px;
    }

    .content-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 30px;
        margin-bottom: 50px;
    }

    .content-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        position: relative;
    }

    .content-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .card-image {
        height: 200px;
        background-size: cover;
        background-position: center;
        position: relative;
    }

    .card-image::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(0,188,212,0.8), rgba(0,151,167,0.8));
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .content-card:hover .card-image::before {
        opacity: 1;
    }

    .card-type-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255,255,255,0.9);
        color: var(--primary-cyan);
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-content {
        padding: 25px;
    }

    .card-title {
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: var(--dark-cyan);
        line-height: 1.3;
    }

    .card-excerpt {
        color: #666;
        line-height: 1.6;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .card-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        font-size: 0.9rem;
        color: #888;
    }

    .card-date {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .card-category {
        background: var(--light-cyan);
        color: var(--dark-cyan);
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .card-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--primary-cyan);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .card-link:hover {
        color: var(--secondary-cyan);
        transform: translateX(5px);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .empty-state i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        margin-bottom: 10px;
        color: #999;
    }

    .stats-section {
        background: white;
        padding: 40px 0;
        margin-bottom: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .stat-item {
        text-align: center;
        padding: 20px;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-cyan);
        margin-bottom: 10px;
    }

    .stat-label {
        font-size: 1rem;
        color: #666;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2rem;
        }
        
        .hero-subtitle {
            font-size: 1.1rem;
        }
        
        .section-title {
            font-size: 2rem;
        }
        
        .content-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .search-input {
            font-size: 1rem;
            padding: 12px 20px;
        }
    }

    .loading-spinner {
        display: none;
        text-align: center;
        padding: 40px;
    }

    .spinner-border {
        color: var(--primary-cyan);
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center hero-content">
                <h1 data-i18n="my_spaces.title">My Digital Spaces</h1>
                <p class="hero-subtitle">Explore my collection of articles, projects, and tools in the digital world</p>
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="search-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <form class="search-form" method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control search-input" name="search" 
                               placeholder="Search articles, projects, or tools..." 
                               value="<?php echo htmlspecialchars($search_query); ?>">
                        <button class="btn btn-primary" type="submit" style="border-radius: 0 50px 50px 0; padding: 15px 25px;">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Filter Section -->
<?php if (empty($search_query)): ?>
<section class="filter-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h5 data-i18n="my_spaces.filter_by_type">Filter by Type:</h5>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['type' => ''])); ?>" 
                   class="btn filter-btn <?php echo empty($type_filter) ? 'active' : ''; ?>">
                    <span data-i18n="my_spaces.all">All</span>
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['type' => 'article'])); ?>" 
                   class="btn filter-btn <?php echo $type_filter === 'article' ? 'active' : ''; ?>">
                    <i class="fas fa-newspaper me-2"></i><span data-i18n="my_spaces.articles">Articles</span>
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['type' => 'project'])); ?>" 
                   class="btn filter-btn <?php echo $type_filter === 'project' ? 'active' : ''; ?>">
                    <i class="fas fa-project-diagram me-2"></i><span data-i18n="my_spaces.projects">Projects</span>
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['type' => 'tool'])); ?>" 
                   class="btn filter-btn <?php echo $type_filter === 'tool' ? 'active' : ''; ?>">
                    <i class="fas fa-tools me-2"></i><span data-i18n="my_spaces.tools">Tools</span>
                </a>
            </div>
        </div>
        
        <?php if (!empty($categories) && ($type_filter === 'tool' || empty($type_filter))): ?>
        <div class="row mt-4">
            <div class="col-12 text-center">
                <h5 data-i18n="my_spaces.filter_by_category">Filter by Category:</h5>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => ''])); ?>" 
                   class="btn filter-btn <?php echo empty($category_filter) ? 'active' : ''; ?>">
                    <span data-i18n="my_spaces.all_categories">All Categories</span>
                </a>
                <?php foreach ($categories as $category): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => $category])); ?>" 
                   class="btn filter-btn <?php echo $category_filter === $category ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($category); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Content Section -->
<section class="content-section">
    <div class="container">
        <?php if (!empty($search_query)): ?>
            <!-- Search Results -->
            <h2 class="section-title" data-i18n="my_spaces.search_results">Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h2>
            
            <?php if (!empty($search_results)): ?>
                <div class="content-grid">
                    <?php foreach ($search_results as $item): ?>
                        <div class="content-card">
                            <div class="card-image" style="background-image: url('<?php echo !empty($item['featured_image']) ? 'uploads/' . $item['featured_image'] : 'assets/images/default-' . $item['type'] . '.jpg'; ?>');">
                                <div class="card-type-badge"><?php echo ucfirst($item['type']); ?></div>
                            </div>
                            <div class="card-content">
                                <h3 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p class="card-excerpt">
                                    <?php echo htmlspecialchars($item['excerpt'] ?? $item['description'] ?? substr(strip_tags($item['content'] ?? ''), 0, 150) . '...'); ?>
                                </p>
                                <div class="card-meta">
                                    <span class="card-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <?php echo formatDate($item['created_at']); ?>
                                    </span>
                                </div>
                                <a href="<?php echo $item['type']; ?>.php?slug=<?php echo $item['slug']; ?>" class="card-link">
                                    <span data-i18n="my_spaces.read_more">Read More</span> <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3 data-i18n="my_spaces.no_results">No results found</h3>
                    <p data-i18n="my_spaces.try_adjusting">Try adjusting your search terms or browse all content below.</p>
                    <a href="my-spaces.php" class="btn btn-primary">
                        <span data-i18n="my_spaces.browse_all">Browse All</span>
                    </a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Regular Content Display -->
            <?php if (empty($type_filter) || $type_filter === 'article'): ?>
                <?php if (!empty($articles)): ?>
                    <h2 class="section-title" data-i18n="my_spaces.latest_articles">Latest Articles</h2>
                    <div class="content-grid">
                        <?php foreach ($articles as $article): ?>
                            <div class="content-card">
                                <div class="card-image" style="background-image: url('<?php echo !empty($article['featured_image']) ? 'uploads/' . $article['featured_image'] : 'assets/images/default-article.jpg'; ?>');">
                                    <div class="card-type-badge">Article</div>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h3>
                                    <p class="card-excerpt"><?php echo htmlspecialchars($article['excerpt'] ?? substr(strip_tags($article['content'] ?? ''), 0, 150) . '...'); ?></p>
                                    <div class="card-meta">
                                        <span class="card-date">
                                            <i class="far fa-calendar-alt"></i>
                                            <?php echo formatDate($article['created_at']); ?>
                                        </span>
                                    </div>
                                    <a href="article.php?slug=<?php echo $article['slug']; ?>" class="card-link">
                                        <span data-i18n="my_spaces.read_article">Read Article</span> <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (empty($type_filter) || $type_filter === 'project'): ?>
                <?php if (!empty($projects)): ?>
                    <h2 class="section-title" data-i18n="my_spaces.featured_projects">Featured Projects</h2>
                    <div class="content-grid">
                        <?php foreach ($projects as $project): ?>
                            <div class="content-card">
                                <div class="card-image" style="background-image: url('<?php echo !empty($project['featured_image']) ? 'uploads/' . $project['featured_image'] : 'assets/images/default-project.jpg'; ?>');">
                                    <div class="card-type-badge">Project</div>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                                    <p class="card-excerpt"><?php echo htmlspecialchars($project['description']); ?></p>
                                    <div class="card-meta">
                                        <span class="card-date">
                                            <i class="far fa-calendar-alt"></i>
                                            <?php echo formatDate($project['created_at']); ?>
                                        </span>
                                    </div>
                                    <a href="project.php?slug=<?php echo $project['slug']; ?>" class="card-link">
                                        <span data-i18n="my_spaces.view_project">View Project</span> <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (empty($type_filter) || $type_filter === 'tool'): ?>
                <?php if (!empty($tools)): ?>
                    <h2 class="section-title" data-i18n="my_spaces.useful_tools">Useful Tools</h2>
                    <div class="content-grid">
                        <?php foreach ($tools as $tool): ?>
                            <div class="content-card">
                                <div class="card-image" style="background-image: url('<?php echo !empty($tool['featured_image']) ? 'uploads/' . $tool['featured_image'] : 'assets/images/default-tool.jpg'; ?>');">
                                    <div class="card-type-badge">Tool</div>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title"><?php echo htmlspecialchars($tool['title']); ?></h3>
                                    <p class="card-excerpt"><?php echo htmlspecialchars($tool['description']); ?></p>
                                    <div class="card-meta">
                                        <span class="card-date">
                                            <i class="far fa-calendar-alt"></i>
                                            <?php echo formatDate($tool['created_at']); ?>
                                        </span>
                                        <?php if (!empty($tool['category'])): ?>
                                            <span class="card-category"><?php echo htmlspecialchars($tool['category']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="tool.php?slug=<?php echo $tool['slug']; ?>" class="card-link">
                                        <span data-i18n="my_spaces.use_tool">Use Tool</span> <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (empty($articles) && empty($projects) && empty($tools)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3 data-i18n="my_spaces.no_content">No content found</h3>
                    <p data-i18n="my_spaces.try_adjusting_criteria">There's no content matching your current filters. Try adjusting your search criteria.</p>
                    <a href="my-spaces.php" class="btn btn-primary">
                        <span data-i18n="my_spaces.view_all_content">View All Content</span>
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
    // Add smooth scrolling and enhanced interactions
    document.addEventListener('DOMContentLoaded', function() {
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

        // Add loading animation for search
        const searchForm = document.querySelector('.search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', function() {
                const searchInput = this.querySelector('input[name="search"]');
                if (searchInput.value.trim()) {
                    // Show loading state
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    submitBtn.disabled = true;
                    
                    // Re-enable after a short delay (in case of errors)
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 3000);
                }
            });
        }

        // Add hover effects for cards
        document.querySelectorAll('.content-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Add intersection observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all content cards
        document.querySelectorAll('.content-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    });
</script>
