<?php // require_once 'config/config.php'; // Removed as it's included in main files ?>
<!DOCTYPE html>
<html lang="en">
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
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <!-- Mobile-only Navbar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>

    <!-- Main Content -->
    <div class="main-content">
