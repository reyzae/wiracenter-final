<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';

// Initialize database connection
$db = new Database();
try {
    $conn = $db->connect();
} catch (PDOException $e) {
    error_log("Database connection failed in contact.php: " . $e->getMessage());
    $conn = null;
}

// Initialize variables
$contact_info = [];
$faqs = [];
$success_message = '';
$error_message = '';

// Rate limiting for contact form
$rate_limit_key = 'contact_form_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$rate_limit_time = 300; // 5 minutes
$rate_limit_attempts = 3; // Max 3 attempts per 5 minutes

if (isset($_SESSION[$rate_limit_key])) {
    $attempts = $_SESSION[$rate_limit_key];
    if ($attempts['count'] >= $rate_limit_attempts && (time() - $attempts['time']) < $rate_limit_time) {
        $error_message = 'Too many attempts. Please wait 5 minutes before trying again.';
    }
}

if ($conn) {
// Get contact information from content blocks
try {
        $stmt = $conn->prepare("SELECT title, content, icon_class FROM content_blocks WHERE type = 'contact_info_card' AND page_slug = 'contact' AND status = 'active' AND deleted_at IS NULL ORDER BY display_order");
    $stmt->execute();
    $contact_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching contact info: " . $e->getMessage());
}

// Get FAQs from content blocks
try {
        $stmt = $conn->prepare("SELECT title, content FROM content_blocks WHERE type = 'faq_item' AND page_slug = 'contact' AND status = 'active' AND deleted_at IS NULL ORDER BY display_order");
    $stmt->execute();
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching FAQs: " . $e->getMessage());
    }
}

// Get site settings
$site_name = getSetting('site_name', 'Wiracenter');
$contact_email = getSetting('contact_email', 'info@wiracenter.com');
$contact_phone = getSetting('contact_phone', '+6281313099914');
$contact_address = getSetting('contact_address', 'Central Jakarta, Indonesia');
$operating_hours = getSetting('operating_hours', 'Mon-Fri: 9 AM - 5 PM');

// Get social media links
$social_media_json = getSetting('social_media', '{}');
$social_media = json_decode($social_media_json, true) ?: [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error_message)) {
    // Sanitize and validate input
    $name = trim($_POST['name'] ?? '');
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $email = trim($_POST['email'] ?? '');
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $subject = trim($_POST['subject'] ?? '');
    $subject = filter_var($subject, FILTER_SANITIZE_STRING);
    $message = trim($_POST['message'] ?? '');
    $message = filter_var($message, FILTER_SANITIZE_STRING);
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    } elseif (strlen($name) > 100) {
        $errors[] = 'Name is too long (max 100 characters)';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    } elseif (strlen($email) > 255) {
        $errors[] = 'Email is too long';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    } elseif (strlen($subject) > 200) {
        $errors[] = 'Subject is too long (max 200 characters)';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    } elseif (strlen($message) > 2000) {
        $errors[] = 'Message is too long (max 2000 characters)';
    }
    
    // If no errors, save to database
    if (empty($errors) && $conn) {
        try {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, ip_address) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$name, $email, $subject, $message, $_SERVER['REMOTE_ADDR'] ?? ''])) {
                // Update rate limiting
                $_SESSION[$rate_limit_key] = [
                    'count' => ($_SESSION[$rate_limit_key]['count'] ?? 0) + 1,
                    'time' => time()
                ];
                
                // Send email notification (improved security)
                $to = $contact_email;
                $email_subject = "New Contact Form Message: " . substr($subject, 0, 50);
                $email_body = "Name: " . htmlspecialchars($name) . "\n";
                $email_body .= "Email: " . htmlspecialchars($email) . "\n";
                $email_body .= "Subject: " . htmlspecialchars($subject) . "\n";
                $email_body .= "IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";
                $email_body .= "Date: " . date('Y-m-d H:i:s') . "\n\n";
                $email_body .= "Message:\n" . htmlspecialchars($message) . "\n";
                
                // Secure headers
                $headers = "From: " . $contact_email . "\r\n";
                $headers .= "Reply-To: " . $email . "\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                
                // Use error suppression but log errors
                if (!@mail($to, $email_subject, $email_body, $headers)) {
                    error_log("Failed to send contact form email to: " . $to);
                }
                
                $success_message = 'Thank you! Your message has been sent successfully. I\'ll get back to you soon.';
                
                // Clear form data
                $name = $email = $subject = $message = '';
            } else {
                $error_message = 'Sorry, there was an error sending your message. Please try again.';
            }
        } catch (Exception $e) {
            error_log("Error saving contact message: " . $e->getMessage());
            $error_message = 'Sorry, there was an error sending your message. Please try again.';
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Set page variables
$page_title = "Contact Us - " . $site_name;
$page_description = "Get in touch with us. We'd love to hear from you and answer any questions you might have.";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="contact, get in touch, support, help, wiracenter">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/contact.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Success Popup -->
    <?php if (!empty($success_message)): ?>
    <div class="popup-success show" id="popupSuccess">
        <i class="fas fa-check-circle me-2" style="font-size:2rem;"></i>
        Message sent successfully
    </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-12 text-center hero-content">
                    <h1 data-i18n="contact.title">Contact Me</h1>
                    <p class="hero-subtitle" data-i18n="contact.subtitle">I'm ready to help you. Please fill out the form below to get in touch.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container"> <!-- [FIXED: markup typo, was <div class="..."> -->
            <h2 class="section-title" data-i18n="contact.contact_info">Contact Information</h2>
            
            <!-- Contact Info and Form Row -->
            <div class="row align-items-stretch">
                <!-- Contact Info Cards - Left Side -->
                <div class="col-lg-5 mb-4 d-flex">
                    <div class="contact-info-stack w-100">
                        <div class="contact-info-card">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h3 class="contact-info-title" data-i18n="contact.email">Email</h3>
                            <p class="contact-info-text">
                                <a href="mailto:<?php echo htmlspecialchars($contact_email); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($contact_email); ?>
                                </a>
                            </p>
                        </div>
                        
                        <div class="contact-info-card">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <h3 class="contact-info-title" data-i18n="contact.phone">Phone</h3>
                            <p class="contact-info-text">
                                <a href="tel:<?php echo str_replace([' ', '+'], ['', ''], $contact_phone); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($contact_phone); ?>
                                </a>
                            </p>
                        </div>
                        
                        <div class="contact-info-card">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h3 class="contact-info-title" data-i18n="contact.location">Location</h3>
                            <p class="contact-info-text"><?php echo htmlspecialchars($contact_address); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form - Right Side -->
                <div class="col-lg-7 d-flex">
                    <div class="contact-form-card w-100">
                        <h3 class="text-center mb-4" style="color: var(--dark-cyan); font-weight: 600;" data-i18n="contact.send_message">Send Me a Message</h3>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <div><?php echo $error_message; ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="contactForm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" data-i18n="contact.name">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($name ?? ''); ?>" required maxlength="100">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" data-i18n="contact.email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($email ?? ''); ?>" required maxlength="255">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject" data-i18n="contact.subject">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       value="<?php echo htmlspecialchars($subject ?? ''); ?>" required maxlength="200">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="message" data-i18n="contact.message">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="6" 
                                          placeholder="Tell me about your project, question, or just say hello..." data-i18n-placeholder="contact.placeholder" required maxlength="2000"><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <button type="submit" class="btn btn-submit" id="submitBtn" data-i18n="contact.send">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <?php if (!empty($faqs)): ?>
    <section class="faq-section">
        <div class="container">
            <h2 class="section-title" data-i18n="contact.faq">Frequently Asked Questions</h2>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <?php foreach ($faqs as $index => $faq): ?>
                            <div class="faq-card">
                                <div class="faq-header" id="faqHeading<?php echo $index; ?>" 
                                     data-bs-toggle="collapse" 
                                     data-bs-target="#faqCollapse<?php echo $index; ?>" 
                                     aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                     aria-controls="faqCollapse<?php echo $index; ?>">
                                    <h4 class="faq-title" data-i18n="faq.q<?php echo $index+1; ?>"><?php echo htmlspecialchars($faq['title']); ?></h4>
                                    <i class="fas fa-chevron-down faq-icon"></i>
                                </div>
                                
                                <div id="faqCollapse<?php echo $index; ?>" 
                                     class="collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                                     aria-labelledby="faqHeading<?php echo $index; ?>" 
                                     data-bs-parent="#faqAccordion">
                                    <div class="faq-body" data-i18n="faq.a<?php echo $index+1; ?>">
                                        <?php echo nl2br(htmlspecialchars($faq['content'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Social Links Section -->
    <?php if (!empty($social_media)): ?>
    <section class="py-5" style="background: var(--gradient-primary);">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h3 class="text-white mb-4" data-i18n="contact.connect_with_me">Connect With Me</h3>
                    <div class="social-links">
                        <?php if (isset($social_media['instagram'])): ?>
                            <a href="<?php echo htmlspecialchars($social_media['instagram']); ?>" class="social-link" target="_blank" rel="noopener noreferrer" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (isset($social_media['linkedin'])): ?>
                            <a href="<?php echo htmlspecialchars($social_media['linkedin']); ?>" class="social-link" target="_blank" rel="noopener noreferrer" title="LinkedIn">
                                <i class="fab fa-linkedin"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (isset($social_media['github'])): ?>
                            <a href="<?php echo htmlspecialchars($social_media['github']); ?>" class="social-link" target="_blank" rel="noopener noreferrer" title="GitHub">
                                <i class="fab fa-github"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (isset($social_media['threads'])): ?>
                            <a href="<?php echo htmlspecialchars($social_media['threads']); ?>" class="social-link" target="_blank" rel="noopener noreferrer" title="Threads">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-threads" viewBox="0 0 16 16">
                                  <path d="M6.321 6.016c-.27-.18-1.166-.802-1.166-.802.756-1.081 1.753-1.502 3.132-1.502.975 0 1.803.327 2.394.948s.928 1.509 1.005 2.644q.492.207.905.484c1.109.745 1.719 1.86 1.719 3.137 0 2.716-2.226 5.075-6.256 5.075C4.594 16 1 13.987 1 7.994 1 2.034 4.482 0 8.044 0 9.69 0 13.55.243 15 5.036l-1.36.353C12.516 1.974 10.163 1.43 8.006 1.43c-3.565 0-5.582 2.171-5.582 6.79 0 4.143 2.254 6.343 5.63 6.343 2.777 0 4.847-1.443 4.847-3.556 0-1.438-1.208-2.127-1.27-2.127-.236 1.234-.868 3.31-3.644 3.31-1.618 0-3.013-1.118-3.013-2.582 0-2.09 1.984-2.847 3.55-2.847.586 0 1.294.04 1.663.114 0-.637-.54-1.728-1.9-1.728-1.25 0-1.566.405-1.967.868ZM8.716 8.19c-2.04 0-2.304.87-2.304 1.416 0 .878 1.043 1.168 1.6 1.168 1.02 0 2.067-.282 2.232-2.423a6.2 6.2 0 0 0-1.528-.161"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/contact.js"></script>
    
<!-- AUTO HIDE NAVBAR COLLAPSE (khusus contact.php, dengan animasi fade out) -->
    <script>
(function() {
  var mainNavbar = document.getElementById('mainNavbar');
  var autoHideTimeout = null;
  if (mainNavbar) {
    mainNavbar.addEventListener('shown.bs.collapse', function () {
      if (autoHideTimeout) clearTimeout(autoHideTimeout);
      autoHideTimeout = setTimeout(function() {
        mainNavbar.classList.add('auto-hide-fade');
        setTimeout(function() {
          var bsCollapse = bootstrap.Collapse.getOrCreateInstance(mainNavbar);
          bsCollapse.hide();
        }, 300); // durasi animasi fade
      }, 2700); // 2.7 detik + 0.3 detik animasi = 3 detik total
    });
    mainNavbar.addEventListener('hide.bs.collapse', function () {
      if (autoHideTimeout) clearTimeout(autoHideTimeout);
      mainNavbar.classList.remove('auto-hide-fade');
    });
    mainNavbar.addEventListener('hidden.bs.collapse', function () {
      mainNavbar.classList.remove('auto-hide-fade');
    });
  }
})();
</script>

</body>
</html>
