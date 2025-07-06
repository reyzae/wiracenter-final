<?php
require_once '../config/config.php';
requireLogin();

if (!hasPermission('admin')) {
    redirect(ADMIN_URL . '/dashboard.php');
}

$db = new Database();
$conn = $db->connect();

$action = $_GET['action'] ?? '';

if ($action == 'articles') {
    $stmt = $conn->prepare("SELECT id, title, slug, content, excerpt, status, publish_date, created_at FROM articles ORDER BY created_at DESC");
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($articles)) {
        $filename = 'articles_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // CSV Header
        fputcsv($output, array_keys($articles[0]));

        // CSV Data
        foreach ($articles as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        logActivity($_SESSION['user_id'], 'Exported articles data', 'export', null);
        exit;
    } else {
        // Handle no data to export
        $_SESSION['error_message'] = 'No articles found to export.';
        redirect(ADMIN_URL . '/export_data.php');
    }
} elseif ($action == 'projects') {
    $stmt = $conn->prepare("SELECT id, title, slug, description, project_url, github_url, technologies, status, publish_date, created_at FROM projects ORDER BY created_at DESC");
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($projects)) {
        $filename = 'projects_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // CSV Header
        fputcsv($output, array_keys($projects[0]));

        // CSV Data
        foreach ($projects as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        logActivity($_SESSION['user_id'], 'Exported projects data', 'export', null);
        exit;
    } else {
        $_SESSION['error_message'] = 'No projects found to export.';
        redirect(ADMIN_URL . '/export_data.php');
    }
} elseif ($action == 'tools') {
    $stmt = $conn->prepare("SELECT id, title, slug, description, tool_url, category, status, publish_date, created_at FROM tools ORDER BY created_at DESC");
    $stmt->execute();
    $tools = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($tools)) {
        $filename = 'tools_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // CSV Header
        fputcsv($output, array_keys($tools[0]));

        // CSV Data
        foreach ($tools as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        logActivity($_SESSION['user_id'], 'Exported tools data', 'export', null);
        exit;
    } else {
        $_SESSION['error_message'] = 'No tools found to export.';
        redirect(ADMIN_URL . '/export_data.php');
    }
} else {
    // Default view for export page, or redirect if no action specified
    $page_title = 'Export Data';
    include 'includes/header.php';
?>

<h1 class="h2 mb-4">Export Data</h1>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Export Content</h5>
    </div>
    <div class="card-body">
        <p>Select the type of data you want to export.</p>
        <a href="?action=articles" class="btn btn-primary me-2">
            <i class="fas fa-file-csv me-2"></i>Export Articles to CSV
        </a>
        <a href="?action=projects" class="btn btn-success me-2">
            <i class="fas fa-file-csv me-2"></i>Export Projects to CSV
        </a>
        <a href="?action=tools" class="btn btn-info me-2">
            <i class="fas fa-file-csv me-2"></i>Export Tools to CSV
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php
}
?>