<?php
require_once '../config/config.php';
requireLogin();

// Pastikan variabel $page_title selalu terdefinisi agar tidak error jika di-include
if (!isset($page_title)) {
    $page_title = '';
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title . ' - '; ?>Admin Dashboard - <?php echo getSetting('site_name', 'Wiracenter'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin-style.css"> <!-- Updated CSS link -->
    <script src="https://cdn.tiny.cloud/1/7t4ysw5ibpvf6otxc72fed05syoih8onsdc91gce3e4sqi3a/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        // Definisikan variabel global untuk autosave TinyMCE
        var pageContentType = '<?php echo isset($pageContentType) ? $pageContentType : (isset($current_page) ? $current_page : "content"); ?>';
        var pageContentId = <?php echo isset($pageContentId) ? $pageContentId : (isset($id) ? json_encode($id) : 'null'); ?>;
    </script>
    
    
</head>
<body class="theme-<?php echo getSetting('theme_mode', 'light'); ?>">
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark border-right" id="sidebar-wrapper">
            <div class="sidebar-heading text-white text-center py-4">
                <h3 class="mb-1" style="font-weight: bold;"><?php echo getSetting('site_name', 'Wiracenter'); ?></h3>
                <h4>Dashboard</h4>
            </div>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo $current_page == 'dashboard' ? 'active' : ''; ?> ">
                    <i class="fas fa-tachometer-alt me-2"></i><span class="menu-text">Dashboard</span>
                </a>

                <?php
                // Define sub-menu items and their parent categories
                $content_management_pages = ['articles', 'pages', 'navigation', 'faqs', 'content_blocks', 'projects', 'tools', 'files', 'trash'];
                $admin_pages = ['settings', 'users', 'activity_logs', 'backup', 'export_data'];

                $is_content_management_active = in_array($current_page, $content_management_pages) ? 'active' : '';
                $is_admin_active = in_array($current_page, $admin_pages) ? 'active' : '';
                ?>

                <?php if (hasPermission('editor')): ?>
                <div class="list-group-item list-group-item-action bg-dark text-white d-flex justify-content-between align-items-center <?php echo $is_content_management_active; ?>" data-bs-toggle="collapse" data-bs-target="#contentManagementSubmenu" role="button" aria-expanded="<?php echo $is_content_management_active ? 'true' : 'false'; ?>" aria-controls="contentManagementSubmenu">
                    <div class="d-flex align-items-center flex-grow-1"><i class="fas fa-folder-open me-2"></i><span class="menu-text">Content Management</span></div>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </div>
                <div class="collapse <?php echo $is_content_management_active ? 'show' : ''; ?>" id="contentManagementSubmenu">
                    <a href="articles.php" class="list-group-item list-group-item-action bg-dark text-white ps-5 <?php echo $current_page == 'articles' ? 'active' : ''; ?>">
                        <i class="fas fa-newspaper me-2"></i><span class="menu-text">Articles</span>
                    </a>
                    <a href="projects.php" class="list-group-item list-group-item-action bg-dark text-white ps-5 <?php echo $current_page == 'projects' ? 'active' : ''; ?>">
                        <i class="fas fa-code me-2"></i><span class="menu-text">Projects</span>
                    </a>
                    <a href="tools.php" class="list-group-item list-group-item-action bg-dark text-white ps-5 <?php echo $current_page == 'tools' ? 'active' : ''; ?>">
                        <i class="fas fa-tools me-2"></i><span class="menu-text">Tools</span>
                    </a>
                    <a href="pages.php" class="list-group-item list-group-item-action bg-dark text-white ps-5 <?php echo $current_page == 'pages' ? 'active' : ''; ?>">
                        <i class="fas fa-file-alt me-2"></i><span class="menu-text">Pages</span>
                    </a>
                    <a href="navigation.php" class="list-group-item list-group-item-action bg-dark text-white ps-5 <?php echo $current_page == 'navigation' ? 'active' : ''; ?>">
                        <i class="fas fa-bars me-2"></i><span class="menu-text">Navigation</span>
                    </a>
                    <a href="faqs.php" class="list-group-item list-group-item-action bg-dark text-white ps-5 <?php echo $current_page == 'faqs' ? 'active' : ''; ?>">
                        <i class="fas fa-question-circle me-2"></i><span class="menu-text">FAQs</span>
                    </a>
                    <a href="content_blocks.php" class="list-group-item list-group-item-action bg-dark text-white ps-5 <?php echo $current_page == 'content_blocks' ? 'active' : ''; ?>">
                        <i class="fas fa-cube me-2"></i><span class="menu-text">Content Blocks</span>
                    </a>
                    <a href="files.php" class="list-group-item list-group-item-action bg-dark text-white ps-5 <?php echo $current_page == 'files' ? 'active' : ''; ?>">
                        <i class="fas fa-folder me-2"></i><span class="menu-text">Files</span>
                    </a>
                    <a href="trash.php" class="list-group-item list-group-item-action bg-dark text-white ps-5 <?php echo $current_page == 'trash' ? 'active' : ''; ?>">
                        <i class="fas fa-trash me-2"></i><span class="menu-text">Trash</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (hasPermission('admin')): ?>
                <div class="list-group-item list-group-item-action bg-dark text-white d-flex justify-content-between align-items-center <?php echo $is_admin_active; ?>" data-bs-toggle="collapse" data-bs-target="#administrationSubmenu" role="button" aria-expanded="<?php echo $is_admin_active ? 'true' : 'false'; ?>" aria-controls="administrationSubmenu">
                    <div class="d-flex align-items-center flex-grow-1"><i class="fas fa-cogs me-2"></i><span class="menu-text">Administration</span></div>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </div>
                <div class="collapse <?php echo $is_admin_active ? 'show' : ''; ?>" id="administrationSubmenu">
                    <a href="settings.php" class="list-group-item list-group-item-action bg-dark text-white ps-5 <?php echo $current_page == 'settings' ? 'active' : ''; ?>">
                        <i class="fas fa-cog me-2"></i><span class="menu-text">Site Settings</span>
                    </a>
                    <a href="users.php" class="list-group-item list-group-item-action bg-dark text-white ps-5 <?php echo $current_page == 'users' ? 'active' : ''; ?>">
                        <i class="fas fa-users me-2"></i><span class="menu-text">Users</span>
                    </a>
                    <a href="activity_logs.php" class="list-group-item list-group-item-action bg-dark text-white ps-5 <?php echo $current_page == 'activity_logs' ? 'active' : ''; ?>">
                        <i class="fas fa-history me-2"></i><span class="menu-text">Activity Logs</span>
                    </a>
                    <a href="backup.php" class="list-group-item list-group-item-action bg-dark text-white ps-5 <?php echo $current_page == 'backup' ? 'active' : ''; ?>">
                        <i class="fas fa-save me-2"></i><span class="menu-text">Backup & Restore</span>
                    </a>
                    <a href="export_data.php" class="list-group-item list-group-item-action bg-dark text-white ps-5 <?php echo $current_page == 'export_data' ? 'active' : ''; ?>">
                        <i class="fas fa-file-export me-2"></i><span class="menu-text">Export Data</span>
                    </a>
                </div>
                <?php endif; ?>

                <a href="help.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo $current_page == 'help' ? 'active' : ''; ?>">
                    <i class="fas fa-question-circle me-2"></i><span class="menu-text">Help & Docs</span>
                </a>
                
                <a href="../index.php" class="list-group-item list-group-item-action bg-dark text-white" target="_blank">
                    <i class="fas fa-external-link-alt me-2"></i><span class="menu-text">View Website</span>
                </a>
            </div>
            <div class="sidebar-footer text-white-50 text-center py-3">
                &copy; <?php echo date('Y'); ?> <?php echo getSetting('site_name', 'Wiracenter'); ?>. All rights reserved.
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <button class="btn btn-primary d-lg-none" id="menu-toggle"><i class="fas fa-bars"></i></button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                        <?php
                        $db = new Database();
                        $conn = $db->connect();
                        $unread_notifications = [];
                        if (isset($_SESSION['user_id'])) {
                            $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = FALSE ORDER BY created_at DESC LIMIT 5");
                            $stmt->execute([$_SESSION['user_id']]);
                            $unread_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        }
                        ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownNotifications" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <?php if (count($unread_notifications) > 0): ?>
                                    <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">
                                        <?php echo count($unread_notifications); ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownNotifications">
                                <?php if (count($unread_notifications) > 0): ?>
                                    <?php foreach ($unread_notifications as $notification): ?>
                                        <div class="dropdown-item d-flex align-items-center justify-content-between" data-notification-id="<?php echo $notification['id']; ?>">
                                            <div>
                                                <?php echo $notification['message']; ?><br>
                                                <small class="text-muted"><?php echo formatDateTime($notification['created_at']); ?></small>
                                            </div>
                                            <div class="ms-2">
                                                <button class="btn btn-sm btn-success mark-read-btn" title="Mark as Read"><i class="fas fa-check"></i></button>
                                                <button class="btn btn-sm btn-danger delete-notification-btn" title="Delete"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <hr class="dropdown-divider">
                                    <a class="dropdown-item text-center" href="notifications.php">Open Notifications</a>
                                <?php else: ?>
                                    <a class="dropdown-item" href="#">No new notifications</a>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-user me-1"></i><?php echo $_SESSION['username'] ?? ''; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="profile.php">Profile</a>
                                
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="container-fluid p-4">
                <!-- Alert container -->
                <div class="alert-container"></div>