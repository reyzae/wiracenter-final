<?php
require_once 'config/config.php';

// Sanitize and validate input
$slug = trim($_GET['slug'] ?? '');
$slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $slug);
if (empty($slug)) {
    header("Location: index.php");
    exit();
}

$db = new Database();
try {
    $conn = $db->connect();
} catch (PDOException $e) {
    error_log("Database connection failed in page.php: " . $e->getMessage());
    header("HTTP/1.0 500 Internal Server Error");
    $page_title = "Server Error";
    include 'includes/header.php';
    echo "<div class='container text-center py-5'><h1 class='display-4'>500</h1><p class='lead'>Sorry, something went wrong. Please try again later.</p><a href='index.php' class='btn btn-primary'>Back to Home</a></div>";
    include 'includes/footer.php';
    exit();
}

$page = null;
$error_message = '';

if (!empty($slug)) {
    try {
        $stmt = $conn->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published' AND deleted_at IS NULL");
        $stmt->execute([$slug]);
        $page = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database query failed in page.php: " . $e->getMessage());
        $page = null;
        $error_message = 'Page not found.';
    }
}

if (!$page) {
    // Redirect to 404 or show a not found message
    header("HTTP/1.0 404 Not Found");
    $page_title = "Page Not Found";
    include 'includes/header.php';
    echo "<div class='container text-center py-5'><h1 class='display-4'>404</h1><p class='lead'>Page not found.</p>";
    if (!empty($error_message)) { echo '<br><span class=\'text-danger\'>' . htmlspecialchars($error_message) . '</span>'; }
    echo "<a href='index.php' class='btn btn-primary'>Back to Home</a></div>";
    include 'includes/footer.php';
    exit();
}

// Set bilingual variables
$lang = $_COOKIE['lang'] ?? 'id';
$title = ($lang === 'en' && !empty($page['title_en'])) ? $page['title_en'] : $page['title'];
$content = ($lang === 'en' && !empty($page['content_en'])) ? $page['content_en'] : $page['content'];
$excerpt = ($lang === 'en' && !empty($page['excerpt_en'])) ? $page['excerpt_en'] : ($page['excerpt'] ?? '');

$page_title = $title;
$page_description = $excerpt ?: substr(strip_tags($content), 0, 160);
?>

<?php include 'includes/header.php'; ?>

<div class="main-content" style="margin-left:0;">
    <section class="py-5">
        <div class="container">
            <?php if ($slug === 'about'): ?>
                <!-- Custom About Page Layout for Digital Technology Enthusiast -->
                <div class="about-hero-section mb-5">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center mb-4 mb-md-0">
                            <?php if (!empty($page['profile_image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($page['profile_image']); ?>" 
                                     alt="Digital Enthusiast" 
                                     class="about-profile-img" 
                                     loading="lazy"
                                     onerror="this.src='assets/images/support-profile.jpg'">
                            <?php else: ?>
                                <img src="assets/images/support-profile.jpg" 
                                     alt="Digital Enthusiast" 
                                     class="about-profile-img"
                                     loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9">
                            <h1 class="about-hero-title mb-2" data-i18n="about.title">Hi, I'm a Digital Technology Enthusiast</h1>
                            <p class="about-hero-desc lead mb-2">
Hi! I'm Reyza Wirakusuma—someone who's genuinely curious and always eager to dive into the world of tech. I love learning, tinkering, and experimenting with all kinds of technologies.<br><br>
Whether it's networking, cybersecurity, or programming, I enjoy exploring different areas just to see how things work and how they can be improved. For me, technology is all about staying curious, getting hands-on, and never stopping the journey of discovery.
</p>
                            <div class="about-hero-meta text-muted">Always learning | Always experimenting | Always sharing</div>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <!-- About Me Card -->
                    <div class="col-lg-6">
                        <div class="about-card h-100">
                            <h3 class="about-card-title"><i class="fas fa-user-astronaut me-2"></i>About Me</h3>
                            <p class="mb-2">I am passionate about digital technology and enjoy exploring, learning, and experimenting with new ideas. My journey covers a wide range of interests—from network engineering, cyber security, to programming and automation.</p>
                            <p class="mb-0">I believe that the best way to grow is by being curious, trying things hands-on, and sharing what I learn with others. Wiracenter is my digital playground to document, experiment, and connect with fellow enthusiasts.</p>
                        </div>
                    </div>
                    <!-- Why Wiracenter Card -->
                    <div class="col-lg-6">
                        <div class="about-card h-100">
                            <h3 class="about-card-title"><i class="fas fa-lightbulb me-2"></i>Why Wiracenter?</h3>
                            <p class="mb-2">Wiracenter was created as a space to:</p>
                            <ul class="mb-2">
                                <li>Document my learning journey and experiments in tech</li>
                                <li>Share practical guides, tips, and resources for others</li>
                                <li>Showcase projects and digital experiments</li>
                                <li>Connect with a community of like-minded tech explorers</li>
                            </ul>
                            <p class="mb-0">The goal is to inspire, help, and grow together in the ever-evolving world of technology.</p>
                        </div>
                    </div>
                    <!-- Network Engineer Card -->
                    <div class="col-lg-4">
                        <div class="about-card h-100">
                            <h3 class="about-card-title"><i class="fas fa-network-wired me-2"></i>Network Engineer</h3>
                            <div class="mb-2 text-muted" style="font-size:0.98rem;">Exploring, configuring, and troubleshooting networks</div>
                            <div class="about-tech-list mb-2">
                                <span class="badge bg-primary me-1 mb-1">Cisco</span>
                                <span class="badge bg-primary me-1 mb-1">MikroTik</span>
                                <span class="badge bg-primary me-1 mb-1">Ubiquiti</span>
                                <span class="badge bg-primary me-1 mb-1">Linux Networking</span>
                                <span class="badge bg-primary me-1 mb-1">Wireshark</span>
                                <span class="badge bg-primary me-1 mb-1">VLAN, VPN, Routing</span>
                            </div>
                        </div>
                    </div>
                    <!-- Cyber Security Card -->
                    <div class="col-lg-4">
                        <div class="about-card h-100">
                            <h3 class="about-card-title"><i class="fas fa-shield-alt me-2"></i>Cyber Security</h3>
                            <div class="mb-2 text-muted" style="font-size:0.98rem;">Learning to secure systems and data, and stay ahead of threats</div>
                            <div class="about-tech-list mb-2">
                                <span class="badge bg-danger me-1 mb-1">Kali Linux</span>
                                <span class="badge bg-danger me-1 mb-1">Burp Suite</span>
                                <span class="badge bg-danger me-1 mb-1">Nmap</span>
                                <span class="badge bg-danger me-1 mb-1">Firewall</span>
                                <span class="badge bg-danger me-1 mb-1">SIEM</span>
                                <span class="badge bg-danger me-1 mb-1">Penetration Testing</span>
                            </div>
                        </div>
                    </div>
                    <!-- Programming Language Card -->
                    <div class="col-lg-4">
                        <div class="about-card h-100">
                            <h3 class="about-card-title"><i class="fas fa-code me-2"></i>Programming Language</h3>
                            <div class="mb-2 text-muted" style="font-size:0.98rem;">Experimenting with code to automate, build, and solve problems</div>
                            <div class="about-tech-list mb-2">
                                <span class="badge bg-success me-1 mb-1">Python</span>
                                <span class="badge bg-success me-1 mb-1">PHP</span>
                                <span class="badge bg-success me-1 mb-1">Bash</span>
                                <span class="badge bg-success me-1 mb-1">JavaScript</span>
                                <span class="badge bg-success me-1 mb-1">SQL</span>
                                <span class="badge bg-success me-1 mb-1">Automation Scripting</span>
                            </div>
                        </div>
                    </div>
                    <!-- Other Interests Card -->
                    <div class="col-lg-6">
                        <div class="about-card h-100">
                            <h3 class="about-card-title"><i class="fas fa-flask me-2"></i>Other Interests</h3>
                            <ul class="mb-2">
                                <li>Open-source software & community</li>
                                <li>Digital privacy & data protection</li>
                                <li>Cloud computing & virtualization</li>
                                <li>Tech blogging & documentation</li>
                                <li>Productivity tools & digital workflows</li>
                                <li>Learning by doing & sharing knowledge</li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Standard page layout -->
                <div class="page-header text-center mb-5">
                    <h1 class="display-4 fw-bold mb-3" style="color: #1a1a2e;"><?php echo htmlspecialchars_decode($title); ?></h1>
                    <?php if (!empty($page['profile_image'])): ?>
                        <div class="text-center mb-4">
                            <img src="uploads/<?php echo htmlspecialchars($page['profile_image']); ?>" 
                                 alt="Profile Photo" 
                                 class="profile-image" 
                                 loading="lazy"
                                 onerror="this.style.display='none'">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="page-content">
                    <?php echo $content; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<link rel="stylesheet" href="assets/css/page-styles.css">

<?php include 'includes/footer.php'; ?>