<?php
/**
 * Nadics Digital Solution — Admin Contact Messages
 */

$admin_page = 'contacts';

require_once __DIR__ . '/includes/auth.php';

$db = getDB();
$flash_message = '';
$flash_type = '';

// Handle actions (delete, mark_read, mark_unread) via POST for security
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $csrf = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf)) {
        $flash_message = 'Security token invalid. Action blocked.';
        $flash_type = 'error';
    } else {
        $msg_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($db && $msg_id > 0) {
            if ($action === 'delete') {
                try {
                    // Fetch details for logging before deleting
                    $stmt = $db->prepare("SELECT full_name FROM contacts WHERE id = :id");
                    $stmt->execute([':id' => $msg_id]);
                    $contact_name = $stmt->fetchColumn() ?: 'Unknown';
                    
                    $stmt = $db->prepare("DELETE FROM contacts WHERE id = :id");
                    $stmt->execute([':id' => $msg_id]);
                    
                    logActivity($_SESSION['admin_id'], 'Deleted message', 'contacts', $msg_id, "Sender: $contact_name");
                    $flash_message = 'Message deleted successfully.';
                    $flash_type = 'success';
                    
                    // If viewing, redirect to list
                    if (isset($_GET['id']) && (int)$_GET['id'] === $msg_id) {
                        $_SESSION['flash_msg'] = $flash_message;
                        $_SESSION['flash_type'] = $flash_type;
                        header('Location: ' . ADMIN_URL . '/contacts.php');
                        exit;
                    }
                } catch (PDOException $e) {
                    $flash_message = 'Error deleting message: ' . $e->getMessage();
                    $flash_type = 'error';
                }
            } elseif ($action === 'mark_unread') {
                try {
                    $stmt = $db->prepare("UPDATE contacts SET is_read = 0 WHERE id = :id");
                    $stmt->execute([':id' => $msg_id]);
                    logActivity($_SESSION['admin_id'], 'Marked message as unread', 'contacts', $msg_id);
                    $flash_message = 'Message marked as unread.';
                    $flash_type = 'success';
                } catch (PDOException $e) {
                    $flash_message = 'Error modifying message: ' . $e->getMessage();
                    $flash_type = 'error';
                }
            } elseif ($action === 'mark_read') {
                try {
                    $stmt = $db->prepare("UPDATE contacts SET is_read = 1 WHERE id = :id");
                    $stmt->execute([':id' => $msg_id]);
                    logActivity($_SESSION['admin_id'], 'Marked message as read', 'contacts', $msg_id);
                    $flash_message = 'Message marked as read.';
                    $flash_type = 'success';
                } catch (PDOException $e) {
                    $flash_message = 'Error modifying message: ' . $e->getMessage();
                    $flash_type = 'error';
                }
            }
        }
    }
}

// Check session for flash message from redirect
if (isset($_SESSION['flash_msg'])) {
    $flash_message = $_SESSION['flash_msg'];
    $flash_type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
}

// Check if viewing a specific message
$view_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$contact_detail = null;

if ($view_id > 0 && $db) {
    try {
        $stmt = $db->prepare("SELECT * FROM contacts WHERE id = :id");
        $stmt->execute([':id' => $view_id]);
        $contact_detail = $stmt->fetch();
        
        if ($contact_detail) {
            $admin_title = 'View Message';
            // Mark as read automatically when opened, if not already read
            if (!$contact_detail['is_read']) {
                $db->prepare("UPDATE contacts SET is_read = 1 WHERE id = :id")->execute([':id' => $view_id]);
                logActivity($_SESSION['admin_id'], 'Read message', 'contacts', $view_id, "Sender: " . $contact_detail['full_name']);
                $contact_detail['is_read'] = 1;
            }
        } else {
            $flash_message = 'Message not found.';
            $flash_type = 'error';
            $view_id = 0;
        }
    } catch (PDOException $e) {
        $flash_message = 'Database error: ' . $e->getMessage();
        $flash_type = 'error';
        $view_id = 0;
    }
}

if ($view_id === 0) {
    $admin_title = 'Contact Messages';
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

<?php if ($contact_detail): ?>
    <!-- ═══ Message Detail View ═══ -->
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">Message from <?= sanitize($contact_detail['full_name']) ?></h2>
            <div class="btn-group">
                <a href="<?= ADMIN_URL ?>/contacts.php" class="admin-btn admin-btn--outline admin-btn--sm">
                    &larr; Back to Messages
                </a>
                
                <!-- Action Form for Detail View -->
                <form action="" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $contact_detail['id'] ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="admin-btn admin-btn--danger admin-btn--sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                        Delete Message
                    </button>
                </form>
            </div>
        </div>
        <div class="admin-card__body">
            <div class="contact-detail">
                <!-- Message Body -->
                <div>
                    <h3 style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: var(--admin-text-muted); letter-spacing: 0.05em;">Subject</h3>
                    <p style="font-size: 1.15rem; font-weight: 600; color: var(--admin-text); margin-bottom: 1.5rem;"><?= sanitize($contact_detail['subject']) ?></p>
                    
                    <h3 style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: var(--admin-text-muted); letter-spacing: 0.05em;">Message</h3>
                    <div class="contact-detail__message"><?= sanitize($contact_detail['message']) ?></div>
                </div>

                <!-- Message Meta Sidebar -->
                <div style="background: var(--admin-card); border-left: 1px solid var(--admin-border-light); padding-left: 1.5rem;">
                    <dl class="contact-detail__meta">
                        <dt>Sender Name</dt>
                        <dd><?= sanitize($contact_detail['full_name']) ?></dd>

                        <dt>Email Address</dt>
                        <dd><a href="mailto:<?= sanitize($contact_detail['email']) ?>"><?= sanitize($contact_detail['email']) ?></a></dd>

                        <dt>Phone Number</dt>
                        <dd>
                            <?php if (!empty($contact_detail['phone'])): ?>
                                <a href="tel:<?= str_replace(' ', '', $contact_detail['phone']) ?>"><?= sanitize($contact_detail['phone']) ?></a>
                            <?php else: ?>
                                <em style="color: var(--admin-text-muted);">None</em>
                            <?php endif; ?>
                        </dd>

                        <dt>Submission Date</dt>
                        <dd><?= date('F j, Y, g:i A', strtotime($contact_detail['created_at'])) ?></dd>

                        <dt>IP Address</dt>
                        <dd><?= sanitize($contact_detail['ip_address'] ?? 'Unknown') ?></dd>

                        <dt>Status</dt>
                        <dd>
                            <form action="" method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $contact_detail['id'] ?>">
                                <input type="hidden" name="action" value="mark_unread">
                                <button type="submit" class="admin-btn admin-btn--outline admin-btn--sm" style="margin-top: 0.25rem;">
                                    Mark as Unread
                                </button>
                            </form>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ═══ Messages List View ═══ -->
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">All Contact Messages</h2>
        </div>
        <div class="admin-card__body" style="padding: 0;">
            <?php
            $contacts = [];
            if ($db) {
                try {
                    $contacts = $db->query("SELECT * FROM contacts ORDER BY created_at DESC")->fetchAll();
                } catch (PDOException $e) {
                    echo '<div class="admin-alert admin-alert--error" style="margin: 1.5rem;">Database query error: ' . sanitize($e->getMessage()) . '</div>';
                }
            }
            
            if (empty($contacts)): ?>
                <div class="admin-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <h3 class="admin-empty__title">No Messages Available</h3>
                    <p class="admin-empty__desc">There are no messages from the contact form at this time.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Sender</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $contact): ?>
                                <tr class="<?= $contact['is_read'] ? '' : 'row-unread' ?>">
                                    <td>
                                        <div style="font-weight: 600; color: var(--admin-text);"><?= sanitize($contact['full_name']) ?></div>
                                        <div style="font-size: 0.75rem; color: var(--admin-text-light);"><?= sanitize($contact['email']) ?></div>
                                    </td>
                                    <td class="truncate" title="<?= sanitize($contact['subject']) ?>">
                                        <?= sanitize($contact['subject']) ?>
                                    </td>
                                    <td style="font-size: 0.85rem; color: var(--admin-text-light); white-space: nowrap;">
                                        <?= date('M j, Y — g:i A', strtotime($contact['created_at'])) ?>
                                    </td>
                                    <td>
                                        <?php if ($contact['is_read']): ?>
                                            <span class="admin-badge admin-badge--muted">Read</span>
                                        <?php else: ?>
                                            <span class="admin-badge admin-badge--warning">New</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= ADMIN_URL ?>/contacts.php?id=<?= $contact['id'] ?>" class="admin-btn admin-btn--outline admin-btn--sm" title="Read Message">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </a>
                                            
                                            <!-- Inline Action Buttons (Need CSRF) -->
                                            <form action="" method="POST" style="display:inline;">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="id" value="<?= $contact['id'] ?>">
                                                <?php if ($contact['is_read']): ?>
                                                    <input type="hidden" name="action" value="mark_unread">
                                                    <button type="submit" class="admin-btn admin-btn--outline admin-btn--sm" title="Mark Unread" style="padding: 0.35rem 0.5rem;">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                        </svg>
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="action" value="mark_read">
                                                    <button type="submit" class="admin-btn admin-btn--outline admin-btn--sm" title="Mark Read" style="padding: 0.35rem 0.5rem;">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="9 11 12 14 22 4"/>
                                                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                                                        </svg>
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                            
                                            <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="id" value="<?= $contact['id'] ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="admin-btn admin-btn--danger admin-btn--sm" title="Delete Message" style="padding: 0.35rem 0.5rem;">
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
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
