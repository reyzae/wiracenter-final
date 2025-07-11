<?php
require_once 'config/config.php';

$db = new Database();
try {
    $conn = $db->connect();
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

$slug = $_GET['slug'] ?? '';
$page = null;

if (!empty($slug)) {
    try {
        $stmt = $conn->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published'");
        $stmt->execute([$slug]);
        $page = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $page = null;
        $error_message = 'Table pages not found in database.';
    }
}

if (!$page) {
    // Redirect to 404 or show a not found message
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Page Not Found</h1>";
    if (!empty($error_message)) { echo '<br><span class=\'text-danger\'>' . htmlspecialchars($error_message) . '</span>'; }
    exit();
}

$page_title = $page['title'];
?>
<?php include 'includes/header.php'; ?>
<div class="main-content" style="margin-left:0;">
    <section class="py-5">
        <div class="container">
            <h1 class="mb-4"><?php echo $page['title']; ?></h1>
            <div>
                <?php echo $page['content']; ?>
            </div>
        </div>
    </section>
</div>
<?php include 'includes/footer.php'; ?>