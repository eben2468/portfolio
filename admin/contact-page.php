<?php
/**
 * Nadics Digital Solution — Admin Contact Page Content Editor
 * Edits all text/content on the public Contact page (stored in site_settings).
 */

$admin_page = 'contact-page';
$admin_title = 'Contact Page Content';

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

        if ($db && !empty($settings)) {
            try {
                $db->beginTransaction();
                $stmt = $db->prepare("
                    INSERT INTO site_settings (setting_key, setting_value)
                    VALUES (:key, :value)
                    ON DUPLICATE KEY UPDATE setting_value = :value_update
                ");
                foreach ($settings as $key => $value) {
                    // Only allow known contact page keys to be written here
                    if (!array_key_exists($key, contactDefaults())) continue;
                    $val = trim($value);
                    $stmt->execute([':key' => $key, ':value' => $val, ':value_update' => $val]);
                }
                $db->commit();
                logActivity($_SESSION['admin_id'], 'Updated contact page content', 'site_settings', null, 'Contact page bulk update');

                $_SESSION['flash_msg'] = 'Contact page content saved successfully.';
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . ADMIN_URL . '/contact-page.php');
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
$current = array_merge(contactDefaults(), $saved);

// Small helper to print a value safely into an attribute/textarea
function cp(array $current, string $key): string {
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
    <span>Edit the text on the Contact page. Several blocks have a <strong>normal</strong> version and a <strong>“Request a Quote”</strong> version (shown when visitors open <em>?type=quote</em>). Your email, phone, address and social links live under <a href="<?= ADMIN_URL ?>/settings.php">Settings</a>.</span>
</div>

<form action="" method="POST" class="admin-form">
    <?= csrfField() ?>

    <!-- ═══ Page Hero ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Page Hero</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Hero Title (Contact)</label>
                    <input type="text" name="settings[contact_hero_title]" class="admin-form__input" value="<?= cp($current, 'contact_hero_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Hero Title (Quote mode)</label>
                    <input type="text" name="settings[contact_hero_title_quote]" class="admin-form__input" value="<?= cp($current, 'contact_hero_title_quote') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ Section Header — Contact Mode ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Section Heading — Contact Mode</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Small Label</label>
                    <input type="text" name="settings[contact_label]" class="admin-form__input" value="<?= cp($current, 'contact_label') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title</label>
                    <input type="text" name="settings[contact_title]" class="admin-form__input" value="<?= cp($current, 'contact_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Highlighted Part</label>
                    <input type="text" name="settings[contact_title_highlight]" class="admin-form__input" value="<?= cp($current, 'contact_title_highlight') ?>">
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Description</label>
                <textarea name="settings[contact_desc]" class="admin-form__textarea"><?= cp($current, 'contact_desc') ?></textarea>
            </div>
        </div>
    </div>

    <!-- ═══ Section Header — Quote Mode ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Section Heading — “Request a Quote” Mode</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Small Label</label>
                    <input type="text" name="settings[contact_label_quote]" class="admin-form__input" value="<?= cp($current, 'contact_label_quote') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title</label>
                    <input type="text" name="settings[contact_title_quote]" class="admin-form__input" value="<?= cp($current, 'contact_title_quote') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Title — Highlighted Part</label>
                    <input type="text" name="settings[contact_title_highlight_quote]" class="admin-form__input" value="<?= cp($current, 'contact_title_highlight_quote') ?>">
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Description</label>
                <textarea name="settings[contact_desc_quote]" class="admin-form__textarea"><?= cp($current, 'contact_desc_quote') ?></textarea>
            </div>
        </div>
    </div>

    <!-- ═══ Form Card Text ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Form Card Text</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Form Title (Contact)</label>
                    <input type="text" name="settings[contact_form_title]" class="admin-form__input" value="<?= cp($current, 'contact_form_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Form Title (Quote mode)</label>
                    <input type="text" name="settings[contact_form_title_quote]" class="admin-form__input" value="<?= cp($current, 'contact_form_title_quote') ?>">
                </div>
            </div>
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Form Subtitle (Contact)</label>
                    <input type="text" name="settings[contact_form_subtitle]" class="admin-form__input" value="<?= cp($current, 'contact_form_subtitle') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Form Subtitle (Quote mode)</label>
                    <input type="text" name="settings[contact_form_subtitle_quote]" class="admin-form__input" value="<?= cp($current, 'contact_form_subtitle_quote') ?>">
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Success Message (Contact)</label>
                <textarea name="settings[contact_success_msg]" class="admin-form__textarea"><?= cp($current, 'contact_success_msg') ?></textarea>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Success Message (Quote mode)</label>
                <textarea name="settings[contact_success_msg_quote]" class="admin-form__textarea"><?= cp($current, 'contact_success_msg_quote') ?></textarea>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Error Message</label>
                <input type="text" name="settings[contact_error_msg]" class="admin-form__input" value="<?= cp($current, 'contact_error_msg') ?>">
                <span class="admin-form__help">Shown if the message fails to send.</span>
            </div>
        </div>
    </div>

    <!-- ═══ Contact Info & Socials ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Contact Info &amp; Socials</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Info Card Heading</label>
                    <input type="text" name="settings[contact_info_heading]" class="admin-form__input" value="<?= cp($current, 'contact_info_heading') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Socials Heading</label>
                    <input type="text" name="settings[contact_socials_heading]" class="admin-form__input" value="<?= cp($current, 'contact_socials_heading') ?>">
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Business Hours</label>
                <textarea name="settings[contact_hours]" class="admin-form__textarea"><?= cp($current, 'contact_hours') ?></textarea>
                <span class="admin-form__help">One line per row — each line shows on its own line on the page.</span>
            </div>
            <span class="admin-form__help">The email, phone and location values, plus the social media links, are managed in <a href="<?= ADMIN_URL ?>/settings.php">Settings</a>.</span>
        </div>
    </div>

    <!-- ═══ Map / Visit ═══ -->
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="admin-card__header"><h2 class="admin-card__title">Map / Visit Us</h2></div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <div class="admin-form__group">
                    <label class="admin-form__label">Location Title</label>
                    <input type="text" name="settings[contact_map_title]" class="admin-form__input" value="<?= cp($current, 'contact_map_title') ?>">
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label">Location Subtitle</label>
                    <input type="text" name="settings[contact_map_desc]" class="admin-form__input" value="<?= cp($current, 'contact_map_desc') ?>">
                </div>
            </div>
            <div class="admin-form__group">
                <label class="admin-form__label">Google Maps Embed URL (optional)</label>
                <input type="text" name="settings[contact_map_embed]" class="admin-form__input" value="<?= cp($current, 'contact_map_embed') ?>" placeholder="https://www.google.com/maps/embed?pb=...">
                <span class="admin-form__help">Paste the <strong>src</strong> URL from Google Maps → Share → Embed a map. When set, a live map replaces the placeholder. Leave blank to show the location title/subtitle instead.</span>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="admin-form__actions" style="justify-content: flex-end; position: sticky; bottom: 0; background: var(--admin-bg); padding: 1rem 0;">
        <a href="<?= SITE_URL ?>/contact.php" target="_blank" class="admin-btn admin-btn--outline">Preview Contact Page</a>
        <button type="submit" class="admin-btn admin-btn--primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save Contact Page Content
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
