<?php
require_once 'config/config.php';

$slug = $_GET['slug'] ?? '';
// Jika slug kosong, coba ambil dari path (untuk clean URL /a/slug)
if (empty($slug) && isset($_SERVER['REQUEST_URI'])) {
    if (preg_match('~/a/([^/?]+)~', $_SERVER['REQUEST_URI'], $matches)) {
        $slug = $matches[1];
    }
}
if (empty($slug)) {
    header("Location: index.php");
    exit();
}

$article = null;
$next_article = null;
$prev_article = null;

try {
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        // Get current article
        $stmt = $conn->prepare("SELECT * FROM articles WHERE slug = ? AND status = 'published' AND deleted_at IS NULL");
        $stmt->execute([$slug]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($article) {
            // Get next article
            $stmt = $conn->prepare("SELECT id, title, slug, slug_en, slug_id FROM articles WHERE status = 'published' AND deleted_at IS NULL AND id > ? ORDER BY id ASC LIMIT 1");
            $stmt->execute([$article['id']]);
            $next_article = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get previous article
            $stmt = $conn->prepare("SELECT id, title, title_en, slug, slug_en, slug_id FROM articles WHERE status = 'published' AND deleted_at IS NULL AND id < ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$article['id']]);
            $prev_article = $stmt->fetch(PDO::FETCH_ASSOC);
        }
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

// Set bilingual variables BEFORE HTML
$lang = $_COOKIE['lang'] ?? 'id';
$title = ($lang === 'en' && !empty($article['title_en'])) ? $article['title_en'] : $article['title'];
$excerpt = ($lang === 'en' && !empty($article['excerpt_en'])) ? $article['excerpt_en'] : $article['excerpt'];
$content = ($lang === 'en' && !empty($article['content_en'])) ? $article['content_en'] : $article['content'];

// Calculate reading time (average 200 words per minute)
$word_count = str_word_count(strip_tags($content));
$reading_time = ceil($word_count / 200);

$page_title = $title;
$page_description = $excerpt;

include 'includes/header.php';
?>

<!-- Reading Progress Bar -->
<div class="reading-progress"></div>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="index.php#articles">Articles</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars_decode($title); ?></li>
                    </ol>
                </nav>

                <!-- Article Header -->
                <header class="article-header mb-4">
                    <h1 class="article-title"><?php echo html_entity_decode($article['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></h1>
                    
                    <!-- Article Meta -->
                    <div class="article-meta">
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <?php if ($article['status'] == 'published' && !empty($article['publish_date'])): ?>
                                Published on <?php echo formatDate($article['publish_date']); ?>
                            <?php else: ?>
                                <?php echo ucfirst($article['status']); ?> on <?php echo formatDate($article['created_at']); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <?php echo $reading_time; ?> min read
                        </div>
                        
                        <?php if (!empty($article['author'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-user"></i>
                            By <?php echo htmlspecialchars($article['author']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($article['updated_at']) && $article['updated_at'] !== $article['created_at']): ?>
                        <div class="meta-item">
                            <i class="fas fa-edit"></i>
                            Last updated: <?php echo formatDate($article['updated_at']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </header>
                
                <!-- Featured Image -->
                <?php if (!empty($article['featured_image'])) : ?>
                    <img src="<?php echo htmlspecialchars(UPLOAD_PATH . $article['featured_image']); ?>" class="img-fluid rounded mb-4" alt="<?php echo htmlspecialchars($title); ?>">
                <?php endif; ?>

                <!-- Language Toggle Data -->
                <div id="slug-translations" data-en="<?php echo htmlspecialchars($article['slug_en'] ?? ''); ?>" data-id="<?php echo htmlspecialchars($article['slug_id'] ?? ''); ?>"></div>

                <!-- Article Content -->
                <div class="article-content">
                    <?php echo html_entity_decode($article['content'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>
                </div>

                <!-- Action Buttons -->
                <div class="article-actions mt-5">
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                        <div class="action-buttons">
                            <button class="btn btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Article
                            </button>
                            <button class="btn btn-outline-secondary" onclick="copyToClipboard(window.location.href)">
                                <i class="fas fa-link"></i> Copy Link
                            </button>
                        </div>
                        
                        <!-- Social Share -->
                        <div class="social-share-mini">
                            <span class="text-muted me-2">Share:</span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Share on Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($title); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Share on Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Share on LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="https://wa.me/?text=<?php echo urlencode($title . ' - ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Share on WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="article-navigation mt-5">
                    <div class="row">
                        <?php if ($prev_article): ?>
                        <div class="col-6">
                            <a href="article/<?php echo htmlspecialchars($lang === 'en' && !empty($prev_article['slug_en']) ? $prev_article['slug_en'] : $prev_article['slug']); ?>" class="nav-link prev-article">
                                <div class="nav-arrow">←</div>
                                <div class="nav-content">
                                    <small class="text-muted">Previous</small>
                                    <div class="nav-title"><?php echo htmlspecialchars($lang === 'en' && !empty($prev_article['title_en']) ? $prev_article['title_en'] : $prev_article['title']); ?></div>
                                </div>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($next_article): ?>
                        <div class="col-6">
                            <a href="article/<?php echo htmlspecialchars($lang === 'en' && !empty($next_article['slug_en']) ? $next_article['slug_en'] : $next_article['slug']); ?>" class="nav-link next-article">
                                <div class="nav-content text-end">
                                    <small class="text-muted">Next</small>
                                    <div class="nav-title"><?php echo htmlspecialchars($next_article['title']); ?></div>
                                </div>
                                <div class="nav-arrow">→</div>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="assets/js/script.js"></script>

<script>
// Reading progress bar
window.addEventListener('scroll', function() {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    document.querySelector('.reading-progress').style.width = scrolled + '%';
});

// Copy to clipboard function
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');
        
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>
