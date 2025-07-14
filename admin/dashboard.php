<?php
$page_title = 'Dashboard';
include 'includes/header.php';

// Add Fira Sans font for dashboard
?>
<style>
body, .stats-card, .card, .list-group-item, .btn, .card-title, .card-header, .card-body {
    font-family: 'Fira Sans', Arial, Helvetica, sans-serif !important;
}
</style>
<?php
// Get statistics
$db = new Database();
$conn = $db->connect();

// Count published articles
$published_articles = 0;
try {
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM articles WHERE status = 'published'");
$stmt->execute();
$published_articles = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (PDOException $e) {
    $published_articles = 0;
    $error_message = 'Table articles not found in database.';
}

// Count published projects
$published_projects = 0;
try {
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM projects WHERE status = 'published'");
$stmt->execute();
$published_projects = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (PDOException $e) {
    $published_projects = 0;
    $error_message = 'Table projects not found in database.';
}

// Count published tools
$published_tools = 0;
try {
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM tools WHERE status = 'published'");
$stmt->execute();
$published_tools = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (PDOException $e) {
    $published_tools = 0;
    $error_message = 'Table tools not found in database.';
}

// Count unread messages
$unread_messages = 0;
try {
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
$stmt->execute();
$unread_messages = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (PDOException $e) {
    $unread_messages = 0;
    $error_message = 'Table contact_messages not found in database.';
}

// Count total users
$total_users = 0;
try {
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
$stmt->execute();
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (PDOException $e) {
    $total_users = 0;
    $error_message = 'Table users not found in database.';
}

// Get recent articles
$recent_articles = [];
try {
$stmt = $conn->prepare("SELECT id, title, status, publish_date FROM articles ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_articles = [];
    $error_message = 'Table articles not found in database.';
}

// Get recent projects
$recent_projects = [];
try {
$stmt = $conn->prepare("SELECT id, title, status, publish_date FROM projects ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_projects = [];
    $error_message = 'Table projects not found in database.';
}

// Get recent messages
$recent_messages = [];
try {
$stmt = $conn->prepare("SELECT id, name, email, subject, status, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_messages = [];
    $error_message = 'Table contact_messages not found in database.';
}

// Get recent tools
$recent_tools = [];
try {
$stmt = $conn->prepare("SELECT id, title, status, publish_date FROM tools ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_tools = [];
    $error_message = 'Table tools not found in database.';
}
?>

<?php if (!empty($error_message)): ?>
<div class="alert alert-danger text-center" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?php echo htmlspecialchars($error_message); ?>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row g-4 mb-4 <?php echo !hasPermission('admin') ? 'justify-content-center' : ''; ?>">
    <div class="col-md-<?php echo !hasPermission('admin') ? '4' : '3'; ?>">
        <div class="stats-card p-4 text-center">
            <i class="fas fa-newspaper fa-3x text-primary mb-3"></i>
            <h3 class="mb-1" id="stat-articles"><?php echo $published_articles; ?></h3>
            <p class="text-muted mb-0">Published Articles</p>
        </div>
    </div>
    <div class="col-md-<?php echo !hasPermission('admin') ? '4' : '3'; ?>">
        <div class="stats-card p-4 text-center">
            <i class="fas fa-code fa-3x text-primary mb-3"></i>
            <h3 class="mb-1" id="stat-projects"><?php echo $published_projects; ?></h3>
            <p class="mb-0">Published Projects</p>
        </div>
    </div>
    <div class="col-md-<?php echo !hasPermission('admin') ? '4' : '3'; ?>">
        <div class="stats-card p-4 text-center">
            <i class="fas fa-tools fa-3x text-primary mb-3"></i>
            <h3 class="mb-1" id="stat-tools"><?php echo $published_tools; ?></h3>
            <p class="mb-0">Published Tools</p>
        </div>
    </div>
    <?php if (hasPermission('admin')): ?>
    <div class="col-md-3">
        <div class="stats-card p-4 text-center">
            <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
            <h3 class="mb-1" id="stat-messages"><?php echo $unread_messages; ?></h3>
            <p class="mb-0">Unread Messages</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card p-4 text-center">
            <i class="fas fa-users fa-3x text-primary mb-3"></i>
            <h3 class="mb-1" id="stat-users"><?php echo $total_users; ?></h3>
            <p class="mb-0">Total Users</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (hasPermission('editor')): ?>
<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Quick Actions</h5>
    </div>
    <div class="card-body">
        <div class="row g-2 <?php echo hasPermission('editor') && !hasPermission('admin') ? 'justify-content-center' : ''; ?>">
            <div class="col-md-<?php echo hasPermission('editor') && !hasPermission('admin') ? '3' : '2'; ?>">
                <a href="articles.php?action=new" class="btn btn-cyan w-100">
                    <i class="fas fa-plus me-2"></i>New Article
                </a>
            </div>
            <div class="col-md-<?php echo hasPermission('editor') && !hasPermission('admin') ? '3' : '2'; ?>">
                <a href="projects.php?action=new" class="btn btn-green w-100">
                    <i class="fas fa-plus me-2"></i>New Project
                </a>
            </div>
            <div class="col-md-<?php echo hasPermission('editor') && !hasPermission('admin') ? '3' : '2'; ?>">
                <a href="tools.php?action=new" class="btn btn-orange w-100">
                    <i class="fas fa-plus me-2"></i>New Tool
                </a>
            </div>
            <div class="col-md-<?php echo hasPermission('editor') && !hasPermission('admin') ? '3' : '2'; ?>">
                <a href="files.php" class="btn btn-purple w-100">
                    <i class="fas fa-upload me-2"></i>Upload File
                </a>
            </div>
            <div class="col-md-2">
                <?php if (hasPermission('admin')): ?>
                <a href="settings.php" class="btn btn-secondary w-100">
                    <i class="fas fa-cog me-2"></i>Settings
                </a>
                <?php endif; ?>
            </div>
            <div class="col-md-2">
                <?php if (hasPermission('admin')): ?>
                <a href="../index.php" class="btn btn-outline-primary w-100" target="_blank">
                    <i class="fas fa-external-link-alt me-2"></i>View Site
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Content -->
<div class="row g-4 <?php echo !hasPermission('admin') ? 'justify-content-center' : ''; ?>">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Articles</h5>
            </div>
            <div class="card-body">
                <?php if ($recent_articles): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_articles as $article): ?>
                            <a href="articles.php?action=edit&id=<?php echo $article['id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars_decode($article['title']); ?></h6>
                                    <small class="text-muted"><?php echo formatDateTime($article['publish_date'] ?? 'Draft'); ?></small>
                                </div>
                                <span class="badge bg-<?php echo $article['status'] == 'published' ? 'success' : 'warning'; ?> rounded-pill">
                                    <?php echo ucfirst($article['status']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="articles.php" class="btn btn-sm btn-outline-primary">View All Articles</a>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-newspaper fa-3x mb-3"></i>
                        <p>No articles yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Projects</h5>
            </div>
            <div class="card-body">
                <?php if ($recent_projects): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_projects as $project): ?>
                            <a href="projects.php?action=edit&id=<?php echo $project['id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars_decode($project['title']); ?></h6>
                                    <small class="text-muted"><?php echo formatDateTime($project['publish_date'] ?? 'Draft'); ?></small>
                                </div>
                                <span class="badge bg-<?php echo $project['status'] == 'published' ? 'success' : 'warning'; ?> rounded-pill">
                                    <?php echo ucfirst($project['status']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="projects.php" class="btn btn-sm btn-outline-primary">View All Projects</a>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-code fa-3x mb-3"></i>
                        <p>No projects yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Tools</h5>
            </div>
            <div class="card-body">
                <?php if ($recent_tools): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_tools as $tool): ?>
                            <a href="tools.php?action=edit&id=<?php echo $tool['id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars_decode($tool['title']); ?></h6>
                                    <small class="text-muted"><?php echo formatDateTime($tool['publish_date'] ?? 'Draft'); ?></small>
                                </div>
                                <span class="badge bg-<?php echo $tool['status'] == 'published' ? 'success' : 'warning'; ?> rounded-pill">
                                    <?php echo ucfirst($tool['status']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="tools.php" class="btn btn-sm btn-outline-primary">View All Tools</a>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-tools fa-3x mb-3"></i>
                        <p>No tools yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (hasPermission('admin')): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Contact Messages</h5>
            </div>
            <div class="card-body">
                <?php if ($recent_messages): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_messages as $message): ?>
                                    <tr>
                                        <td><?php echo $message['name']; ?></td>
                                        <td><?php echo $message['email']; ?></td>
                                        <td><?php echo $message['subject']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $message['status'] == 'unread' ? 'danger' : ($message['status'] == 'read' ? 'warning' : 'success'); ?>">
                                                <?php echo ucfirst($message['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDateTime($message['created_at']); ?></td>
                                        <td>
                                            <a href="messages.php?id=<?php echo $message['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="messages.php" class="btn btn-sm btn-outline-primary">View All Messages</a>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-envelope fa-3x mb-3"></i>
                        <p>No messages yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>