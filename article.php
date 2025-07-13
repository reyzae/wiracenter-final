<?php
require_once 'config/config.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header("Location: index.php");
    exit();
}

$article = null;
try {
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        $stmt = $conn->prepare("SELECT * FROM articles WHERE slug = ? AND status = 'published' AND deleted_at IS NULL");
        $stmt->execute([$slug]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Database connection failed in article.php: " . $e->getMessage());
}

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
                <h1 data-i18n="articles.title"><?php echo htmlspecialchars($article['title']); ?></h1>
                <p class="text-muted mb-4" data-i18n="articles.published">Published on <?php echo formatDate($article['publish_date']); ?></p>
                
                <!-- Social Share Buttons -->
                <div class="social-share">
                    <h4 data-i18n="share.article"><i class="fas fa-share-alt me-2"></i>Share this article</h4>
                    <div class="share-buttons">
                        <button class="share-btn facebook" onclick="shareContent('facebook')">
                            <i class="fab fa-facebook-f"></i>
                            <span>Facebook</span>
                        </button>
                        <button class="share-btn twitter" onclick="shareContent('twitter')">
                            <i class="fab fa-twitter"></i>
                            <span>Twitter</span>
                        </button>
                        <button class="share-btn linkedin" onclick="shareContent('linkedin')">
                            <i class="fab fa-linkedin-in"></i>
                            <span>LinkedIn</span>
                        </button>
                        <button class="share-btn whatsapp" onclick="shareContent('whatsapp')">
                            <i class="fab fa-whatsapp"></i>
                            <span>WhatsApp</span>
                        </button>
                        <button class="share-btn telegram" onclick="shareContent('telegram')">
                            <i class="fab fa-telegram-plane"></i>
                            <span>Telegram</span>
                        </button>
                        <button class="share-btn email" onclick="shareContent('email')">
                            <i class="fas fa-envelope"></i>
                            <span>Email</span>
                        </button>
                        <button class="share-btn copy-link" onclick="shareContent('copy')">
                            <i class="fas fa-link"></i>
                            <span>Copy Link</span>
                        </button>
                    </div>
                </div>
                
                <?php if (!empty($article['featured_image'])) : ?>
                    <img src="<?php echo htmlspecialchars(UPLOAD_PATH . $article['featured_image']); ?>" class="img-fluid rounded mb-4" alt="<?php echo htmlspecialchars($article['title']); ?>">
                <?php endif; ?>

                <div class="article-content">
                    <?php echo $article['content']; // Assuming content is saved as HTML from a rich text editor ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="assets/js/script.js"></script>
