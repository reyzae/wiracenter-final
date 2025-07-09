<?php
require_once 'config/config.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header("Location: index.php");
    exit();
}

$db = new Database();
$conn = $db->connect();

$stmt = $conn->prepare("SELECT * FROM articles WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header("HTTP/1.0 404 Not Found");
    $page_title = "Article Not Found";
    include 'includes/header.php';
    echo "<div class='container text-center py-5'><h1 class='display-4'>404</h1><p class='lead'>Sorry, the article you are looking for could not be found.</p><a href='index.php' class='btn btn-primary'>Back to Home</a></div>";
    include 'includes/footer.php';
    exit();
}

$page_title = $article['title'];
$page_description = $article['excerpt'];

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h1 class="display-5 mb-3"><?php echo htmlspecialchars($article['title']); ?></h1>
                <p class="text-muted mb-4">Published on <?php echo formatDate($article['publish_date']); ?></p>
                
                <?php if (!empty($article['featured_image'])) : ?>
                    <img src="<?php echo UPLOAD_PATH . htmlspecialchars($article['featured_image']); ?>" class="img-fluid rounded mb-4" alt="<?php echo htmlspecialchars($article['title']); ?>">
                <?php endif; ?>

                <div class="article-content">
                    <?php echo $article['content']; // Assuming content is saved as HTML from a rich text editor ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
