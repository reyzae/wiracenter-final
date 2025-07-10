<?php
require_once 'config/config.php';

$db = new Database();
$conn = $db->connect();

// Get site settings
$site_name = getSetting('site_name', 'Wiracenter');
$contact_email = getSetting('contact_email', 'info@wiracenter.com');
$contact_phone = getSetting('contact_phone', '+1234567890');

$success_message = '';
$error_message = '';

// Get content for the contact page from the database
$page_content = null;
try {
    $stmt = $conn->prepare("SELECT * FROM pages WHERE slug = 'contact' AND status = 'published'");
    $stmt->execute();
    $page_content = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Table pages not found in database.';
    $page_content = null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Save to database
        try {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $subject, $message])) {
                $success_message = 'Thank you for your message! I will get back to you soon.';
                // Send email notification (optional)
                $to = $contact_email;
                $email_subject = "New Contact Form Message: $subject";
                $email_body = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";
                $headers = "From: $email\r\nReply-To: $email\r\n";
                @mail($to, $email_subject, $email_body, $headers);
            } else {
                $error_message = 'Sorry, there was an error sending your message. Please try again.';
            }
        } catch (PDOException $e) {
            $error_message = 'Table contact_messages not found in database.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="main-content" style="margin-left:0;">
    <!-- Page Header -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 mb-4">Get in Touch</h1>
                    <p class="lead">I'd love to hear from you. Send me a message and I'll respond as soon as possible.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Dynamic Page Content (if available) -->
    <?php if ($page_content): ?>
        <section class="py-5">
            <div class="container">
                <?php echo $page_content['content']; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <!-- Alert Messages -->
                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Contact Form -->
                    <div class="contact-form">
                        <form method="POST" id="contactForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Info Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="section-title">Contact Information</h2>
                </div>
            </div>
            <div class="row">
                <?php
                $contact_info_blocks = [];
                $stmt = $conn->prepare("SELECT * FROM content_blocks WHERE type = 'contact_info_card' AND page_slug = 'contact' AND status = 'active' ORDER BY display_order ASC");
                $stmt->execute();
                $contact_info_blocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($contact_info_blocks as $block) {
                ?>
                    <div class="col-md-4">
                        <div class="text-center mb-4">
                            <div class="contact-icon">
                                <i class="<?php echo $block['icon_class']; ?> fa-3x text-primary mb-3"></i>
                            </div>
                            <h4><?php echo $block['title']; ?></h4>
                            <p class="text-muted">
                                <?php echo nl2br($block['content']); // Use nl2br for simple text, or just echo if content is HTML ?>
                            </p>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="section-title">Frequently Asked Questions</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        <?php
                        $faq_blocks = [];
                        $stmt = $conn->prepare("SELECT * FROM content_blocks WHERE type = 'faq_item' AND page_slug = 'contact' AND status = 'active' ORDER BY display_order ASC");
                        $stmt->execute();
                        $faq_blocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (!empty($faq_blocks)) {
                            foreach ($faq_blocks as $index => $block) {
                                $collapse_id = 'collapse' . $block['id'];
                                $heading_id = 'faq' . $block['id'];
                                $is_show = ($index === 0) ? 'show' : ''; // Show first FAQ by default
                        ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="<?php echo $heading_id; ?>">
                                        <button class="accordion-button <?php echo $is_show ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapse_id; ?>">
                                            <?php echo $block['title']; // Question is stored in title ?>
                                        </button>
                                    </h2>
                                    <div id="<?php echo $collapse_id; ?>" class="accordion-collapse collapse <?php echo $is_show; ?>" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            <?php echo $block['content']; // Answer is stored in content ?>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        } else {
                            echo '<p class="text-center text-muted">No FAQs available at the moment.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php include 'includes/footer.php'; ?>