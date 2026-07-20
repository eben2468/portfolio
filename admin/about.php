<?php
/**
 * Nadics Digital Solution — Admin About Page Content Editor
 * Edits all text/content on the public About page (stored in site_settings).
 */

$admin_page = 'about';
$admin_title = 'About Page Content';

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

        // Optional: handle Story image upload (overrides the URL field)
        if (isset($_FILES['story_image_file']) && $_FILES['story_image_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['story_image_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'svg', 'webp', 'gif'];

            if (!in_array($ext, $allowed_exts)) {
                $flash_message = 'Invalid image type. Allowed formats: JPG, PNG, WEBP, SVG, GIF.';
                $flash_type = 'error';
            } elseif ($file['size'] > 15 * 1024 * 1024) {
                $flash_message = 'Image is too large. Maximum size is 15MB.';
                $flash_type = 'error';
            } else {
                $upload_dir = __DIR__ . '/../assets/images/about/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $new_filename = uniqid('story_', true) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
                    // Relative path keeps the value portable across environments
                    $settings['about_story_image'] = 'assets/images/about/' . $new_filename;
                } else {
                    $flash_message = 'Failed to save uploaded image.';
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
                    // Only allow known About page keys to be written here
                    if (!array_key_exists($key, aboutDefaults())) continue;
                    $val = trim($value);
                    $stmt->execute([':key' => $key, ':value' => $val, ':value_update' => $val]);
                }
                $db->commit();
                logActivity($_SESSION['admin_id'], 'Updated about page content', 'site_settings', null, 'About page bulk update');

                $_SESSION['flash_msg'] = 'About page content saved successfully.';
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . ADMIN_URL . '/about.php');
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
$current = array_merge(aboutDefaults(), $saved);

// Safe value printer for attributes/textareas
if (!function_exists('hp')) {
    function hp(array $current, string $key): string {
        return sanitize($current[$key] ?? '');
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
    <span>Edit every text block on the About page here. The <strong>leadership cards</strong> are managed under <a href="<?= ADMIN_URL ?>/team.php">Team</a>. For headings, the “highlighted part” is shown in the gradient colour.</span>
</div>

<form action="" method="POST" enctype="multipart/form-data" class="admin-form">
    <?= csrfField() ?>

    <!-- ═══ Page Hero ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Page Hero</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__group">
                <label class="admin-form__label">Hero Title</label>
                <input type="text" name="settings[about_hero_title]" class="admin-form__input" value="<?= hp($current, 'about_hero_title') ?>">
            </div>
        </div>
    </div>

    <!-- ═══ Our Story ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Our Story</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Small Label</label>
                    <input type="text" name="settings[about_story_label]" class="admin-form__input" value="<?= hp($current, 'about_story_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title</label>
                    <input type="text" name="settings[about_story_title]" class="admin-form__input" value="<?= hp($current, 'about_story_title') ?>">
                </div>
            </div>
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Highlighted Part</label>
                    <input type="text" name="settings[about_story_title_highlight]" class="admin-form__input" value="<?= hp($current, 'about_story_title_highlight') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — After Highlight (optional)</label>
                    <input type="text" name="settings[about_story_title_suffix]" class="admin-form__input" value="<?= hp($current, 'about_story_title_suffix') ?>">
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Lead Paragraph</label>
                <textarea name="settings[about_story_lead]" class="admin-form__textarea"><?= hp($current, 'about_story_lead') ?></textarea>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Body Paragraph 1</label>
                <textarea name="settings[about_story_body1]" class="admin-form__textarea"><?= hp($current, 'about_story_body1') ?></textarea>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Body Paragraph 2</label>
                <textarea name="settings[about_story_body2]" class="admin-form__textarea"><?= hp($current, 'about_story_body2') ?></textarea>
            </div>

            <div class="admin-form__group">
                <label class="admin-form__label">Story Image</label>
                <div class="image-upload" onclick="document.getElementById('story_image_file').click();">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <div class="image-upload__text">Click to upload an image or drag &amp; drop</div>
                    <div class="image-upload__hint">PNG, JPG, WEBP, SVG or GIF up to 15MB</div>
                    <input type="file" name="story_image_file" id="story_image_file" style="display:none;" accept="image/*" onchange="previewStoryImage(this)">
                </div>
                <div class="image-preview" id="storyPreviewContainer" style="<?= !empty($current['about_story_image']) ? '' : 'display:none;' ?>">
                    <img id="storyPreviewImage" src="<?= hp($current, 'about_story_image') ?>" alt="Story image preview" style="max-height: 200px; border-radius: var(--radius-md);">
                    <span class="admin-form__help" id="storyPreviewHelp" style="display:block; margin-top:0.25rem;">Current image. Upload a new one to replace it.</span>
                </div>
                <label class="admin-form__label" style="margin-top: 0.85rem;">Or paste an Image URL</label>
                <input type="text" name="settings[about_story_image]" class="admin-form__input" value="<?= hp($current, 'about_story_image') ?>">
            </div>

            <label class="admin-form__label">Experience Badge</label>
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Number</label>
                    <input type="number" name="settings[about_story_badge_number]" class="admin-form__input" min="0" value="<?= (int)($current['about_story_badge_number'] ?? 0) ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Suffix</label>
                    <input type="text" name="settings[about_story_badge_suffix]" class="admin-form__input" value="<?= hp($current, 'about_story_badge_suffix') ?>" maxlength="4">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Caption</label>
                    <input type="text" name="settings[about_story_badge_text]" class="admin-form__input" value="<?= sanitize(str_replace("\n", ' ', $current['about_story_badge_text'] ?? '')) ?>">
                    <span class="admin-form__help">Shown under the number, e.g. “Years Experience”.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ What NADICS Means ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">What the Name Means</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Small Label</label>
                    <input type="text" name="settings[about_meaning_label]" class="admin-form__input" value="<?= hp($current, 'about_meaning_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Before</label>
                    <input type="text" name="settings[about_meaning_title]" class="admin-form__input" value="<?= hp($current, 'about_meaning_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Highlighted</label>
                    <input type="text" name="settings[about_meaning_title_highlight]" class="admin-form__input" value="<?= hp($current, 'about_meaning_title_highlight') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — After</label>
                    <input type="text" name="settings[about_meaning_title_suffix]" class="admin-form__input" value="<?= hp($current, 'about_meaning_title_suffix') ?>">
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Description</label>
                <textarea name="settings[about_meaning_desc]" class="admin-form__textarea"><?= hp($current, 'about_meaning_desc') ?></textarea>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Acronym Cards</label>
                <textarea name="settings[about_meaning_acronyms]" class="admin-form__textarea" style="min-height: 150px; font-family: monospace;"><?= hp($current, 'about_meaning_acronyms') ?></textarea>
                <span class="admin-form__help">One card per line, format: <code>Letter | Word</code>.</span>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Closing Statement</label>
                <textarea name="settings[about_meaning_statement]" class="admin-form__textarea"><?= hp($current, 'about_meaning_statement') ?></textarea>
            </div>
        </div>
    </div>

    <!-- ═══ Mission / Vision / Values ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Mission, Vision &amp; Values</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Small Label</label>
                    <input type="text" name="settings[about_mvv_label]" class="admin-form__input" value="<?= hp($current, 'about_mvv_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title</label>
                    <input type="text" name="settings[about_mvv_title]" class="admin-form__input" value="<?= hp($current, 'about_mvv_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Highlighted</label>
                    <input type="text" name="settings[about_mvv_title_highlight]" class="admin-form__input" value="<?= hp($current, 'about_mvv_title_highlight') ?>">
                </div>
            </div>
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Mission — Title</label>
                    <input type="text" name="settings[about_mvv_mission_title]" class="admin-form__input" value="<?= hp($current, 'about_mvv_mission_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Vision — Title</label>
                    <input type="text" name="settings[about_mvv_vision_title]" class="admin-form__input" value="<?= hp($current, 'about_mvv_vision_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Values — Title</label>
                    <input type="text" name="settings[about_mvv_values_title]" class="admin-form__input" value="<?= hp($current, 'about_mvv_values_title') ?>">
                </div>
            </div>
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Mission — Description</label>
                    <textarea name="settings[about_mvv_mission_desc]" class="admin-form__textarea"><?= hp($current, 'about_mvv_mission_desc') ?></textarea>
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Vision — Description</label>
                    <textarea name="settings[about_mvv_vision_desc]" class="admin-form__textarea"><?= hp($current, 'about_mvv_vision_desc') ?></textarea>
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Values — Description</label>
                    <textarea name="settings[about_mvv_values_desc]" class="admin-form__textarea"><?= hp($current, 'about_mvv_values_desc') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ Timeline ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Journey / Milestones</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Small Label</label>
                    <input type="text" name="settings[about_timeline_label]" class="admin-form__input" value="<?= hp($current, 'about_timeline_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title</label>
                    <input type="text" name="settings[about_timeline_title]" class="admin-form__input" value="<?= hp($current, 'about_timeline_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Highlighted</label>
                    <input type="text" name="settings[about_timeline_title_highlight]" class="admin-form__input" value="<?= hp($current, 'about_timeline_title_highlight') ?>">
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Description</label>
                <textarea name="settings[about_timeline_desc]" class="admin-form__textarea"><?= hp($current, 'about_timeline_desc') ?></textarea>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Milestones</label>
                <textarea name="settings[about_timeline_items]" class="admin-form__textarea" style="min-height: 180px; font-family: monospace;"><?= hp($current, 'about_timeline_items') ?></textarea>
                <span class="admin-form__help">One milestone per line, format: <code>Year | Title | Description</code>. They alternate left/right automatically.</span>
            </div>
        </div>
    </div>

    <!-- ═══ Leadership ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Leadership Heading</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Small Label</label>
                    <input type="text" name="settings[about_leadership_label]" class="admin-form__input" value="<?= hp($current, 'about_leadership_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title</label>
                    <input type="text" name="settings[about_leadership_title]" class="admin-form__input" value="<?= hp($current, 'about_leadership_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Highlighted</label>
                    <input type="text" name="settings[about_leadership_title_highlight]" class="admin-form__input" value="<?= hp($current, 'about_leadership_title_highlight') ?>">
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Description</label>
                <textarea name="settings[about_leadership_desc]" class="admin-form__textarea"><?= hp($current, 'about_leadership_desc') ?></textarea>
                <span class="admin-form__help">The team member cards are managed under <a href="<?= ADMIN_URL ?>/team.php">Team</a>.</span>
            </div>
        </div>
    </div>

    <!-- ═══ Tech Stack ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Tech Stack</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Small Label</label>
                    <input type="text" name="settings[about_tech_label]" class="admin-form__input" value="<?= hp($current, 'about_tech_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title</label>
                    <input type="text" name="settings[about_tech_title]" class="admin-form__input" value="<?= hp($current, 'about_tech_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Highlighted</label>
                    <input type="text" name="settings[about_tech_title_highlight]" class="admin-form__input" value="<?= hp($current, 'about_tech_title_highlight') ?>">
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Description</label>
                <textarea name="settings[about_tech_desc]" class="admin-form__textarea"><?= hp($current, 'about_tech_desc') ?></textarea>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Technologies</label>
                <textarea name="settings[about_tech_items]" class="admin-form__textarea" style="min-height: 200px; font-family: monospace;"><?= hp($current, 'about_tech_items') ?></textarea>
                <span class="admin-form__help">One technology per line, format: <code>icon | Name</code> (icon can be an emoji).</span>
            </div>
        </div>
    </div>

    <!-- ═══ CTA ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Call-to-Action Banner</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__group">
                <label class="admin-form__label">Title</label>
                <input type="text" name="settings[about_cta_title]" class="admin-form__input" value="<?= hp($current, 'about_cta_title') ?>">
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Description</label>
                <textarea name="settings[about_cta_desc]" class="admin-form__textarea"><?= hp($current, 'about_cta_desc') ?></textarea>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Button Label</label>
                <input type="text" name="settings[about_cta_btn_label]" class="admin-form__input" value="<?= hp($current, 'about_cta_btn_label') ?>">
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="admin-form__actions" style="justify-content: flex-end; position: sticky; bottom: 0; background: var(--admin-bg); padding: 1rem 0;">
        <a href="<?= SITE_URL ?>/about.php" target="_blank" class="admin-btn admin-btn--outline">Preview About Page</a>
        <button type="submit" class="admin-btn admin-btn--primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save About Page Content
        </button>
    </div>
</form>

<script>
function previewStoryImage(input) {
    const preview = document.getElementById('storyPreviewImage');
    const container = document.getElementById('storyPreviewContainer');
    const helpText = document.getElementById('storyPreviewHelp');
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
