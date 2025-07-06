<?php
$page_title = 'Site Settings';
include 'includes/header.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $settings = [
            'site_name' => sanitize($_POST['site_name']),
            'site_description' => sanitize($_POST['site_description']),
            'site_keywords' => sanitize($_POST['site_keywords']),
            'site_logo' => sanitize($_POST['site_logo'] ?? ''), // Assuming you might add an input for this later
            'site_favicon' => sanitize($_POST['site_favicon'] ?? ''),
            'hero_title' => sanitize($_POST['hero_title']),
            'hero_subtitle' => sanitize($_POST['hero_subtitle']),
            'about_title' => sanitize($_POST['about_title']),
            'about_content' => $_POST['about_content'],
            'contact_email' => sanitize($_POST['contact_email']),
            'contact_phone' => sanitize($_POST['contact_phone']),
            'contact_address' => sanitize($_POST['contact_address'] ?? ''),
            'operating_hours' => sanitize($_POST['operating_hours'] ?? ''),
            'social_media' => json_encode([
                'instagram' => sanitize($_POST['instagram']),
                'threads' => sanitize($_POST['threads']),
                'linkedin' => sanitize($_POST['linkedin']),
                'github' => sanitize($_POST['github'])
            ]),
            'theme_mode' => sanitize($_POST['theme_mode'] ?? 'light'),
            'debug_mode' => sanitize($_POST['debug_mode'] ?? '0'),
            'google_analytics_id' => sanitize($_POST['google_analytics_id'] ?? ''),
            'maintenance_mode' => sanitize($_POST['maintenance_mode'] ?? '0'),
            'maintenance_message' => sanitize($_POST['maintenance_message'] ?? ''),
            'maintenance_countdown' => sanitize($_POST['maintenance_countdown'] ?? ''),
            'log_retention_days' => sanitize($_POST['log_retention_days'] ?? '30')
        ];
        
        // Update each setting
        foreach ($settings as $key => $value) {
            setSetting($key, $value);
        }
        
        $success_message = 'Settings updated successfully!';
        
    } catch (Exception $e) {
        $error_message = 'Error updating settings: ' . $e->getMessage();
    }
}

// Get current settings
$current_settings = [
    'site_name' => getSetting('site_name', 'Wiracenter'),
    'site_description' => getSetting('site_description', 'Personal Portfolio Website'),
    'site_keywords' => getSetting('site_keywords', 'portfolio, web development, programming'),
    'hero_title' => getSetting('hero_title', 'Welcome to Wiracenter'),
    'hero_subtitle' => getSetting('hero_subtitle', 'Your Digital Solutions Partner'),
    'about_title' => getSetting('about_title', 'About Me'),
    'about_content' => getSetting('about_content', 'I am a passionate web developer...'),
    'contact_email' => getSetting('contact_email', 'contact@wiracenter.com'),
    'contact_phone' => getSetting('contact_phone', '+1234567890'),
    'social_media' => json_decode(getSetting('social_media', '{}'), true)
];
?>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<h1 class="h2 mb-4">Site Settings</h1>

<form method="POST">
    <div class="row">
        <div class="col-md-8">
            <!-- General Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">General Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="site_name" class="form-label">Site Name</label>
                                <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo $current_settings['site_name']; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contact_email" class="form-label">Contact Email</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo $current_settings['contact_email']; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="site_description" class="form-label">Site Description</label>
                        <textarea class="form-control" id="site_description" name="site_description" rows="3"><?php echo $current_settings['site_description']; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="site_keywords" class="form-label">SEO Keywords</label>
                        <input type="text" class="form-control" id="site_keywords" name="site_keywords" value="<?php echo $current_settings['site_keywords']; ?>">
                        <small class="form-text text-muted">Separate keywords with commas</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact_phone" class="form-label">Contact Phone</label>
                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo getSetting('contact_phone', ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact_address" class="form-label">Contact Address</label>
                        <textarea class="form-control" id="contact_address" name="contact_address" rows="3"><?php echo getSetting('contact_address', ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="operating_hours" class="form-label">Operating Hours</label>
                        <input type="text" class="form-control" id="operating_hours" name="operating_hours" value="<?php echo getSetting('operating_hours', ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="site_favicon" class="form-label">Site Favicon URL</label>
                        <input type="text" class="form-control" id="site_favicon" name="site_favicon" value="<?php echo getSetting('site_favicon', ''); ?>">
                        <small class="form-text text-muted">URL to your favicon (e.g., /assets/images/favicon.ico)</small>
                    </div>
                </div>
            </div>
            
            <!-- Homepage Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Homepage Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="hero_title" class="form-label">Hero Title</label>
                        <input type="text" class="form-control" id="hero_title" name="hero_title" value="<?php echo $current_settings['hero_title']; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="hero_subtitle" class="form-label">Hero Subtitle</label>
                        <input type="text" class="form-control" id="hero_subtitle" name="hero_subtitle" value="<?php echo $current_settings['hero_subtitle']; ?>">
                    </div>
                </div>
            </div>
            
            <!-- About Page Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">About Page Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="about_title" class="form-label">About Title</label>
                        <input type="text" class="form-control" id="about_title" name="about_title" value="<?php echo $current_settings['about_title']; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="about_content" class="form-label">About Content</label>
                        <textarea class="form-control tinymce" id="about_content" name="about_content" rows="10"><?php echo $current_settings['about_content']; ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Social Media Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Social Media Links</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="instagram" class="form-label">Instagram URL</label>
                                <input type="url" class="form-control" id="instagram" name="instagram" value="<?php echo $current_settings['social_media']['instagram'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="threads" class="form-label">Threads URL</label>
                                <input type="url" class="form-control" id="threads" name="threads" value="<?php echo $current_settings['social_media']['threads'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="linkedin" class="form-label">LinkedIn URL</label>
                                <input type="url" class="form-control" id="linkedin" name="linkedin" value="<?php echo $current_settings['social_media']['linkedin'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="github" class="form-label">GitHub URL</label>
                                <input type="url" class="form-control" id="github" name="github" value="<?php echo $current_settings['social_media']['github'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Theme Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Theme Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Theme Mode</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="theme_mode" id="theme_light" value="light" <?php echo (getSetting('theme_mode', 'light') == 'light') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="theme_light">Light</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="theme_mode" id="theme_dark" value="dark" <?php echo (getSetting('theme_mode', 'light') == 'dark') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="theme_dark">Dark</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Debug Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Debug Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Debug Mode</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="debug_mode" id="debug_on" value="1" <?php echo (getSetting('debug_mode', '0') == '1') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="debug_on">On</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="debug_mode" id="debug_off" value="0" <?php echo (getSetting('debug_mode', '0') == '0') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="debug_off">Off</label>
                            </div>
                        </div>
                        <small class="form-text text-muted">Turning debug mode on will display PHP errors. Use only for development.</small>
                    </div>
                </div>
            </div>
        </div>

            <!-- Google Analytics Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Google Analytics Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="google_analytics_id" class="form-label">Google Analytics Tracking ID</label>
                        <input type="text" class="form-control" id="google_analytics_id" name="google_analytics_id" value="<?php echo getSetting('google_analytics_id', ''); ?>">
                        <small class="form-text text-muted">e.g., UA-XXXXX-Y or G-XXXXXXXXXX</small>
                    </div>
                </div>
            </div>

            <!-- Maintenance Mode Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Maintenance Mode Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Enable Maintenance Mode</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maintenance_mode" id="maintenance_on" value="1" <?php echo (getSetting('maintenance_mode', '0') == '1') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="maintenance_on">On</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maintenance_mode" id="maintenance_off" value="0" <?php echo (getSetting('maintenance_mode', '0') == '0') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="maintenance_off">Off</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="maintenance_message" class="form-label">Maintenance Message</label>
                        <textarea class="form-control" id="maintenance_message" name="maintenance_message" rows="3"><?php echo getSetting('maintenance_message', 'Our website is currently undergoing scheduled maintenance. We will be back shortly!'); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="maintenance_countdown" class="form-label">Countdown End Time (YYYY-MM-DD HH:MM:SS)</label>
                        <input type="text" class="form-control" id="maintenance_countdown" name="maintenance_countdown" value="<?php echo getSetting('maintenance_countdown', ''); ?>" placeholder="e.g., 2025-12-31 23:59:59">
                        <small class="form-text text-muted">Optional. Leave empty to not show a countdown.</small>
                    </div>
                </div>
            </div>

            <!-- Database Cleanup Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Database Cleanup Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="log_retention_days" class="form-label">Activity Log Retention (Days)</label>
                        <input type="number" class="form-control" id="log_retention_days" name="log_retention_days" value="<?php echo getSetting('log_retention_days', '30'); ?>" min="0">
                        <small class="form-text text-muted">Number of days to retain activity logs. Set to 0 for indefinite retention.</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Save Settings -->
            <div class="card mb-4 sticky-top">
                <div class="card-header">
                    <h6 class="card-title mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                    
                    <a href="../index.php" class="btn btn-outline-primary w-100 mb-3" target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i>Preview Site
                    </a>
                    
                    <hr>
                    
                    <h6>Quick Links</h6>
                    <div class="list-group list-group-flush">
                        <a href="articles.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-newspaper me-2"></i>Manage Articles
                        </a>
                        <a href="projects.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-code me-2"></i>Manage Projects
                        </a>
                        <a href="files.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-folder me-2"></i>Manage Files
                        </a>
                        <a href="users.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- System Info -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">System Information</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?><br>
                        <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?><br>
                        <strong>Database:</strong> MySQL<br>
                        <strong>Upload Max Size:</strong> <?php echo ini_get('upload_max_filesize'); ?><br>
                        <strong>Time Zone:</strong> <?php echo date_default_timezone_get(); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</form>

<?php include 'includes/footer.php'; ?>