<?php
require_once 'config/config.php';

// Security: Prevent direct access if not in maintenance mode
if (getSetting('maintenance_mode', '0') != '1') {
    header('Location: index.php');
    exit();
}

// Allow admin/editor access even in maintenance mode
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$maintenance_message = getSetting('maintenance_message', 'Our website is currently undergoing scheduled maintenance. We will be back shortly!');
$maintenance_countdown = getSetting('maintenance_countdown', '');
$site_name = getSetting('site_name', 'WiraCenter');

// Set maintenance headers
header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 3600'); // Retry after 1 hour
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$countdown_timestamp = !empty($maintenance_countdown) ? strtotime($maintenance_countdown) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Maintenance Mode - <?php echo htmlspecialchars($site_name); ?></title>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/maintenance.css">
</head>
<body>
    <div class="maintenance-container">
        <i class="fas fa-tools maintenance-icon"></i>
        <h1 class="maintenance-title">Under Maintenance</h1>
        <p class="maintenance-message"><?php echo htmlspecialchars($maintenance_message); ?></p>

        <?php if ($countdown_timestamp > 0): ?>
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            <div class="countdown-timer" id="countdown"></div>
            <div class="countdown-label">Estimated time remaining</div>
        <?php endif; ?>

        <div class="footer-text">
            <p>Thank you for your patience.</p>
            <p><small>If you need immediate assistance, please <a href="contact.php" class="text-decoration-underline">contact our support team</a>.</small></p>
        </div>
    </div>

    <?php if ($countdown_timestamp > 0): ?>
    <script>
        (function() {
            'use strict';
            
            const countdownElement = document.getElementById('countdown');
            const progressFill = document.getElementById('progress-fill');
            const countdownDate = new Date(<?php echo json_encode($maintenance_countdown); ?>).getTime();
            const startTime = Date.now();
            const totalDuration = countdownDate - startTime;
            
            function updateCountdown() {
                const now = new Date().getTime();
                const distance = countdownDate - now;
                
                // Update progress bar
                if (progressFill) {
                    const elapsed = now - startTime;
                    const progress = Math.min((elapsed / totalDuration) * 100, 100);
                    progressFill.style.width = progress + '%';
                }
                
                if (distance > 0) {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    countdownElement.innerHTML = 
                        (days > 0 ? days + "d " : "") +
                        (hours > 0 ? hours + "h " : "") +
                        minutes + "m " + seconds + "s";
                } else {
                    clearInterval(countdownInterval);
                    countdownElement.innerHTML = "Maintenance Complete!";
                    if (progressFill) progressFill.style.width = '100%';
                    
                    // Redirect after 3 seconds
                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 3000);
                }
            }
            
            // Update immediately and then every second
            updateCountdown();
            const countdownInterval = setInterval(updateCountdown, 1000);
        })();
    </script>
    <?php endif; ?>
</body>
</html>