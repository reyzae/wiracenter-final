<?php
require_once '../config/config.php';

if (!isset($_SESSION['force_change_password']) || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (empty($new_password) || empty($confirm_password)) {
        $error_message = 'Please enter and confirm your new password.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error_message = 'Password must be at least 6 characters.';
    } else {
        $db = new Database();
        $conn = $db->connect();
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=?, temp_password=NULL, temp_password_expired_at=NULL WHERE id=?");
        $stmt->execute([$hashed, $_SESSION['user_id']]);
        unset($_SESSION['force_change_password']);
        $success_message = 'Password changed successfully! Redirecting...';
        echo '<meta http-equiv="refresh" content="2;url=dashboard.php">';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Wiracenter Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h1 class="h4 mb-3 text-primary">Change Your Password</h1>
                            <p class="text-muted">You must change your password before accessing the dashboard.</p>
                        </div>
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($success_message): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Change Password</button>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <small class="text-muted">
                        &copy; <?php echo date('Y'); ?> Wiracenter. All rights reserved.
                    </small>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 