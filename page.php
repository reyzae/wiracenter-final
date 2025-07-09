<?php
require_once 'config/config.php';

$db = new Database();
$conn = $db->connect();

$slug = $_GET['slug'] ?? '';
$page = null;

if (!empty($slug)) {
    try {
        $stmt = $conn->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published'");
        $stmt->execute([$slug]);
        $page = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $page = null;
        $error_message = 'Table pages not found in database.';
    }
}

if (!$page) {
    // Redirect to 404 or show a not found message
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Page Not Found</h1>";
    if (!empty($error_message)) { echo '<br><span class=\'text-danger\'>' . htmlspecialchars($error_message) . '</span>'; }
    exit();
}

$page_title = $page['title'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo getSetting('site_name', 'Wiracenter'); ?></title>
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
            <a href="index.php" class="sidebar-brand"><?php echo getSetting('site_name', 'Wiracenter'); ?></a>
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
        <section class="py-5">
            <div class="container">
                <h1 class="mb-4"><?php echo $page['title']; ?></h1>
                <div>
                    <?php echo $page['content']; ?>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>