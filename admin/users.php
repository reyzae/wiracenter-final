<?php
$page_title = 'Users';
include 'includes/header.php';

requireLogin();
if (!hasPermission('admin')) {
    redirect(ADMIN_URL . '/dashboard.php');
}

$db = new Database();
$conn = $db->connect();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $role = sanitize($_POST['role']);
    $password = $_POST['password'];
    
    if ($action == 'new' || $action == 'edit') {
        if (empty($username) || empty($email) || empty($role)) {
            $error_message = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Invalid email format.';
        } else {
            if ($action == 'new') {
                if (empty($password)) {
                    $error_message = 'Password is required for new users.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    if ($stmt->execute([$username, $email, $hashed_password, $role])) {
                        $success_message = 'User created successfully!';
                        $action = 'list';
                    } else {
                        $error_message = 'Failed to create user. Username or email might already exist.';
                    }
                }
            } elseif ($action == 'edit' && $id) {
                $sql = "UPDATE users SET username=?, email=?, role=?";
                $params = [$username, $email, $role];
                
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql .= ", password=?";
                    $params[] = $hashed_password;
                }
                $sql .= " WHERE id=?";
                $params[] = $id;
                
                $stmt = $conn->prepare($sql);
                if ($stmt->execute($params)) {
                    $success_message = 'User updated successfully!';
                    $action = 'list';
                } else {
                    $error_message = 'Failed to update user. Username or email might already exist.';
                }
            }
        }
    }
}

// Handle delete action
if ($action == 'delete' && $id) {
    // Prevent deleting the currently logged-in user
    if ($id == $_SESSION['user_id']) {
        $error_message = 'You cannot delete your own account.';
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success_message = 'User deleted successfully!';
            logActivity($_SESSION['user_id'], 'Deleted user', 'user', $id);
        } else {
            $error_message = 'Failed to delete user.';
        }
    }
    $action = 'list';
}

// Get user for editing
$user = null;
if ($action == 'edit' && $id) {
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all users for listing
if ($action == 'list') {
    $stmt = $conn->prepare("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<h1 class="h2 mb-4">Users</h1>

<?php if ($action == 'list'): ?>
    <!-- Users List -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>All Users</h4>
        <a href="?action=new" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>New User
        </a>
    </div>
    
    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'editor' ? 'info' : 'secondary'); ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($user['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                            <a href="?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-outline-danger delete-btn" data-item="user">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($action == 'new' || $action == 'edit'): ?>
    <!-- User Form -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><?php echo $action == 'new' ? 'New User' : 'Edit User'; ?></h4>
        <a href="?action=list" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>
    
    <form method="POST">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">User Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Role *</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="viewer" <?php echo ($user['role'] ?? '') == 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                                <option value="editor" <?php echo ($user['role'] ?? '') == 'editor' ? 'selected' : ''; ?>>Editor</option>
                                <option value="admin" <?php echo ($user['role'] ?? '') == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <?php echo ($action == 'edit' ? '(leave blank to keep current)' : '*'); ?></label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i><?php echo $action == 'new' ? 'Create User' : 'Update User'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>