<?php
require_once 'config/config.php';

$error_code = $_GET['code'] ?? '404';
$error_message = $_GET['message'] ?? '';

// Set appropriate HTTP status code
switch ($error_code) {
    case '404':
        http_response_code(404);
        $title = 'Page Not Found';
        $description = 'The page you are looking for could not be found.';
        $icon = 'fa-search';
        break;
    case '500':
        http_response_code(500);
        $title = 'Internal Server Error';
        $description = 'Something went wrong on our end. Please try again later.';
        $icon = 'fa-exclamation-triangle';
        break;
    case '403':
        http_response_code(403);
        $title = 'Access Forbidden';
        $description = 'You do not have permission to access this resource.';
        $icon = 'fa-ban';
        break;
    default:
        http_response_code(404);
        $title = 'Error';
        $description = $error_message ?: 'An error occurred.';
        $icon = 'fa-exclamation-circle';
}

$site_name = getSetting('site_name', 'WiraCenter');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo htmlspecialchars($title); ?> - <?php echo htmlspecialchars($site_name); ?></title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        :root {
            --primary-color: #00BCD4;
            --secondary-color: #0097A7;
            --text-color: #343a40;
            --bg-color: #f8f9fa;
        }
        
        body {
            background: linear-gradient(135deg, var(--bg-color) 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            max-width: 600px;
            text-align: center;
            padding: 40px 20px;
        }
        
        .error-icon {
            font-size: 6rem;
            color: var(--primary-color);
            margin-bottom: 2rem;
            animation: bounce 2s infinite;
        }
        
        .error-code {
            font-size: 4rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .error-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-color);
        }
        
        .error-description {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-custom {
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid var(--primary-color);
        }
        
        .btn-primary-custom {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary-custom:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-outline-custom {
            background: transparent;
            color: var(--primary-color);
        }
        
        .btn-outline-custom:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        @media (max-width: 768px) {
            .error-container {
                padding: 20px;
            }
            
            .error-code {
                font-size: 3rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-actions {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <i class="fas <?php echo $icon; ?> error-icon"></i>
        <div class="error-code"><?php echo htmlspecialchars($error_code); ?></div>
        <h1 class="error-title"><?php echo htmlspecialchars($title); ?></h1>
        <p class="error-description"><?php echo htmlspecialchars($description); ?></p>
        
        <div class="error-actions">
            <a href="index.php" class="btn btn-custom btn-primary-custom">
                <i class="fas fa-home me-2"></i>Go Home
            </a>
            <a href="javascript:history.back()" class="btn btn-custom btn-outline-custom">
                <i class="fas fa-arrow-left me-2"></i>Go Back
            </a>
            <a href="contact.php" class="btn btn-custom btn-outline-custom">
                <i class="fas fa-envelope me-2"></i>Contact Support
            </a>
        </div>
        
        <?php if ($error_code == '404'): ?>
        <div class="mt-4">
            <p class="text-muted">Try searching for what you're looking for:</p>
            <form action="index.php" method="GET" class="d-flex justify-content-center">
                <div class="input-group" style="max-width: 400px;">
                    <input type="text" name="search" class="form-control" placeholder="Search articles, projects, tools...">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 