<?php
require_once 'config/config.php';

// Get site settings
$site_name = getSetting('site_name', 'Wiracenter');

// Get projects
$db = new Database();
$conn = $db->connect();
$projects = [];
try {
    $stmt = $conn->prepare("SELECT * FROM projects WHERE status = 'published' ORDER BY publish_date DESC");
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $projects = [];
    $error_message = 'Table projects not found in database.';
}

// Get articles
$articles = [];
try {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE status = 'published' ORDER BY publish_date DESC");
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $articles = [];
    $error_message = 'Table articles not found in database.';
}

// Get tools
$tools = [];
try {
    $stmt = $conn->prepare("SELECT * FROM tools WHERE status = 'published' ORDER BY publish_date DESC");
    $stmt->execute();
    $tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tools = [];
    $error_message = 'Table tools not found in database.';
}
?>
<?php include 'includes/header.php'; ?>
<div class="main-content" style="margin-left:0;">
    <!-- Page Header -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 mb-4">My Spaces</h1>
                    <p class="lead">Discover my projects, articles, and tools</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter Navigation -->
    <section class="py-3 bg-white shadow-sm">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <ul class="nav nav-pills justify-content-center" id="spacesTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="projects-tab" data-bs-toggle="pill" data-bs-target="#projects" type="button" role="tab">
                                <i class="fas fa-code me-2"></i>Projects
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="articles-tab" data-bs-toggle="pill" data-bs-target="#articles" type="button" role="tab">
                                <i class="fas fa-newspaper me-2"></i>Articles
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tools-tab" data-bs-toggle="pill" data-bs-target="#tools" type="button" role="tab">
                                <i class="fas fa-tools me-2"></i>Tools
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Tabs -->
    <section class="py-5">
        <div class="container">
            <div class="tab-content" id="spacesTabContent">
                <!-- Projects Tab -->
                <div class="tab-pane fade show active" id="projects" role="tabpanel">
                    <div class="row">
                        <?php if (!empty($projects)): ?>
                            <?php foreach ($projects as $project): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <?php if ($project['featured_image']): ?>
                                            <img src="<?php echo UPLOAD_PATH . $project['featured_image']; ?>" class="card-img-top" alt="<?php echo $project['title']; ?>">
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $project['title']; ?></h5>
                                            <p class="card-text"><?php echo substr($project['description'], 0, 150) . '...'; ?></p>
                                            <?php if ($project['technologies']): ?>
                                                <div class="mb-3">
                                                    <?php 
                                                    $technologies = json_decode($project['technologies'], true);
                                                    if ($technologies) {
                                                        foreach ($technologies as $tech) {
                                                            echo "<span class='badge bg-primary me-1'>$tech</span>";
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="btn-group">
                                                    <a href="project.php?slug=<?php echo $project['slug']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                                    <?php if ($project['project_url']): ?>
                                                        <a href="<?php echo $project['project_url']; ?>" class="btn btn-sm btn-primary" target="_blank">Live Demo</a>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted"><?php echo formatDate($project['publish_date']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="text-center">
                                    <i class="fas fa-folder-open fa-5x text-muted mb-3"></i>
                                    <h4>No Projects Yet</h4>
                                    <p class="text-muted">Projects will be displayed here once they are published.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Articles Tab -->
                <div class="tab-pane fade" id="articles" role="tabpanel">
                    <div class="row">
                        <?php if (!empty($articles)): ?>
                            <?php foreach ($articles as $article): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <?php if ($article['featured_image']): ?>
                                            <img src="<?php echo UPLOAD_PATH . $article['featured_image']; ?>" class="card-img-top" alt="<?php echo $article['title']; ?>">
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $article['title']; ?></h5>
                                            <p class="card-text"><?php echo $article['excerpt'] ?? substr(strip_tags($article['content']), 0, 150) . '...'; ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <a href="article.php?slug=<?php echo $article['slug']; ?>" class="btn btn-primary">Read More</a>
                                                <small class="text-muted"><?php echo formatDate($article['publish_date']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="text-center">
                                    <i class="fas fa-newspaper fa-5x text-muted mb-3"></i>
                                    <h4>No Articles Yet</h4>
                                    <p class="text-muted">Articles will be displayed here once they are published.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tools Tab -->
                <div class="tab-pane fade" id="tools" role="tabpanel">
                    <div class="row">
                        <?php if (!empty($tools)): ?>
                            <?php foreach ($tools as $tool): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <?php if ($tool['featured_image']): ?>
                                            <img src="<?php echo UPLOAD_PATH . $tool['featured_image']; ?>" class="card-img-top" alt="<?php echo $tool['title']; ?>">
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $tool['title']; ?></h5>
                                            <?php if ($tool['category']): ?>
                                                <span class="badge bg-info mb-2"><?php echo $tool['category']; ?></span>
                                            <?php endif; ?>
                                            <p class="card-text"><?php echo substr($tool['description'], 0, 150) . '...'; ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="btn-group">
                                                    <a href="tool.php?slug=<?php echo $tool['slug']; ?>" class="btn btn-sm btn-outline-primary">Learn More</a>
                                                    <?php if ($tool['tool_url']): ?>
                                                        <a href="<?php echo $tool['tool_url']; ?>" class="btn btn-sm btn-primary" target="_blank">Use Tool</a>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted"><?php echo formatDate($tool['publish_date']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="text-center">
                                    <i class="fas fa-tools fa-5x text-muted mb-3"></i>
                                    <h4>No Tools Yet</h4>
                                    <p class="text-muted">Tools will be displayed here once they are published.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php include 'includes/footer.php'; ?>