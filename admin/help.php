<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Help & Docs';
include 'includes/header.php';

// =====================
// FAQ & Guide Data (English)
// =====================
$faqs = [
    [
        'q' => 'How do I add a new user?',
        'a' => 'Go to the Users menu, click Create User, fill in the details, then click Create User.'
    ],
    [
        'q' => 'How do I backup the database?',
        'a' => 'Go to the Backup & Restore menu, click the Backup Now button. The backup file will appear in the list.'
    ],
    [
        'q' => 'How do I restore the database?',
        'a' => 'Only admin can restore. Click the Restore button on the desired backup file.'
    ],
    [
        'q' => 'How do I export data to Excel?',
        'a' => 'Go to the Export Data menu, select the data type and CSV format, then click Export.'
    ],
    [
        'q' => 'How do I change the website appearance?',
        'a' => 'Go to the Site Settings menu and adjust the settings as needed.'
    ],
];

$guides = [
    [
        'title' => 'Basic Admin Guide',
        'steps' => [
            'Log in to the admin dashboard using your username and password.',
            'Use the sidebar menu to manage content, users, files, and settings.',
            'Always backup the database before making major changes.',
            'Use the Export Data feature to regularly save important data.',
        ]
    ],
    [
        'title' => 'Security Tips',
        'steps' => [
            'Never share your admin password with others.',
            'Use a strong and unique password.',
            'Log out after finishing your session in the dashboard.',
            'Regularly backup your database.',
        ]
    ],
];

$troubleshoot = [
    [
        'problem' => 'Cannot log in to admin',
        'solution' => 'Make sure your username and password are correct. If you forgot your password, contact the super admin.'
    ],
    [
        'problem' => 'Database error / Table not found',
        'solution' => 'Check the database connection in the .env file and config/database.php. Make sure the database has been imported.'
    ],
    [
        'problem' => 'File upload failed',
        'solution' => 'Make sure the file size does not exceed the limit and the file format is allowed.'
    ],
    [
        'problem' => 'Backup/restore failed',
        'solution' => 'Ensure the server allows mysqldump/mysql execution. Check the permissions of the backups/ folder.'
    ],
];

$support = [
    'email' => getSetting('contact_email', 'info@wiracenter.com'),
    'phone' => getSetting('contact_phone', '+6281313099914'),
    'wa' => 'https://wa.me/6281313099914',
];

// =====================
// Tab Navigation
// =====================
$tab = $_GET['tab'] ?? 'faq';
$tabs = [
    'faq' => 'FAQ',
    'guide' => 'Guide',
    'troubleshoot' => 'Troubleshooting',
    'contact' => 'Contact',
];
if (!isset($tabs[$tab])) $tab = 'faq';
?>
<div class="container-fluid">
    <h1 class="h2 mb-4"><i class="fas fa-question-circle me-2"></i>Help & Documentation</h1>
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4">
        <?php foreach ($tabs as $k => $label): ?>
            <li class="nav-item">
                <a class="nav-link<?php if ($tab == $k) echo ' active'; ?>" href="?tab=<?php echo $k; ?>"><?php echo $label; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
    <!-- Tab Content -->
    <div class="card">
        <div class="card-body">
            <?php if ($tab == 'faq'): ?>
                <!-- FAQ Section -->
                <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Frequently Asked Questions</h5>
                <div class="accordion" id="faqAccordion">
                    <?php foreach ($faqs as $i => $f): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqHeading<?php echo $i; ?>">
                                <button class="accordion-button<?php if ($i > 0) echo ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse<?php echo $i; ?>" aria-expanded="<?php echo $i == 0 ? 'true' : 'false'; ?>" aria-controls="faqCollapse<?php echo $i; ?>">
                                    <?php echo htmlspecialchars($f['q']); ?>
                                </button>
                            </h2>
                            <div id="faqCollapse<?php echo $i; ?>" class="accordion-collapse collapse<?php if ($i == 0) echo ' show'; ?>" aria-labelledby="faqHeading<?php echo $i; ?>" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?php echo htmlspecialchars($f['a']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($tab == 'guide'): ?>
                <!-- Guide Section -->
                <h5 class="mb-3"><i class="fas fa-book me-2"></i>Admin Guide</h5>
                <?php foreach ($guides as $g): ?>
                    <div class="mb-4">
                        <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($g['title']); ?></h6>
                        <ol class="mb-0">
                            <?php foreach ($g['steps'] as $step): ?>
                                <li><?php echo htmlspecialchars($step); ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                <?php endforeach; ?>
            <?php elseif ($tab == 'troubleshoot'): ?>
                <!-- Troubleshooting Section -->
                <h5 class="mb-3"><i class="fas fa-tools me-2"></i>Troubleshooting</h5>
                <div class="row">
                    <?php foreach ($troubleshoot as $t): ?>
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-warning h-100">
                                <b><i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($t['problem']); ?></b><br>
                                <span><?php echo htmlspecialchars($t['solution']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($tab == 'contact'): ?>
                <!-- Contact Section -->
                <h5 class="mb-3"><i class="fas fa-envelope me-2"></i>Contact Support</h5>
                <div class="mb-3">
                    <b>Email:</b> <a href="mailto:<?php echo htmlspecialchars($support['email']); ?>"><?php echo htmlspecialchars($support['email']); ?></a><br>
                    <b>Phone/WA:</b> <a href="<?php echo $support['wa']; ?>" target="_blank"><?php echo htmlspecialchars($support['phone']); ?></a>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>For further assistance, please contact the support above or check the official Wiracenter documentation.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
