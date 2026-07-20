<?php
/**
 * Nadics Digital Solution — Admin Home Page Content Editor
 * Edits all text/content on the public home page (stored in site_settings).
 */

$admin_page = 'homepage';
$admin_title = 'Home Page Content';

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

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verifyCSRFToken($csrf)) {
        $flash_message = 'Security token invalid. Action blocked.';
        $flash_type = 'error';
    } else {
        $settings = $_POST['settings'] ?? [];

        // Hero visual: build the selected image list from the gallery checkboxes
        $hero_slides_posted = $_POST['hero_slides'] ?? [];
        if (is_array($hero_slides_posted)) {
            $hero_slides_posted = array_values(array_filter(array_map('trim', $hero_slides_posted)));
            $settings['home_hero_slides'] = implode("\n", $hero_slides_posted);
            $settings['home_hero_image']  = $hero_slides_posted[0] ?? '';
        }

        // Optional: handle About image upload (overrides the URL field)
        if (isset($_FILES['about_image_file']) && $_FILES['about_image_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['about_image_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'svg', 'webp', 'gif'];

            if (!in_array($ext, $allowed_exts)) {
                $flash_message = 'Invalid image type. Allowed formats: JPG, PNG, WEBP, SVG, GIF.';
                $flash_type = 'error';
            } elseif ($file['size'] > 15 * 1024 * 1024) {
                $flash_message = 'Image is too large. Maximum size is 15MB.';
                $flash_type = 'error';
            } else {
                $upload_dir = __DIR__ . '/../assets/images/home/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $new_filename = uniqid('about_', true) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
                    $settings['home_about_image'] = SITE_URL . '/assets/images/home/' . $new_filename;
                } else {
                    $flash_message = 'Failed to save uploaded image.';
                    $flash_type = 'error';
                }
            }
        }

        // Optional: handle Hero design image upload (overrides the selected preset)
        if (isset($_FILES['hero_image_file']) && $_FILES['hero_image_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['hero_image_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'svg', 'webp', 'gif'];

            if (!in_array($ext, $allowed_exts, true)) {
                $flash_message = 'Invalid hero image type. Allowed formats: PNG, JPG, WEBP, SVG, GIF.';
                $flash_type = 'error';
            } elseif ($file['size'] > 15 * 1024 * 1024) {
                $flash_message = 'Hero image is too large. Maximum size is 15MB.';
                $flash_type = 'error';
            } else {
                $upload_dir = __DIR__ . '/../assets/images/png-design/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $new_filename = uniqid('hero_', true) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
                    $new_url = SITE_URL . '/assets/images/png-design/' . rawurlencode($new_filename);
                    // Add the new upload to the front of the chosen slide list
                    $existing = ($settings['home_hero_slides'] ?? '') !== ''
                        ? explode("\n", $settings['home_hero_slides']) : [];
                    array_unshift($existing, $new_url);
                    $existing = array_values(array_unique(array_filter(array_map('trim', $existing))));
                    $settings['home_hero_slides'] = implode("\n", $existing);
                    $settings['home_hero_image']  = $new_url;
                    $settings['home_hero_visual'] = 'image'; // switch to the new upload automatically
                } else {
                    $flash_message = 'Failed to save the uploaded hero image.';
                    $flash_type = 'error';
                }
            }
        }

        if ($db && !empty($settings) && $flash_type !== 'error') {
            try {
                $db->beginTransaction();
                $stmt = $db->prepare("
                    INSERT INTO site_settings (setting_key, setting_value)
                    VALUES (:key, :value)
                    ON DUPLICATE KEY UPDATE setting_value = :value_update
                ");
                foreach ($settings as $key => $value) {
                    // Only allow known home page keys to be written here
                    if (!array_key_exists($key, homepageDefaults())) continue;
                    $val = trim($value);
                    $stmt->execute([':key' => $key, ':value' => $val, ':value_update' => $val]);
                }
                $db->commit();
                logActivity($_SESSION['admin_id'], 'Updated home page content', 'site_settings', null, 'Home page bulk update');

                $_SESSION['flash_msg'] = 'Home page content saved successfully.';
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . ADMIN_URL . '/homepage.php');
                exit;
            } catch (PDOException $e) {
                $db->rollBack();
                $flash_message = 'Error saving content: ' . $e->getMessage();
                $flash_type = 'error';
            }
        }
    }
}

// Load current values merged over defaults
$saved = [];
if ($db) {
    try {
        $saved = $db->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        $flash_message = 'Database query error: ' . $e->getMessage();
        $flash_type = 'error';
    }
}
$current = array_merge(homepageDefaults(), $saved);

// Small helper to print a value safely into an attribute/textarea
function hp(array $current, string $key): string {
    return sanitize($current[$key] ?? '');
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
    <span>Edit every text block on the home page here. The <strong>service cards</strong> come from <a href="<?= ADMIN_URL ?>/services.php">Services</a>, <strong>featured projects</strong> from <a href="<?= ADMIN_URL ?>/portfolio.php">Portfolio</a>, <strong>testimonials</strong> from <a href="<?= ADMIN_URL ?>/testimonials.php">Testimonials</a>, and the <strong>stat numbers</strong> from <a href="<?= ADMIN_URL ?>/settings.php">Settings</a>.</span>
</div>

<form action="" method="POST" enctype="multipart/form-data" class="admin-form">
    <?= csrfField() ?>

    <!-- ═══ Hero ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Hero Section</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__group">
                <label class="admin-form__label">Badge Text</label>
                <input type="text" name="settings[home_hero_badge]" class="admin-form__input" value="<?= hp($current, 'home_hero_badge') ?>">
            </div>
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Line 1</label>
                    <input type="text" name="settings[home_hero_title_1]" class="admin-form__input" value="<?= hp($current, 'home_hero_title_1') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Line 2 Prefix</label>
                    <input type="text" name="settings[home_hero_title_2_prefix]" class="admin-form__input" value="<?= hp($current, 'home_hero_title_2_prefix') ?>">
                    <span class="admin-form__help">Shown before the animated word, e.g. “That”.</span>
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Animated (Typed) Words</label>
                <input type="text" name="settings[home_hero_typed_words]" class="admin-form__input" value="<?= hp($current, 'home_hero_typed_words') ?>">
                <span class="admin-form__help">Comma-separated. These rotate with the typing animation.</span>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Description</label>
                <textarea name="settings[home_hero_desc]" class="admin-form__textarea"><?= hp($current, 'home_hero_desc') ?></textarea>
            </div>
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Primary Button — Label</label>
                    <input type="text" name="settings[home_hero_btn1_label]" class="admin-form__input" value="<?= hp($current, 'home_hero_btn1_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Primary Button — Link</label>
                    <input type="text" name="settings[home_hero_btn1_url]" class="admin-form__input" value="<?= hp($current, 'home_hero_btn1_url') ?>">
                </div>
            </div>
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Secondary Button — Label</label>
                    <input type="text" name="settings[home_hero_btn2_label]" class="admin-form__input" value="<?= hp($current, 'home_hero_btn2_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Secondary Button — Link</label>
                    <input type="text" name="settings[home_hero_btn2_url]" class="admin-form__input" value="<?= hp($current, 'home_hero_btn2_url') ?>">
                </div>
            </div>
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Stat 1 Label</label>
                    <input type="text" name="settings[home_hero_stat1_label]" class="admin-form__input" value="<?= hp($current, 'home_hero_stat1_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Stat 2 Label</label>
                    <input type="text" name="settings[home_hero_stat2_label]" class="admin-form__input" value="<?= hp($current, 'home_hero_stat2_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Stat 3 Label</label>
                    <input type="text" name="settings[home_hero_stat3_label]" class="admin-form__input" value="<?= hp($current, 'home_hero_stat3_label') ?>">
                </div>
            </div>
            <label class="admin-form__label">Floating Icons (emoji)</label>
            <div class="admin-form__row">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                <div class="admin-form__group">
                    <input type="text" name="settings[home_hero_icon_<?= $i ?>]" class="admin-form__input" style="text-align:center; font-size:1.2rem;" value="<?= hp($current, 'home_hero_icon_' . $i) ?>" maxlength="8">
                </div>
                <?php endfor; ?>
            </div>
            <span class="admin-form__help">Six emojis that float around the globe.</span>
        </div>
    </div>

    <!-- ═══ Hero Visual / Graphic ═══ -->
    <?php
        $hero_visual   = $current['home_hero_visual'] ?? 'globe';
        $hero_slides_v = $current['home_hero_slides'] ?? '';
        $hero_slides_a = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $hero_slides_v))));
        if (empty($hero_slides_a) && !empty($current['home_hero_image'])) {
            $hero_slides_a = [$current['home_hero_image']];
        }
        $icons_show     = ($current['home_hero_icons_show'] ?? '1') !== '0';
        $img_size       = (int)($current['home_hero_image_size'] ?? 100); if ($img_size <= 0) $img_size = 100;
        $slide_interval = (int)($current['home_hero_slide_interval'] ?? 4); if ($slide_interval < 1) $slide_interval = 4;
        $hero_designs   = heroDesignImages();
    ?>
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Hero Visual / Graphic</h2></div>
        <div class="admin-card__body">
            <p class="admin-form__help" style="margin-bottom: 1rem;">Choose what shows on the right side of the hero: the built-in animated <strong>Globe</strong>, a single <strong>Image</strong>, or a <strong>Slideshow</strong> that rotates through several images.</p>

            <!-- Toggle: Globe / Single Image / Slideshow -->
            <div class="hero-visual-toggle hero-visual-toggle--three">
                <label class="hero-visual-toggle__option <?= $hero_visual === 'globe' ? 'is-active' : '' ?>">
                    <input type="radio" name="settings[home_hero_visual]" value="globe" <?= $hero_visual === 'globe' ? 'checked' : '' ?>>
                    <span class="hero-visual-toggle__title">🌐 Globe</span>
                    <span class="hero-visual-toggle__sub">Built-in animated design (default)</span>
                </label>
                <label class="hero-visual-toggle__option <?= $hero_visual === 'image' ? 'is-active' : '' ?>">
                    <input type="radio" name="settings[home_hero_visual]" value="image" <?= $hero_visual === 'image' ? 'checked' : '' ?>>
                    <span class="hero-visual-toggle__title">🖼️ Single Image</span>
                    <span class="hero-visual-toggle__sub">Show one selected image</span>
                </label>
                <label class="hero-visual-toggle__option <?= $hero_visual === 'slideshow' ? 'is-active' : '' ?>">
                    <input type="radio" name="settings[home_hero_visual]" value="slideshow" <?= $hero_visual === 'slideshow' ? 'checked' : '' ?>>
                    <span class="hero-visual-toggle__title">🎞️ Slideshow</span>
                    <span class="hero-visual-toggle__sub">Rotate through several images</span>
                </label>
            </div>

            <!-- Display options -->
            <div class="hero-visual-options">
                <!-- Orbiting icons on/off -->
                <div class="hero-visual-option">
                    <label class="admin-form__label" style="margin:0;">Orbiting Emoji Icons</label>
                    <label class="hero-switch">
                        <input type="hidden" name="settings[home_hero_icons_show]" value="0">
                        <input type="checkbox" name="settings[home_hero_icons_show]" value="1" <?= $icons_show ? 'checked' : '' ?>>
                        <span class="hero-switch__track"><span class="hero-switch__thumb"></span></span>
                        <span class="hero-switch__label"><?= $icons_show ? 'Shown' : 'Hidden' ?></span>
                    </label>
                    <span class="admin-form__help">Toggle the six floating icons on or off.</span>
                </div>

                <!-- Image size -->
                <div class="hero-visual-option">
                    <label class="admin-form__label" for="heroImgSize" style="margin:0;">Image Size — <span id="heroImgSizeVal"><?= $img_size ?></span>%</label>
                    <input type="range" id="heroImgSize" name="settings[home_hero_image_size]" min="40" max="220" step="5" value="<?= $img_size ?>"
                           style="width:100%;" oninput="document.getElementById('heroImgSizeVal').textContent = this.value;">
                    <span class="admin-form__help">Enlarge small images or shrink large ones. 100% fills the area.</span>
                </div>

                <!-- Slideshow interval -->
                <div class="hero-visual-option">
                    <label class="admin-form__label" for="heroInterval" style="margin:0;">Slideshow Speed (seconds)</label>
                    <input type="number" id="heroInterval" name="settings[home_hero_slide_interval]" min="2" max="30" step="1" value="<?= $slide_interval ?>" class="admin-form__input">
                    <span class="admin-form__help">How long each slide shows before changing.</span>
                </div>
            </div>

            <!-- Image gallery (multi-select) -->
            <label class="admin-form__label" style="margin-top: 1.25rem;">Image Designs</label>
            <span class="admin-form__help" style="display:block; margin-bottom:0.75rem;">Tick the image(s) you want. <strong>Single Image</strong> uses the first ticked image; <strong>Slideshow</strong> rotates through all ticked images.</span>
            <div class="hero-design-gallery">
                <?php if (empty($hero_designs)): ?>
                    <p class="admin-form__help">No image designs found yet. Upload one below.</p>
                <?php else: foreach ($hero_designs as $i => $design):
                    $checked = in_array($design['url'], $hero_slides_a, true);
                ?>
                <label class="hero-design-card <?= $checked ? 'is-active' : '' ?>">
                    <input type="checkbox" name="hero_slides[]" value="<?= sanitize($design['url']) ?>" <?= $checked ? 'checked' : '' ?>>
                    <img src="<?= sanitize($design['url']) ?>" alt="Design <?= $i + 1 ?>" loading="lazy">
                    <span class="hero-design-card__check">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                </label>
                <?php endforeach; endif; ?>
            </div>

            <!-- Upload a new design -->
            <label class="admin-form__label" style="margin-top: 1.25rem;">Upload a New Design</label>
            <div class="image-upload" onclick="document.getElementById('hero_image_file').click();">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <div class="image-upload__text">Click to upload a PNG design</div>
                <div class="image-upload__hint">Transparent PNG works best • up to 15MB</div>
                <input type="file" name="hero_image_file" id="hero_image_file" style="display:none;" accept="image/*" onchange="previewHeroDesign(this)">
            </div>
            <div class="image-preview" id="heroDesignPreview" style="display:none; margin-top:0.75rem;">
                <img id="heroDesignPreviewImg" src="" alt="New design preview" style="max-height: 180px; border-radius: var(--radius-md);">
                <span class="admin-form__help" id="heroDesignPreviewHelp" style="display:block; margin-top:0.25rem;"></span>
            </div>
        </div>
    </div>

    <!-- ═══ Services Preview ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Services Section Heading</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Small Label</label>
                    <input type="text" name="settings[home_services_label]" class="admin-form__input" value="<?= hp($current, 'home_services_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title</label>
                    <input type="text" name="settings[home_services_title]" class="admin-form__input" value="<?= hp($current, 'home_services_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Highlighted Part</label>
                    <input type="text" name="settings[home_services_title_highlight]" class="admin-form__input" value="<?= hp($current, 'home_services_title_highlight') ?>">
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Description</label>
                <textarea name="settings[home_services_desc]" class="admin-form__textarea"><?= hp($current, 'home_services_desc') ?></textarea>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">“View All” Button Label</label>
                <input type="text" name="settings[home_services_btn_label]" class="admin-form__input" value="<?= hp($current, 'home_services_btn_label') ?>">
                <span class="admin-form__help">The service cards below the heading are managed under <a href="<?= ADMIN_URL ?>/services.php">Services</a>.</span>
            </div>
        </div>
    </div>

    <!-- ═══ About Teaser ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">About Section</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Small Label</label>
                    <input type="text" name="settings[home_about_label]" class="admin-form__input" value="<?= hp($current, 'home_about_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title</label>
                    <input type="text" name="settings[home_about_title]" class="admin-form__input" value="<?= hp($current, 'home_about_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Highlighted Part</label>
                    <input type="text" name="settings[home_about_title_highlight]" class="admin-form__input" value="<?= hp($current, 'home_about_title_highlight') ?>">
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Description</label>
                <textarea name="settings[home_about_desc]" class="admin-form__textarea"><?= hp($current, 'home_about_desc') ?></textarea>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Feature Bullets</label>
                <textarea name="settings[home_about_features]" class="admin-form__textarea" placeholder="One per line"><?= hp($current, 'home_about_features') ?></textarea>
                <span class="admin-form__help">One feature per line (each gets a check mark).</span>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Button Label</label>
                <input type="text" name="settings[home_about_btn_label]" class="admin-form__input" value="<?= hp($current, 'home_about_btn_label') ?>">
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">About Image</label>

                <div class="image-upload" onclick="document.getElementById('about_image_file').click();">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <div class="image-upload__text">Click to upload an image or drag &amp; drop</div>
                    <div class="image-upload__hint">PNG, JPG, WEBP, SVG or GIF up to 15MB</div>
                    <input type="file" name="about_image_file" id="about_image_file" style="display:none;" accept="image/*" onchange="previewAboutImage(this)">
                </div>

                <div class="image-preview" id="aboutPreviewContainer" style="<?= !empty($current['home_about_image']) ? '' : 'display:none;' ?>">
                    <img id="aboutPreviewImage" src="<?= hp($current, 'home_about_image') ?>" alt="About image preview" style="max-height: 200px; border-radius: var(--radius-md);">
                    <span class="admin-form__help" id="aboutPreviewHelp" style="display:block; margin-top:0.25rem;">Current image. Upload a new one to replace it.</span>
                </div>

                <label class="admin-form__label" style="margin-top: 0.85rem;">Or paste an Image URL</label>
                <input type="text" name="settings[home_about_image]" id="home_about_image" class="admin-form__input" value="<?= hp($current, 'home_about_image') ?>">
                <span class="admin-form__help">Uploading a file above will replace whatever is in this field on save.</span>
            </div>
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Floating Stat — Number</label>
                    <input type="number" name="settings[home_about_stat_value]" class="admin-form__input" min="0" value="<?= (int)($current['home_about_stat_value'] ?? 0) ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Floating Stat — Suffix</label>
                    <input type="text" name="settings[home_about_stat_suffix]" class="admin-form__input" value="<?= hp($current, 'home_about_stat_suffix') ?>" maxlength="4">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Floating Stat — Label</label>
                    <input type="text" name="settings[home_about_stat_label]" class="admin-form__input" value="<?= hp($current, 'home_about_stat_label') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ Stats Counter ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Statistics Bar Labels</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Label 1</label>
                    <input type="text" name="settings[home_stats_label1]" class="admin-form__input" value="<?= hp($current, 'home_stats_label1') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Label 2</label>
                    <input type="text" name="settings[home_stats_label2]" class="admin-form__input" value="<?= hp($current, 'home_stats_label2') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Label 3</label>
                    <input type="text" name="settings[home_stats_label3]" class="admin-form__input" value="<?= hp($current, 'home_stats_label3') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Label 4</label>
                    <input type="text" name="settings[home_stats_label4]" class="admin-form__input" value="<?= hp($current, 'home_stats_label4') ?>">
                </div>
            </div>
            <span class="admin-form__help">The numbers themselves are edited in <a href="<?= ADMIN_URL ?>/settings.php">Settings → Statistics Counters</a>.</span>
        </div>
    </div>

    <!-- ═══ Portfolio Showcase ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Featured Projects Heading</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Small Label</label>
                    <input type="text" name="settings[home_portfolio_label]" class="admin-form__input" value="<?= hp($current, 'home_portfolio_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title</label>
                    <input type="text" name="settings[home_portfolio_title]" class="admin-form__input" value="<?= hp($current, 'home_portfolio_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Highlighted Part</label>
                    <input type="text" name="settings[home_portfolio_title_highlight]" class="admin-form__input" value="<?= hp($current, 'home_portfolio_title_highlight') ?>">
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Description</label>
                <textarea name="settings[home_portfolio_desc]" class="admin-form__textarea"><?= hp($current, 'home_portfolio_desc') ?></textarea>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">“View All” Button Label</label>
                <input type="text" name="settings[home_portfolio_btn_label]" class="admin-form__input" value="<?= hp($current, 'home_portfolio_btn_label') ?>">
                <span class="admin-form__help">The projects shown are the featured items from <a href="<?= ADMIN_URL ?>/portfolio.php">Portfolio</a>.</span>
            </div>
        </div>
    </div>

    <!-- ═══ Testimonials ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Testimonials Heading</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Small Label</label>
                    <input type="text" name="settings[home_testimonials_label]" class="admin-form__input" value="<?= hp($current, 'home_testimonials_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title</label>
                    <input type="text" name="settings[home_testimonials_title]" class="admin-form__input" value="<?= hp($current, 'home_testimonials_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Highlighted Part</label>
                    <input type="text" name="settings[home_testimonials_title_highlight]" class="admin-form__input" value="<?= hp($current, 'home_testimonials_title_highlight') ?>">
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Description</label>
                <textarea name="settings[home_testimonials_desc]" class="admin-form__textarea"><?= hp($current, 'home_testimonials_desc') ?></textarea>
                <span class="admin-form__help">The testimonial cards are managed under <a href="<?= ADMIN_URL ?>/testimonials.php">Testimonials</a>.</span>
            </div>
        </div>
    </div>

    <!-- ═══ CTA Banner ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Call-to-Action Banner</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__group">
                <label class="admin-form__label">Title</label>
                <input type="text" name="settings[home_cta_title]" class="admin-form__input" value="<?= hp($current, 'home_cta_title') ?>">
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Description</label>
                <textarea name="settings[home_cta_desc]" class="admin-form__textarea"><?= hp($current, 'home_cta_desc') ?></textarea>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Button Label</label>
                <input type="text" name="settings[home_cta_btn_label]" class="admin-form__input" value="<?= hp($current, 'home_cta_btn_label') ?>">
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="admin-form__actions" style="justify-content: flex-end; position: sticky; bottom: 0; background: var(--admin-bg); padding: 1rem 0;">
        <a href="<?= SITE_URL ?>/" target="_blank" class="admin-btn admin-btn--outline">Preview Home Page</a>
        <button type="submit" class="admin-btn admin-btn--primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save Home Page Content
        </button>
    </div>
</form>

<style>
    /* Hero visual toggle */
    .hero-visual-toggle {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .hero-visual-toggle--three { grid-template-columns: repeat(3, 1fr); }

    /* Display options row */
    .hero-visual-options {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
        margin-top: 1.25rem;
        padding: 1rem;
        border: 1px solid var(--admin-border);
        border-radius: var(--radius-md);
        background: rgba(0,0,0,0.02);
    }
    .hero-visual-option { display: flex; flex-direction: column; gap: 0.4rem; }

    /* On/off switch */
    .hero-switch { display: inline-flex; align-items: center; gap: 0.55rem; cursor: pointer; }
    .hero-switch input { position: absolute; opacity: 0; pointer-events: none; }
    .hero-switch__track {
        position: relative;
        width: 42px; height: 24px;
        border-radius: 999px;
        background: var(--admin-border);
        transition: background var(--duration-fast);
        flex-shrink: 0;
    }
    .hero-switch__thumb {
        position: absolute;
        top: 3px; left: 3px;
        width: 18px; height: 18px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        transition: transform var(--duration-fast);
    }
    .hero-switch input[type="checkbox"]:checked ~ .hero-switch__track { background: var(--primary); }
    .hero-switch input[type="checkbox"]:checked ~ .hero-switch__track .hero-switch__thumb { transform: translateX(18px); }
    .hero-switch__label { font-size: 0.85rem; font-weight: 600; color: var(--admin-text-light); }
    .hero-visual-toggle__option {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        padding: 1rem 1.1rem;
        border: 2px solid var(--admin-border);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: all var(--duration-fast);
    }
    .hero-visual-toggle__option:hover { border-color: var(--primary); }
    .hero-visual-toggle__option.is-active {
        border-color: var(--primary);
        background: rgba(71,23,219,0.05);
    }
    .hero-visual-toggle__option input { position: absolute; opacity: 0; pointer-events: none; }
    .hero-visual-toggle__title { font-weight: 600; color: var(--admin-text-light); }
    .hero-visual-toggle__sub { font-size: 0.78rem; color: var(--admin-text-muted); }

    /* Design gallery */
    .hero-design-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 0.9rem;
    }
    .hero-design-card {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        aspect-ratio: 1 / 1;
        padding: 0.6rem;
        border: 2px solid var(--admin-border);
        border-radius: var(--radius-md);
        cursor: pointer;
        overflow: hidden;
        background:
            linear-gradient(160deg, #1a1130 0%, #231540 60%, #0d0b1a 100%);
        transition: all var(--duration-fast);
    }
    .hero-design-card:hover { border-color: var(--primary); transform: translateY(-2px); }
    .hero-design-card.is-active { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(71,23,219,0.25); }
    .hero-design-card input { position: absolute; opacity: 0; pointer-events: none; }
    .hero-design-card img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .hero-design-card__check {
        position: absolute;
        top: 6px; right: 6px;
        width: 24px; height: 24px;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .hero-design-card__check svg { width: 14px; height: 14px; }
    .hero-design-card.is-active .hero-design-card__check { display: flex; }

    @media (max-width: 640px) {
        .hero-visual-toggle,
        .hero-visual-toggle--three { grid-template-columns: 1fr; }
        .hero-visual-options { grid-template-columns: 1fr; }
    }
</style>

<script>
// ── Hero visual toggle + gallery interactivity ──
function syncVisualToggle() {
    document.querySelectorAll('.hero-visual-toggle__option').forEach(function (opt) {
        opt.classList.toggle('is-active', opt.querySelector('input').checked);
    });
}
document.querySelectorAll('input[name="settings[home_hero_visual]"]').forEach(function (radio) {
    radio.addEventListener('change', syncVisualToggle);
});

// Gallery multi-select: reflect ticked state on the cards
document.querySelectorAll('.hero-design-card input[type="checkbox"]').forEach(function (box) {
    box.addEventListener('change', function () {
        box.closest('.hero-design-card').classList.toggle('is-active', box.checked);
        // If nothing is set to globe, nudge the mode away from globe when picking images
        var globe = document.querySelector('input[name="settings[home_hero_visual]"][value="globe"]');
        var anyChecked = document.querySelectorAll('.hero-design-card input[type="checkbox"]:checked').length > 0;
        if (globe && globe.checked && anyChecked) {
            var img = document.querySelector('input[name="settings[home_hero_visual]"][value="image"]');
            if (img) { img.checked = true; syncVisualToggle(); }
        }
    });
});

// On/off switch label text
document.querySelectorAll('.hero-switch input[type="checkbox"]').forEach(function (box) {
    box.addEventListener('change', function () {
        var label = box.closest('.hero-switch').querySelector('.hero-switch__label');
        if (label) label.textContent = box.checked ? 'Shown' : 'Hidden';
    });
});

function previewHeroDesign(input) {
    var box = document.getElementById('heroDesignPreview');
    var img = document.getElementById('heroDesignPreviewImg');
    var help = document.getElementById('heroDesignPreviewHelp');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
            box.style.display = 'block';
            help.textContent = 'New design selected: ' + input.files[0].name + ' — saves & switches to Image Design on submit.';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewAboutImage(input) {
    const preview = document.getElementById('aboutPreviewImage');
    const container = document.getElementById('aboutPreviewContainer');
    const helpText = document.getElementById('aboutPreviewHelp');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            container.style.display = 'block';
            helpText.textContent = 'New image selected: ' + input.files[0].name + ' (saves when you submit).';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
