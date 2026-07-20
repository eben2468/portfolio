<?php
/**
 * Nadics Digital Solution — Admin Team Management
 */

$admin_page = 'team';

require_once __DIR__ . '/includes/auth.php';

$db = getDB();
$flash_message = '';
$flash_type = '';

// Make sure newer optional columns (e.g. linkedin_url) exist
if ($db) {
    try { ensureTeamMemberColumns($db); } catch (PDOException $e) { /* handled elsewhere */ }
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
    if ($db) {
        try {
            $stmt = $db->prepare("SELECT * FROM team_members WHERE id = :id");
            $stmt->execute([':id' => $edit_id]);
            $edit_item = $stmt->fetch();
            if (!$edit_item) {
                $_SESSION['flash_msg'] = 'Team member not found.';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . ADMIN_URL . '/team.php');
                exit;
            }
        } catch (PDOException $e) {
            $flash_message = 'Database error: ' . $e->getMessage();
            $flash_type = 'error';
            $action = 'list';
        }
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
                    // Get image path to delete file
                    $stmt = $db->prepare("SELECT image_url, name FROM team_members WHERE id = :id");
                    $stmt->execute([':id' => $delete_id]);
                    $item = $stmt->fetch();
                    
                    if ($item) {
                        $stmt = $db->prepare("DELETE FROM team_members WHERE id = :id");
                        $stmt->execute([':id' => $delete_id]);
                        
                        // Delete custom uploaded file if not a seed image
                        $img_path = __DIR__ . '/../' . $item['image_url'];
                        if (!str_contains($item['image_url'], 'member-') && file_exists($img_path)) {
                            @unlink($img_path);
                        }
                        
                        logActivity($_SESSION['admin_id'], 'Deleted team member', 'team_members', $delete_id, "Name: " . $item['name']);
                        $_SESSION['flash_msg'] = 'Team member deleted successfully.';
                        $_SESSION['flash_type'] = 'success';
                        header('Location: ' . ADMIN_URL . '/team.php');
                        exit;
                    }
                } catch (PDOException $e) {
                    $flash_message = 'Error deleting team member: ' . $e->getMessage();
                    $flash_type = 'error';
                }
            }
        } elseif ($post_action === 'save') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $name = trim($_POST['name'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $linkedin_url = trim($_POST['linkedin_url'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
            
            // Validate required inputs
            if (empty($name) || empty($title) || empty($bio)) {
                $flash_message = 'Please fill in all required fields (Name, Title, Biography).';
                $flash_type = 'error';
            } else {
                $image_url = $edit_item['image_url'] ?? '';
                
                // Handle file upload if present
                if (isset($_FILES['team_image']) && $_FILES['team_image']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['team_image']['tmp_name'];
                    $file_name = $_FILES['team_image']['name'];
                    $file_size = $_FILES['team_image']['size'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
                    
                    if (!in_array($file_ext, $allowed_exts)) {
                        $flash_message = 'Invalid file type. Allowed formats: JPG, JPEG, PNG, SVG, WEBP.';
                        $flash_type = 'error';
                    } elseif ($file_size > 15 * 1024 * 1024) { // 15MB limit
                        $flash_message = 'File is too large. Maximum size is 15MB.';
                        $flash_type = 'error';
                    } else {
                        // Create directory if it doesn't exist
                        $upload_dir = __DIR__ . '/../assets/images/team/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        // Sanitize filename & save
                        $new_filename = uniqid('team_', true) . '.' . $file_ext;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($file_tmp, $upload_path)) {
                            // Delete old image if custom uploaded and not seed
                            if ($id > 0 && !empty($image_url) && !str_contains($image_url, 'member-')) {
                                $old_img_path = __DIR__ . '/../' . $image_url;
                                if (file_exists($old_img_path)) {
                                    @unlink($old_img_path);
                                }
                            }
                            $image_url = 'assets/images/team/' . $new_filename;
                        } else {
                            $flash_message = 'Failed to save uploaded image.';
                            $flash_type = 'error';
                        }
                    }
                }
                
                // Proceed if no error
                if ($flash_type !== 'error') {
                    if (empty($image_url) && $id === 0) {
                        $flash_message = 'A photo is required for new team members.';
                        $flash_type = 'error';
                    } else {
                        if ($db) {
                            try {
                                if ($id > 0) {
                                    // Update
                                    $stmt = $db->prepare("
                                        UPDATE team_members SET
                                            name = :name,
                                            title = :title,
                                            bio = :bio,
                                            linkedin_url = :linkedin_url,
                                            image_url = :image_url,
                                            is_active = :is_active,
                                            display_order = :display_order
                                        WHERE id = :id
                                    ");
                                    $stmt->execute([
                                        ':name'          => $name,
                                        ':title'         => $title,
                                        ':bio'           => $bio,
                                        ':linkedin_url'  => $linkedin_url ?: null,
                                        ':image_url'     => $image_url,
                                        ':is_active'     => $is_active,
                                        ':display_order' => $display_order,
                                        ':id'            => $id
                                    ]);
                                    logActivity($_SESSION['admin_id'], 'Updated team member details', 'team_members', $id, "Name: $name");
                                    $_SESSION['flash_msg'] = 'Team member updated successfully.';
                                } else {
                                    // Insert
                                    $stmt = $db->prepare("
                                        INSERT INTO team_members (name, title, bio, linkedin_url, image_url, is_active, display_order)
                                        VALUES (:name, :title, :bio, :linkedin_url, :image_url, :is_active, :display_order)
                                    ");
                                    $stmt->execute([
                                        ':name'          => $name,
                                        ':title'         => $title,
                                        ':bio'           => $bio,
                                        ':linkedin_url'  => $linkedin_url ?: null,
                                        ':image_url'     => $image_url,
                                        ':is_active'     => $is_active,
                                        ':display_order' => $display_order
                                    ]);
                                    $new_id = $db->lastInsertId();
                                    logActivity($_SESSION['admin_id'], 'Created new team member', 'team_members', $new_id, "Name: $name");
                                    $_SESSION['flash_msg'] = 'Team member added successfully.';
                                }
                                $_SESSION['flash_type'] = 'success';
                                header('Location: ' . ADMIN_URL . '/team.php');
                                exit;
                            } catch (PDOException $e) {
                                $flash_message = 'Database save error: ' . $e->getMessage();
                                $flash_type = 'error';
                            }
                        }
                    }
                }
            }
        }
    }
}

$admin_title = 'Manage Leadership';
if ($action === 'add') $admin_title = 'Add Team Member';
if ($action === 'edit') $admin_title = 'Edit Team Member';

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
    <!-- ═══ Team Listing Mode ═══ -->
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">Leadership Team Members</h2>
            <a href="<?= ADMIN_URL ?>/team.php?action=add" class="admin-btn admin-btn--primary admin-btn--sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Team Member
            </a>
        </div>
        <div class="admin-card__body" style="padding: 0;">
            <?php
            $team = [];
            if ($db) {
                try {
                    $team = $db->query("SELECT * FROM team_members ORDER BY display_order ASC, name ASC")->fetchAll();
                } catch (PDOException $e) {
                    echo '<div class="admin-alert admin-alert--error" style="margin: 1.5rem;">Database query error: ' . sanitize($e->getMessage()) . '</div>';
                }
            }
            
            if (empty($team)): ?>
                <div class="admin-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <h3 class="admin-empty__title">No Team Members Found</h3>
                    <p class="admin-empty__desc">There are no leadership team members configured yet.</p>
                    <a href="<?= ADMIN_URL ?>/team.php?action=add" class="admin-btn admin-btn--primary" style="margin-top: 1rem;">Add First Member</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Title / Role</th>
                                <th>Display Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($team as $member): ?>
                                <tr>
                                    <td>
                                        <div style="width: 48px; height: 48px; border-radius: 50%; overflow: hidden; background: var(--admin-bg); border: 1px solid var(--admin-border-light);">
                                            <img src="<?= SITE_URL . '/' . sanitize($member['image_url']) ?>" alt="<?= sanitize($member['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--admin-text);"><?= sanitize($member['name']) ?></div>
                                    </td>
                                    <td>
                                        <div style="color: var(--admin-text-light);"><?= sanitize($member['title']) ?></div>
                                    </td>
                                    <td>
                                        <span style="font-family: monospace; font-size: 0.9rem;"><?= $member['display_order'] ?></span>
                                    </td>
                                    <td>
                                        <?php if ($member['is_active']): ?>
                                            <span class="admin-badge admin-badge--success">Active</span>
                                        <?php else: ?>
                                            <span class="admin-badge admin-badge--muted">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= ADMIN_URL ?>/team.php?action=edit&id=<?= $member['id'] ?>" class="admin-btn admin-btn--outline admin-btn--sm" title="Edit Member">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                    <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                </svg>
                                            </a>
                                            <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this team member?');">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="id" value="<?= $member['id'] ?>">
                                                <input type="hidden" name="post_action" value="delete">
                                                <button type="submit" class="admin-btn admin-btn--danger admin-btn--sm" title="Delete Member" style="padding: 0.35rem 0.5rem;">
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

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <!-- ═══ Team Add / Edit Mode ═══ -->
    <div class="admin-card" style="max-width: 800px; margin: 0 auto;">
        <div class="admin-card__header">
            <h2 class="admin-card__title"><?= $action === 'add' ? 'Add New Team Member' : 'Edit Team Member: ' . sanitize($edit_item['name']) ?></h2>
            <a href="<?= ADMIN_URL ?>/team.php" class="admin-btn admin-btn--outline admin-btn--sm">Cancel</a>
        </div>
        <div class="admin-card__body">
            <form action="" method="POST" enctype="multipart/form-data" class="admin-form">
                <?= csrfField() ?>
                <input type="hidden" name="post_action" value="save">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $edit_item['id'] ?>">
                <?php endif; ?>

                <!-- Row 1: Name & Title -->
                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="name" class="admin-form__label">Full Name <span class="required">*</span></label>
                        <input type="text" name="name" id="name" class="admin-form__input" placeholder="e.g. Ebenezer Owusu" value="<?= sanitize($_POST['name'] ?? $edit_item['name'] ?? '') ?>" required>
                    </div>
                    <div class="admin-form__group">
                        <label for="title" class="admin-form__label">Title / Role <span class="required">*</span></label>
                        <input type="text" name="title" id="title" class="admin-form__input" placeholder="e.g. Chief Executive Officer" value="<?= sanitize($_POST['title'] ?? $edit_item['title'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- Row 2: Display Order & Status -->
                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="display_order" class="admin-form__label">Display Order</label>
                        <input type="number" name="display_order" id="display_order" class="admin-form__input" min="0" value="<?= (int)($_POST['display_order'] ?? $edit_item['display_order'] ?? 0) ?>">
                        <span class="admin-form__help">Smaller values are shown first in the team grid.</span>
                    </div>
                    <div class="admin-form__group">
                        <label class="admin-form__label">Publish Status</label>
                        <label class="admin-form__checkbox" style="margin-top: 0.85rem;">
                            <input type="checkbox" name="is_active" value="1" <?= (isset($_POST['is_active']) || ($action === 'edit' && $edit_item['is_active']) || $action === 'add') ? 'checked' : '' ?>>
                            <span><strong>Active Member</strong> (displays on the About Us page)</span>
                        </label>
                    </div>
                </div>

                <!-- Biography -->
                <div class="admin-form__group">
                    <label for="bio" class="admin-form__label">Short Biography <span class="required">*</span></label>
                    <textarea name="bio" id="bio" class="admin-form__textarea" placeholder="Enter a brief background, qualifications, and focus area for this team member..." required><?= sanitize($_POST['bio'] ?? $edit_item['bio'] ?? '') ?></textarea>
                </div>

                <!-- LinkedIn -->
                <div class="admin-form__group">
                    <label for="linkedin_url" class="admin-form__label">LinkedIn Profile URL</label>
                    <input type="url" name="linkedin_url" id="linkedin_url" class="admin-form__input" placeholder="e.g. https://www.linkedin.com/in/username" value="<?= sanitize($_POST['linkedin_url'] ?? $edit_item['linkedin_url'] ?? '') ?>">
                    <span class="admin-form__help">Optional. Adds a LinkedIn button to the member's card on the About page.</span>
                </div>

                <!-- Photo Upload & Preview -->
                <div class="admin-form__group">
                    <label class="admin-form__label">Team Member Photo <span class="required"><?= $action === 'add' ? '*' : '' ?></span></label>
                    
                    <div class="image-upload" onclick="document.getElementById('project_image').click();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <div class="image-upload__text">Click to choose photo or drag &amp; drop</div>
                        <div class="image-upload__hint">PNG, JPG, WEBP or SVG up to 15MB</div>
                        <input type="file" name="team_image" id="project_image" style="display:none;" accept="image/*" onchange="previewImage(this)">
                    </div>

                    <!-- Image Preview Area -->
                    <div class="image-preview" id="imagePreviewContainer" style="<?= ($action === 'edit' && !empty($edit_item['image_url'])) ? '' : 'display:none;' ?>">
                        <img id="imagePreviewImage" src="<?= ($action === 'edit' && !empty($edit_item['image_url'])) ? SITE_URL . '/' . $edit_item['image_url'] : '#' ?>" alt="Preview" style="max-height: 200px; border-radius: var(--radius-md);">
                        <span class="admin-form__help" id="imagePreviewHelp" style="display:block; margin-top:0.25rem;">
                            <?= $action === 'edit' ? 'Current photo shown above. Upload a new one to replace it.' : 'Selected photo preview.' ?>
                        </span>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="admin-form__actions">
                    <button type="submit" class="admin-btn admin-btn--primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save Team Member
                    </button>
                    <a href="<?= ADMIN_URL ?>/team.php" class="admin-btn admin-btn--outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Script for Image Preview -->
    <script>
    function previewImage(input) {
        const preview = document.getElementById('imagePreviewImage');
        const container = document.getElementById('imagePreviewContainer');
        const helpText = document.getElementById('imagePreviewHelp');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                container.style.display = 'block';
                helpText.textContent = 'Selected image file: ' + input.files[0].name;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
