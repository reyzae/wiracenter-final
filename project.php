<?php
require_once 'config/config.php';

// Sanitize and validate input
$slug = trim($_GET['slug'] ?? '');
$slug = filter_var($slug, FILTER_SANITIZE_STRING);
if (empty($slug)) {
    header("Location: index.php");
    exit();
}

$project = null;
$next_project = null;
$prev_project = null;

try {
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        // Optimized query: Get current project with next/prev in one query
        $stmt = $conn->prepare("
            SELECT 
                p.*,
                (SELECT id FROM projects WHERE status = 'published' AND deleted_at IS NULL AND id > p.id ORDER BY id ASC LIMIT 1) as next_id,
                (SELECT id FROM projects WHERE status = 'published' AND deleted_at IS NULL AND id < p.id ORDER BY id DESC LIMIT 1) as prev_id
            FROM projects p 
            WHERE p.slug = ? AND p.status = 'published' AND p.deleted_at IS NULL
        ");
        $stmt->execute([$slug]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($project) {
            // Get next project details if exists
            if ($project['next_id']) {
                $stmt = $conn->prepare("SELECT id, title, title_en, slug, slug_en, slug_id FROM projects WHERE id = ?");
                $stmt->execute([$project['next_id']]);
                $next_project = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Get previous project details if exists
            if ($project['prev_id']) {
                $stmt = $conn->prepare("SELECT id, title, title_en, slug, slug_en, slug_id FROM projects WHERE id = ?");
                $stmt->execute([$project['prev_id']]);
                $prev_project = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    }
} catch (PDOException $e) {
    error_log("Database connection failed in project.php: " . $e->getMessage());
}

if (!$project) {
    header("HTTP/1.0 404 Not Found");
    $page_title = "Project Not Found";
    include 'includes/header.php';
    echo "<div class='container text-center py-5'><h1 class='display-4'>404</h1><p class='lead'>Sorry, the project you are looking for could not be found.</p><a href='index.php' class='btn btn-primary'>Back to Home</a></div>";
    include 'includes/footer.php';
    exit();
}

// Set bilingual variables BEFORE HTML
$lang = $_COOKIE['lang'] ?? 'id';
$title = ($lang === 'en' && !empty($project['title_en'])) ? $project['title_en'] : $project['title'];
$description = ($lang === 'en' && !empty($project['description_en'])) ? $project['description_en'] : $project['description'];
$content = ($lang === 'en' && !empty($project['content_en'])) ? $project['content_en'] : $project['content'];

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
                        <li class="breadcrumb-item"><a href="index.php#projects">Projects</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars_decode($title); ?></li>
                    </ol>
                </nav>

                <!-- Project Header -->
                <header class="article-header mb-4">
                    <h1 class="article-title"><?php echo htmlspecialchars_decode($title); ?></h1>
                    
                    <!-- Project Meta -->
                    <div class="article-meta">
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <?php if ($project['status'] == 'published' && !empty($project['publish_date'])): ?>
                                Published on <?php echo formatDate($project['publish_date']); ?>
                            <?php else: ?>
                                <?php echo ucfirst($project['status']); ?> on <?php echo formatDate($project['created_at']); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <?php echo $reading_time; ?> min read
                        </div>
                        
                        <?php if (!empty($project['author'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-user"></i>
                            By <?php echo htmlspecialchars($project['author']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($project['updated_at']) && $project['updated_at'] !== $project['created_at']): ?>
                        <div class="meta-item">
                            <i class="fas fa-edit"></i>
                            Last updated: <?php echo formatDate($project['updated_at']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($project['project_url'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-external-link-alt"></i>
                            <a href="<?php echo htmlspecialchars($project['project_url']); ?>" target="_blank" rel="noopener noreferrer">Live Project</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </header>
                
                <!-- Featured Image -->
                <?php if (!empty($project['featured_image'])) : ?>
                    <img src="<?php echo htmlspecialchars(UPLOAD_PATH . $project['featured_image']); ?>" 
                         class="img-fluid rounded mb-4" 
                         alt="<?php echo htmlspecialchars($title); ?>"
                         loading="lazy"
                         onerror="this.style.display='none'">
                <?php endif; ?>

                <!-- Language Toggle Data -->
                <div id="slug-translations" data-en="<?php echo htmlspecialchars($project['slug_en'] ?? ''); ?>" data-id="<?php echo htmlspecialchars($project['slug_id'] ?? ''); ?>"></div>

                <!-- Project Content -->
                <div class="article-content">
                    <?php echo $content; ?>
                </div>

                <!-- Action Buttons -->
                <div class="article-actions mt-5">
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                        <div class="action-buttons">
                            <button class="btn btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Project
                            </button>
                            <button class="btn btn-outline-secondary" onclick="copyToClipboard('<?php echo htmlspecialchars($current_url); ?>')">
                                <i class="fas fa-link"></i> Copy Link
                            </button>
                            <?php if (!empty($project['project_url'])) : ?>
                            <a href="<?php echo htmlspecialchars($project['project_url']); ?>" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                                <i class="fas fa-external-link-alt"></i> Visit Project
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
                        <?php if ($prev_project): ?>
                        <div class="col-6">
                            <a href="project.php?slug=<?php echo htmlspecialchars($lang === 'en' && !empty($prev_project['slug_en']) ? $prev_project['slug_en'] : $prev_project['slug']); ?>" class="nav-link prev-article">
                                <div class="nav-arrow">←</div>
                                <div class="nav-content">
                                    <small class="text-muted">Previous</small>
                                    <div class="nav-title"><?php echo htmlspecialchars($lang === 'en' && !empty($prev_project['title_en']) ? $prev_project['title_en'] : $prev_project['title']); ?></div>
                                </div>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($next_project): ?>
                        <div class="col-6">
                            <a href="project.php?slug=<?php echo htmlspecialchars($lang === 'en' && !empty($next_project['slug_en']) ? $next_project['slug_en'] : $next_project['slug']); ?>" class="nav-link next-article">
                                <div class="nav-content text-end">
                                    <small class="text-muted">Next</small>
                                    <div class="nav-title"><?php echo htmlspecialchars($lang === 'en' && !empty($next_project['title_en']) ? $next_project['title_en'] : $next_project['title']); ?></div>
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
