    </main>
    <!-- ═══ End Main Content ═══ -->

    <!-- ═══ Footer ═══ -->
    <footer class="footer" id="siteFooter">
        <!-- Wave Separator -->
        <div class="footer__wave">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,60 C360,120 720,0 1080,60 C1260,90 1380,80 1440,60 L1440,120 L0,120 Z" fill="currentColor"/>
            </svg>
        </div>

        <div class="footer__content container">
            <div class="footer__grid">
                <!-- Brand Column -->
                <div class="footer__col footer__brand">
                    <a href="<?= SITE_URL ?>/" class="footer__logo" aria-label="Nadics Digital Solution">
                        <img src="<?= SITE_URL ?>/assets/nadics-logo.png" alt="Nadics Digital Solution logo" width="36" height="36">
                        <span>Nadics Digital</span>
                    </a>
                    <p class="footer__tagline">Your Partner in Digital Innovation</p>
                    <p class="footer__desc">
                        Empowering businesses with innovative digital solutions. From web development to IT support, we transform your ideas into reality.
                    </p>
                    <div class="footer__socials">
                        <a href="<?= FACEBOOK_URL ?>" class="footer__social" aria-label="Facebook" target="_blank">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="<?= TWITTER_URL ?>" class="footer__social" aria-label="Twitter" target="_blank">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <a href="<?= INSTAGRAM_URL ?>" class="footer__social" aria-label="Instagram" target="_blank">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a href="<?= LINKEDIN_URL ?>" class="footer__social" aria-label="LinkedIn" target="_blank">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <a href="<?= TIKTOK_URL ?>" class="footer__social" aria-label="TikTok" target="_blank">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.17-2.86-.74-3.94-1.74-.22-.2-.41-.43-.58-.67-.02 3.28-.01 6.56-.01 9.84-.04 2.03-.58 4.14-1.87 5.73-1.63 2.08-4.32 3.12-6.93 2.87-2.73-.18-5.32-1.76-6.49-4.24-1.43-2.88-1-6.6 1.05-9.14 1.59-1.99 4.19-2.92 6.7-2.58v4.1c-1.39-.33-2.96.03-3.87 1.12-.99 1.15-1.12 2.96-.34 4.28.73 1.29 2.29 2.08 3.77 1.86 1.48-.15 2.76-1.32 2.97-2.8.05-2.03.02-4.07.03-6.11V.02z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer__col">
                    <h4 class="footer__heading">Quick Links</h4>
                    <ul class="footer__links">
                        <li><a href="<?= SITE_URL ?>/">Home</a></li>
                        <li><a href="<?= SITE_URL ?>/about.php">About Us</a></li>
                        <li><a href="<?= SITE_URL ?>/services.php">Services</a></li>
                        <li><a href="<?= SITE_URL ?>/portfolio.php">Portfolio</a></li>
                        <li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div class="footer__col">
                    <h4 class="footer__heading">Our Services</h4>
                    <ul class="footer__links">
                        <li><a href="<?= SITE_URL ?>/services.php#web-dev">Web Development</a></li>
                        <li><a href="<?= SITE_URL ?>/services.php#graphic-design">Graphic Design</a></li>
                        <li><a href="<?= SITE_URL ?>/services.php#research">Research Consultation</a></li>
                        <li><a href="<?= SITE_URL ?>/services.php#voting">Voting &amp; Ticketing</a></li>
                        <li><a href="<?= SITE_URL ?>/services.php#it-support">IT Support</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="footer__col">
                    <h4 class="footer__heading">Get in Touch</h4>
                    <ul class="footer__contact">
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                            <a href="tel:<?= str_replace(' ', '', SITE_PHONE) ?>"><?= SITE_PHONE ?></a>
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span><?= SITE_ADDRESS ?></span>
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span><?= SITE_HOURS ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="footer__bottom">
                <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                <div class="footer__bottom-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>

        <!-- Back to Top -->
        <button class="back-to-top" id="backToTop" aria-label="Back to top">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="18 15 12 9 6 15"/>
            </svg>
            <svg class="back-to-top__progress" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="46" stroke-width="4" fill="none"/>
            </svg>
        </button>
    </footer>

    <!-- ═══ Scripts ═══ -->
    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/animations.js"></script>
    <?php if (!empty($page_js)): ?>
    <script src="<?= SITE_URL ?>/assets/js/<?= $page_js ?>"></script>
    <?php endif; ?>
</body>
</html>
