<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';

// Initialize database connection
$db = new Database();
$conn = $db->getConnection();

// Initialize variables
$contact_info = [];
$faqs = [];
$success_message = '';
$error_message = '';

// Get contact information from content blocks
try {
    $stmt = $conn->prepare("SELECT title, content, icon_class FROM content_blocks WHERE type = 'contact_info_card' AND page_slug = 'contact' AND status = 'active' ORDER BY display_order");
    $stmt->execute();
    $contact_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching contact info: " . $e->getMessage());
}

// Get FAQs from content blocks
try {
    $stmt = $conn->prepare("SELECT title, content FROM content_blocks WHERE type = 'faq_item' AND page_slug = 'contact' AND status = 'active' ORDER BY display_order");
    $stmt->execute();
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching FAQs: " . $e->getMessage());
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$name, $email, $subject, $message])) {
                // Send email notification
                $to = $contact_email;
                $email_subject = "New Contact Form Message: $subject";
                $email_body = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";
                $headers = "From: $email\r\nReply-To: $email\r\n";
                
                @mail($to, $email_subject, $email_body, $headers);
                
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo $site_name; ?></title>
    <meta name="description" content="Get in touch with us. We'd love to hear from you and answer any questions you might have.">
    <meta name="keywords" content="contact, get in touch, support, help, wiracenter">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Custom CSS for Contact Page -->
    <style>
        :root {
            --primary-cyan: #00BCD4;
            --secondary-cyan: #0097A7;
            --accent-cyan: #26C6DA;
            --light-cyan: #B2EBF2;
            --dark-cyan: #00695C;
            --gradient-primary: linear-gradient(135deg, var(--primary-cyan), var(--secondary-cyan));
            --gradient-secondary: linear-gradient(135deg, var(--accent-cyan), var(--light-cyan));
        }

        body {
            font-family: 'Fira Sans', Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .hero-section {
            background: var(--gradient-primary);
            color: white;
            padding: 80px 0 60px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .contact-section {
            padding: 80px 0;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--dark-cyan);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--gradient-primary);
            border-radius: 2px;
        }

        .contact-info-stack {
            display: flex;
            flex-direction: column;
            gap: 15px;
            height: 100%;
        }

        .contact-info-card {
            background: white;
            border-radius: 20px;
            padding: 20px 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: none;
            flex: 1;
            min-height: 0;
        }

        .contact-info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .contact-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            color: white;
        }

        .contact-info-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark-cyan);
        }

        .contact-info-text {
            color: #666;
            font-size: 1rem;
            line-height: 1.4;
        }

        .contact-form-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
            height: 100%;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-cyan);
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            border-color: var(--primary-cyan);
            box-shadow: 0 0 0 0.2rem rgba(0, 188, 212, 0.25);
            background: white;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .btn-submit {
            background: var(--gradient-primary);
            border: none;
            border-radius: 50px;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 188, 212, 0.4);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            transform: none;
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
        }

        .faq-section {
            padding: 80px 0;
            background: white;
        }

        .faq-card {
            background: white;
            border-radius: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
        }

        .faq-header {
            background: var(--gradient-primary);
            color: white;
            padding: 20px 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-header:hover {
            background: var(--secondary-cyan);
        }

        .faq-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        .faq-icon {
            transition: transform 0.3s ease;
        }

        .faq-header[aria-expanded="true"] .faq-icon {
            transform: rotate(180deg);
        }

        .faq-body {
            padding: 25px;
            color: #666;
            line-height: 1.6;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .social-link {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }

        .social-link:hover {
            transform: translateY(-5px) scale(1.1);
            color: white;
            box-shadow: 0 10px 25px rgba(0, 188, 212, 0.4);
        }

        .map-section {
            padding: 60px 0;
            background: #f8f9fa;
        }

        .map-container {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .map-placeholder {
            background: var(--gradient-secondary);
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            text-align: center;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner-border {
            color: var(--primary-cyan);
        }

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: none;
            min-width: 300px;
            max-width: 400px;
        }

        .toast-header {
            background: var(--gradient-primary);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
            padding: 15px 20px;
        }

        .toast-header .btn-close {
            filter: invert(1);
        }

        .toast-body {
            padding: 20px;
            color: #333;
            font-size: 1rem;
            line-height: 1.5;
        }

        .toast-success {
            border-left: 4px solid #28a745;
        }

        .toast-success .toast-header {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .toast-error {
            border-left: 4px solid #dc3545;
        }

        .toast-error .toast-header {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
        }

        .toast-icon {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* Popup Success Notification */
        .popup-success {
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translate(-50%, 0);
            background: #fff;
            color: #28a745;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            padding: 1.5rem 2.5rem;
            z-index: 99999;
            display: flex;
            align-items: center;
            font-size: 1.2rem;
            font-weight: 600;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.5s;
        }
        .popup-success.show {
            opacity: 1;
            pointer-events: auto;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .contact-form-card {
                padding: 30px 20px;
            }
            
            .contact-info-stack {
                margin-bottom: 30px;
                height: auto;
            }
            
            .contact-info-card {
                margin-bottom: 20px;
                flex: none;
            }
            
            .col-lg-5.d-flex,
            .col-lg-7.d-flex {
                flex-direction: column;
            }
        }

        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 40px;
            height: 40px;
            top: 40%;
            left: 80%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Success Popup -->
    <?php if (!empty($success_message)): ?>
    <div class="popup-success show" id="popupSuccess">
        <i class="fas fa-check-circle me-2" style="font-size:2rem;"></i>
        Pesan telah terkirim
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
                    <h1 data-i18n="contact.title">Hubungi Saya</h1>
                    <p class="hero-subtitle" data-i18n="contact.subtitle">Kami siap membantu Anda. Silakan isi form di bawah ini untuk menghubungi kami.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
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
                                <a href="mailto:<?php echo $contact_email; ?>" class="text-decoration-none">
                                    <?php echo $contact_email; ?>
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
                                    <?php echo $contact_phone; ?>
                                </a>
                            </p>
                        </div>
                        
                        <div class="contact-info-card">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h3 class="contact-info-title" data-i18n="contact.location">Location</h3>
                            <p class="contact-info-text"><?php echo $contact_address; ?></p>
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
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" data-i18n="contact.name">Nama</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" data-i18n="contact.email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject" data-i18n="contact.subject">Subjek</label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="message" data-i18n="contact.message">Pesan</label>
                                <textarea class="form-control" id="message" name="message" rows="6" 
                                          placeholder="Tell me about your project, question, or just say hello..." data-i18n-placeholder="contact.placeholder" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <button type="submit" class="btn btn-submit" data-i18n="contact.send">Kirim Pesan</button>
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
                            <a href="<?php echo $social_media['instagram']; ?>" class="social-link" target="_blank" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (isset($social_media['linkedin'])): ?>
                            <a href="<?php echo $social_media['linkedin']; ?>" class="social-link" target="_blank" title="LinkedIn">
                                <i class="fab fa-linkedin"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (isset($social_media['github'])): ?>
                            <a href="<?php echo $social_media['github']; ?>" class="social-link" target="_blank" title="GitHub">
                                <i class="fab fa-github"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (isset($social_media['threads'])): ?>
                            <a href="<?php echo $social_media['threads']; ?>" class="social-link" target="_blank" title="Threads">
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
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('contactForm');
            const submitBtn = document.getElementById('submitBtn');
            
            // Form validation
            function validateForm() {
                let isValid = true;
                const requiredFields = ['name', 'email', 'subject', 'message'];
                
                requiredFields.forEach(fieldName => {
                    const field = document.getElementById(fieldName);
                    const feedback = field.nextElementSibling;
                    
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        feedback.textContent = 'This field is required.';
                        isValid = false;
                    } else if (fieldName === 'email' && !isValidEmail(field.value)) {
                        field.classList.add('is-invalid');
                        feedback.textContent = 'Please enter a valid email address.';
                        isValid = false;
                    } else {
                        field.classList.remove('is-invalid');
                        feedback.textContent = '';
                    }
                });
                
                return isValid;
            }
            
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }
            
            // Real-time validation
            const inputs = form.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    const feedback = this.nextElementSibling;
                    
                    if (!this.value.trim()) {
                        this.classList.add('is-invalid');
                        feedback.textContent = 'This field is required.';
                    } else if (this.type === 'email' && !isValidEmail(this.value)) {
                        this.classList.add('is-invalid');
                        feedback.textContent = 'Please enter a valid email address.';
                    } else {
                        this.classList.remove('is-invalid');
                        feedback.textContent = '';
                    }
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid')) {
                        const feedback = this.nextElementSibling;
                        if (this.value.trim() && (this.type !== 'email' || isValidEmail(this.value))) {
                            this.classList.remove('is-invalid');
                            feedback.textContent = '';
                        }
                    }
                });
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    return;
                }
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
                
                // Re-enable after a delay (in case of errors)
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Message';
                }, 10000);
            });
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Add hover effects for contact info cards
            document.querySelectorAll('.contact-info-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 5000);
            });

            // Toast notification functions
            function showToast(message, type = 'success', duration = 5000) {
                const toastContainer = document.getElementById('toastContainer');
                const toastId = 'toast-' + Date.now();
                
                const toastHTML = `
                    <div class="toast toast-${type}" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} toast-icon"></i>
                            <strong class="me-auto">${type === 'success' ? 'Success!' : 'Error!'}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${message}
                        </div>
                    </div>
                `;
                
                toastContainer.insertAdjacentHTML('beforeend', toastHTML);
                
                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement, {
                    autohide: true,
                    delay: duration
                });
                
                toast.show();
                
                // Remove toast element after it's hidden
                toastElement.addEventListener('hidden.bs.toast', function() {
                    toastElement.remove();
                });
            }

            // Show success toast if there's a success message from PHP
            <?php if (!empty($success_message)): ?>
            setTimeout(() => {
                showToast('<?php echo addslashes($success_message); ?>', 'success', 6000);
            }, 500);
            <?php endif; ?>

            // Show error toast if there's an error message from PHP
            <?php if (!empty($error_message)): ?>
            setTimeout(() => {
                showToast('<?php echo addslashes($error_message); ?>', 'error', 8000);
            }, 500);
            <?php endif; ?>

            // After reload, if success_message exists, show checkmark animation
            <?php if (!empty($success_message)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                // Hide spinner, show checkmark and success text
                var spinnerIcon = document.getElementById('spinnerIcon');
                var loadingText = document.getElementById('loadingText');
                if (spinnerIcon && loadingText) {
                    spinnerIcon.outerHTML = '<span style="font-size:2.5rem;color:#28a745;"><i class="fas fa-check-circle"></i></span>';
                    loadingText.textContent = 'Pesan telah terkirim';
                    setTimeout(function() {
                        loadingSpinner.style.opacity = '0';
                        loadingSpinner.style.transition = 'opacity 0.5s';
                        setTimeout(function() { loadingSpinner.style.display = 'none'; }, 500);
                    }, 2500);
                }
            });
            <?php endif; ?>

            // Fade popup success if exists
            <?php if (!empty($success_message)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var popup = document.getElementById('popupSuccess');
                if (popup) {
                    setTimeout(function() {
                        popup.classList.remove('show');
                    }, 2500);
                }
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>
