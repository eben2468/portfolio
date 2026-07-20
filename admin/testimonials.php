<?php
/**
 * Nadics Digital Solution — Admin Testimonials Management
 */

$admin_page = 'testimonials';

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
$edit_testimonial = null;

// Load testimonial if in edit mode
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    if ($db) {
        try {
            $stmt = $db->prepare("SELECT * FROM testimonials WHERE id = :id");
            $stmt->execute([':id' => $edit_id]);
            $edit_testimonial = $stmt->fetch();
            if (!$edit_testimonial) {
                $_SESSION['flash_msg'] = 'Testimonial not found.';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . ADMIN_URL . '/testimonials.php');
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
                    $stmt = $db->prepare("SELECT client_name FROM testimonials WHERE id = :id");
                    $stmt->execute([':id' => $delete_id]);
                    $client = $stmt->fetchColumn() ?: 'Unknown';
                    
                    $stmt = $db->prepare("DELETE FROM testimonials WHERE id = :id");
                    $stmt->execute([':id' => $delete_id]);
                    
                    logActivity($_SESSION['admin_id'], 'Deleted testimonial', 'testimonials', $delete_id, "Client: $client");
                    $_SESSION['flash_msg'] = 'Testimonial deleted successfully.';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: ' . ADMIN_URL . '/testimonials.php');
                    exit;
                } catch (PDOException $e) {
                    $flash_message = 'Error deleting testimonial: ' . $e->getMessage();
                    $flash_type = 'error';
                }
            }
        } elseif ($post_action === 'save') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $client_name = trim($_POST['client_name'] ?? '');
            $client_role = trim($_POST['client_role'] ?? '');
            $quote = trim($_POST['quote'] ?? '');
            $avatar_initials = strtoupper(trim($_POST['avatar_initials'] ?? ''));
            $rating = (int)($_POST['rating'] ?? 5);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
            
            // Auto generate initials if empty
            if (empty($avatar_initials) && !empty($client_name)) {
                $words = explode(' ', $client_name);
                foreach ($words as $w) {
                    $avatar_initials .= strtoupper(substr($w, 0, 1));
                }
                $avatar_initials = substr($avatar_initials, 0, 2);
            }
            
            // Validate required fields
            if (empty($client_name) || empty($client_role) || empty($quote)) {
                $flash_message = 'Please fill in all required fields (Client Name, Role, Quote).';
                $flash_type = 'error';
            } else {
                if ($db) {
                    try {
                        if ($id > 0) {
                            // Update
                            $stmt = $db->prepare("
                                UPDATE testimonials SET
                                    client_name = :client_name,
                                    client_role = :client_role,
                                    quote = :quote,
                                    avatar_initials = :avatar_initials,
                                    rating = :rating,
                                    is_active = :is_active,
                                    display_order = :display_order
                                WHERE id = :id
                            ");
                            $stmt->execute([
                                ':client_name'     => $client_name,
                                ':client_role'     => $client_role,
                                ':quote'           => $quote,
                                ':avatar_initials' => $avatar_initials,
                                ':rating'          => $rating,
                                ':is_active'       => $is_active,
                                ':display_order'   => $display_order,
                                ':id'              => $id,
                            ]);
                            logActivity($_SESSION['admin_id'], 'Updated testimonial', 'testimonials', $id, "Client: $client_name");
                            $_SESSION['flash_msg'] = 'Testimonial updated successfully.';
                            $_SESSION['flash_type'] = 'success';
                        } else {
                            // Insert
                            $stmt = $db->prepare("
                                INSERT INTO testimonials (client_name, client_role, quote, avatar_initials, rating, is_active, display_order)
                                VALUES (:client_name, :client_role, :quote, :avatar_initials, :rating, :is_active, :display_order)
                            ");
                            $stmt->execute([
                                ':client_name'     => $client_name,
                                ':client_role'     => $client_role,
                                ':quote'           => $quote,
                                ':avatar_initials' => $avatar_initials,
                                ':rating'          => $rating,
                                ':is_active'       => $is_active,
                                ':display_order'   => $display_order,
                            ]);
                            $new_id = $db->lastInsertId();
                            logActivity($_SESSION['admin_id'], 'Created testimonial', 'testimonials', $new_id, "Client: $client_name");
                            $_SESSION['flash_msg'] = 'Testimonial created successfully.';
                            $_SESSION['flash_type'] = 'success';
                        }
                        header('Location: ' . ADMIN_URL . '/testimonials.php');
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

$admin_title = 'Client Testimonials';
if ($action === 'add') $admin_title = 'Add Testimonial';
if ($action === 'edit') $admin_title = 'Edit Testimonial';

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
    <!-- ═══ Testimonials List Mode ═══ -->
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">All Testimonials</h2>
            <a href="<?= ADMIN_URL ?>/testimonials.php?action=add" class="admin-btn admin-btn--primary admin-btn--sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Testimonial
            </a>
        </div>
        <div class="admin-card__body" style="padding: 0;">
            <?php
            $testimonials = [];
            if ($db) {
                try {
                    $testimonials = $db->query("SELECT * FROM testimonials ORDER BY display_order ASC, created_at DESC")->fetchAll();
                } catch (PDOException $e) {
                    echo '<div class="admin-alert admin-alert--error" style="margin: 1.5rem;">Database query error: ' . sanitize($e->getMessage()) . '</div>';
                }
            }
            
            if (empty($testimonials)): ?>
                <div class="admin-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <h3 class="admin-empty__title">No Testimonials Found</h3>
                    <p class="admin-empty__desc">Manage and show client testimonials on the home page carousel.</p>
                    <a href="<?= ADMIN_URL ?>/testimonials.php?action=add" class="admin-btn admin-btn--primary">Add Testimonial</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">Avatar</th>
                                <th>Client Name</th>
                                <th>Role / Designation</th>
                                <th>Quote</th>
                                <th>Rating</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($testimonials as $t): ?>
                                <tr>
                                    <td>
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 0.85rem;">
                                            <?= sanitize($t['avatar_initials'] ?? 'CL') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--admin-text);"><?= sanitize($t['client_name']) ?></div>
                                    </td>
                                    <td style="font-size: 0.85rem; color: var(--admin-text-light);">
                                        <?= sanitize($t['client_role']) ?>
                                    </td>
                                    <td class="truncate" style="max-width: 300px;" title="<?= sanitize($t['quote']) ?>">
                                        <?= sanitize($t['quote']) ?>
                                    </td>
                                    <td>
                                        <div style="color: var(--warning); letter-spacing: 1px;">
                                            <?= str_repeat('★', $t['rating']) . str_repeat('☆', 5 - $t['rating']) ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center; font-weight: 500;">
                                        <?= $t['display_order'] ?>
                                    </td>
                                    <td>
                                        <?php if ($t['is_active']): ?>
                                            <span class="admin-badge admin-badge--success">Active</span>
                                        <?php else: ?>
                                            <span class="admin-badge admin-badge--muted">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= ADMIN_URL ?>/testimonials.php?action=edit&id=<?= $t['id'] ?>" class="admin-btn admin-btn--outline admin-btn--sm" title="Edit">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                            </a>
                                            
                                            <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this testimonial?');">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
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
    <!-- ═══ Testimonial Add / Edit Mode ═══ -->
    <div class="admin-card" style="max-width: 800px; margin: 0 auto;">
        <div class="admin-card__header">
            <h2 class="admin-card__title"><?= $action === 'add' ? 'Add New Testimonial' : 'Edit Testimonial: ' . sanitize($edit_testimonial['client_name']) ?></h2>
            <a href="<?= ADMIN_URL ?>/testimonials.php" class="admin-btn admin-btn--outline admin-btn--sm">Cancel</a>
        </div>
        <div class="admin-card__body">
            <form action="" method="POST" class="admin-form">
                <?= csrfField() ?>
                <input type="hidden" name="post_action" value="save">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $edit_testimonial['id'] ?>">
                <?php endif; ?>

                <!-- Row 1: Client Name & Role -->
                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="client_name" class="admin-form__label">Client Name <span class="required">*</span></label>
                        <input type="text" name="client_name" id="client_name" class="admin-form__input" placeholder="e.g. Kwame Asante" value="<?= sanitize($_POST['client_name'] ?? $edit_testimonial['client_name'] ?? '') ?>" required>
                    </div>
                    <div class="admin-form__group">
                        <label for="client_role" class="admin-form__label">Role / Designation <span class="required">*</span></label>
                        <input type="text" name="client_role" id="client_role" class="admin-form__input" placeholder="e.g. CEO, RetailHub Ghana" value="<?= sanitize($_POST['client_role'] ?? $edit_testimonial['client_role'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- Row 2: Avatar Initials, Rating, Display Order -->
                <div class="admin-form__row" style="grid-template-columns: 1fr 1fr 1fr;">
                    <div class="admin-form__group">
                        <label for="avatar_initials" class="admin-form__label">Avatar Initials</label>
                        <input type="text" name="avatar_initials" id="avatar_initials" class="admin-form__input" placeholder="e.g. KA" maxlength="3" value="<?= sanitize($_POST['avatar_initials'] ?? $edit_testimonial['avatar_initials'] ?? '') ?>">
                        <span class="admin-form__help">Leave blank to auto-generate.</span>
                    </div>
                    <div class="admin-form__group">
                        <label for="rating" class="admin-form__label">Rating (Stars) <span class="required">*</span></label>
                        <select name="rating" id="rating" class="admin-form__select" required>
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>" <?= (int)($_POST['rating'] ?? $edit_testimonial['rating'] ?? 5) === $i ? 'selected' : '' ?>>
                                    <?= $i ?> Star<?= $i > 1 ? 's' : '' ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="admin-form__group">
                        <label for="display_order" class="admin-form__label">Display Order</label>
                        <input type="number" name="display_order" id="display_order" class="admin-form__input" min="0" value="<?= (int)($_POST['display_order'] ?? $edit_testimonial['display_order'] ?? 0) ?>">
                    </div>
                </div>

                <!-- Quote Textarea -->
                <div class="admin-form__group">
                    <label for="quote" class="admin-form__label">Client Quote / Testimonial <span class="required">*</span></label>
                    <textarea name="quote" id="quote" class="admin-form__textarea" placeholder="Enter what the client said about your work..." required><?= sanitize($_POST['quote'] ?? $edit_testimonial['quote'] ?? '') ?></textarea>
                </div>

                <!-- Active Toggle -->
                <div class="admin-form__group">
                    <label class="admin-form__checkbox">
                        <input type="checkbox" name="is_active" value="1" <?= (isset($_POST['is_active']) || ($action === 'edit' && $edit_testimonial['is_active']) || $action === 'add') ? 'checked' : '' ?>>
                        <span><strong>Publish Testimonial</strong> (displays in the testimonial carousel on the website)</span>
                    </label>
                </div>

                <!-- Form Actions -->
                <div class="admin-form__actions">
                    <button type="submit" class="admin-btn admin-btn--primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save Testimonial
                    </button>
                    <a href="<?= ADMIN_URL ?>/testimonials.php" class="admin-btn admin-btn--outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
