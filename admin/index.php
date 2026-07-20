<?php
/**
 * Nadics Digital Solution — Admin Login
 */

require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to dashboard
if (isAdminLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    // Verify CSRF
    if (!verifyCSRFToken($csrf)) {
        $error = 'Invalid security token. Please try again.';
    } else {
        // Implement session-based rate limiter (built into includes/functions.php)
        if (isRateLimited('admin_login', 5, 300)) {
            $error = 'Too many login attempts. Please try again in 5 minutes.';
        } else {
            // Attempt login
            if (adminLogin($email, $password)) {
                // Log action
                if (isset($_SESSION['admin_id'])) {
                    logActivity($_SESSION['admin_id'], 'Logged in', 'admins', $_SESSION['admin_id'], 'IP: ' . $_SERVER['REMOTE_ADDR']);
                }
                header('Location: ' . ADMIN_URL . '/dashboard.php');
                exit;
            } else {
                $error = 'Invalid email address or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login — Nadics Digital Solution</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=clash-display@400,500,600,700&f[]=satoshi@300,400,500,700&display=swap" rel="stylesheet">

    <!-- Admin Styles -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/nadics-favicon.png?v=2">
</head>
<body class="admin-body">

    <div class="login-page">
        <div class="login-card">
            <!-- Brand Logo -->
            <div class="login-card__logo">
                <img src="<?= SITE_URL ?>/assets/nadics-logo.png" alt="Nadics Digital Solution logo" width="64" height="64">
                <span class="login-card__logo-name">Nadics Digital</span>
                <span class="login-card__logo-sub">Admin Access</span>
            </div>

            <h2 class="login-card__title">Welcome Back</h2>
            <p class="login-card__subtitle">Please enter your credentials to login</p>

            <?php if (!empty($error)): ?>
                <div class="login-error">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span><?= sanitize($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="login-form">
                <!-- CSRF Token -->
                <?= csrfField() ?>

                <div class="login-form__group">
                    <label for="email" class="login-form__label">Email Address</label>
                    <input type="email" name="email" id="email" class="login-form__input" placeholder="admin@nadicsdigital.com" value="<?= sanitize($email ?? '') ?>" required autofocus>
                </div>

                <div class="login-form__group">
                    <label for="password" class="login-form__label">Password</label>
                    <div class="login-form__password">
                        <input type="password" name="password" id="password" class="login-form__input" placeholder="••••••••" required>
                        <button type="button" class="login-form__toggle" id="passwordToggle" aria-label="Show password" aria-pressed="false">
                            <svg class="login-form__toggle-icon login-form__toggle-icon--show" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="login-form__toggle-icon login-form__toggle-icon--hide" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" hidden>
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-form__submit">Sign In</button>
            </form>

            <div class="login-card__footer">
                <a href="<?= SITE_URL ?>/">&larr; Back to Website</a>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var toggle = document.getElementById('passwordToggle');
            var input  = document.getElementById('password');
            if (!toggle || !input) return;
            var showIcon = toggle.querySelector('.login-form__toggle-icon--show');
            var hideIcon = toggle.querySelector('.login-form__toggle-icon--hide');
            toggle.addEventListener('click', function () {
                var reveal = input.type === 'password';
                input.type = reveal ? 'text' : 'password';
                showIcon.hidden = reveal;
                hideIcon.hidden = !reveal;
                toggle.setAttribute('aria-pressed', reveal ? 'true' : 'false');
                toggle.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
                input.focus();
            });
        })();
    </script>

</body>
</html>
