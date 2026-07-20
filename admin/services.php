<?php
/**
 * Nadics Digital Solution — Admin Services Management
 */

$admin_page = 'services';

require_once __DIR__ . '/includes/auth.php';

$db = getDB();
$flash_message = '';
$flash_type = '';

// Make sure the table exists (also seeds defaults on first run)
if ($db) {
    try { ensureServicesTable($db); } catch (PDOException $e) { /* handled below */ }
}

// Check session for flash message
if (isset($_SESSION['flash_msg'])) {
    $flash_message = $_SESSION['flash_msg'];
    $flash_type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
}

$action = $_GET['action'] ?? 'list';
$edit_item = null;

// Load item if in edit mode
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    $edit_item = getServiceById($edit_id);
    if (!$edit_item) {
        $_SESSION['flash_msg'] = 'Service not found.';
        $_SESSION['flash_type'] = 'error';
        header('Location: ' . ADMIN_URL . '/services.php');
        exit;
    }
}

// Handle Form Submissions (Add / Edit / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verifyCSRFToken($csrf)) {
        $flash_message = 'Security token invalid. Action blocked.';
        $flash_type = 'error';
    } else {
        $post_action = $_POST['post_action'] ?? '';

        if ($post_action === 'delete') {
            $delete_id = (int)$_POST['id'];
            if ($db && $delete_id > 0) {
                try {
                    $stmt = $db->prepare("SELECT title FROM services WHERE id = :id");
                    $stmt->execute([':id' => $delete_id]);
                    $item = $stmt->fetch();

                    if ($item) {
                        $stmt = $db->prepare("DELETE FROM services WHERE id = :id");
                        $stmt->execute([':id' => $delete_id]);
                        logActivity($_SESSION['admin_id'], 'Deleted service', 'services', $delete_id, "Title: " . $item['title']);
                        $_SESSION['flash_msg'] = 'Service deleted successfully.';
                        $_SESSION['flash_type'] = 'success';
                        header('Location: ' . ADMIN_URL . '/services.php');
                        exit;
                    }
                } catch (PDOException $e) {
                    $flash_message = 'Error deleting service: ' . $e->getMessage();
                    $flash_type = 'error';
                }
            }
        } elseif ($post_action === 'save') {
            $id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $title         = trim($_POST['title'] ?? '');
            $subtitle      = trim($_POST['subtitle'] ?? '');
            $description   = trim($_POST['description'] ?? '');
            $icon_svg      = trim($_POST['icon_svg'] ?? '');
            $features      = trim($_POST['features'] ?? '');
            $link_url      = trim($_POST['link_url'] ?? '');
            $link_label    = trim($_POST['link_label'] ?? '');
            $anchor_id     = trim($_POST['anchor_id'] ?? '');
            $is_active     = isset($_POST['is_active']) ? 1 : 0;
            $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;

            // Auto-generate anchor id from title if blank
            if ($anchor_id === '' && $title !== '') {
                $anchor_id = slugify($title);
            } else {
                $anchor_id = slugify($anchor_id);
            }

            // Normalise features (one per line, no blanks)
            $features = implode("\n", serviceFeatureList($features));

            // Carry over the existing image unless a new one is uploaded
            $image_url = $edit_item['image_url'] ?? ($_POST['existing_image'] ?? '');

            // Handle service image upload
            if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] === UPLOAD_ERR_OK) {
                $file_tmp  = $_FILES['service_image']['tmp_name'];
                $file_name = $_FILES['service_image']['name'];
                $file_size = $_FILES['service_image']['size'];
                $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'svg', 'webp', 'gif'];

                if (!in_array($file_ext, $allowed_exts)) {
                    $flash_message = 'Invalid image type. Allowed formats: JPG, PNG, WEBP, SVG, GIF.';
                    $flash_type = 'error';
                } elseif ($file_size > 15 * 1024 * 1024) { // 15MB limit
                    $flash_message = 'Image is too large. Maximum size is 15MB.';
                    $flash_type = 'error';
                } else {
                    $upload_dir = __DIR__ . '/../assets/images/services/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    $new_filename = uniqid('service_', true) . '.' . $file_ext;
                    if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
                        // Remove the previous uploaded image if one existed
                        if (!empty($image_url) && str_contains($image_url, 'assets/images/services/')) {
                            $old_img = __DIR__ . '/../' . $image_url;
                            if (file_exists($old_img)) @unlink($old_img);
                        }
                        $image_url = 'assets/images/services/' . $new_filename;
                    } else {
                        $flash_message = 'Failed to save the uploaded image.';
                        $flash_type = 'error';
                    }
                }
            }

            // Validate required inputs
            if ($flash_type === 'error') {
                // fall through to re-render with the error message
            } elseif (empty($title) || empty($description)) {
                $flash_message = 'Please fill in all required fields (Title, Description).';
                $flash_type = 'error';
            } elseif ($db) {
                try {
                    if ($id > 0) {
                        $stmt = $db->prepare("
                            UPDATE services SET
                                title = :title,
                                subtitle = :subtitle,
                                description = :description,
                                icon_svg = :icon_svg,
                                image_url = :image_url,
                                features = :features,
                                link_url = :link_url,
                                link_label = :link_label,
                                anchor_id = :anchor_id,
                                is_active = :is_active,
                                display_order = :display_order
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            ':title'         => $title,
                            ':subtitle'      => $subtitle ?: null,
                            ':description'   => $description,
                            ':icon_svg'      => $icon_svg ?: null,
                            ':image_url'     => $image_url ?: null,
                            ':features'      => $features ?: null,
                            ':link_url'      => $link_url ?: null,
                            ':link_label'    => $link_label ?: null,
                            ':anchor_id'     => $anchor_id,
                            ':is_active'     => $is_active,
                            ':display_order' => $display_order,
                            ':id'            => $id,
                        ]);
                        logActivity($_SESSION['admin_id'], 'Updated service', 'services', $id, "Title: $title");
                        $_SESSION['flash_msg'] = 'Service updated successfully.';
                    } else {
                        $stmt = $db->prepare("
                            INSERT INTO services (title, subtitle, description, icon_svg, image_url, features, link_url, link_label, anchor_id, is_active, display_order)
                            VALUES (:title, :subtitle, :description, :icon_svg, :image_url, :features, :link_url, :link_label, :anchor_id, :is_active, :display_order)
                        ");
                        $stmt->execute([
                            ':title'         => $title,
                            ':subtitle'      => $subtitle ?: null,
                            ':description'   => $description,
                            ':icon_svg'      => $icon_svg ?: null,
                            ':image_url'     => $image_url ?: null,
                            ':features'      => $features ?: null,
                            ':link_url'      => $link_url ?: null,
                            ':link_label'    => $link_label ?: null,
                            ':anchor_id'     => $anchor_id,
                            ':is_active'     => $is_active,
                            ':display_order' => $display_order,
                        ]);
                        $new_id = $db->lastInsertId();
                        logActivity($_SESSION['admin_id'], 'Created service', 'services', $new_id, "Title: $title");
                        $_SESSION['flash_msg'] = 'Service created successfully.';
                    }
                    $_SESSION['flash_type'] = 'success';
                    header('Location: ' . ADMIN_URL . '/services.php');
                    exit;
                } catch (PDOException $e) {
                    $flash_message = 'Database save error: ' . $e->getMessage();
                    $flash_type = 'error';
                }
            }
        }
    }
}

$admin_title = 'Manage Services';
if ($action === 'add') $admin_title = 'Add Service';
if ($action === 'edit') $admin_title = 'Edit Service';

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

<?php if ($action === 'list'): ?>
    <!-- ═══ Services Listing Mode ═══ -->
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">Services Offered</h2>
            <a href="<?= ADMIN_URL ?>/services.php?action=add" class="admin-btn admin-btn--primary admin-btn--sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add New Service
            </a>
        </div>
        <div class="admin-card__body" style="padding: 0;">
            <?php
            $services = getServices(false); // include inactive in admin list
            if (empty($services)): ?>
                <div class="admin-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    <h3 class="admin-empty__title">No Services Found</h3>
                    <p class="admin-empty__desc">Add the services your company offers to display them on the Services page.</p>
                    <a href="<?= ADMIN_URL ?>/services.php?action=add" class="admin-btn admin-btn--primary" style="margin-top: 1rem;">Add First Service</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width: 64px;">Icon</th>
                                <th>Title</th>
                                <th>Features</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service):
                                $featureCount = count(serviceFeatureList($service['features'])); ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($service['image_url'])):
                                            $thumb_src = str_contains($service['image_url'], '://') ? $service['image_url'] : SITE_URL . '/' . ltrim($service['image_url'], '/'); ?>
                                        <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); overflow: hidden; background: var(--admin-bg);">
                                            <img src="<?= sanitize($thumb_src) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                        <?php else: ?>
                                        <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; background: var(--admin-bg); color: var(--admin-primary, #6d5efc);">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><?= $service['icon_svg'] ?></svg>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--admin-text);"><?= sanitize($service['title']) ?></div>
                                        <div style="font-size: 0.78rem; color: var(--admin-text-light);"><?= sanitize($service['subtitle'] ?? '') ?></div>
                                    </td>
                                    <td style="font-size: 0.85rem; color: var(--admin-text-light);">
                                        <?= $featureCount ?> feature<?= $featureCount === 1 ? '' : 's' ?>
                                        <?php if (!empty($service['link_url'])): ?>
                                            <span class="admin-badge admin-badge--primary" style="margin-left: 0.35rem;">Link</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center; font-weight: 500;"><?= (int)$service['display_order'] ?></td>
                                    <td>
                                        <?php if ($service['is_active']): ?>
                                            <span class="admin-badge admin-badge--success">Active</span>
                                        <?php else: ?>
                                            <span class="admin-badge admin-badge--muted">Hidden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= ADMIN_URL ?>/services.php?action=edit&id=<?= $service['id'] ?>" class="admin-btn admin-btn--outline admin-btn--sm" title="Edit">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                            </a>
                                            <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this service?');">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="id" value="<?= $service['id'] ?>">
                                                <input type="hidden" name="post_action" value="delete">
                                                <button type="submit" class="admin-btn admin-btn--danger admin-btn--sm" title="Delete">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'):
    // Icon presets pulled from the shipped defaults
    $icon_presets = [];
    foreach (defaultServicesData() as $preset) {
        $icon_presets[$preset['title']] = $preset['icon_svg'];
    }
    $current_icon = $_POST['icon_svg'] ?? $edit_item['icon_svg'] ?? '';
?>
    <!-- ═══ Services Add / Edit Mode ═══ -->
    <div class="admin-card" style="max-width: 820px; margin: 0 auto;">
        <div class="admin-card__header">
            <h2 class="admin-card__title"><?= $action === 'add' ? 'Add New Service' : 'Edit Service: ' . sanitize($edit_item['title']) ?></h2>
            <a href="<?= ADMIN_URL ?>/services.php" class="admin-btn admin-btn--outline admin-btn--sm">Cancel</a>
        </div>
        <div class="admin-card__body">
            <form action="" method="POST" class="admin-form" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="post_action" value="save">
                <input type="hidden" name="existing_image" value="<?= sanitize($_POST['existing_image'] ?? $edit_item['image_url'] ?? '') ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $edit_item['id'] ?>">
                <?php endif; ?>

                <!-- Row 1: Title & Subtitle -->
                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="title" class="admin-form__label">Service Title <span class="required">*</span></label>
                        <input type="text" name="title" id="title" class="admin-form__input" placeholder="e.g. School Management System" value="<?= sanitize($_POST['title'] ?? $edit_item['title'] ?? '') ?>" required>
                    </div>
                    <div class="admin-form__group">
                        <label for="subtitle" class="admin-form__label">Subtitle / Tagline</label>
                        <input type="text" name="subtitle" id="subtitle" class="admin-form__input" placeholder="e.g. All-in-one platform for schools" value="<?= sanitize($_POST['subtitle'] ?? $edit_item['subtitle'] ?? '') ?>">
                    </div>
                </div>

                <!-- Row 2: Display Order & Anchor -->
                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="display_order" class="admin-form__label">Display Order</label>
                        <input type="number" name="display_order" id="display_order" class="admin-form__input" min="0" value="<?= (int)($_POST['display_order'] ?? $edit_item['display_order'] ?? 0) ?>">
                        <span class="admin-form__help">Smaller values appear first.</span>
                    </div>
                    <div class="admin-form__group">
                        <label for="anchor_id" class="admin-form__label">Anchor ID</label>
                        <input type="text" name="anchor_id" id="anchor_id" class="admin-form__input" placeholder="e.g. school-management" value="<?= sanitize($_POST['anchor_id'] ?? $edit_item['anchor_id'] ?? '') ?>">
                        <span class="admin-form__help">Used for #links. Leave blank to auto-generate from the title.</span>
                    </div>
                </div>

                <!-- Description -->
                <div class="admin-form__group">
                    <label for="description" class="admin-form__label">Description <span class="required">*</span></label>
                    <textarea name="description" id="description" class="admin-form__textarea" placeholder="Describe what this service offers..." required><?= sanitize($_POST['description'] ?? $edit_item['description'] ?? '') ?></textarea>
                </div>

                <!-- Features -->
                <div class="admin-form__group">
                    <label for="features" class="admin-form__label">Features</label>
                    <textarea name="features" id="features" class="admin-form__textarea" placeholder="One feature per line, e.g.&#10;Attendance Tracking&#10;Grading &amp; Report Cards&#10;Fees &amp; Billing"><?= sanitize($_POST['features'] ?? $edit_item['features'] ?? '') ?></textarea>
                    <span class="admin-form__help">Enter one feature per line. Each becomes a check-marked item in the grid.</span>
                </div>

                <!-- Icon -->
                <div class="admin-form__group">
                    <label class="admin-form__label">Icon</label>
                    <div style="display: flex; gap: 1rem; align-items: flex-start;">
                        <div style="flex-shrink: 0; width: 64px; height: 64px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; background: var(--admin-bg); border: 1px solid var(--admin-border-light); color: var(--admin-primary, #6d5efc);">
                            <svg id="iconPreview" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><?= $current_icon ?></svg>
                        </div>
                        <div style="flex: 1;">
                            <select id="iconPreset" class="admin-form__select" style="margin-bottom: 0.5rem;">
                                <option value="">— Choose a preset icon —</option>
                                <?php foreach ($icon_presets as $name => $svg): ?>
                                    <option value="<?= sanitize($svg) ?>"><?= sanitize($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <textarea name="icon_svg" id="icon_svg" class="admin-form__textarea" style="min-height: 90px; font-family: monospace; font-size: 0.8rem;" placeholder="Inner SVG markup, e.g. <path d=&quot;...&quot;/>"><?= sanitize($current_icon) ?></textarea>
                            <span class="admin-form__help">Pick a preset above, or paste inner SVG markup (paths, circles, lines) drawn on a 24×24 grid. The preview updates as you type.</span>
                        </div>
                    </div>
                </div>

                <!-- Service Image -->
                <?php
                    $existing_image = $_POST['existing_image'] ?? $edit_item['image_url'] ?? '';
                    $existing_image_src = $existing_image ? (str_contains($existing_image, '://') ? $existing_image : SITE_URL . '/' . ltrim($existing_image, '/')) : '';
                ?>
                <div class="admin-form__group">
                    <label class="admin-form__label">Service Image</label>
                    <div class="image-upload" onclick="document.getElementById('service_image').click();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <div class="image-upload__text">Click to upload a service image</div>
                        <div class="image-upload__hint">PNG, JPG, WEBP, SVG or GIF up to 15MB • shown on the card</div>
                        <input type="file" name="service_image" id="service_image" style="display:none;" accept="image/*" onchange="previewServiceImage(this)">
                    </div>
                    <div class="image-preview" id="serviceImagePreview" style="<?= $existing_image_src ? '' : 'display:none;' ?> margin-top: 0.75rem;">
                        <img id="serviceImagePreviewImg" src="<?= sanitize($existing_image_src) ?>" alt="Service image preview" style="max-height: 200px; border-radius: var(--radius-md);">
                        <span class="admin-form__help" id="serviceImagePreviewHelp" style="display:block; margin-top:0.25rem;">Current image. Upload a new one to replace it.</span>
                    </div>
                    <span class="admin-form__help">Optional. When set, the card shows this image; otherwise it falls back to the icon on a gradient background.</span>
                </div>

                <!-- Row 3: Link URL & Label -->
                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="link_url" class="admin-form__label">Link URL (optional)</label>
                        <input type="url" name="link_url" id="link_url" class="admin-form__input" placeholder="e.g. https://school.nadicssolution.com" value="<?= sanitize($_POST['link_url'] ?? $edit_item['link_url'] ?? '') ?>">
                        <span class="admin-form__help">Adds a call-to-action button to the service card.</span>
                    </div>
                    <div class="admin-form__group">
                        <label for="link_label" class="admin-form__label">Link Button Label</label>
                        <input type="text" name="link_label" id="link_label" class="admin-form__input" placeholder="e.g. View Live System" value="<?= sanitize($_POST['link_label'] ?? $edit_item['link_label'] ?? '') ?>">
                    </div>
                </div>

                <!-- Status -->
                <div class="admin-form__group">
                    <label class="admin-form__checkbox">
                        <input type="checkbox" name="is_active" value="1" <?= (isset($_POST['is_active']) || ($action === 'edit' && $edit_item['is_active']) || $action === 'add') ? 'checked' : '' ?>>
                        <span><strong>Active</strong> (displays on the public Services page)</span>
                    </label>
                </div>

                <!-- Form Actions -->
                <div class="admin-form__actions">
                    <button type="submit" class="admin-btn admin-btn--primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save Service
                    </button>
                    <a href="<?= ADMIN_URL ?>/services.php" class="admin-btn admin-btn--outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Script for Icon preview & preset picker -->
    <script>
    (function () {
        const iconInput   = document.getElementById('icon_svg');
        const iconPreview = document.getElementById('iconPreview');
        const iconPreset  = document.getElementById('iconPreset');

        function refreshPreview() {
            iconPreview.innerHTML = iconInput.value;
        }

        iconInput.addEventListener('input', refreshPreview);
        iconPreset.addEventListener('change', function () {
            if (this.value) {
                iconInput.value = this.value;
                refreshPreview();
            }
        });
    })();

    function previewServiceImage(input) {
        var box  = document.getElementById('serviceImagePreview');
        var img  = document.getElementById('serviceImagePreviewImg');
        var help = document.getElementById('serviceImagePreviewHelp');
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                img.src = e.target.result;
                box.style.display = 'block';
                help.textContent = 'New image selected: ' + input.files[0].name + ' (saves when you submit).';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
