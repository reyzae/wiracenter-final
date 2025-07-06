<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->connect();

    $item_type = sanitize($_POST['item_type'] ?? '');
    $item_id = $_POST['item_id'] ?? null;
    $title = sanitize($_POST['title'] ?? '');
    $slug = generateSlug($_POST['slug'] ?? $title);
    $content = $_POST['content'] ?? '';
    $status = sanitize($_POST['status'] ?? 'draft');
    $publish_date = $_POST['publish_date'] ?? null;

    $table_name = '';
    $content_field = '';
    $excerpt_field = '';

    switch ($item_type) {
        case 'article':
            $table_name = 'articles';
            $content_field = 'content';
            $excerpt_field = 'excerpt';
            $excerpt = sanitize($_POST['excerpt'] ?? '');
            if (empty($excerpt)) {
                $plain_content = strip_tags($content);
                $excerpt = substr($plain_content, 0, 160);
                if (strlen($plain_content) > 160) {
                    $excerpt .= '...';
                }
            }
            break;
        case 'project':
            $table_name = 'projects';
            $content_field = 'content';
            $excerpt_field = 'description'; // Projects use 'description' as excerpt
            $excerpt = sanitize($_POST['excerpt'] ?? ''); // Use excerpt from POST for description
            break;
        case 'tool':
            $table_name = 'tools';
            $content_field = 'content';
            $excerpt_field = 'description'; // Tools use 'description' as excerpt
            $excerpt = sanitize($_POST['excerpt'] ?? ''); // Use excerpt from POST for description
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid item type.']);
            exit;
    }

    if ($item_id) {
        // Update existing item
        $sql = "UPDATE " . $table_name . " SET title=?, slug=?, " . $content_field . "=?, " . $excerpt_field . "=?, status=?, publish_date=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$title, $slug, $content, $excerpt, $status, $publish_date, $item_id])) {
            echo json_encode(['success' => true, 'message' => 'Draft updated.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update draft.']);
        }
    } else {
        // For new items, we cannot auto-save without an ID from the initial save.
        // A more robust solution would involve creating a draft entry and returning its ID.
        echo json_encode(['success' => false, 'message' => 'Cannot auto-save new item without an ID. Please save the item first.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>