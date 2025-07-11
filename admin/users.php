<?php
ob_start();
// Aktifkan error reporting untuk debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log debug ke file
file_put_contents(__DIR__.'/debug_users_action.log', date('Y-m-d H:i:s')." | Masuk blok aksi | ".json_encode($_GET).PHP_EOL, FILE_APPEND);

require_once __DIR__ . '/../config/config.php';
$page_title = 'Users';

// Initialize variables to prevent undefined variable errors
$tab = $_GET['tab'] ?? 'active';
$id = $_GET['id'] ?? null;
$users = [];
$roles = [];
$success_message = '';
$error_message = '';
$action = $_GET['action'] ?? 'list';

$db = new Database();
$conn = $db->connect();

requireLogin();
if (!hasPermission('admin')) {
    redirect(ADMIN_URL . '/dashboard.php');
}

// --- HANDLE ACTIONS DI PALING ATAS, SEBELUM APAPUN ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $logMsg = '';
    
    if ($_GET['action'] === 'delete') {
        $stmt = $conn->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        $logMsg = 'Delete user id '.$id;
    } elseif ($_GET['action'] === 'restore') {
        $stmt = $conn->prepare("UPDATE users SET deleted_at = NULL WHERE id = ?");
        $stmt->execute([$id]);
        $logMsg = 'Restore user id '.$id;
    } elseif ($_GET['action'] === 'reset_password') {
        $rand = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $temp_password = '#user-' . $rand;
        $hashed = password_hash($temp_password, PASSWORD_DEFAULT);
        $expired_at = date('Y-m-d H:i:s', time() + 3600);
        $stmt = $conn->prepare("UPDATE users SET password=?, temp_password=?, temp_password_expired_at=? WHERE id=?");
        $stmt->execute([$hashed, $temp_password, $expired_at, $id]);
        $logMsg = 'Reset password user id '.$id;
        file_put_contents(__DIR__.'/debug_users_action.log', date('Y-m-d H:i:s')." | ".$logMsg.PHP_EOL, FILE_APPEND);
        if (!headers_sent()) {
            header('Location: users.php?tab=active&resetpw='.$id);
            ob_end_clean();
            exit();
        }
    }
    file_put_contents(__DIR__.'/debug_users_action.log', date('Y-m-d H:i:s')." | ".$logMsg.PHP_EOL, FILE_APPEND);
    if (!headers_sent()) {
        header('Location: users.php?tab=active');
        ob_end_clean();
        exit();
    }
}

// Handle create user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'viewer';
    $status = $_POST['status'] ?? 'active';
    $rand = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $temp_password = '#user-' . $rand;
    $hashed = password_hash($temp_password, PASSWORD_DEFAULT);
    $expired_at = date('Y-m-d H:i:s', time() + 3600);
    
    if (!$username || !$email) {
        $error_message = 'Username and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email format.';
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status, temp_password, temp_password_expired_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $ok = $stmt->execute([$username, $email, $hashed, $role, $status, $temp_password, $expired_at]);
        if ($ok) {
            $success_message = 'User created successfully! Temporary password: <b>' . htmlspecialchars($temp_password) . '</b> (valid 1 hour)';
        } else {
            $error_message = 'Failed to create user. Username or email might already exist.';
        }
    }
}

// Filter
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';

if ($tab === 'trashed') {
    $sql = "SELECT * FROM users WHERE deleted_at IS NOT NULL";
    $params = [];
    if ($role_filter && $role_filter !== 'all') {
        $sql .= " AND role = ?";
        $params[] = $role_filter;
    }
    $sql .= " ORDER BY deleted_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql = "SELECT * FROM users WHERE deleted_at IS NULL";
    $params = [];
    if ($role_filter && $role_filter !== 'all') {
        $sql .= " AND role = ?";
        $params[] = $role_filter;
    }
    if ($status_filter && $status_filter !== 'all') {
        $sql .= " AND status = ?";
        $params[] = $status_filter;
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all roles for dropdown
$roleStmt = $conn->query("SELECT DISTINCT role FROM users");
foreach ($roleStmt->fetchAll(PDO::FETCH_COLUMN) as $r) {
    $roles[] = $r;
}

// Handle success/error messages from URL parameters
if (isset($_GET['msg'])) {
    $success_message = $_GET['msg'];
}

include 'includes/header.php';
// include 'includes/navigation.php';
?>
<div class="container-fluid">
    <h1 class="h2 mb-4">Users</h1>
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link<?php if ($tab == 'active') echo ' active'; ?>" href="?tab=active">Active Users</a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?php if ($tab == 'trashed') echo ' active'; ?>" href="?tab=trashed">Trashed Users</a>
        </li>
    </ul>
    <form method="GET" class="row g-3 align-items-center mb-3">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="all">All Roles</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo $role; ?>" <?php if ($role_filter === $role) echo 'selected'; ?>><?php echo ucfirst($role); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($tab !== 'trashed'): ?>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="all">All Statuses</option>
                <option value="active" <?php if ($status_filter === 'active') echo 'selected'; ?>>Active</option>
                <option value="suspended" <?php if ($status_filter === 'suspended') echo 'selected'; ?>>Suspended</option>
            </select>
        </div>
        <?php endif; ?>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <div class="card mb-4">
        <div class="card-header"><b>Create User</b></div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="admin">Admin</option>
                            <option value="editor">Editor</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Password</label>
                        <div class="input-group mb-2" style="max-width:400px;">
                            <input type="password" id="createUserPassword" class="form-control bg-light" value="" readonly style="background:#eee;">
                            <button class="btn btn-outline-secondary" type="button" id="toggleCreatePwBtn" onclick="toggleCreateUserPassword()">Show</button>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyCreateUserPassword()">Copy</button>
                            <button class="btn btn-outline-secondary" type="button" onclick="setCreateUserPassword(generateCreateUserPassword())">Generate</button>
                        </div>
                        <small class="text-muted">Password format: #user-xxxx (valid 1 hour, user must change on first login)</small>
                    </div>
                    <div class="col-12 text-end mt-2">
                        <button type="submit" name="create_user" value="1" class="btn btn-primary px-4">Create User</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($users): foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($user['status'] ?? '-'); ?></td>
                            <td><?php echo isset($user['created_at']) ? date('Y-m-d H:i', strtotime($user['created_at'])) : '-'; ?></td>
                            <td>
                                <?php if ($tab === 'trashed'): ?>
                                    <a href="users.php?action=restore&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Restore this user?');">Restore</a>
                                <?php else: ?>
                                    <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <?php if (($user['status'] ?? '') === 'active'): ?>
                                        <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#confirmModal" data-action="suspend" data-id="<?php echo $user['id']; ?>">Suspend</a>
                                    <?php elseif (($user['status'] ?? '') === 'suspended'): ?>
                                        <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal" data-action="unsuspend" data-id="<?php echo $user['id']; ?>">Unsuspend</a>
                                    <?php endif; ?>
                                    <a href="#" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmModal" data-action="delete" data-id="<?php echo $user['id']; ?>">Delete</a>
                                    <?php if (($user['status'] ?? '') === 'active'): ?>
                                        <button type="button" class="btn btn-sm btn-info" onclick="showResetPasswordModal(<?php echo $user['id']; ?>)">Reset Password</button>
                                    <?php endif; ?>
                                    <?php if (!empty($user['temp_password']) && (!isset($user['temp_password_expired_at']) || strtotime($user['temp_password_expired_at']) > time())): ?>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="showTempPasswordModal('<?php echo $user['id']; ?>', '<?php echo htmlspecialchars($user['temp_password']); ?>', '<?php echo $user['temp_password_expired_at']; ?>')">Show Temp Password</button>
                                    <?php elseif (!empty($user['temp_password']) && isset($user['temp_password_expired_at']) && strtotime($user['temp_password_expired_at']) <= time()): ?>
                                        <form method="GET" action="users.php" style="display:inline-block"><input type="hidden" name="action" value="reset_password"><input type="hidden" name="id" value="<?php echo $user['id']; ?>"><button type="submit" class="btn btn-sm btn-warning">Generate New Temp Password</button></form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No users found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<?php if ($tab === 'active'): ?>
<script>
function generatePassword() {
    const rand = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
    return '#user-' + rand;
}
function setPasswordField(val) {
    document.getElementById('resetPasswordInput').value = val;
}
function togglePassword() {
    const input = document.getElementById('resetPasswordInput');
    const btn = document.getElementById('togglePwBtn');
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = 'Hide';
    } else {
        input.type = 'password';
        btn.innerHTML = 'Show';
    }
}
function copyPassword() {
    const input = document.getElementById('resetPasswordInput');
    input.select();
    document.execCommand('copy');
}
</script>
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="GET" action="users.php">
        <input type="hidden" name="action" value="reset_password">
        <input type="hidden" name="id" id="resetUserId">
        <div class="modal-header">
          <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Temporary Password</label>
          <div class="input-group mb-2">
            <input type="password" class="form-control bg-light" id="resetPasswordInput" name="temp_password" value="" readonly style="background:#eee;">
            <button class="btn btn-outline-secondary" type="button" id="togglePwBtn" onclick="togglePassword()">Show</button>
            <button class="btn btn-outline-secondary" type="button" onclick="copyPassword()">Copy</button>
            <button class="btn btn-outline-secondary" type="button" onclick="setPasswordField(generatePassword())">Generate</button>
          </div>
          <small class="text-muted">Password format: #user-xxxx (valid for 1 hour, user must change on first login)</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Reset Password</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
// Show modal and set user id
function showResetPasswordModal(userId) {
    setPasswordField(generatePassword());
    document.getElementById('resetUserId').value = userId;
    var modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    modal.show();
}
</script>
<?php endif; ?>
<?php if ($tab === 'active'): ?>
<script>
function showTempPasswordModal(userId, tempPassword, expiredAt) {
    document.getElementById('showTempUserId').value = userId;
    document.getElementById('showTempPasswordInput').value = tempPassword;
    document.getElementById('showTempPasswordExpired').innerText = expiredAt;
    var modal = new bootstrap.Modal(document.getElementById('showTempPasswordModal'));
    modal.show();
}
function toggleShowTempPassword() {
    const input = document.getElementById('showTempPasswordInput');
    const btn = document.getElementById('toggleShowTempPwBtn');
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = 'Hide';
    } else {
        input.type = 'password';
        btn.innerHTML = 'Show';
    }
}
function copyShowTempPassword() {
    const input = document.getElementById('showTempPasswordInput');
    input.select();
    document.execCommand('copy');
}
</script>
<div class="modal fade" id="showTempPasswordModal" tabindex="-1" aria-labelledby="showTempPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="GET" action="users.php">
        <input type="hidden" name="action" value="reset_password">
        <input type="hidden" name="id" id="showTempUserId">
        <div class="modal-header">
          <h5 class="modal-title" id="showTempPasswordModalLabel">Temporary Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Temporary Password</label>
          <div class="input-group mb-2">
            <input type="password" class="form-control bg-light" id="showTempPasswordInput" value="" readonly style="background:#eee;">
            <button class="btn btn-outline-secondary" type="button" id="toggleShowTempPwBtn" onclick="toggleShowTempPassword()">Show</button>
            <button class="btn btn-outline-secondary" type="button" onclick="copyShowTempPassword()">Copy</button>
          </div>
          <div class="mb-2">
            <small class="text-muted">Expires at: <span id="showTempPasswordExpired"></span></small>
          </div>
          <div class="mb-2">
            <button type="submit" class="btn btn-warning btn-sm">Generate New Temp Password</button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
<script>
function generateCreateUserPassword() {
    const rand = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
    return '#user-' + rand;
}
function setCreateUserPassword(val) {
    document.getElementById('createUserPassword').value = val;
}
function toggleCreateUserPassword() {
    const input = document.getElementById('createUserPassword');
    const btn = document.getElementById('toggleCreatePwBtn');
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = 'Hide';
    } else {
        input.type = 'password';
        btn.innerHTML = 'Show';
    }
}
function copyCreateUserPassword() {
    const input = document.getElementById('createUserPassword');
    input.select();
    document.execCommand('copy');
}
// Set initial password on page load
window.addEventListener('DOMContentLoaded', function() {
    setCreateUserPassword(generateCreateUserPassword());
});
</script>
<!-- Modal Konfirmasi Bootstrap -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalLabel">Konfirmasi Aksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="confirmModalBody">
        Apakah Anda yakin ingin melakukan aksi ini?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="confirmModalYes">Ya, Lanjutkan</button>
      </div>
    </div>
  </div>
</div>
<script>
var confirmModal = document.getElementById('confirmModal');
var confirmUrl = '#';
confirmModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var action = button.getAttribute('data-action');
  var id = button.getAttribute('data-id');
  var modalBody = confirmModal.querySelector('#confirmModalBody');
  var confirmBtn = confirmModal.querySelector('#confirmModalYes');
  var actionText = '';
  if(action === 'suspend') actionText = 'Suspend user ini?';
  else if(action === 'unsuspend') actionText = 'Aktifkan kembali user ini?';
  else if(action === 'delete') actionText = 'Hapus user ini?';
  modalBody.textContent = actionText;
  confirmUrl = 'users.php?action=' + action + '&id=' + id;
});
document.getElementById('confirmModalYes').onclick = function() {
  window.location.href = confirmUrl;
};
</script>