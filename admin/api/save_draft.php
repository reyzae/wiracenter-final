<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$entity_type = $_POST['entity_type'] ?? ''; // e.g., 'article', 'project', 'tool'
$entity_id = $_POST['entity_id'] ?? null;
$content = $_POST['content'] ?? '';

// Sanitize content using HTMLPurifier
require_once __DIR__ . '/../../vendor/autoload.php';
$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);
$purified_content = $purifier->purify($content);

$db = new Database();
$conn = $db->connect();

$table_name = '';
switch ($entity_type) {
    case 'article':
        $table_name = 'articles';
        break;
    case 'project':
        $table_name = 'projects';
        break;
    case 'tool':
        $table_name = 'tools';
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid entity type.']);
        exit;
}

if ($entity_id) {
    // Update existing draft
    $sql = "UPDATE " . $table_name . " SET draft_content = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$purified_content, $entity_id])) {
        echo json_encode(['status' => 'success', 'message' => 'Draft saved.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save draft.']);
    }
} else {
    // For new entries, we can't save a draft without an ID yet.
    // TinyMCE autosave usually works by updating an existing entry.
    // For new entries, the content is typically in the form fields until first save.
    echo json_encode(['status' => 'error', 'message' => 'Cannot save draft for new entity without an ID.']);
}

logActivity($_SESSION['user_id'] ?? null, 'Saved draft for ' . $entity_type, $entity_type, $entity_id);

?>