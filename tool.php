<?php
require_once 'config/config.php';

// Sanitize and validate input
$slug = trim($_GET['slug'] ?? '');
$slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $slug);
if (empty($slug)) {
    header("Location: index.php");
    exit();
}

$tool = null;
$next_tool = null;
$prev_tool = null;
$error_message = '';

try {
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        // Optimized query: Get current tool with next/prev in one query
        $stmt = $conn->prepare("
            SELECT 
                t.*,
                (SELECT id FROM tools WHERE status = 'published' AND deleted_at IS NULL AND id > t.id ORDER BY id ASC LIMIT 1) as next_id,
                (SELECT id FROM tools WHERE status = 'published' AND deleted_at IS NULL AND id < t.id ORDER BY id DESC LIMIT 1) as prev_id
            FROM tools t 
            WHERE t.slug = ? AND t.status = 'published' AND t.deleted_at IS NULL
        ");
        $stmt->execute([$slug]);
        $tool = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($tool) {
            // Get next tool details if exists
            if ($tool['next_id']) {
                $stmt = $conn->prepare("SELECT id, title, title_en, slug, slug_en, slug_id FROM tools WHERE id = ?");
                $stmt->execute([$tool['next_id']]);
                $next_tool = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Get previous tool details if exists
            if ($tool['prev_id']) {
                $stmt = $conn->prepare("SELECT id, title, title_en, slug, slug_en, slug_id FROM tools WHERE id = ?");
                $stmt->execute([$tool['prev_id']]);
                $prev_tool = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    }
} catch (PDOException $e) {
    error_log("Database connection failed in tool.php: " . $e->getMessage());
    $error_message = 'Database connection failed.';
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

// Set bilingual variables BEFORE HTML
$lang = $_COOKIE['lang'] ?? 'id';
$title = ($lang === 'en' && !empty($tool['title_en'])) ? $tool['title_en'] : $tool['title'];
$description = ($lang === 'en' && !empty($tool['description_en'])) ? $tool['description_en'] : $tool['description'];
$content = ($lang === 'en' && !empty($tool['content_en'])) ? $tool['content_en'] : $tool['content'];

// Calculate reading time (average 200 words per minute)
$word_count = str_word_count(strip_tags($content));
$reading_time = ceil($word_count / 200);

$page_title = $title;
$page_description = $description;

// Get current URL for social sharing
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

include 'includes/header.php';
?>

<!-- Reading Progress Bar -->
<div class="reading-progress"></div>

<div class="main-content" style="margin-left:0;">
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="index.php#tools">Tools</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars_decode($title); ?></li>
                    </ol>
                </nav>

                <!-- Tool Header -->
                <header class="article-header mb-4">
                    <h1 class="article-title"><?php echo html_entity_decode($tool['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></h1>
                    
                    <!-- Tool Meta -->
                    <div class="article-meta">
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <?php if ($tool['status'] == 'published' && !empty($tool['publish_date'])): ?>
                                Published on <?php echo formatDate($tool['publish_date']); ?>
                            <?php else: ?>
                                <?php echo ucfirst($tool['status']); ?> on <?php echo formatDate($tool['created_at']); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <?php echo $reading_time; ?> min read
                        </div>
                        
                        <?php if (!empty($tool['author'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-user"></i>
                            By <?php echo htmlspecialchars($tool['author']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($tool['updated_at']) && $tool['updated_at'] !== $tool['created_at']): ?>
                        <div class="meta-item">
                            <i class="fas fa-edit"></i>
                            Last updated: <?php echo formatDate($tool['updated_at']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($tool['tool_url'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-external-link-alt"></i>
                            <a href="<?php echo htmlspecialchars($tool['tool_url']); ?>" target="_blank" rel="noopener noreferrer">Live Tool</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </header>
                
                <!-- Featured Image -->
                <?php if (!empty($tool['featured_image'])) : ?>
                    <img src="<?php echo htmlspecialchars(UPLOAD_PATH . $tool['featured_image']); ?>" 
                         class="img-fluid rounded mb-4" 
                         alt="<?php echo htmlspecialchars($title); ?>"
                         loading="lazy"
                         onerror="this.style.display='none'">
                <?php endif; ?>

                <!-- Language Toggle Data -->
                <div id="slug-translations" data-en="<?php echo htmlspecialchars($tool['slug_en'] ?? ''); ?>" data-id="<?php echo htmlspecialchars($tool['slug_id'] ?? ''); ?>"></div>

                <!-- Tool Content -->
                <div class="article-content">
                    <?php echo $content; ?>
                </div>

                <!-- Action Buttons -->
                <div class="article-actions mt-5">
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                        <div class="action-buttons">
                            <button class="btn btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Tool
                            </button>
                            <button class="btn btn-outline-secondary" onclick="copyToClipboard('<?php echo htmlspecialchars($current_url); ?>')">
                                <i class="fas fa-link"></i> Copy Link
                            </button>
                            <?php if (!empty($tool['tool_url'])) : ?>
                            <a href="<?php echo htmlspecialchars($tool['tool_url']); ?>" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                                <i class="fas fa-external-link-alt"></i> Visit Tool
                            </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Social Share -->
                        <div class="social-share-mini">
                            <span class="text-muted me-2">Share:</span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($current_url); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Share on Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($current_url); ?>&text=<?php echo urlencode($title); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Share on Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($current_url); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Share on LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="https://wa.me/?text=<?php echo urlencode($title . ' - ' . $current_url); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Share on WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="article-navigation mt-5">
                    <div class="row">
                        <?php if ($prev_tool): ?>
                        <div class="col-6">
                            <a href="tool.php?slug=<?php echo htmlspecialchars($lang === 'en' && !empty($prev_tool['slug_en']) ? $prev_tool['slug_en'] : $prev_tool['slug']); ?>" class="nav-link prev-article">
                                <div class="nav-arrow">←</div>
                                <div class="nav-content">
                                    <small class="text-muted">Previous</small>
                                    <div class="nav-title"><?php echo htmlspecialchars($lang === 'en' && !empty($prev_tool['title_en']) ? $prev_tool['title_en'] : $prev_tool['title']); ?></div>
                                </div>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($next_tool): ?>
                        <div class="col-6">
                            <a href="tool.php?slug=<?php echo htmlspecialchars($lang === 'en' && !empty($next_tool['slug_en']) ? $next_tool['slug_en'] : $next_tool['slug']); ?>" class="nav-link next-article">
                                <div class="nav-content text-end">
                                    <small class="text-muted">Next</small>
                                    <div class="nav-title"><?php echo htmlspecialchars($lang === 'en' && !empty($next_tool['title_en']) ? $next_tool['title_en'] : $next_tool['title']); ?></div>
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
</div>

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
