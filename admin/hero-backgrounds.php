<?php
/**
 * Nadics Digital Solution — Admin Hero Background Overlays
 * Upload / manage the background overlay image behind each page's hero.
 * Images are stored in site_settings and rendered on the public pages.
 */

$admin_page  = 'hero-backgrounds';
$admin_title = 'Hero Backgrounds';

require_once __DIR__ . '/includes/auth.php';

$db = getDB();
$flash_message = '';
$flash_type = '';

// Flash from a previous redirect
if (isset($_SESSION['flash_msg'])) {
    $flash_message = $_SESSION['flash_msg'];
    $flash_type    = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
}

$hero_fields   = heroBackgrounds();
$hero_defaults = heroBackgroundDefaults();
$allowed_exts  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$upload_dir    = __DIR__ . '/../assets/images/bg-overlay/';
// Store a domain-relative path (not SITE_URL) so the value stays portable
// across environments (local XAMPP, staging, production).
$upload_url    = 'assets/images/bg-overlay/';

// ─── Handle submission ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verifyCSRFToken($csrf)) {
        $flash_message = 'Security token invalid. Action blocked.';
        $flash_type = 'error';
    } else {
        $to_save = [];   // key => value to write to site_settings
        $errors  = [];

        foreach ($hero_fields as $key => $label) {
            // 1) Explicit "remove" clears the overlay for this hero
            if (!empty($_POST['remove'][$key])) {
                $to_save[$key] = '';
                continue;
            }

            // 2) A newly uploaded file wins over everything else
            if (isset($_FILES['bg_file']['name'][$key]) && $_FILES['bg_file']['error'][$key] === UPLOAD_ERR_OK) {
                $name = $_FILES['bg_file']['name'][$key];
                $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed_exts, true)) {
                    $errors[] = "$label: invalid image type (use JPG, PNG, WEBP or GIF).";
                    continue;
                }
                if ($_FILES['bg_file']['size'][$key] > 15 * 1024 * 1024) {
                    $errors[] = "$label: image is too large (max 15MB).";
                    continue;
                }
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $new_filename = uniqid($key . '_', true) . '.' . $ext;
                if (move_uploaded_file($_FILES['bg_file']['tmp_name'][$key], $upload_dir . $new_filename)) {
                    $to_save[$key] = $upload_url . $new_filename;
                } else {
                    $errors[] = "$label: failed to save the uploaded image.";
                }
                continue;
            }

            // 3) Otherwise keep whatever URL was posted in the text field
            if (isset($_POST['settings'][$key])) {
                $to_save[$key] = trim($_POST['settings'][$key]);
            }
        }

        if ($errors) {
            $flash_message = implode(' ', $errors);
            $flash_type = 'error';
        } elseif ($db && $to_save) {
            try {
                $db->beginTransaction();
                $stmt = $db->prepare("
                    INSERT INTO site_settings (setting_key, setting_value)
                    VALUES (:key, :value)
                    ON DUPLICATE KEY UPDATE setting_value = :value_update
                ");
                foreach ($to_save as $key => $value) {
                    if (!array_key_exists($key, $hero_fields)) continue; // whitelist
                    $stmt->execute([':key' => $key, ':value' => $value, ':value_update' => $value]);
                }
                $db->commit();
                logActivity($_SESSION['admin_id'], 'Updated hero backgrounds', 'site_settings', null, 'Hero overlay images');

                $_SESSION['flash_msg']  = 'Hero backgrounds saved successfully.';
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . ADMIN_URL . '/hero-backgrounds.php');
                exit;
            } catch (PDOException $e) {
                $db->rollBack();
                $flash_message = 'Error saving backgrounds: ' . $e->getMessage();
                $flash_type = 'error';
            }
        }
    }
}

// ─── Load current values ─────────────────────────────────────────────
$saved = [];
if ($db) {
    try {
        $saved = $db->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        $flash_message = 'Database query error: ' . $e->getMessage();
        $flash_type = 'error';
    }
}

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

<div class="admin-alert admin-alert--info" style="margin-bottom: 1.5rem;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    <span>Upload a background image for each page's hero banner. A dark brand overlay is applied automatically so headings stay readable. Landscape images around <strong>1920×800px</strong> look best. Leave a hero empty to keep the default gradient.</span>
</div>

<form action="" method="POST" enctype="multipart/form-data" class="admin-form">
    <?= csrfField() ?>

    <div class="settings-grid">
        <?php foreach ($hero_fields as $key => $label):
            // Show the effective value: a saved value (even empty) wins over
            // the shipped default so the admin preview matches the live site.
            $current_url = array_key_exists($key, $saved) ? $saved[$key] : ($hero_defaults[$key] ?? '');
            $has_image   = $current_url !== '';
        ?>
        <div class="admin-card" style="margin-bottom: 1.5rem;">
            <div class="admin-card__header">
                <h2 class="admin-card__title"><?= sanitize($label) ?></h2>
            </div>
            <div class="admin-card__body">

                <!-- Live preview of the current overlay -->
                <div class="hero-bg-preview" id="preview_<?= $key ?>"
                     style="<?= $has_image ? "background-image:url('" . htmlspecialchars($current_url, ENT_QUOTES) . "');" : '' ?>">
                    <span class="hero-bg-preview__label"><?= $has_image ? 'Current overlay' : 'No image — default gradient' ?></span>
                </div>

                <!-- Upload dropzone -->
                <div class="image-upload" style="margin-top: 1rem;" onclick="document.getElementById('file_<?= $key ?>').click();">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <div class="image-upload__text">Click to upload an image</div>
                    <div class="image-upload__hint">JPG, PNG, WEBP or GIF up to 15MB</div>
                    <input type="file" name="bg_file[<?= $key ?>]" id="file_<?= $key ?>" style="display:none;" accept="image/*"
                           onchange="previewHeroBg(this, '<?= $key ?>')">
                </div>

                <!-- Or paste a URL -->
                <div class="admin-form__group" style="margin-top: 1rem;">
                    <label for="url_<?= $key ?>" class="admin-form__label">Or use an image URL</label>
                    <input type="text" name="settings[<?= $key ?>]" id="url_<?= $key ?>" class="admin-form__input"
                           value="<?= htmlspecialchars($current_url, ENT_QUOTES) ?>" placeholder="https://…">
                </div>

                <?php if ($has_image): ?>
                <label class="admin-form__checkbox" style="display:flex; align-items:center; gap:0.5rem; margin-top:0.75rem; font-size:0.85rem;">
                    <input type="checkbox" name="remove[<?= $key ?>]" value="1">
                    Remove this background (revert to default gradient)
                </label>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="admin-form__actions" style="margin-top: 0.5rem; justify-content: flex-end;">
        <button type="submit" class="admin-btn admin-btn--primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save Hero Backgrounds
        </button>
    </div>
</form>

<style>
    .hero-bg-preview {
        position: relative;
        height: 130px;
        border-radius: var(--radius-md);
        background-color: #1a1130;
        background-size: cover;
        background-position: center;
        overflow: hidden;
        display: flex;
        align-items: flex-end;
    }
    /* Same wash the front-end applies, so the admin preview matches the site */
    .hero-bg-preview::after {
        content: '';
        position: absolute;
        inset: 0;
        background:
            linear-gradient(180deg, rgba(13,11,26,0.55) 0%, rgba(13,11,26,0.85) 100%),
            linear-gradient(120deg, rgba(71,23,219,0.45) 0%, rgba(26,17,48,0.30) 45%, rgba(167,57,179,0.35) 100%);
    }
    .hero-bg-preview__label {
        position: relative;
        z-index: 1;
        margin: 0.6rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #fff;
        text-shadow: 0 1px 4px rgba(0,0,0,0.6);
    }
</style>

<script>
    function previewHeroBg(input, key) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            const box = document.getElementById('preview_' + key);
            box.style.backgroundImage = "url('" + e.target.result + "')";
            const label = box.querySelector('.hero-bg-preview__label');
            if (label) label.textContent = 'New image selected (saves when you submit)';
        };
        reader.readAsDataURL(input.files[0]);
    }
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
