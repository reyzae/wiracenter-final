<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Activity Logs';
include 'includes/header.php';

$db = new Database();
$conn = $db->connect();

// Filter
$user_filter = $_GET['user'] ?? '';
$date_start = $_GET['date_start'] ?? '';
$date_end = $_GET['date_end'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 25;
$offset = ($page - 1) * $per_page;

// Get all users for filter dropdown
$userStmt = $conn->query("SELECT id, username FROM users ORDER BY username");
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

// Build query
$sql = "SELECT l.*, u.username FROM activity_logs l LEFT JOIN users u ON l.user_id = u.id WHERE 1=1";
$params = [];
if ($user_filter) {
    $sql .= " AND l.user_id = ?";
    $params[] = $user_filter;
}
if ($date_start) {
    $sql .= " AND l.created_at >= ?";
    $params[] = $date_start . ' 00:00:00';
}
if ($date_end) {
    $sql .= " AND l.created_at <= ?";
    $params[] = $date_end . ' 23:59:59';
}
if ($search) {
    $sql .= " AND (l.action LIKE ? OR l.item_type LIKE ? OR l.ip_address LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql_count = str_replace('SELECT l.*, u.username', 'SELECT COUNT(*) as cnt', $sql);
$sql .= " ORDER BY l.created_at DESC LIMIT $per_page OFFSET $offset";

// Get total count
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute($params);
$total = $stmt_count->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
$total_pages = ceil($total / $per_page);

// Get logs
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="container-fluid">
    <h1 class="h2 mb-4"><i class="fas fa-history me-2"></i>Activity Logs</h1>
    <form method="GET" class="row g-3 align-items-end mb-3">
        <div class="col-md-3">
            <label class="form-label">User</label>
            <select name="user" class="form-select">
                <option value="">All Users</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u['id']; ?>" <?php if ($user_filter == $u['id']) echo 'selected'; ?>><?php echo htmlspecialchars($u['username']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Date Start</label>
            <input type="date" name="date_start" class="form-control" value="<?php echo htmlspecialchars($date_start); ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Date End</label>
            <input type="date" name="date_end" class="form-control" value="<?php echo htmlspecialchars($date_end); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Action, item, IP, user..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Item Type</th>
                            <th>Item ID</th>
                            <th>IP Address</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($logs): foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td><?php echo htmlspecialchars($log['item_type'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($log['item_id'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($log['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-history fa-2x mb-3 d-block"></i>
                                <strong>No activity logs found</strong><br>
                                <small>All user and system activities will appear here</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center mt-3">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item<?php if ($i == $page) echo ' active'; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
