<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Auth check (optional, add if needed)
// requireLogin();

$db = new Database();
$conn = $db->connect();

// Mark as read if requested
if (isset($_GET['action']) && $_GET['action'] === 'read' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->prepare("UPDATE contact_messages SET status='read' WHERE id=?")->execute([$id]);
    header('Location: contact_messages.php?view=' . $id);
    exit();
}

// Get message detail if requested
$message = null;
if (isset($_GET['view'])) {
    $id = intval($_GET['view']);
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id=?");
    $stmt->execute([$id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($message && $message['status'] === 'unread') {
        $conn->prepare("UPDATE contact_messages SET status='read' WHERE id=?")->execute([$id]);
        $message['status'] = 'read';
    }
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'], $_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
    $ids = array_map('intval', $_POST['selected_ids']);
    $in = str_repeat('?,', count($ids) - 1) . '?';
    if ($_POST['bulk_action'] === 'mark_read') {
        $conn->prepare("UPDATE contact_messages SET status='read' WHERE id IN ($in)")->execute($ids);
    } elseif ($_POST['bulk_action'] === 'delete') {
        $conn->prepare("DELETE FROM contact_messages WHERE id IN ($in)")->execute($ids);
    } elseif ($_POST['bulk_action'] === 'important') {
        $conn->prepare("UPDATE contact_messages SET important=1 WHERE id IN ($in)")->execute($ids);
    } elseif ($_POST['bulk_action'] === 'unimportant') {
        $conn->prepare("UPDATE contact_messages SET important=0 WHERE id IN ($in)")->execute($ids);
    }
    header('Location: contact_messages.php');
    exit();
}

// Handle admin reply
$reply_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'], $_POST['reply_to_id'])) {
    $reply_id = intval($_POST['reply_to_id']);
    $reply_text = trim($_POST['reply_message']);
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id=?");
    $stmt->execute([$reply_id]);
    $msg = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($msg && $reply_text !== '') {
        $to = $msg['email'];
        $subject = 'Re: ' . $msg['subject'];
        $headers = "From: " . getSetting('contact_email', 'info@wiracenter.com') . "\r\nReply-To: " . getSetting('contact_email', 'info@wiracenter.com');
        @mail($to, $subject, $reply_text, $headers);
        $conn->prepare("UPDATE contact_messages SET status='replied' WHERE id=?")->execute([$reply_id]);
        $reply_success = 'Reply sent successfully!';
        // Optionally, log reply in a separate table
    }
}

// AJAX endpoint for real-time notification
if (isset($_GET['ajax']) && $_GET['ajax'] === 'check_new') {
    $last_id = intval($_GET['last_id'] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id > ? ORDER BY id DESC");
    $stmt->execute([$last_id]);
    $new_msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode(['new_count'=>count($new_msgs), 'messages'=>$new_msgs]);
    exit();
}

// Get all messages with search and filter
$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$filter_sql = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];
if ($search !== '') {
    $filter_sql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($status !== '') {
    $filter_sql .= " AND status = ?";
    $params[] = $status;
}
$filter_sql .= " ORDER BY created_at DESC";
// Pagination setup
$per_page = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;
// Count total filtered messages
$count_sql = "SELECT COUNT(*) FROM contact_messages WHERE 1=1";
$count_params = [];
if ($search !== '') {
    $count_sql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ?)";
    $count_params[] = "%$search%";
    $count_params[] = "%$search%";
    $count_params[] = "%$search%";
}
if ($status !== '') {
    $count_sql .= " AND status = ?";
    $count_params[] = $status;
}
$total_filtered = $conn->prepare($count_sql);
$total_filtered->execute($count_params);
$total_filtered_count = $total_filtered->fetchColumn();
$total_pages = max(1, ceil($total_filtered_count / $per_page));
// Fetch paginated messages
$filter_sql .= " LIMIT $per_page OFFSET $offset";
$stmt = $conn->prepare($filter_sql);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistik pesan
$total_count = $conn->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$unread_count = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE status='unread'")->fetchColumn();
$replied_count = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE status='replied'")->fetchColumn();
$important_count = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE important=1")->fetchColumn();

// Export to CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="contact_messages.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Name', 'Email', 'Subject', 'Message', 'Status', 'Important', 'Created At']);
    foreach ($messages as $msg) {
        fputcsv($out, [
            $msg['id'],
            $msg['name'],
            $msg['email'],
            $msg['subject'],
            $msg['message'],
            $msg['status'],
            !empty($msg['important']) ? 'Yes' : 'No',
            $msg['created_at']
        ]);
    }
    fclose($out);
    exit();
}

$page_title = 'Contact Messages';
include 'includes/header.php';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-envelope me-2"></i>Contact Messages</h1>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['export'=>'csv'])); ?>" class="btn btn-success btn-sm"><i class="fas fa-file-csv me-1"></i>Export CSV</a>
    </div>
    <!-- Statistik -->
    <div class="row mb-3">
        <div class="col-md-3 mb-2">
            <div class="card text-center shadow-sm">
                <div class="card-body p-2">
                    <div class="fw-bold" style="font-size:1.3rem;"><i class="fas fa-inbox me-1 text-primary"></i> <?php echo $total_count; ?></div>
                    <div class="small text-muted">Total</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card text-center shadow-sm">
                <div class="card-body p-2">
                    <div class="fw-bold" style="font-size:1.3rem;"><i class="fas fa-envelope-open me-1 text-danger"></i> <?php echo $unread_count; ?></div>
                    <div class="small text-muted">Unread</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card text-center shadow-sm">
                <div class="card-body p-2">
                    <div class="fw-bold" style="font-size:1.3rem;"><i class="fas fa-reply me-1 text-success"></i> <?php echo $replied_count; ?></div>
                    <div class="small text-muted">Replied</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card text-center shadow-sm">
                <div class="card-body p-2">
                    <div class="fw-bold" style="font-size:1.3rem;"><i class="fas fa-star me-1 text-warning"></i> <?php echo $important_count; ?></div>
                    <div class="small text-muted">Important</div>
                </div>
            </div>
        </div>
    </div>
    <!-- Search and Filter -->
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control" placeholder="Search name, email, or subject..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="unread" <?php if(($_GET['status'] ?? '')==='unread') echo 'selected'; ?>>Unread</option>
                <option value="replied" <?php if(($_GET['status'] ?? '')==='replied') echo 'selected'; ?>>Replied</option>
            </select>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
        </div>
    </form>
    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <form method="post" id="bulkActionForm">
                        <div class="d-flex mb-2 align-items-center gap-2">
                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input me-2">
                            <label for="selectAllCheckbox" class="form-label mb-0 me-2" style="font-size:0.95em;">All</label>
                            <select name="bulk_action" class="form-select form-select-sm w-auto" required>
                                <option value="">Bulk Action</option>
                                <option value="mark_read">Mark as Read</option>
                                <option value="delete">Delete</option>
                                <option value="important">Mark as Important</option>
                                <option value="unimportant">Unmark Important</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-check"></i> Apply</button>
                            <span id="selectedCount" class="ms-2 text-muted small"></span>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if (empty($messages)): ?>
                                <div class="list-group-item text-center text-muted">No messages found.</div>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <div class="list-group-item d-flex align-items-center<?php if ($message && $msg['id'] == $message['id']) echo ' active'; ?>" style="cursor:pointer;">
                                        <input type="checkbox" name="selected_ids[]" value="<?php echo $msg['id']; ?>" class="form-check-input me-2 bulk-checkbox">
                                        <a href="contact_messages.php?view=<?php echo $msg['id']; ?>" class="flex-grow-1 text-decoration-none text-dark d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div>
                                                    <span class="fw-bold" style="font-size:1.05em;"><?php echo htmlspecialchars($msg['name']); ?></span>
                                                    <span class="badge bg-<?php echo $msg['status'] === 'unread' ? 'danger' : ($msg['status'] === 'replied' ? 'success' : 'secondary'); ?> ms-2" style="font-size:0.8em;">
                                                        <?php echo ucfirst($msg['status']); ?>
                                                    </span>
                                                    <?php if (!empty($msg['important'])): ?>
                                                        <i class="fas fa-star text-warning ms-1" title="Important"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted" style="white-space:nowrap;"><?php echo date('d M Y H:i', strtotime($msg['created_at'])); ?></small>
                                            </div>
                                            <div class="fw-semibold text-truncate" style="font-size:1em; color:#222; max-width:100%;"><?php echo htmlspecialchars($msg['subject']); ?></div>
                                            <div class="text-truncate" style="font-size:0.95em; color:#555; max-width:100%;"><?php echo htmlspecialchars($msg['message']); ?></div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination pagination-sm justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item<?php if ($i == $page) echo ' active'; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$i])); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
        <div class="col-lg-8 mt-4 mt-lg-0">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white fw-bold">Message Detail</div>
                <div class="card-body">
                    <?php if ($reply_success): ?>
                        <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <div><?php echo $reply_success; ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($message): ?>
                        <div class="mb-2">
                            <span class="fw-bold">From:</span> <?php echo htmlspecialchars($message['name']); ?> &lt;<?php echo htmlspecialchars($message['email']); ?>&gt;
                        </div>
                        <div class="mb-2">
                            <span class="fw-bold">Subject:</span> <?php echo htmlspecialchars($message['subject']); ?>
                        </div>
                        <div class="mb-2">
                            <span class="fw-bold">Received:</span> <?php echo date('d M Y H:i', strtotime($message['created_at'])); ?>
                        </div>
                        <div class="mb-3">
                            <span class="fw-bold">Status:</span> <span class="badge bg-<?php echo $message['status'] === 'unread' ? 'danger' : ($message['status'] === 'replied' ? 'success' : 'secondary'); ?>"><?php echo ucfirst($message['status']); ?></span>
                        </div>
                        <div class="mb-4" style="white-space:pre-line; border-left:3px solid #eee; padding-left:1rem;">
                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                        </div>
                        <form method="post" class="mb-3">
                            <input type="hidden" name="reply_to_id" value="<?php echo $message['id']; ?>">
                            <div class="mb-2">
                                <label for="reply_message" class="form-label fw-bold">Reply Message</label>
                                <textarea name="reply_message" id="reply_message" class="form-control" rows="4" required placeholder="Type your reply here..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success"><i class="fas fa-reply me-1"></i>Send Reply</button>
                        </form>
                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo rawurlencode($message['subject']); ?>" class="btn btn-outline-primary me-2"><i class="fas fa-reply me-1"></i>Reply via Email</a>
                        <a href="contact_messages.php" class="btn btn-outline-secondary">Back to Inbox</a>
                    <?php else: ?>
                        <div class="d-flex flex-column align-items-center justify-content-center" style="min-height:200px;">
                            <i class="fas fa-envelope-open-text fa-3x text-muted mb-3"></i>
                            <div class="text-muted">Select a message to view details.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Toast for real-time notification -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
    <div id="realtimeToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="display:none;">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-envelope me-2"></i> Pesan baru masuk!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 
<script>
// Real-time notification polling
(function() {
    let lastId = <?php echo !empty($messages) ? max(array_column($messages, 'id')) : 0; ?>;
    setInterval(function() {
        fetch('?ajax=check_new&last_id=' + lastId)
            .then(res => res.json())
            .then(data => {
                if (data.new_count > 0) {
                    lastId = Math.max(...data.messages.map(m => parseInt(m.id)));
                    var toast = document.getElementById('realtimeToast');
                    if (toast) {
                        toast.style.display = 'block';
                        var bsToast = new bootstrap.Toast(toast, { delay: 4000 });
                        bsToast.show();
                    }
                }
            });
    }, 10000); // 10 seconds
})();

// Select all checkbox logic and selected count
const selectAll = document.getElementById('selectAllCheckbox');
const checkboxes = document.querySelectorAll('.bulk-checkbox');
const selectedCount = document.getElementById('selectedCount');
function updateSelectedCount() {
    const checked = document.querySelectorAll('.bulk-checkbox:checked').length;
    selectedCount.textContent = checked > 0 ? checked + ' selected' : '';
}
if (selectAll) {
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
        updateSelectedCount();
    });
}
checkboxes.forEach(cb => {
    cb.addEventListener('change', function() {
        updateSelectedCount();
        if (!this.checked) selectAll.checked = false;
        else if (document.querySelectorAll('.bulk-checkbox:checked').length === checkboxes.length) selectAll.checked = true;
    });
});
updateSelectedCount();
</script> 