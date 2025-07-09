<!-- Sidebar Navigation -->
<div class="sidebar">
    <div class="sidebar-header">
        <a href="index.php" class="sidebar-brand"><?php echo getSetting('site_name', 'Wiracenter'); ?></a>
    </div>
    <ul class="sidebar-nav">
        <?php
        $nav_items = [];
        try {
            $nav_db = new Database();
            $nav_conn = $nav_db->connect();
            $nav_stmt = $nav_conn->prepare("SELECT * FROM navigation_items WHERE status = 'active' ORDER BY display_order ASC");
            $nav_stmt->execute();
            $nav_items = $nav_stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // If navigation_items table doesn't exist, use default items
            if ($e->getCode() == '42S02') { // SQLSTATE for base table or view not found
                $nav_items = [
                    ['name' => 'Home', 'url' => 'index.php', 'icon' => 'home'],
                    ['name' => 'About', 'url' => 'about.php', 'icon' => 'user'],
                    ['name' => 'My Spaces', 'url' => 'my-spaces.php', 'icon' => 'rocket'],
                    ['name' => 'My Spaces', 'url' => 'my-spaces.php', 'icon' => 'rocket'],
                    ['name' => 'Contact', 'url' => 'contact.php', 'icon' => 'envelope'],
                ];
            } else {
                // Re-throw other PDO exceptions
                throw $e;
            }
        }

        foreach ($nav_items as $item) {
            $is_active = false;
            $current_page = basename($_SERVER['PHP_SELF']);
            $slug = $_GET['slug'] ?? '';

            if ($item['url'] == $current_page && $slug == '') {
                $is_active = true;
            } elseif (strpos($item['url'], 'page.php?slug=') !== false) {
                $slug_from_url = explode('slug=', $item['url'])[1];
                if ($current_page == 'page.php' && $slug == $slug_from_url) {
                    $is_active = true;
                }
            } elseif ($item['url'] == basename($_SERVER['PHP_SELF'])) {
                $is_active = true;
            }
        ?>
            <li class="sidebar-nav-item">
                <a href="<?php echo $item['url']; ?>" class="sidebar-nav-link <?php echo $is_active ? 'active' : ''; ?>">
                    <i class="fas fa-<?php echo $item['icon'] ?? 'circle'; ?>"></i>
                    <span><?php echo $item['name']; ?></span>
                </a>
            </li>
        <?php } ?>
    </ul>
    <div class="sidebar-footer">
        <p>&copy; <?php echo date('Y'); ?> Wiracenter. All rights reserved.</p>
    </div>
</div>