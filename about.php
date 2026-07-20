<?php
/**
 * Nadics Digital Solution — About Page
 */

$page_title = 'About Us — Nadics Digital Solution';
$page_desc  = 'Learn about Nadics Digital Solution, our mission, values, and the passionate team behind our innovative IT and digital services in Accra, Ghana.';
$page_css   = 'about.css';

include 'includes/header.php';
?>

    <!-- ═══ Page Hero ═══ -->
    <section class="page-hero">
        <?= heroBgTag('hero_bg_about') ?>
        <div class="container">
            <h1 class="page-hero__title reveal"><?= sanitize(aboutText('about_hero_title')) ?></h1>
            <div class="page-hero__breadcrumb reveal">
                <a href="<?= SITE_URL ?>/">Home</a>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                <span>About</span>
            </div>
        </div>
    </section>


    <!-- ═══ Our Story ═══ -->
    <section class="section" id="story">
        <div class="container">
            <div class="story__content">
                <div class="story__text reveal-left">
                    <span class="section-header__label"><?= sanitize(aboutText('about_story_label')) ?></span>
                    <h2 class="section-header__title" style="text-align:left; margin-bottom: var(--space-lg);"><?= sanitize(aboutText('about_story_title')) ?> <span class="text-gradient"><?= sanitize(aboutText('about_story_title_highlight')) ?></span><?php if (aboutText('about_story_title_suffix') !== ''): ?> <?= sanitize(aboutText('about_story_title_suffix')) ?><?php endif; ?></h2>
                    <p class="story__lead">
                        <?= sanitize(aboutText('about_story_lead')) ?>
                    </p>
                    <p class="story__body">
                        <?= sanitize(aboutText('about_story_body1')) ?>
                    </p>
                    <p class="story__body">
                        <?= sanitize(aboutText('about_story_body2')) ?>
                    </p>
                </div>

                <div class="story__visual reveal-right">
                    <div class="story__image-wrapper">
                        <img src="<?= sanitize(aboutText('about_story_image')) ?>" alt="The <?= sanitize(SITE_NAME) ?> team" loading="lazy">
                    </div>
                    <div class="story__image-accent"></div>
                    <div class="story__experience-badge">
                        <div class="story__experience-number" data-count="<?= (int)aboutText('about_story_badge_number') ?>" data-suffix="<?= sanitize(aboutText('about_story_badge_suffix')) ?>">0<?= sanitize(aboutText('about_story_badge_suffix')) ?></div>
                        <div class="story__experience-text"><?= nl2br(sanitize(aboutText('about_story_badge_text'))) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══ What NADICS Means ═══ -->
    <section class="section section--soft" id="meaning">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-header__label"><?= sanitize(aboutText('about_meaning_label')) ?></span>
                <h2 class="section-header__title"><?= sanitize(aboutText('about_meaning_title')) ?> <span class="text-gradient"><?= sanitize(aboutText('about_meaning_title_highlight')) ?></span><?php if (aboutText('about_meaning_title_suffix') !== ''): ?> <?= sanitize(aboutText('about_meaning_title_suffix')) ?><?php endif; ?></h2>
                <p class="section-header__desc"><?= sanitize(aboutText('about_meaning_desc')) ?></p>
            </div>

            <div class="acronym__grid stagger-children">
                <?php foreach (splitRows(aboutText('about_meaning_acronyms')) as $acronym): ?>
                <div class="acronym-card reveal">
                    <span class="acronym-card__letter"><?= sanitize($acronym[0] ?? '') ?></span>
                    <span class="acronym-card__word"><?= sanitize($acronym[1] ?? '') ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="acronym__statement reveal">
                <p><?= sanitize(aboutText('about_meaning_statement')) ?></p>
            </div>
        </div>
    </section>


    <!-- ═══ Mission, Vision, Values ═══ -->
    <section class="section section--dark" id="mission">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-header__label"><?= sanitize(aboutText('about_mvv_label')) ?></span>
                <h2 class="section-header__title"><?= sanitize(aboutText('about_mvv_title')) ?> <span class="text-gradient"><?= sanitize(aboutText('about_mvv_title_highlight')) ?></span><?php if (aboutText('about_mvv_title_suffix') !== ''): ?> <?= sanitize(aboutText('about_mvv_title_suffix')) ?><?php endif; ?></h2>
            </div>

            <div class="mvv__grid stagger-children">
                <div class="mvv-card reveal">
                    <div class="mvv-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/><path d="M8 12h8"/></svg>
                    </div>
                    <h3 class="mvv-card__title"><?= sanitize(aboutText('about_mvv_mission_title')) ?></h3>
                    <p class="mvv-card__desc"><?= sanitize(aboutText('about_mvv_mission_desc')) ?></p>
                </div>

                <div class="mvv-card reveal">
                    <div class="mvv-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>
                    <h3 class="mvv-card__title"><?= sanitize(aboutText('about_mvv_vision_title')) ?></h3>
                    <p class="mvv-card__desc"><?= sanitize(aboutText('about_mvv_vision_desc')) ?></p>
                </div>

                <div class="mvv-card reveal">
                    <div class="mvv-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </div>
                    <h3 class="mvv-card__title"><?= sanitize(aboutText('about_mvv_values_title')) ?></h3>
                    <p class="mvv-card__desc"><?= sanitize(aboutText('about_mvv_values_desc')) ?></p>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══ Timeline ═══ -->
    <section class="section" id="journey">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-header__label"><?= sanitize(aboutText('about_timeline_label')) ?></span>
                <h2 class="section-header__title"><?= sanitize(aboutText('about_timeline_title')) ?> <span class="text-gradient"><?= sanitize(aboutText('about_timeline_title_highlight')) ?></span><?php if (aboutText('about_timeline_title_suffix') !== ''): ?> <?= sanitize(aboutText('about_timeline_title_suffix')) ?><?php endif; ?></h2>
                <p class="section-header__desc"><?= sanitize(aboutText('about_timeline_desc')) ?></p>
            </div>

            <div class="timeline">
                <?php foreach (splitRows(aboutText('about_timeline_items')) as $i => $item): ?>
                <div class="timeline-item reveal">
                    <?php if ($i % 2 === 0): // left-aligned ?>
                    <div class="timeline-item__content">
                        <span class="timeline-item__year"><?= sanitize($item[0] ?? '') ?></span>
                        <h4 class="timeline-item__title"><?= sanitize($item[1] ?? '') ?></h4>
                        <p class="timeline-item__desc"><?= sanitize($item[2] ?? '') ?></p>
                    </div>
                    <div class="timeline-item__dot"></div>
                    <div class="timeline-item__spacer"></div>
                    <?php else: // right-aligned ?>
                    <div class="timeline-item__spacer"></div>
                    <div class="timeline-item__dot"></div>
                    <div class="timeline-item__content">
                        <span class="timeline-item__year"><?= sanitize($item[0] ?? '') ?></span>
                        <h4 class="timeline-item__title"><?= sanitize($item[1] ?? '') ?></h4>
                        <p class="timeline-item__desc"><?= sanitize($item[2] ?? '') ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <!-- ═══ Meet the Leadership ═══ -->
    <section class="section" id="leadership">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-header__label"><?= sanitize(aboutText('about_leadership_label')) ?></span>
                <h2 class="section-header__title"><?= sanitize(aboutText('about_leadership_title')) ?> <span class="text-gradient"><?= sanitize(aboutText('about_leadership_title_highlight')) ?></span><?php if (aboutText('about_leadership_title_suffix') !== ''): ?> <?= sanitize(aboutText('about_leadership_title_suffix')) ?><?php endif; ?></h2>
                <p class="section-header__desc"><?= sanitize(aboutText('about_leadership_desc')) ?></p>
            </div>

            <?php
            $team_members = getTeamMembers();
            if (!empty($team_members)): ?>
                <div class="leadership__grid stagger-children">
                    <?php foreach ($team_members as $member):
                        $member_linkedin = trim($member['linkedin_url'] ?? '');
                    ?>
                        <article class="leader-card reveal">
                            <div class="leader-card__photo">
                                <img src="<?= SITE_URL ?>/<?= sanitize($member['image_url']) ?>" alt="<?= sanitize($member['name']) ?>" loading="lazy">
                                <?php if (!empty($member['bio'])): ?>
                                <div class="leader-card__overlay">
                                    <p class="leader-card__bio"><?= sanitize($member['bio']) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="leader-card__plate">
                                <div class="leader-card__plate-text">
                                    <h3 class="leader-card__name"><?= sanitize($member['name']) ?></h3>
                                    <span class="leader-card__title"><?= sanitize($member['title']) ?></span>
                                </div>
                                <?php if ($member_linkedin !== ''): ?>
                                <a class="leader-card__social" href="<?= sanitize($member_linkedin) ?>" target="_blank" rel="noopener" aria-label="<?= sanitize($member['name']) ?> on LinkedIn">
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                </a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center" style="color: var(--text-light);">Leadership team details will appear here soon.</p>
            <?php endif; ?>
        </div>
    </section>


    <!-- ═══ Tech Stack ═══ -->
    <section class="section section--soft" id="tech-stack">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-header__label"><?= sanitize(aboutText('about_tech_label')) ?></span>
                <h2 class="section-header__title"><?= sanitize(aboutText('about_tech_title')) ?> <span class="text-gradient"><?= sanitize(aboutText('about_tech_title_highlight')) ?></span><?php if (aboutText('about_tech_title_suffix') !== ''): ?> <?= sanitize(aboutText('about_tech_title_suffix')) ?><?php endif; ?></h2>
                <p class="section-header__desc"><?= sanitize(aboutText('about_tech_desc')) ?></p>
            </div>

            <div class="tech-stack__grid stagger-children">
                <?php foreach (splitRows(aboutText('about_tech_items')) as $tech): ?>
                <div class="tech-item reveal"><span class="tech-item__icon"><?= sanitize($tech[0] ?? '') ?></span><span class="tech-item__name"><?= sanitize($tech[1] ?? '') ?></span></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <!-- ═══ CTA ═══ -->
    <section class="cta-banner">
        <div class="cta-banner__content container reveal">
            <h2 class="cta-banner__title"><?= sanitize(aboutText('about_cta_title')) ?></h2>
            <p class="cta-banner__desc"><?= sanitize(aboutText('about_cta_desc')) ?></p>
            <a href="<?= SITE_URL ?>/contact.php" class="btn btn-outline btn-lg">
                <?= sanitize(aboutText('about_cta_btn_label')) ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
