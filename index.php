<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/config.php';

// Check for maintenance mode
$maintenance_mode = getSetting('maintenance_mode', '0');
if ($maintenance_mode == '1' && !isLoggedIn()) {
    header('Location: maintenance.php');
    exit();
}

// Get site settings
$site_name = getSetting('site_name', 'Wiracenter');
$hero_title = getSetting('hero_title', 'Welcome to Wiracenter');
$hero_subtitle = getSetting('hero_subtitle', 'Your Digital Solutions Partner');

// Pastikan $site_name selalu terdefinisi
if (!isset($site_name) || empty($site_name)) {
    $site_name = 'Wiracenter';
}

// Get recent projects
$db = new Database();
$conn = $db->connect();
$stmt = $conn->prepare("SELECT * FROM projects WHERE status = 'published' ORDER BY publish_date DESC LIMIT 6");
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent articles
$stmt = $conn->prepare("SELECT * FROM articles WHERE status = 'published' ORDER BY publish_date DESC LIMIT 3");
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent tools
$stmt = $conn->prepare("SELECT * FROM tools WHERE status = 'published' ORDER BY publish_date DESC LIMIT 3");
$stmt->execute();
$tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_name; ?> - Portfolio</title>
    <meta name="description" content="<?php echo getSetting('site_description', 'Personal Portfolio Website'); ?>">
    <meta name="keywords" content="<?php echo getSetting('site_keywords', 'portfolio, web development'); ?>">
    <?php if (getSetting('site_favicon')): ?>
    <link rel="icon" href="<?php echo getSetting('site_favicon'); ?>" type="image/x-icon">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="index.php" class="sidebar-brand"><?php echo $site_name; ?></a>
        </div>
        <ul class="sidebar-nav">
            <?php
            $nav_db = new Database();
            $nav_conn = $nav_db->connect();
            $nav_items = [];
            if ($nav_conn) {
                try {
                    $nav_stmt = $nav_conn->prepare("SELECT * FROM navigation_items WHERE status = 'active' ORDER BY display_order ASC");
                    if ($nav_stmt->execute()) {
                        $nav_items = $nav_stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                } catch (PDOException $e) {
                    // Tabel navigation_items tidak ada atau error query
                    $nav_items = [];
                }
            }

            foreach ($nav_items as $item) {
                $is_active = false;
                // Determine active state based on current page/slug
                if ($item['url'] == 'index.php' && basename($_SERVER['PHP_SELF']) == 'index.php') {
                    $is_active = true;
                } elseif (strpos($item['url'], 'page.php?slug=') !== false) {
                    $slug_from_url = explode('slug=', $item['url'])[1];
                    if (basename($_SERVER['PHP_SELF']) == 'page.php' && ($_GET['slug'] ?? '') == $slug_from_url) {
                        $is_active = true;
                    }
                } elseif ($item['url'] == basename($_SERVER['PHP_SELF'])) {
                    $is_active = true;
                }
            ?>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $item['url']; ?>" class="sidebar-nav-link <?php echo $is_active ? 'active' : ''; ?>">
                        <i class="fas fa-<?php 
                            if ($item['name'] == 'Home') echo 'home';
                            else if ($item['name'] == 'About') echo 'user';
                            else if ($item['name'] == 'My Spaces') echo 'rocket';
                            else if ($item['name'] == 'Contact') echo 'envelope';
                            else echo 'circle'; // Default icon
                        ?>"></i>
                        <span><?php echo $item['name']; ?></span>
                    </a>
                </li>
            <?php }
            ?>
        </ul>
        <div class="sidebar-footer">
            © <?php echo date('Y'); ?> <?php echo getSetting('site_name', 'Wiracenter'); ?>. All rights reserved.
        </div>
    </div>

    <!-- Mobile-only Navbar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Hero Section -->
        <section class="hero-section text-center">
            <div class="container">
                <h1 class="display-3 text-white mb-3"><?php echo $hero_title; ?></h1>
                <p class="lead text-white-50 mb-4"><?php echo $hero_subtitle; ?></p>
                <a href="my-spaces.php" class="btn btn-primary btn-lg">Explore My Work</a>
            </div>
        </section>

    <!-- Recent Articles Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center mb-5">Recent Articles</h2>
                </div>
            </div>
            <div class="row">
                <?php if (!empty($articles)): ?>
                    <?php foreach ($articles as $article): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $article['title']; ?></h5>
                                    <p class="card-text"><?php echo $article['excerpt'] ?? substr(strip_tags($article['content']), 0, 100) . '...'; ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="article.php?slug=<?php echo $article['slug']; ?>" class="btn btn-outline-primary">Read More</a>
                                        <small class="text-muted"><?php echo $article['publish_date'] ? formatDate($article['publish_date']) : '-'; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center">
                            <p class="text-muted">No articles available at the moment.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Projects Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center mb-5">Featured Projects</h2>
                </div>
            </div>
            <div class="row">
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $project): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <?php if ($project['featured_image']): ?>
                                    <img src="<?php echo UPLOAD_PATH . $project['featured_image']; ?>" class="card-img-top" alt="<?php echo $project['title']; ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $project['title']; ?></h5>
                                    <p class="card-text"><?php echo substr($project['description'], 0, 100) . '...'; ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="project.php?slug=<?php echo $project['slug']; ?>" class="btn btn-primary">View Project</a>
                                        <small class="text-muted"><?php echo $project['publish_date'] ? formatDate($project['publish_date']) : '-'; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center">
                            <p class="text-muted">No projects available at the moment.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Recent My Tools Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center mb-5">Recent My Tools</h2>
                </div>
            </div>
            <div class="row">
                <?php if (!empty($tools)): ?>
                    <?php foreach ($tools as $tool): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <?php if ($tool['featured_image']): ?>
                                    <img src="<?php echo UPLOAD_PATH . $tool['featured_image']; ?>" class="card-img-top" alt="<?php echo $tool['title']; ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $tool['title']; ?></h5>
                                    <p class="card-text"><?php echo $tool['excerpt'] ?? substr(strip_tags($tool['content']), 0, 100) . '...'; ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="tool.php?slug=<?php echo $tool['slug']; ?>" class="btn btn-primary">View Tool</a>
                                        <small class="text-muted"><?php echo $tool['publish_date'] ? formatDate($tool['publish_date']) : '-'; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center">
                            <p class="text-muted">No tools available at the moment.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>