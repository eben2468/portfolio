<?php
/**
 * Nadics Digital Solution — Admin Header
 * Sidebar navigation and top bar for admin panel
 */

requireAdmin();

$admin_page = $admin_page ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= sanitize($admin_title ?? 'Dashboard') ?> — Admin | <?= SITE_NAME ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=clash-display@400,500,600,700&f[]=satoshi@300,400,500,700&display=swap" rel="stylesheet">

    <!-- Admin Styles -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/nadics-favicon.png?v=2">
</head>
<body class="admin-body">

    <!-- ═══ Sidebar ═══ -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar__header">
            <a href="<?= ADMIN_URL ?>/dashboard.php" class="admin-sidebar__logo">
                <img src="<?= SITE_URL ?>/assets/nadics-logo.png" alt="Nadics logo" width="36" height="36">
                <div class="admin-sidebar__logo-text">
                    <span class="admin-sidebar__logo-name">Nadics</span>
                    <span class="admin-sidebar__logo-sub">Admin Panel</span>
                </div>
            </a>
        </div>

        <nav class="admin-sidebar__nav">
            <div class="admin-sidebar__section">
                <span class="admin-sidebar__section-title">Main</span>
                <a href="<?= ADMIN_URL ?>/dashboard.php" class="admin-sidebar__link <?= $admin_page === 'dashboard' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="admin-sidebar__section">
                <span class="admin-sidebar__section-title">Content</span>
                <a href="<?= ADMIN_URL ?>/homepage.php" class="admin-sidebar__link <?= $admin_page === 'homepage' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span>Home Page</span>
                </a>
                <a href="<?= ADMIN_URL ?>/about.php" class="admin-sidebar__link <?= $admin_page === 'about' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span>About Page</span>
                </a>
                <a href="<?= ADMIN_URL ?>/services.php" class="admin-sidebar__link <?= $admin_page === 'services' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                    <span>Services</span>
                </a>
                <a href="<?= ADMIN_URL ?>/portfolio.php" class="admin-sidebar__link <?= $admin_page === 'portfolio' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                    <span>Portfolio</span>
                </a>
                <a href="<?= ADMIN_URL ?>/contact-page.php" class="admin-sidebar__link <?= $admin_page === 'contact-page' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Contact Page</span>
                </a>
                <a href="<?= ADMIN_URL ?>/contacts.php" class="admin-sidebar__link <?= $admin_page === 'contacts' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <span>Messages</span>
                    <?php
                    $db = getDB();
                    $unreadCount = 0;
                    if ($db) {
                        try { $unreadCount = (int) $db->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn(); } catch (Exception $e) {}
                    }
                    if ($unreadCount > 0): ?>
                    <span class="admin-sidebar__badge"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= ADMIN_URL ?>/testimonials.php" class="admin-sidebar__link <?= $admin_page === 'testimonials' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Testimonials</span>
                </a>
                <a href="<?= ADMIN_URL ?>/team.php" class="admin-sidebar__link <?= $admin_page === 'team' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Team</span>
                </a>
                <a href="<?= ADMIN_URL ?>/hero-backgrounds.php" class="admin-sidebar__link <?= $admin_page === 'hero-backgrounds' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <span>Hero Backgrounds</span>
                </a>
            </div>

            <div class="admin-sidebar__section">
                <span class="admin-sidebar__section-title">System</span>
                <a href="<?= ADMIN_URL ?>/settings.php" class="admin-sidebar__link <?= $admin_page === 'settings' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span>Settings</span>
                </a>
                <a href="<?= SITE_URL ?>/" class="admin-sidebar__link" target="_blank">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    <span>View Site</span>
                </a>
            </div>
        </nav>

        <div class="admin-sidebar__footer">
            <div class="admin-sidebar__user">
                <div class="admin-sidebar__user-avatar">
                    <?= strtoupper(substr(getAdminName(), 0, 1)) ?>
                </div>
                <div class="admin-sidebar__user-info">
                    <span class="admin-sidebar__user-name"><?= sanitize(getAdminName()) ?></span>
                    <span class="admin-sidebar__user-role">Administrator</span>
                </div>
            </div>
            <a href="<?= ADMIN_URL ?>/logout.php" class="admin-sidebar__logout" title="Logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </a>
        </div>
    </aside>

    <!-- ═══ Main Area ═══ -->
    <div class="admin-main" id="adminMain">
        <!-- Top Bar -->
        <header class="admin-topbar">
            <button class="admin-topbar__toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>

            <div class="admin-topbar__title">
                <h1><?= sanitize($admin_title ?? 'Dashboard') ?></h1>
            </div>

            <div class="admin-topbar__actions">
                <span class="admin-topbar__greeting">Hello, <?= sanitize(getAdminName()) ?></span>
                <a href="<?= ADMIN_URL ?>/logout.php" class="admin-topbar__btn" title="Logout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </a>
            </div>
        </header>

        <!-- Page Content -->
        <div class="admin-content">
