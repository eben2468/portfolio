<?php
/**
 * Nadics Digital Solution — Admin Settings
 */

$admin_page = 'settings';
$admin_title = 'System Settings';

require_once __DIR__ . '/includes/auth.php';

$db = getDB();
$flash_message = '';
$flash_type = '';

// Check session for flash message
if (isset($_SESSION['flash_msg'])) {
    $flash_message = $_SESSION['flash_msg'];
    $flash_type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
}

// Handle settings submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf)) {
        $flash_message = 'Security token invalid. Action blocked.';
        $flash_type = 'error';
    } else {
        $settings = $_POST['settings'] ?? [];
        
        if ($db && !empty($settings)) {
            try {
                $db->beginTransaction();
                
                $stmt = $db->prepare("
                    INSERT INTO site_settings (setting_key, setting_value)
                    VALUES (:key, :value)
                    ON DUPLICATE KEY UPDATE setting_value = :value_update
                ");
                
                foreach ($settings as $key => $value) {
                    $stmt->execute([
                        ':key'          => $key,
                        ':value'        => trim($value),
                        ':value_update' => trim($value)
                    ]);
                }
                
                $db->commit();
                logActivity($_SESSION['admin_id'], 'Updated system settings', 'site_settings', null, 'Bulk update');
                
                $_SESSION['flash_msg'] = 'Settings saved successfully.';
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . ADMIN_URL . '/settings.php');
                exit;
            } catch (PDOException $e) {
                $db->rollBack();
                $flash_message = 'Error saving settings: ' . $e->getMessage();
                $flash_type = 'error';
            }
        }
    }
}

// Load current settings from database
$settings = [];
if ($db) {
    try {
        $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        $flash_message = 'Database query error: ' . $e->getMessage();
        $flash_type = 'error';
    }
}

// Default settings fallback
$defaults = [
    'site_name' => 'Nadics Digital Solution',
    'site_tagline' => 'Transforming Ideas Into Digital Reality',
    'site_email' => 'info@nadicsdigital.com',
    'site_phone' => '+233 24 000 0000',
    'site_address' => 'Accra, Ghana',
    'business_hours' => 'Mon – Fri: 8:00 AM – 6:00 PM | Sat: 9:00 AM – 2:00 PM',
    'facebook_url' => '#',
    'twitter_url' => '#',
    'instagram_url' => '#',
    'linkedin_url' => '#',
    'tiktok_url' => '#',
    'stats_projects' => '120',
    'stats_clients' => '85',
    'stats_years' => '5',
    'stats_services' => '7'
];

$current_settings = array_merge($defaults, $settings);

require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Flash Alerts -->
<?php if (!empty($flash_message)): ?>
    <div class="admin-alert admin-alert--<?= $flash_type ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <?php if ($flash_type === 'success'): ?>
                <polyline points="20 6 9 17 4 12"/>
            <?php else: ?>
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            <?php endif; ?>
        </svg>
        <span><?= sanitize($flash_message) ?></span>
    </div>
<?php endif; ?>

<form action="" method="POST" class="admin-form">
    <?= csrfField() ?>
    
    <div class="settings-grid">
        <!-- Left Column: General & Contact -->
        <div>
            <!-- Card: General Settings -->
            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">General Configuration</h2>
                </div>
                <div class="admin-card__body">
                    <div class="admin-form__group">
                        <label for="site_name" class="admin-form__label">Business / Website Name</label>
                        <input type="text" name="settings[site_name]" id="site_name" class="admin-form__input" value="<?= sanitize($current_settings['site_name']) ?>" required>
                    </div>
                    <div class="admin-form__group">
                        <label for="site_tagline" class="admin-form__label">Website Tagline</label>
                        <input type="text" name="settings[site_tagline]" id="site_tagline" class="admin-form__input" value="<?= sanitize($current_settings['site_tagline']) ?>" required>
                    </div>
                </div>
            </div>

            <!-- Card: Contact Settings -->
            <div class="admin-card">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">Contact Information</h2>
                </div>
                <div class="admin-card__body">
                    <div class="admin-form__group">
                        <label for="site_email" class="admin-form__label">Contact Email Address</label>
                        <input type="email" name="settings[site_email]" id="site_email" class="admin-form__input" value="<?= sanitize($current_settings['site_email']) ?>" required>
                    </div>
                    <div class="admin-form__group">
                        <label for="site_phone" class="admin-form__label">Contact Phone Number</label>
                        <input type="text" name="settings[site_phone]" id="site_phone" class="admin-form__input" value="<?= sanitize($current_settings['site_phone']) ?>" required>
                    </div>
                    <div class="admin-form__group">
                        <label for="site_address" class="admin-form__label">Business Location Address</label>
                        <input type="text" name="settings[site_address]" id="site_address" class="admin-form__input" value="<?= sanitize($current_settings['site_address']) ?>" required>
                    </div>
                    <div class="admin-form__group">
                        <label for="business_hours" class="admin-form__label">Business / Working Hours</label>
                        <input type="text" name="settings[business_hours]" id="business_hours" class="admin-form__input" value="<?= sanitize($current_settings['business_hours']) ?>" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Social & Counters -->
        <div>
            <!-- Card: Social Links -->
            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">Social Media Links</h2>
                </div>
                <div class="admin-card__body">
                    <div class="admin-form__group">
                        <label for="facebook_url" class="admin-form__label">Facebook Profile URL</label>
                        <input type="text" name="settings[facebook_url]" id="facebook_url" class="admin-form__input" value="<?= sanitize($current_settings['facebook_url']) ?>">
                    </div>
                    <div class="admin-form__group">
                        <label for="twitter_url" class="admin-form__label">Twitter/X Profile URL</label>
                        <input type="text" name="settings[twitter_url]" id="twitter_url" class="admin-form__input" value="<?= sanitize($current_settings['twitter_url']) ?>">
                    </div>
                    <div class="admin-form__group">
                        <label for="instagram_url" class="admin-form__label">Instagram Profile URL</label>
                        <input type="text" name="settings[instagram_url]" id="instagram_url" class="admin-form__input" value="<?= sanitize($current_settings['instagram_url']) ?>">
                    </div>
                    <div class="admin-form__group">
                        <label for="linkedin_url" class="admin-form__label">LinkedIn Profile URL</label>
                        <input type="text" name="settings[linkedin_url]" id="linkedin_url" class="admin-form__input" value="<?= sanitize($current_settings['linkedin_url']) ?>">
                    </div>
                    <div class="admin-form__group">
                        <label for="tiktok_url" class="admin-form__label">TikTok Profile URL</label>
                        <input type="text" name="settings[tiktok_url]" id="tiktok_url" class="admin-form__input" value="<?= sanitize($current_settings['tiktok_url']) ?>">
                    </div>
                </div>
            </div>

            <!-- Card: Statistics Counters -->
            <div class="admin-card">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">Statistics Counters (Home Page)</h2>
                </div>
                <div class="admin-card__body">
                    <div class="admin-form__row">
                        <div class="admin-form__group">
                            <label for="stats_projects" class="admin-form__label">Projects Completed</label>
                            <input type="number" name="settings[stats_projects]" id="stats_projects" class="admin-form__input" min="0" value="<?= (int)$current_settings['stats_projects'] ?>" required>
                        </div>
                        <div class="admin-form__group">
                            <label for="stats_clients" class="admin-form__label">Happy Clients</label>
                            <input type="number" name="settings[stats_clients]" id="stats_clients" class="admin-form__input" min="0" value="<?= (int)$current_settings['stats_clients'] ?>" required>
                        </div>
                    </div>
                    <div class="admin-form__row">
                        <div class="admin-form__group">
                            <label for="stats_years" class="admin-form__label">Years of Experience</label>
                            <input type="number" name="settings[stats_years]" id="stats_years" class="admin-form__input" min="0" value="<?= (int)$current_settings['stats_years'] ?>" required>
                        </div>
                        <div class="admin-form__group">
                            <label for="stats_services" class="admin-form__label">Services Offered</label>
                            <input type="number" name="settings[stats_services]" id="stats_services" class="admin-form__input" min="0" value="<?= (int)$current_settings['stats_services'] ?>" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Settings Button -->
    <div class="admin-form__actions" style="margin-top: 1.5rem; justify-content: flex-end;">
        <button type="submit" class="admin-btn admin-btn--primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save All Settings
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
