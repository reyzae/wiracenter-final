<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Site Settings';
include 'includes/header.php';

$db = new Database();
$conn = $db->connect();

$success_message = '';
$error_message = '';

// Fetch all settings
$stmt = $conn->query("SELECT * FROM site_settings ORDER BY id ASC");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($settings as $key => $setting) {
        $val = $_POST[$key] ?? '';
        if ($setting['setting_type'] === 'image') {
            if (!empty($_FILES[$key]['name'])) {
                $uploadDir = '../assets/img/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION);
                $filename = $key . '_' . time() . '.' . $ext;
                $filepath = $uploadDir . $filename;
                if (move_uploaded_file($_FILES[$key]['tmp_name'], $filepath)) {
                    $val = 'assets/img/' . $filename;
                } else {
                    $error_message .= 'Failed to upload image for ' . $key . '. ';
                    continue;
                }
            } else {
                $val = $setting['setting_value'];
            }
        } elseif ($setting['setting_type'] === 'json') {
            $json_val = json_decode($val, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error_message .= 'Invalid JSON for ' . $key . '. ';
                continue;
            }
            $val = json_encode($json_val);
        } elseif ($setting['setting_type'] === 'text' && isset($_POST[$key])) {
            $val = trim($_POST[$key]);
        } elseif ($setting['setting_type'] === 'textarea' && isset($_POST[$key])) {
            $val = trim($_POST[$key]);
        }
        $stmt2 = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
        $stmt2->execute([$val, $key]);
    }
    if (!$error_message) {
        $success_message = 'Settings updated successfully!';
    }
    // Refresh settings after update
    header('Location: settings.php?msg=' . urlencode($success_message ?: $error_message));
    ob_end_clean();
    exit();
}
if (isset($_GET['msg'])) {
    $success_message = urldecode($_GET['msg']);
}
// Group settings by category
$categories = [
    'General' => ['site_name', 'site_description', 'site_keywords', 'site_logo', 'site_favicon'], // Pengaturan umum website (nama, deskripsi, logo, favicon, SEO)
    'Homepage' => ['hero_title', 'hero_subtitle'], // Konten utama di halaman depan
    'About' => ['about_title', 'about_content'], // Konten halaman About
    'Contact' => ['contact_email', 'contact_phone', 'contact_address', 'operating_hours'], // Info kontak dan jam operasional
    'Social' => ['social_media'], // Link sosial media (format JSON)
    'Theme' => ['theme_mode'], // Mode tema (light/dark)
    'Maintenance' => ['maintenance_mode', 'maintenance_message', 'maintenance_countdown'], // Mode & pesan maintenance
    'Advanced' => ['google_analytics_id', 'debug_mode', 'log_retention_days'], // Pengaturan lanjutan (analytics, debug, log)
];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-cog me-2"></i>Site Settings</h1>
    </div>
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <div class="accordion" id="settingsAccordion">
            <?php $catIndex = 0; foreach (
                $categories as $cat => $fields):
                // Hide About section from settings UI
                if ($cat === 'About') {
            ?>
                <div class="accordion-item mb-3">
                    <h2 class="accordion-header" id="heading<?php echo $catIndex; ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $catIndex; ?>" aria-expanded="false" aria-controls="collapse<?php echo $catIndex; ?>">
                            <?php echo $cat; ?>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $catIndex; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $catIndex; ?>" data-bs-parent="#settingsAccordion">
                        <div class="accordion-body">
                            <div class="alert alert-warning mb-0">
                                <strong>Perhatian:</strong> Konten halaman <b>About</b> yang tampil ke publik <u>tidak diambil dari sini</u>.<br>
                                Silakan edit halaman About melalui menu <b>Pages</b> (slug: <code>about</code>) di Content Management.<br>
                                <span class="text-muted">(Bagian ini hanya untuk referensi lama, tidak digunakan di website publik.)</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php $catIndex++; continue; }
            ?>
                <div class="accordion-item mb-3">
                    <h2 class="accordion-header" id="heading<?php echo $catIndex; ?>">
                        <button class="accordion-button<?php if ($catIndex > 0) echo ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $catIndex; ?>" aria-expanded="<?php echo $catIndex === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $catIndex; ?>">
                            <?php echo $cat; ?>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $catIndex; ?>" class="accordion-collapse collapse<?php if ($catIndex === 0) echo ' show'; ?>" aria-labelledby="heading<?php echo $catIndex; ?>" data-bs-parent="#settingsAccordion">
                        <div class="accordion-body">
                            <?php // Tambahkan penjelasan kategori di sini ?>
                            <?php if ($cat === 'General'): ?>
                                <div class="alert alert-info py-2 mb-3">Pengaturan umum website: nama, deskripsi, logo, favicon, dan SEO.</div>
                            <?php elseif ($cat === 'Homepage'): ?>
                                <div class="alert alert-info py-2 mb-3">Konten utama yang tampil di halaman depan website.</div>
                            <?php elseif ($cat === 'About'): ?>
                                <div class="alert alert-info py-2 mb-3">Konten yang tampil di halaman About.</div>
                            <?php elseif ($cat === 'Contact'): ?>
                                <div class="alert alert-info py-2 mb-3">Informasi kontak, email, telepon, alamat, dan jam operasional.</div>
                            <?php elseif ($cat === 'Social'): ?>
                                <div class="alert alert-info py-2 mb-3">Link ke sosial media (format JSON, contoh: {"instagram": "url", "linkedin": "url"}).</div>
                            <?php elseif ($cat === 'Theme'): ?>
                                <div class="alert alert-info py-2 mb-3">Pengaturan mode tema website (light/dark).</div>
                            <?php elseif ($cat === 'Maintenance'): ?>
                                <div class="alert alert-info py-2 mb-3">Mode maintenance, pesan, dan countdown jika website sedang perbaikan.</div>
                            <?php elseif ($cat === 'Advanced'): ?>
                                <div class="alert alert-info py-2 mb-3">Pengaturan lanjutan: Google Analytics, debug mode, dan retensi log.</div>
                            <?php endif; ?>
                            <?php foreach ($fields as $key): if (!isset($settings[$key])) continue; $setting = $settings[$key]; ?>
                                <div class="mb-3 row align-items-center">
                                    <label class="col-md-3 col-form-label fw-bold" for="<?php echo $key; ?>"><?php echo ucwords(str_replace('_', ' ', $key)); ?></label>
                                    <div class="col-md-9">
                                        <?php if ($setting['setting_type'] === 'text'): ?>
                                            <input type="text" class="form-control" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                        <?php elseif ($setting['setting_type'] === 'textarea'): ?>
                                            <textarea class="form-control" id="<?php echo $key; ?>" name="<?php echo $key; ?>" rows="3"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                        <?php elseif ($setting['setting_type'] === 'image'): ?>
                                            <?php if (!empty($setting['setting_value'])): ?>
                                                <img src="../<?php echo $setting['setting_value']; ?>" alt="<?php echo $key; ?>" style="max-height:60px;max-width:120px;" class="mb-2 d-block">
                                            <?php endif; ?>
                                            <input type="file" class="form-control" id="<?php echo $key; ?>" name="<?php echo $key; ?>">
                                        <?php elseif ($setting['setting_type'] === 'json'): ?>
                                            <textarea class="form-control" id="<?php echo $key; ?>" name="<?php echo $key; ?>" rows="3"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                            <small class="text-muted">Format: JSON. Contoh: {"instagram": "url", "linkedin": "url"}</small>
                                        <?php elseif ($setting['setting_type'] === 'text' && ($key === 'theme_mode' || $key === 'maintenance_mode' || $key === 'debug_mode')): ?>
                                            <select class="form-select" id="<?php echo $key; ?>" name="<?php echo $key; ?>">
                                                <?php if ($key === 'theme_mode'): ?>
                                                    <option value="light" <?php if ($setting['setting_value'] === 'light') echo 'selected'; ?>>Light</option>
                                                    <option value="dark" <?php if ($setting['setting_value'] === 'dark') echo 'selected'; ?>>Dark</option>
                                                <?php elseif ($key === 'maintenance_mode' || $key === 'debug_mode'): ?>
                                                    <option value="1" <?php if ($setting['setting_value'] == '1') echo 'selected'; ?>>On</option>
                                                    <option value="0" <?php if ($setting['setting_value'] == '0') echo 'selected'; ?>>Off</option>
                                                <?php endif; ?>
                                            </select>
                                        <?php else: ?>
                                            <input type="text" class="form-control" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php $catIndex++; endforeach; ?>
        </div>
        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Save Settings</button>
        </div>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
