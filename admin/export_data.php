<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Export Data';
include 'includes/header.php';

requireLogin();
if (!hasPermission('admin')) {
    redirect(ADMIN_URL . '/dashboard.php');
}

$db = new Database();
$conn = $db->connect();

$success_message = '';
$error_message = '';

// ========================
// 1. Konfigurasi Export
// ========================
$export_types = [
    'articles' => 'Articles',
    'projects' => 'Projects',
    'tools' => 'Tools',
    'users' => 'Users',
    'pages' => 'Pages',
    'faqs' => 'FAQs',
    'files' => 'Files',
];

// ========================
// 2. Handle Export ZIP Semua Data
// ========================
if (isset($_GET['export_zip']) && $_GET['export_zip'] === 'all') {
    // Buat file CSV untuk semua tipe, lalu zip
    $tmp_dir = sys_get_temp_dir() . '/export_' . uniqid();
    mkdir($tmp_dir);
    $csv_files = [];
    foreach ($export_types as $type => $label) {
        $query = '';
        switch ($type) {
            case 'articles':
                $query = "SELECT id, title, slug, content, excerpt, status, publish_date, created_at FROM articles ORDER BY created_at DESC";
                break;
            case 'projects':
                $query = "SELECT id, title, slug, description, content, project_url, github_url, technologies, status, publish_date, created_at FROM projects ORDER BY created_at DESC";
                break;
            case 'tools':
                $query = "SELECT id, title, slug, description, content, tool_url, category, status, publish_date, created_at FROM tools ORDER BY created_at DESC";
                break;
            case 'users':
                $query = "SELECT id, username, email, role, status, created_at FROM users ORDER BY created_at DESC";
                break;
            case 'pages':
                $query = "SELECT id, title, slug, content, status, created_at FROM pages ORDER BY created_at DESC";
                break;
            case 'faqs':
                $query = "SELECT id, question, answer, display_order, status, created_at FROM faqs ORDER BY created_at DESC";
                break;
            case 'files':
                $query = "SELECT id, original_name, filename, file_path, file_size, file_type, uploaded_by, created_at FROM files WHERE deleted_at IS NULL ORDER BY created_at DESC";
                break;
        }
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $csv_path = "$tmp_dir/{$type}.csv";
        $csv_files[] = $csv_path;
        $f = fopen($csv_path, 'w');
        if (!empty($rows)) {
            fputcsv($f, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($f, $row);
            }
        }
        fclose($f);
    }
    // Buat ZIP
    $zipname = 'all_data_export_' . date('Ymd_His') . '.zip';
    $zip = new ZipArchive();
    $zip_path = sys_get_temp_dir() . '/' . $zipname;
    $zip->open($zip_path, ZipArchive::CREATE);
    foreach ($csv_files as $csv) {
        $zip->addFile($csv, basename($csv));
    }
    $zip->close();
    // Download ZIP
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipname . '"');
    readfile($zip_path);
    // Cleanup
    foreach ($csv_files as $csv) unlink($csv);
    rmdir($tmp_dir);
    unlink($zip_path);
    logActivity($_SESSION['user_id'], 'Exported all data as ZIP', 'export', null);
    exit;
}

// ========================
// 3. Handle Export Filter, Format, dan Custom Query
// ========================
$filter_type = $_GET['type'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_user = $_GET['user'] ?? '';
$filter_date_start = $_GET['date_start'] ?? '';
$filter_date_end = $_GET['date_end'] ?? '';
$export_format = $_GET['format'] ?? 'csv';
$custom_query = $_POST['custom_query'] ?? '';

// Export dengan filter, format, atau custom query
if (isset($_GET['export']) && isset($export_types[$_GET['export']])) {
    $type = $_GET['export'];
    $filename = $type . '_export_' . date('Ymd_His') . '.' . $export_format;
    $query = '';
    $params = [];
    // Query dasar
    switch ($type) {
        case 'articles':
            $query = "SELECT id, title, slug, content, excerpt, status, publish_date, created_at FROM articles WHERE 1=1";
            if ($filter_status) { $query .= " AND status = ?"; $params[] = $filter_status; }
            if ($filter_date_start) { $query .= " AND created_at >= ?"; $params[] = $filter_date_start . ' 00:00:00'; }
            if ($filter_date_end) { $query .= " AND created_at <= ?"; $params[] = $filter_date_end . ' 23:59:59'; }
            $query .= " ORDER BY created_at DESC";
            break;
        case 'projects':
            $query = "SELECT id, title, slug, description, content, project_url, github_url, technologies, status, publish_date, created_at FROM projects WHERE 1=1";
            if ($filter_status) { $query .= " AND status = ?"; $params[] = $filter_status; }
            if ($filter_date_start) { $query .= " AND created_at >= ?"; $params[] = $filter_date_start . ' 00:00:00'; }
            if ($filter_date_end) { $query .= " AND created_at <= ?"; $params[] = $filter_date_end . ' 23:59:59'; }
            $query .= " ORDER BY created_at DESC";
            break;
        case 'tools':
            $query = "SELECT id, title, slug, description, content, tool_url, category, status, publish_date, created_at FROM tools WHERE 1=1";
            if ($filter_status) { $query .= " AND status = ?"; $params[] = $filter_status; }
            if ($filter_date_start) { $query .= " AND created_at >= ?"; $params[] = $filter_date_start . ' 00:00:00'; }
            if ($filter_date_end) { $query .= " AND created_at <= ?"; $params[] = $filter_date_end . ' 23:59:59'; }
            $query .= " ORDER BY created_at DESC";
            break;
        case 'users':
            $query = "SELECT id, username, email, role, status, created_at FROM users WHERE 1=1";
            if ($filter_status) { $query .= " AND status = ?"; $params[] = $filter_status; }
            if ($filter_user) { $query .= " AND id = ?"; $params[] = $filter_user; }
            if ($filter_date_start) { $query .= " AND created_at >= ?"; $params[] = $filter_date_start . ' 00:00:00'; }
            if ($filter_date_end) { $query .= " AND created_at <= ?"; $params[] = $filter_date_end . ' 23:59:59'; }
            $query .= " ORDER BY created_at DESC";
            break;
        case 'pages':
            $query = "SELECT id, title, slug, content, status, created_at FROM pages WHERE 1=1";
            if ($filter_status) { $query .= " AND status = ?"; $params[] = $filter_status; }
            if ($filter_date_start) { $query .= " AND created_at >= ?"; $params[] = $filter_date_start . ' 00:00:00'; }
            if ($filter_date_end) { $query .= " AND created_at <= ?"; $params[] = $filter_date_end . ' 23:59:59'; }
            $query .= " ORDER BY created_at DESC";
            break;
        case 'faqs':
            $query = "SELECT id, question, answer, display_order, status, created_at FROM faqs WHERE 1=1";
            if ($filter_status) { $query .= " AND status = ?"; $params[] = $filter_status; }
            if ($filter_date_start) { $query .= " AND created_at >= ?"; $params[] = $filter_date_start . ' 00:00:00'; }
            if ($filter_date_end) { $query .= " AND created_at <= ?"; $params[] = $filter_date_end . ' 23:59:59'; }
            $query .= " ORDER BY created_at DESC";
            break;
        case 'files':
            $query = "SELECT id, original_name, filename, file_path, file_size, file_type, uploaded_by, created_at FROM files WHERE deleted_at IS NULL";
            if ($filter_user) { $query .= " AND uploaded_by = ?"; $params[] = $filter_user; }
            if ($filter_date_start) { $query .= " AND created_at >= ?"; $params[] = $filter_date_start . ' 00:00:00'; }
            if ($filter_date_end) { $query .= " AND created_at <= ?"; $params[] = $filter_date_end . ' 23:59:59'; }
            $query .= " ORDER BY created_at DESC";
            break;
    }
    // Jika custom query (hanya untuk admin advanced)
    if ($custom_query && isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
        $query = $custom_query;
        $params = [];
    }
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Export ke format yang dipilih
    if ($export_format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        if (!empty($rows)) {
            fputcsv($output, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
        }
        fclose($output);
    } elseif ($export_format === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($rows, JSON_PRETTY_PRINT);
    } elseif ($export_format === 'xml') {
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $xml = new SimpleXMLElement('<data/>' );
        foreach ($rows as $row) {
            $item = $xml->addChild('item');
            foreach ($row as $k => $v) {
                $item->addChild($k, htmlspecialchars($v));
            }
        }
        echo $xml->asXML();
    }
    logActivity($_SESSION['user_id'], 'Exported ' . $type . ' data (' . $export_format . ')', 'export', null);
    exit;
}

// ========================
// 4. Ambil data user untuk filter
// ========================
$userStmt = $conn->query("SELECT id, username FROM users ORDER BY username");
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="container-fluid">
    <h1 class="h2 mb-4"><i class="fas fa-file-export me-2"></i>Export Data</h1>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Export Content</h5>
        </div>
        <div class="card-body">
            <p>Select the type of data you want to export as CSV, JSON, or XML. You can also filter data before export.</p>
            <!--
                Fitur: Export ZIP semua data
            -->
            <a href="?export_zip=all" class="btn btn-outline-success mb-3">
                <i class="fas fa-file-archive me-2"></i>Download All Data (ZIP)
            </a>
            <!--
                Fitur: Filter export
            -->
            <form method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="export" class="form-select">
                        <option value="">Select Type</option>
                        <?php foreach ($export_types as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php if ($filter_type == $key) echo 'selected'; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <input type="text" name="status" class="form-control" value="<?php echo htmlspecialchars($filter_status); ?>" placeholder="published/active">
                </div>
                <div class="col-md-2">
                    <label class="form-label">User</label>
                    <select name="user" class="form-select">
                        <option value="">All Users</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php if ($filter_user == $u['id']) echo 'selected'; ?>><?php echo htmlspecialchars($u['username']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date Start</label>
                    <input type="date" name="date_start" class="form-control" value="<?php echo htmlspecialchars($filter_date_start); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date End</label>
                    <input type="date" name="date_end" class="form-control" value="<?php echo htmlspecialchars($filter_date_end); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Format</label>
                    <select name="format" class="form-select">
                        <option value="csv" <?php if ($export_format == 'csv') echo 'selected'; ?>>CSV</option>
                        <option value="json" <?php if ($export_format == 'json') echo 'selected'; ?>>JSON</option>
                        <option value="xml" <?php if ($export_format == 'xml') echo 'selected'; ?>>XML</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary">Export</button>
                </div>
            </form>
            <!--
                Fitur: Export custom query (khusus admin)
            -->
            <?php if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin'): ?>
            <div class="card mt-4">
                <div class="card-header bg-warning"><b>Advanced: Export Custom Query (Admin Only)</b></div>
                <div class="card-body">
                    <form method="GET" class="mb-2">
                        <input type="hidden" name="export" value="custom">
                        <label class="form-label">SQL Query</label>
                        <textarea name="custom_query" class="form-control mb-2" rows="2" placeholder="SELECT * FROM ..."></textarea>
                        <select name="format" class="form-select mb-2" style="max-width: 200px; display: inline-block;">
                            <option value="csv">CSV</option>
                            <option value="json">JSON</option>
                            <option value="xml">XML</option>
                        </select>
                        <button type="submit" class="btn btn-danger">Export Custom Query</button>
                    </form>
                    <div class="alert alert-warning mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Gunakan dengan hati-hati! Query salah bisa menyebabkan error atau data bocor.</div>
                </div>
            </div>
            <?php endif; ?>
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <b>Note:</b> Exported files can be opened in Excel, Google Sheets, or imported to another system. Use filters and formats as needed.
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
