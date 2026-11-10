        </div>
        <!-- End Page Content -->

        <!-- Admin Footer -->
        <footer class="admin-footer">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Admin Panel.</p>
        </footer>
    </div>

    <!-- Session timeout warning -->
    <div class="session-warning" id="sessionWarning" role="alertdialog" aria-live="assertive" aria-labelledby="sessionWarningText" hidden>
        <p id="sessionWarningText">You'll be signed out in <strong id="sessionWarningCount">60</strong> seconds because of inactivity.</p>
        <div class="session-warning__actions">
            <button type="button" class="admin-btn admin-btn--primary admin-btn--sm" id="sessionStay">Stay signed in</button>
            <a href="<?= ADMIN_URL ?>/logout" class="admin-btn admin-btn--outline admin-btn--sm">Sign out</a>
        </div>
    </div>
    <script>
        window.ADMIN_SESSION = {
            secondsLeft: <?= (int) adminSessionSecondsLeft() ?>,
            idleTimeout: <?= (int) ADMIN_IDLE_TIMEOUT ?>,
            endpoint: <?= json_encode(ADMIN_URL . '/session') ?>,
            loginUrl: <?= json_encode(ADMIN_URL . '/?expired=1') ?>
        };
    </script>
    <script src="<?= SITE_URL ?>/assets/js/admin-session.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/admin.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/admin-crop.js"></script>
</body>
</html>
