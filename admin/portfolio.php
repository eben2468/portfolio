<?php
/**
 * Nadics Digital Solution — Admin Portfolio Management
 */

$admin_page = 'portfolio';

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

$action = $_GET['action'] ?? 'list';
$edit_item = null;

// Load item if in edit mode
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    if ($db) {
        try {
            $stmt = $db->prepare("SELECT * FROM portfolio_items WHERE id = :id");
            $stmt->execute([':id' => $edit_id]);
            $edit_item = $stmt->fetch();
            if (!$edit_item) {
                $_SESSION['flash_msg'] = 'Portfolio item not found.';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . ADMIN_URL . '/portfolio.php');
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
                    // Get image path to delete file if it's not a seed project
                    $stmt = $db->prepare("SELECT image_url, title FROM portfolio_items WHERE id = :id");
                    $stmt->execute([':id' => $delete_id]);
                    $item = $stmt->fetch();
                    
                    if ($item) {
                        $stmt = $db->prepare("DELETE FROM portfolio_items WHERE id = :id");
                        $stmt->execute([':id' => $delete_id]);
                        
                        // Delete custom uploaded file if not seed image
                        $img_path = __DIR__ . '/../' . $item['image_url'];
                        if (!str_contains($item['image_url'], 'project-') && file_exists($img_path)) {
                            @unlink($img_path);
                        }
                        
                        logActivity($_SESSION['admin_id'], 'Deleted portfolio project', 'portfolio_items', $delete_id, "Title: " . $item['title']);
                        $_SESSION['flash_msg'] = 'Portfolio item deleted successfully.';
                        $_SESSION['flash_type'] = 'success';
                        header('Location: ' . ADMIN_URL . '/portfolio.php');
                        exit;
                    }
                } catch (PDOException $e) {
                    $flash_message = 'Error deleting item: ' . $e->getMessage();
                    $flash_type = 'error';
                }
            }
        } elseif ($post_action === 'save') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $category = $_POST['category'] ?? '';
            $description = trim($_POST['description'] ?? '');
            $client_name = trim($_POST['client_name'] ?? '');
            $project_url = trim($_POST['project_url'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
            
            // Auto-generate slug if empty
            if (empty($slug) && !empty($title)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            }
            
            // Validate required inputs
            if (empty($title) || empty($category) || empty($description)) {
                $flash_message = 'Please fill in all required fields (Title, Category, Description).';
                $flash_type = 'error';
            } else {
                $image_url = $edit_item['image_url'] ?? '';
                
                // Handle file upload if present
                if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['project_image']['tmp_name'];
                    $file_name = $_FILES['project_image']['name'];
                    $file_size = $_FILES['project_image']['size'];
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
                        $upload_dir = __DIR__ . '/../assets/images/portfolio/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        // Sanitize filename & save
                        $new_filename = uniqid('project_', true) . '.' . $file_ext;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($file_tmp, $upload_path)) {
                            // Delete old image if custom uploaded
                            if ($id > 0 && !empty($image_url) && !str_contains($image_url, 'project-')) {
                                $old_img_path = __DIR__ . '/../' . $image_url;
                                if (file_exists($old_img_path)) {
                                    @unlink($old_img_path);
                                }
                            }
                            $image_url = 'assets/images/portfolio/' . $new_filename;
                        } else {
                            $flash_message = 'Failed to save uploaded image.';
                            $flash_type = 'error';
                        }
                    }
                }
                
                // Proceed if no upload error or error was resolved
                if ($flash_type !== 'error') {
                    if (empty($image_url) && $id === 0) {
                        $flash_message = 'An image is required for new portfolio items.';
                        $flash_type = 'error';
                    } else {
                        if ($db) {
                            try {
                                // Check if slug is unique (excluding current item)
                                $stmt = $db->prepare("SELECT COUNT(*) FROM portfolio_items WHERE slug = :slug AND id != :id");
                                $stmt->execute([':slug' => $slug, ':id' => $id]);
                                
                                if ($stmt->fetchColumn() > 0) {
                                    // Make slug unique by appending id or random string
                                    $slug .= '-' . rand(100, 999);
                                }
                                
                                if ($id > 0) {
                                    // Update
                                    $stmt = $db->prepare("
                                        UPDATE portfolio_items SET
                                            title = :title,
                                            slug = :slug,
                                            category = :category,
                                            description = :description,
                                            image_url = :image_url,
                                            client_name = :client_name,
                                            project_url = :project_url,
                                            is_featured = :is_featured,
                                            display_order = :display_order
                                        WHERE id = :id
                                    ");
                                    $stmt->execute([
                                        ':title'         => $title,
                                        ':slug'          => $slug,
                                        ':category'      => $category,
                                        ':description'   => $description,
                                        ':image_url'     => $image_url,
                                        ':client_name'   => $client_name ?: null,
                                        ':project_url'   => $project_url ?: null,
                                        ':is_featured'   => $is_featured,
                                        ':display_order' => $display_order,
                                        ':id'            => $id,
                                    ]);
                                    
                                    logActivity($_SESSION['admin_id'], 'Updated portfolio project', 'portfolio_items', $id, "Title: $title");
                                    $_SESSION['flash_msg'] = 'Portfolio item updated successfully.';
                                    $_SESSION['flash_type'] = 'success';
                                } else {
                                    // Insert
                                    $stmt = $db->prepare("
                                        INSERT INTO portfolio_items (title, slug, category, description, image_url, client_name, project_url, is_featured, display_order)
                                        VALUES (:title, :slug, :category, :description, :image_url, :client_name, :project_url, :is_featured, :display_order)
                                    ");
                                    $stmt->execute([
                                        ':title'         => $title,
                                        ':slug'          => $slug,
                                        ':category'      => $category,
                                        ':description'   => $description,
                                        ':image_url'     => $image_url,
                                        ':client_name'   => $client_name ?: null,
                                        ':project_url'   => $project_url ?: null,
                                        ':is_featured'   => $is_featured,
                                        ':display_order' => $display_order,
                                    ]);
                                    $new_id = $db->lastInsertId();
                                    logActivity($_SESSION['admin_id'], 'Created portfolio project', 'portfolio_items', $new_id, "Title: $title");
                                    $_SESSION['flash_msg'] = 'Portfolio item created successfully.';
                                    $_SESSION['flash_type'] = 'success';
                                }
                                header('Location: ' . ADMIN_URL . '/portfolio.php');
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

$admin_title = 'Portfolio Projects';
if ($action === 'add') $admin_title = 'Add Project';
if ($action === 'edit') $admin_title = 'Edit Project';

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
    <!-- ═══ Portfolio List Mode ═══ -->
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">All Projects</h2>
            <a href="<?= ADMIN_URL ?>/portfolio.php?action=add" class="admin-btn admin-btn--primary admin-btn--sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add New Project
            </a>
        </div>
        <div class="admin-card__body" style="padding: 0;">
            <?php
            $items = getPortfolioItems('all', false);
            if (empty($items)): ?>
                <div class="admin-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                    </svg>
                    <h3 class="admin-empty__title">No Projects Found</h3>
                    <p class="admin-empty__desc">Add some projects to showcase your digital solutions portfolio.</p>
                    <a href="<?= ADMIN_URL ?>/portfolio.php?action=add" class="admin-btn admin-btn--primary">Add Project</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Preview</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Client</th>
                                <th>Display Order</th>
                                <th>Featured?</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div style="width: 60px; height: 45px; border-radius: var(--radius-sm); overflow: hidden; background: var(--admin-bg);">
                                            <img src="<?= SITE_URL ?>/<?= sanitize($item['image_url']) ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--admin-text);"><?= sanitize($item['title']) ?></div>
                                        <div style="font-size: 0.75rem; color: var(--admin-text-light);">/<?= sanitize($item['slug']) ?></div>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge--primary"><?= formatCategory($item['category']) ?></span>
                                    </td>
                                    <td style="font-size: 0.85rem; color: var(--admin-text-light);">
                                        <?= sanitize($item['client_name'] ?? '—') ?>
                                    </td>
                                    <td style="text-align: center; font-weight: 500;">
                                        <?= $item['display_order'] ?>
                                    </td>
                                    <td>
                                        <?php if ($item['is_featured']): ?>
                                            <span class="admin-badge admin-badge--success">Featured</span>
                                        <?php else: ?>
                                            <span class="admin-badge admin-badge--muted">Regular</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= ADMIN_URL ?>/portfolio.php?action=edit&id=<?= $item['id'] ?>" class="admin-btn admin-btn--outline admin-btn--sm" title="Edit">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                            </a>
                                            
                                            <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this project? This will permanently delete the database entry and image.');">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
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

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <!-- ═══ Portfolio Add / Edit Mode ═══ -->
    <div class="admin-card" style="max-width: 800px; margin: 0 auto;">
        <div class="admin-card__header">
            <h2 class="admin-card__title"><?= $action === 'add' ? 'Add New Project' : 'Edit Project: ' . sanitize($edit_item['title']) ?></h2>
            <a href="<?= ADMIN_URL ?>/portfolio.php" class="admin-btn admin-btn--outline admin-btn--sm">Cancel</a>
        </div>
        <div class="admin-card__body">
            <form action="" method="POST" enctype="multipart/form-data" class="admin-form">
                <?= csrfField() ?>
                <input type="hidden" name="post_action" value="save">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $edit_item['id'] ?>">
                <?php endif; ?>

                <!-- Row 1: Title & Slug -->
                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="title" class="admin-form__label">Project Title <span class="required">*</span></label>
                        <input type="text" name="title" id="title" class="admin-form__input" placeholder="e.g. Corporate Brand Redesign" value="<?= sanitize($_POST['title'] ?? $edit_item['title'] ?? '') ?>" required>
                    </div>
                    <div class="admin-form__group">
                        <label for="slug" class="admin-form__label">Slug (URL identifier)</label>
                        <input type="text" name="slug" id="slug" class="admin-form__input" placeholder="e.g. corporate-brand-redesign" value="<?= sanitize($_POST['slug'] ?? $edit_item['slug'] ?? '') ?>">
                        <span class="admin-form__help">Leave blank to auto-generate from the title.</span>
                    </div>
                </div>

                <!-- Row 2: Category & Display Order -->
                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="category" class="admin-form__label">Category <span class="required">*</span></label>
                        <select name="category" id="category" class="admin-form__select" required>
                            <option value="">-- Select Category --</option>
                            <option value="web-dev" <?= ($_POST['category'] ?? $edit_item['category'] ?? '') === 'web-dev' ? 'selected' : '' ?>>Web Development</option>
                            <option value="graphic-design" <?= ($_POST['category'] ?? $edit_item['category'] ?? '') === 'graphic-design' ? 'selected' : '' ?>>Graphic Design</option>
                            <option value="it-solutions" <?= ($_POST['category'] ?? $edit_item['category'] ?? '') === 'it-solutions' ? 'selected' : '' ?>>IT Solutions</option>
                            <option value="voting-systems" <?= ($_POST['category'] ?? $edit_item['category'] ?? '') === 'voting-systems' ? 'selected' : '' ?>>Voting Systems</option>
                        </select>
                    </div>
                    <div class="admin-form__group">
                        <label for="display_order" class="admin-form__label">Display Order</label>
                        <input type="number" name="display_order" id="display_order" class="admin-form__input" min="0" value="<?= (int)($_POST['display_order'] ?? $edit_item['display_order'] ?? 0) ?>">
                        <span class="admin-form__help">Smaller values are shown first in the gallery.</span>
                    </div>
                </div>

                <!-- Row 3: Client Name & Project URL -->
                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="client_name" class="admin-form__label">Client Name</label>
                        <input type="text" name="client_name" id="client_name" class="admin-form__input" placeholder="e.g. MedCare Hospital" value="<?= sanitize($_POST['client_name'] ?? $edit_item['client_name'] ?? '') ?>">
                    </div>
                    <div class="admin-form__group">
                        <label for="project_url" class="admin-form__label">Project URL (External Link)</label>
                        <input type="url" name="project_url" id="project_url" class="admin-form__input" placeholder="e.g. https://example.com" value="<?= sanitize($_POST['project_url'] ?? $edit_item['project_url'] ?? '') ?>">
                    </div>
                </div>

                <!-- Description -->
                <div class="admin-form__group">
                    <label for="description" class="admin-form__label">Project Description <span class="required">*</span></label>
                    <textarea name="description" id="description" class="admin-form__textarea" placeholder="Describe the details of the project, technical stack used, client requests, and the final solution..." required><?= sanitize($_POST['description'] ?? $edit_item['description'] ?? '') ?></textarea>
                </div>

                <!-- Image Upload & Preview -->
                <div class="admin-form__group">
                    <label class="admin-form__label">Project Image <span class="required"><?= $action === 'add' ? '*' : '' ?></span></label>
                    
                    <div class="image-upload" onclick="document.getElementById('project_image').click();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <div class="image-upload__text">Click to choose image or drag &amp; drop</div>
                        <div class="image-upload__hint">PNG, JPG, WEBP or SVG up to 15MB</div>
                        <input type="file" name="project_image" id="project_image" style="display:none;" accept="image/*" onchange="previewImage(this)">
                    </div>

                    <!-- Image Preview Area -->
                    <div class="image-preview" id="imagePreviewContainer" style="<?= ($action === 'edit' && !empty($edit_item['image_url'])) ? '' : 'display:none;' ?>">
                        <img id="imagePreviewImage" src="<?= ($action === 'edit' && !empty($edit_item['image_url'])) ? SITE_URL . '/' . $edit_item['image_url'] : '#' ?>" alt="Preview">
                        <span class="admin-form__help" id="imagePreviewHelp" style="display:block; margin-top:0.25rem;">
                            <?= $action === 'edit' ? 'Current image shown above. Upload a new one to replace it.' : 'Selected image preview.' ?>
                        </span>
                    </div>
                </div>

                <!-- Featured Toggle -->
                <div class="admin-form__group">
                    <label class="admin-form__checkbox">
                        <input type="checkbox" name="is_featured" value="1" <?= (isset($_POST['is_featured']) || ($action === 'edit' && $edit_item['is_featured'])) ? 'checked' : '' ?>>
                        <span><strong>Feature this project</strong> (displays prominently on the home page showcase grid)</span>
                    </label>
                </div>

                <!-- Form Actions -->
                <div class="admin-form__actions">
                    <button type="submit" class="admin-btn admin-btn--primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save Project
                    </button>
                    <a href="<?= ADMIN_URL ?>/portfolio.php" class="admin-btn admin-btn--outline">Cancel</a>
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
