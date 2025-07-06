<?php
require_once 'config/config.php';

$maintenance_message = getSetting('maintenance_message', 'Our website is currently undergoing scheduled maintenance. We will be back shortly!');
$maintenance_countdown = getSetting('maintenance_countdown', '');

// Redirect to home if maintenance mode is off or user is logged in (admin/editor)
if (getSetting('maintenance_mode', '0') == '0' || isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$countdown_timestamp = !empty($maintenance_countdown) ? strtotime($maintenance_countdown) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - <?php echo getSetting('site_name', 'Wiracenter'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa;
            color: #343a40;
            text-align: center;
            font-family: 'Arial', sans-serif;
        }
        .maintenance-container {
            max-width: 600px;
            padding: 30px;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .countdown-timer {
            font-size: 2.5rem;
            font-weight: bold;
            margin-top: 20px;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <i class="fas fa-tools fa-5x mb-4 text-primary"></i>
        <h1 class="display-4">Under Maintenance</h1>
        <p class="lead"><?php echo $maintenance_message; ?></p>

        <?php if ($countdown_timestamp > 0): ?>
            <div class="countdown-timer" id="countdown"></div>
        <?php endif; ?>

        <p class="mt-4">
            <small>Thank you for your patience.</small>
        </p>
    </div>

    <?php if ($countdown_timestamp > 0): ?>
    <script>
        const countdownElement = document.getElementById('countdown');
        const countdownDate = new Date(<?php echo json_encode($maintenance_countdown); ?>).getTime();

        const x = setInterval(function() {
            const now = new Date().getTime();
            const distance = countdownDate - now;

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownElement.innerHTML = days + "d " + hours + "h "
            + minutes + "m " + seconds + "s ";

            if (distance < 0) {
                clearInterval(x);
                countdownElement.innerHTML = "EXPIRED";
                // Optionally, redirect to home page after countdown expires
                window.location.href = 'index.php';
            }
        }, 1000);
    </script>
    <?php endif; ?>
</body>
</html>