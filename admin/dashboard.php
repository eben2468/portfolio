<?php
/**
 * Nadics Digital Solution — Admin Dashboard
 */

$admin_title = 'Dashboard';
$admin_page = 'dashboard';

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/admin-header.php';

$stats = getDashboardStats();
$activities = getRecentActivity(6);
?>

<!-- ─── Statistics Grid ─── -->
<div class="stats-grid">
    <!-- Stat: Total Messages -->
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
        </div>
        <div>
            <div class="stat-card__value"><?= $stats['total_contacts'] ?></div>
            <div class="stat-card__label">Total Messages</div>
        </div>
    </div>

    <!-- Stat: Unread Messages -->
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div>
            <div class="stat-card__value"><?= $stats['unread_contacts'] ?></div>
            <div class="stat-card__label">Unread Messages</div>
        </div>
    </div>

    <!-- Stat: Portfolio Items -->
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
            </svg>
        </div>
        <div>
            <div class="stat-card__value"><?= $stats['total_portfolio'] ?></div>
            <div class="stat-card__label">Portfolio Projects</div>
        </div>
    </div>

    <!-- Stat: Testimonials -->
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--accent">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </div>
        <div>
            <div class="stat-card__value"><?= $stats['total_testimonials'] ?></div>
            <div class="stat-card__label">Testimonials</div>
        </div>
    </div>
</div>

<!-- ─── Dashboard Main Grid ─── -->
<div class="dashboard-grid">
    <!-- Left Column: Recent Messages -->
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">Recent Contact Messages</h2>
            <a href="<?= ADMIN_URL ?>/contacts.php" class="admin-btn admin-btn--outline admin-btn--sm">View All Messages</a>
        </div>
        <div class="admin-card__body" style="padding: 0;">
            <?php if (empty($stats['recent_contacts'])): ?>
                <div class="admin-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <h3 class="admin-empty__title">No Messages Received</h3>
                    <p class="admin-empty__desc">When users submit contact forms, they will show up here.</p>
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
                            <?php foreach ($stats['recent_contacts'] as $contact): ?>
                                <tr class="<?= $contact['is_read'] ? '' : 'row-unread' ?>">
                                    <td>
                                        <div style="font-weight: 600; color: var(--admin-text);"><?= sanitize($contact['full_name']) ?></div>
                                        <div style="font-size: 0.75rem; color: var(--admin-text-light);"><?= sanitize($contact['email']) ?></div>
                                    </td>
                                    <td class="truncate" title="<?= sanitize($contact['subject']) ?>">
                                        <?= sanitize($contact['subject']) ?>
                                    </td>
                                    <td style="font-size: 0.8rem; color: var(--admin-text-light); white-space: nowrap;">
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
                                            <a href="<?= ADMIN_URL ?>/contacts.php?id=<?= $contact['id'] ?>" class="admin-btn admin-btn--outline admin-btn--sm" title="View Message">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </a>
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

    <!-- Right Column: Recent Activities -->
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">Recent Activities</h2>
        </div>
        <div class="admin-card__body">
            <?php if (empty($activities)): ?>
                <div class="admin-empty" style="padding: 1.5rem 0;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <h3 class="admin-empty__title">No Recent Activities</h3>
                    <p class="admin-empty__desc" style="margin-bottom: 0;">Actions will be recorded as you use the panel.</p>
                </div>
            <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($activities as $act): ?>
                        <div class="activity-item">
                            <span class="activity-item__dot"></span>
                            <div class="activity-item__text">
                                <strong><?= sanitize($act['action']) ?></strong>
                                <?php if (!empty($act['details'])): ?>
                                    <div style="font-size: 0.75rem; color: var(--admin-text-light); margin-top: 0.1rem;">
                                        <?= sanitize($act['details']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="activity-item__time" title="<?= sanitize($act['created_at']) ?>">
                                <?php
                                $timeDiff = time() - strtotime($act['created_at']);
                                if ($timeDiff < 60) {
                                    echo 'Just now';
                                } elseif ($timeDiff < 3600) {
                                    echo floor($timeDiff / 60) . 'm ago';
                                } elseif ($timeDiff < 86400) {
                                    echo floor($timeDiff / 3600) . 'h ago';
                                } else {
                                    echo date('M j', strtotime($act['created_at']));
                                }
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
