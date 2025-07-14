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

<title><?php echo isset($page_title) ? html_entity_decode($page_title, ENT_QUOTES | ENT_HTML5, 'UTF-8') : getSetting('site_name', 'Wiracenter'); ?></title>

<div class="main-content" style="margin-left:0;">
    <section class="py-5">
        <div class="container">
            <?php if ($slug === 'about'): ?>
<!-- Custom About Page Layout for Wiracenter Project -->
<div class="about-hero-section mb-5">
  <div class="row align-items-center">
    <div class="col-md-3 text-center mb-4 mb-md-0">
      <img src="assets/images/support-profile.jpg" alt="Reyza Wirakusuma" class="about-profile-img rounded-circle shadow" loading="lazy" style="width: 160px; height: 160px; object-fit: cover;">
    </div>
    <div class="col-md-9">
      <h1 class="about-hero-title mb-2 fw-bold">Wiracenter</h1>
      <h4 class="text-primary mb-2">Personal Tech Blog & Digital Experiments</h4>
      <p class="about-hero-desc lead mb-2">
        Wiracenter is a digital playground for documenting experiments, showcasing projects, and sharing knowledge about technology, programming, and digital innovation. This platform is built to inspire, help, and connect tech enthusiasts, developers, freelancers, and digital creators.
      </p>
      <div class="about-hero-meta text-muted">Always learning | Always sharing | Always experimenting</div>
    </div>
  </div>
</div>
<div class="row g-4 mb-5">
  <!-- Main Features -->
  <div class="col-lg-6">
    <div class="about-card h-100 shadow-sm">
      <h3 class="about-card-title mb-3"><i class="fas fa-star me-2 text-warning"></i>Main Features</h3>
      <ul class="list-unstyled mb-0">
        <li><i class="fas fa-file-alt text-primary me-2"></i>Tech Articles & Blog</li>
        <li><i class="fas fa-project-diagram text-success me-2"></i>Digital Project Showcase</li>
        <li><i class="fas fa-tools text-info me-2"></i>Online Tools & Utilities</li>
        <li><i class="fas fa-language text-secondary me-2"></i>Bilingual Support (ID/EN)</li>
        <li><i class="fas fa-moon text-dark me-2"></i>Dark Mode</li>
        <li><i class="fas fa-user-shield text-danger me-2"></i>Admin Dashboard</li>
      </ul>
    </div>
  </div>
  <!-- Who is it for? -->
  <div class="col-lg-6">
    <div class="about-card h-100 shadow-sm">
      <h3 class="about-card-title mb-3"><i class="fas fa-users me-2 text-primary"></i>Who is it for?</h3>
      <ul class="list-unstyled mb-0">
        <li><i class="fas fa-user-astronaut me-2"></i>Tech Enthusiast & Learner</li>
        <li><i class="fas fa-laptop-code me-2"></i>Developer & Programmer</li>
        <li><i class="fas fa-briefcase me-2"></i>Freelancer & Digital Creator</li>
        <li><i class="fas fa-store me-2"></i>SMEs & Digital Business</li>
      </ul>
    </div>
  </div>
</div>
<div class="row g-4 mb-5">
  <!-- Values & Philosophy -->
  <div class="col-lg-6">
    <div class="about-card h-100 shadow-sm">
      <h3 class="about-card-title mb-3"><i class="fas fa-heart me-2 text-danger"></i>Values & Philosophy</h3>
      <ul class="list-unstyled mb-0">
        <li><i class="fas fa-lightbulb me-2"></i>Always learning & experimenting</li>
        <li><i class="fas fa-share-alt me-2"></i>Sharing knowledge & inspiration</li>
        <li><i class="fas fa-users me-2"></i>Collaboration & community</li>
        <li><i class="fas fa-code-branch me-2"></i>Open-source & innovation</li>
      </ul>
    </div>
  </div>
  <!-- Collaboration & Contact -->
  <div class="col-lg-6">
    <div class="about-card h-100 shadow-sm">
      <h3 class="about-card-title mb-3"><i class="fas fa-handshake me-2 text-success"></i>Collaboration & Contact</h3>
      <p class="mb-2">Interested in collaborating, discussing, or giving feedback? Feel free to contact me via the <a href="contact.php" class="text-primary fw-bold">Contact</a> page or available social media.</p>
      <div class="d-flex gap-3">
        <a href="mailto:admin@wiracenter.com" class="btn btn-outline-primary btn-sm"><i class="fas fa-envelope me-1"></i>Email</a>
        <a href="https://github.com/reyzawirakusuma" target="_blank" class="btn btn-outline-dark btn-sm"><i class="fab fa-github me-1"></i>GitHub</a>
        <a href="https://www.linkedin.com/in/reyzawirakusuma/" target="_blank" class="btn btn-outline-info btn-sm"><i class="fab fa-linkedin me-1"></i>LinkedIn</a>
      </div>
    </div>
  </div>
</div>
<div class="row g-4">
  <!-- About Me Card -->
  <div class="col-lg-6">
    <div class="about-card h-100">
      <h3 class="about-card-title">About Me</h3>
      <p class="mb-2">I am passionate about digital technology and enjoy exploring, learning, and experimenting with new ideas. My journey covers a wide range of interests—from network engineering, cyber security, to programming and automation.</p>
      <p class="mb-0">I believe that the best way to grow is by being curious, trying things hands-on, and sharing what I learn with others. Wiracenter is my digital playground to document, experiment, and connect with fellow enthusiasts.</p>
    </div>
  </div>
  <!-- Why Wiracenter Card -->
  <div class="col-lg-6">
    <div class="about-card h-100">
      <h3 class="about-card-title">Why Wiracenter?</h3>
      <ul class="mb-2"><li>Document my learning journey and experiments in tech</li><li>Share practical guides, tips, and resources for others</li><li>Showcase projects and digital experiments</li><li>Connect with a community of like-minded tech explorers</li></ul>
      <p class="mb-0">The goal is to inspire, help, and grow together in the ever-evolving world of technology.</p>
    </div>
  </div>
  <!-- Network Engineer Card -->
  <div class="col-lg-4">
    <div class="about-card h-100">
      <h3 class="about-card-title">Network Engineer</h3>
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
      <h3 class="about-card-title">Cyber Security</h3>
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
      <h3 class="about-card-title">Programming Language</h3>
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
  <div class="col-lg-12">
    <div class="about-card h-100">
      <h3 class="about-card-title">Other Interests</h3>
      <ul>
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