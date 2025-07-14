<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';

// Sanitize and validate input parameters
$category_filter = trim($_GET['category'] ?? '');
$category_filter = preg_replace('/[^a-zA-Z0-9_\-]/', '', $category_filter);
$type_filter = trim($_GET['type'] ?? '');
$type_filter = preg_replace('/[^a-zA-Z0-9_\-]/', '', $type_filter);
$search_query = trim($_GET['search'] ?? '');
$search_query = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $search_query);

// Initialize database connection
$db = new Database();
try {
    $conn = $db->connect();
} catch (PDOException $e) {
    error_log("Database connection failed in my-spaces.php: " . $e->getMessage());
    $conn = null;
}

// Initialize empty arrays
$articles = [];
$projects = [];
$tools = [];
$categories = [];
$search_results = [];

if ($conn) {
    try {
        // Get all categories from tools
        $stmt = $conn->prepare("SELECT DISTINCT category FROM tools WHERE status = 'published' AND category IS NOT NULL AND category != '' AND deleted_at IS NULL ORDER BY category");
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
                                   WHERE status = 'published' AND deleted_at IS NULL AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?) 
                                   ORDER BY created_at DESC LIMIT 20");
            $stmt->execute([$search_term, $search_term, $search_term]);
            $search_results = array_merge($search_results, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Search in projects
            $stmt = $conn->prepare("SELECT id, title, description, featured_image, created_at, slug, 'project' as type FROM projects 
                                   WHERE status = 'published' AND deleted_at IS NULL AND (title LIKE ? OR description LIKE ? OR content LIKE ?) 
                                   ORDER BY created_at DESC LIMIT 20");
            $stmt->execute([$search_term, $search_term, $search_term]);
            $search_results = array_merge($search_results, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Search in tools
            $stmt = $conn->prepare("SELECT id, title, description, featured_image, created_at, slug, 'tool' as type FROM tools 
                                   WHERE status = 'published' AND deleted_at IS NULL AND (title LIKE ? OR description LIKE ? OR content LIKE ?) 
                                   ORDER BY created_at DESC LIMIT 20");
            $stmt->execute([$search_term, $search_term, $search_term]);
            $search_results = array_merge($search_results, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get articles based on filters
            $article_sql = "SELECT id, title, excerpt, featured_image, created_at, slug FROM articles WHERE status = 'published' AND deleted_at IS NULL";
            $article_params = [];
            
            if (!empty($type_filter) && $type_filter !== 'article') {
                $article_sql .= " AND 1=0"; // Don't show articles if type filter is not article
            }
            
            $article_sql .= " ORDER BY created_at DESC LIMIT 12";
            
            $stmt = $conn->prepare($article_sql);
            $stmt->execute($article_params);
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get projects based on filters
            $project_sql = "SELECT id, title, description, featured_image, created_at, slug FROM projects WHERE status = 'published' AND deleted_at IS NULL";
            $project_params = [];
            
            if (!empty($type_filter) && $type_filter !== 'project') {
                $project_sql .= " AND 1=0"; // Don't show projects if type filter is not project
            }
            
            $project_sql .= " ORDER BY created_at DESC LIMIT 12";
            
            $stmt = $conn->prepare($project_sql);
            $stmt->execute($project_params);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get tools based on filters
            $tool_sql = "SELECT id, title, description, featured_image, created_at, slug, category FROM tools WHERE status = 'published' AND deleted_at IS NULL";
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
<link rel="stylesheet" href="assets/css/my-spaces.css">

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
                                <h3 class="card-title"><?php echo htmlspecialchars_decode($item['title']); ?></h3>
                                <p class="card-excerpt">
                                    <?php echo htmlspecialchars_decode($item['excerpt'] ?? $item['description'] ?? substr(strip_tags($item['content'] ?? ''), 0, 150) . '...'); ?>
                                </p>
                                <div class="card-meta">
                                    <span class="card-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <?php echo formatDate($item['created_at']); ?>
                                    </span>
                                </div>
                                <a href="<?php echo $item['type']; ?>.php?slug=<?php echo htmlspecialchars($item['slug']); ?>" class="card-link">
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
                                    <h3 class="card-title"><?php echo htmlspecialchars_decode($article['title']); ?></h3>
                                    <p class="card-excerpt"><?php echo htmlspecialchars_decode($article['excerpt'] ?? substr(strip_tags($article['content'] ?? ''), 0, 150) . '...'); ?></p>
                                    <div class="card-meta">
                                        <span class="card-date">
                                            <i class="far fa-calendar-alt"></i>
                                            <?php echo formatDate($article['created_at']); ?>
                                        </span>
                                    </div>
                                    <a href="article.php?slug=<?php echo htmlspecialchars($article['slug']); ?>" class="card-link">
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
                                    <h3 class="card-title"><?php echo htmlspecialchars_decode($project['title']); ?></h3>
                                    <p class="card-excerpt"><?php echo htmlspecialchars_decode($project['description']); ?></p>
                                    <div class="card-meta">
                                        <span class="card-date">
                                            <i class="far fa-calendar-alt"></i>
                                            <?php echo formatDate($project['created_at']); ?>
                                        </span>
                                    </div>
                                    <a href="project.php?slug=<?php echo htmlspecialchars($project['slug']); ?>" class="card-link">
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
                                    <h3 class="card-title"><?php echo htmlspecialchars_decode($tool['title']); ?></h3>
                                    <p class="card-excerpt"><?php echo htmlspecialchars_decode($tool['description']); ?></p>
                                    <div class="card-meta">
                                        <span class="card-date">
                                            <i class="far fa-calendar-alt"></i>
                                            <?php echo formatDate($tool['created_at']); ?>
                                        </span>
                                        <?php if (!empty($tool['category'])): ?>
                                            <span class="card-category"><?php echo htmlspecialchars($tool['category']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="tool.php?slug=<?php echo htmlspecialchars($tool['slug']); ?>" class="card-link">
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

<script src="assets/js/my-spaces.js"></script>
