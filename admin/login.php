<?php
require_once '../config/config.php';

$error_message = '';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect(ADMIN_URL . '/dashboard.php');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        $db = new Database();
        $conn = $db->connect();
        try {
            $stmt = $conn->prepare("SELECT id, username, password, role, status, temp_password, temp_password_expired_at FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $user = false;
            $error_message = 'Table users not found in database.';
        }
        if (!isset($error_message) || $error_message === '') {
            if (!$user) {
                $error_message = 'Your account has been deleted. Please contact the administrator if you believe this is a mistake.';
            } elseif (isset($user['status']) && $user['status'] === 'suspended') {
                $error_message = 'Your account has been suspended. Please contact the administrator for more information.';
            } elseif ($user && isset($user['temp_password']) && $user['temp_password'] && password_verify($password, $user['password'])) {
                // Cek expired
                if (isset($user['temp_password_expired_at']) && strtotime($user['temp_password_expired_at']) < time()) {
                    $error_message = 'Your temporary password has expired. Please contact the administrator to get a new password.';
                } else {
                    // Force change password
                    $_SESSION['force_change_password'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    session_regenerate_id(true);
                    header('Location: force_change_password.php');
                    exit();
                }
            } elseif ($user && password_verify($password, $user['password'])) {
                error_log('admin/login.php: Login successful for user: ' . $user['username']);
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                session_regenerate_id(true);
                logActivity($user['id'], 'User logged in');
                // Redirect to dashboard
                redirect(ADMIN_URL . '/dashboard.php');
            } else {
                logActivity(null, 'Failed login attempt for username: ' . $username);
                $error_message = 'Invalid username or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo getSetting('site_name', 'Wiracenter'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h1 class="h3 mb-3 text-primary">Admin Login</h1>
                            <p class="text-muted">Access your dashboard</p>
                        </div>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username or Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <a href="../index.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Website
                                </a>
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        &copy; <?php echo date('Y'); ?> <?php echo getSetting('site_name', 'Wiracenter'); ?>. All rights reserved.
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>