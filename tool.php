<?php
require_once 'config/config.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header("Location: index.php");
    exit();
}

$db = new Database();
$conn = $db->connect();

$tool = null;
try {
    $stmt = $conn->prepare("SELECT * FROM tools WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    $tool = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tool = null;
    $error_message = 'Table tools not found in database.';
}

if (!$tool) {
    header("HTTP/1.0 404 Not Found");
    $page_title = "Tool Not Found";
    include 'includes/header.php';
    echo "<div class='container text-center py-5'><h1 class='display-4'>404</h1><p class='lead'>Sorry, the tool you are looking for could not be found.";
    if (!empty($error_message)) { echo '<br><span class=\'text-danger\'>' . htmlspecialchars($error_message) . '</span>'; }
    echo "</p><a href='index.php' class='btn btn-primary'>Back to Home</a></div>";
    include 'includes/footer.php';
    exit();
}

$page_title = $tool['title'];
$page_description = $tool['description'];

include 'includes/header.php';
?>
<div class="main-content" style="margin-left:0;">
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h1 class="display-5 mb-3"><?php echo htmlspecialchars($tool['title']); ?></h1>
                <p class="text-muted mb-4">Published on <?php echo formatDate($tool['publish_date']); ?></p>
                <?php if (!empty($tool['featured_image'])) : ?>
                    <img src="<?php echo UPLOAD_PATH . htmlspecialchars($tool['featured_image']); ?>" class="img-fluid rounded mb-4" alt="<?php echo htmlspecialchars($tool['title']); ?>">
                <?php endif; ?>
                <div class="tool-content">
                    <?php echo $tool['content']; ?>
                </div>
                <?php if (!empty($tool['tool_url'])) : ?>
                <div class="mt-5">
                    <a href="<?php echo htmlspecialchars($tool['tool_url']); ?>" class="btn btn-primary" target="_blank" rel="noopener noreferrer">Visit Tool</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
</div>
<?php include 'includes/footer.php'; ?>
