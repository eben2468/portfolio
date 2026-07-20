<?php
/**
 * Nadics Digital Solution — Contact Page
 */

$is_quote = isset($_GET['type']) && $_GET['type'] === 'quote';
$selected_service = $_GET['service'] ?? '';

// Contact page text has a normal and a "quote" variant for several fields.
// Pick the right key suffix once so the markup can stay clean.
$cs = $is_quote ? '_quote' : '';

$page_title = $is_quote ? 'Request a Quote — Nadics Digital Solution' : 'Contact Us — Nadics Digital Solution';
$page_desc  = $is_quote 
    ? 'Request a free, custom quote for your IT project, web development, or design services from Nadics Digital Solution.' 
    : 'Get in touch with Nadics Digital Solution for web development, graphic design, IT support, and digital services. Request a free quote for your project.';
$page_css   = 'contact.css';
$page_js    = 'contact.js';

include 'includes/header.php';
?>

    <!-- ═══ Page Hero ═══ -->
    <section class="page-hero">
        <?= heroBgTag('hero_bg_contact') ?>
        <div class="container">
            <h1 class="page-hero__title reveal"><?= sanitize(contactText('contact_hero_title' . $cs)) ?></h1>
            <div class="page-hero__breadcrumb reveal">
                <a href="<?= SITE_URL ?>/">Home</a>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                <span><?= sanitize(contactText('contact_hero_title' . $cs)) ?></span>
            </div>
        </div>
    </section>


    <!-- ═══ Contact Section ═══ -->
    <section class="section" id="contact">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-header__label"><?= sanitize(contactText('contact_label' . $cs)) ?></span>
                <h2 class="section-header__title"><?= sanitize(contactText('contact_title' . $cs)) ?> <span class="text-gradient"><?= sanitize(contactText('contact_title_highlight' . $cs)) ?></span></h2>
                <p class="section-header__desc"><?= sanitize(contactText('contact_desc' . $cs)) ?></p>
            </div>

            <div class="contact__content">
                <!-- Contact Form -->
                <div class="contact-form-wrapper reveal-left">
                    <!-- Success Message -->
                    <div class="form-message form-message--success" id="formSuccess">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span><?= sanitize(contactText('contact_success_msg' . $cs)) ?></span>
                    </div>

                    <!-- Error Message -->
                    <div class="form-message form-message--error" id="formError">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        <span><?= sanitize(contactText('contact_error_msg')) ?></span>
                    </div>

                    <h3 class="contact-form__title"><?= sanitize(contactText('contact_form_title' . $cs)) ?></h3>
                    <p class="contact-form__subtitle"><?= sanitize(contactText('contact_form_subtitle' . $cs)) ?></p>

                    <form id="contactForm" action="<?= SITE_URL ?>/api/contact.php" method="POST" novalidate>
                        <?= csrfField() ?>
                        <?php if ($is_quote): ?>
                        <input type="hidden" name="form_type" value="quote">
                        <?php endif; ?>

                        <div class="contact-form__grid">
                            <!-- Name -->
                            <div class="form-group">
                                <label class="form-group__label" for="contactName">Full Name <span class="required">*</span></label>
                                <input type="text" id="contactName" name="name" class="form-group__input" placeholder="e.g. Kwame Asante" required>
                                <div class="form-group__error">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <span></span>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label class="form-group__label" for="contactEmail">Email Address <span class="required">*</span></label>
                                <input type="email" id="contactEmail" name="email" class="form-group__input" placeholder="e.g. kwame@example.com" required>
                                <div class="form-group__error">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <span></span>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="form-group">
                                <label class="form-group__label" for="contactPhone">Phone Number</label>
                                <input type="tel" id="contactPhone" name="phone" class="form-group__input" placeholder="e.g. +233 24 000 0000">
                                <div class="form-group__error">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <span></span>
                                </div>
                            </div>

                            <!-- Subject / Services Required -->
                            <div class="form-group">
                                <label class="form-group__label" for="contactSubject"><?= $is_quote ? 'Service Required' : 'Subject' ?> <span class="required">*</span></label>
                                <select id="contactSubject" name="subject" class="form-group__select" required>
                                    <option value=""><?= $is_quote ? 'Select the service you need' : 'Select a subject' ?></option>
                                    <option value="Web Development" <?= ($selected_service === 'web-dev' || $selected_service === 'Web Development') ? 'selected' : '' ?>>Web Development</option>
                                    <option value="Graphic Design" <?= ($selected_service === 'graphic-design' || $selected_service === 'Graphic Design') ? 'selected' : '' ?>>Graphic Design</option>
                                    <option value="Research Consultation" <?= ($selected_service === 'research' || $selected_service === 'Research Consultation') ? 'selected' : '' ?>>Research Consultation</option>
                                    <option value="Voting & Ticketing System" <?= ($selected_service === 'voting' || $selected_service === 'Voting & Ticketing System') ? 'selected' : '' ?>>Voting & Ticketing System</option>
                                    <option value="Software Installation" <?= ($selected_service === 'software' || $selected_service === 'Software Installation') ? 'selected' : '' ?>>Software Installation</option>
                                    <option value="IT Support" <?= ($selected_service === 'it-support' || $selected_service === 'IT Support') ? 'selected' : '' ?>>IT Support</option>
                                    <option value="General Inquiry" <?= ($selected_service === 'general' || $selected_service === 'General Inquiry') ? 'selected' : '' ?>>General Inquiry</option>
                                    <option value="Partnership" <?= ($selected_service === 'partnership' || $selected_service === 'Partnership') ? 'selected' : '' ?>>Partnership</option>
                                </select>
                                <div class="form-group__error">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <span></span>
                                </div>
                            </div>

                            <?php if ($is_quote): ?>
                            <!-- Estimated Budget -->
                            <div class="form-group">
                                <label class="form-group__label" for="contactBudget">Estimated Budget <span class="required">*</span></label>
                                <select id="contactBudget" name="budget" class="form-group__select" required>
                                    <option value="">Select a budget range</option>
                                    <option value="Below GH₵ 2,000 (Below $150)">Below GH₵ 2,000 (Below $150)</option>
                                    <option value="GH₵ 2,000 - GH₵ 5,000 ($150 - $350)">GH₵ 2,000 - GH₵ 5,000 ($150 - $350)</option>
                                    <option value="GH₵ 5,000 - GH₵ 10,000 ($350 - $700)">GH₵ 5,000 - GH₵ 10,000 ($350 - $700)</option>
                                    <option value="GH₵ 10,000 - GH₵ 25,000 ($700 - $1,750)">GH₵ 10,000 - GH₵ 25,000 ($700 - $1,750)</option>
                                    <option value="GH₵ 25,000+ ($1,750+)">GH₵ 25,000+ ($1,750+)</option>
                                </select>
                                <div class="form-group__error">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <span></span>
                                </div>
                            </div>

                            <!-- Estimated Timeline -->
                            <div class="form-group">
                                <label class="form-group__label" for="contactTimeline">Estimated Timeline <span class="required">*</span></label>
                                <select id="contactTimeline" name="timeline" class="form-group__select" required>
                                    <option value="">Select a timeline</option>
                                    <option value="Urgent (Less than 1 week)">Urgent (Less than 1 week)</option>
                                    <option value="1 to 2 Weeks">1 to 2 Weeks</option>
                                    <option value="2 to 4 Weeks">2 to 4 Weeks</option>
                                    <option value="1 to 2 Months">1 to 2 Months</option>
                                    <option value="2+ Months">2+ Months</option>
                                    <option value="Flexible">Flexible</option>
                                </select>
                                <div class="form-group__error">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <span></span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Message -->
                            <div class="form-group form-group--full">
                                <label class="form-group__label" for="contactMessage"><?= $is_quote ? 'Project Details / Requirements' : 'Message' ?> <span class="required">*</span></label>
                                <textarea id="contactMessage" name="message" class="form-group__textarea" placeholder="<?= $is_quote ? 'Tell us about your project requirements, target audience, and key goals...' : 'Tell us about your project or how we can help...' ?>" required></textarea>
                                <div class="form-group__error">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <span></span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="contactSubmit" class="btn btn-primary contact-form__submit">
                            <span class="btn__text"><?= $is_quote ? 'Submit Quote Request' : 'Send Message' ?></span>
                            <span class="btn__spinner"></span>
                            <svg class="btn__text" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        </button>
                    </form>
                </div>

                <!-- Contact Info Sidebar -->
                <div class="contact-info reveal-right">
                    <div class="contact-info__card">
                        <h4 class="contact-info__heading"><?= sanitize(contactText('contact_info_heading')) ?></h4>

                        <div class="contact-info-item">
                            <div class="contact-info-item__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </div>
                            <div>
                                <div class="contact-info-item__label">Email</div>
                                <div class="contact-info-item__value"><a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></div>
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div class="contact-info-item__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <div>
                                <div class="contact-info-item__label">Phone</div>
                                <div class="contact-info-item__value"><a href="tel:<?= str_replace(' ', '', SITE_PHONE) ?>"><?= SITE_PHONE ?></a></div>
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div class="contact-info-item__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <div>
                                <div class="contact-info-item__label">Location</div>
                                <div class="contact-info-item__value"><?= SITE_ADDRESS ?></div>
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div class="contact-info-item__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <div>
                                <div class="contact-info-item__label">Business Hours</div>
                                <div class="contact-info-item__value"><?= nl2br(sanitize(contactText('contact_hours'))) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Links -->
                    <div class="contact-socials">
                        <h4 class="contact-socials__heading"><?= sanitize(contactText('contact_socials_heading')) ?></h4>
                        <div class="contact-socials__grid">
                            <a href="<?= FACEBOOK_URL ?>" target="_blank" rel="noopener" class="contact-social">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                Facebook
                            </a>
                            <a href="<?= TWITTER_URL ?>" target="_blank" rel="noopener" class="contact-social">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                Twitter / X
                            </a>
                            <a href="<?= INSTAGRAM_URL ?>" target="_blank" rel="noopener" class="contact-social">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                Instagram
                            </a>
                            <a href="<?= LINKEDIN_URL ?>" target="_blank" rel="noopener" class="contact-social">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                LinkedIn
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="map-section reveal">
                <?php $map_embed = trim(contactText('contact_map_embed')); ?>
                <?php if ($map_embed !== ''): ?>
                <iframe class="map-section__frame" src="<?= sanitize($map_embed) ?>" width="100%" height="100%" style="border:0; min-height:320px; display:block;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?= sanitize(contactText('contact_map_title')) ?>"></iframe>
                <?php else: ?>
                <div class="map-section__placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <p style="font-family: var(--font-heading); font-weight: 600; font-size: 1.1rem;"><?= sanitize(contactText('contact_map_title')) ?></p>
                    <p><?= sanitize(contactText('contact_map_desc')) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
