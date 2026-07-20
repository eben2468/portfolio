<?php
/**
 * Nadics Digital Solution — Home Page
 */

$page_title = 'Nadics Digital Solution — Transforming Ideas Into Digital Reality';
$page_desc  = 'Nadics Digital Solution offers professional web development, graphic design, IT support, and digital services in Accra, Ghana. Transform your business with our innovative solutions.';
$page_css   = 'home.css';
$page_js    = 'home.js';

include 'includes/header.php';

// ─── Query Featured Projects from Database ───
$featured_db = getPortfolioItems('all', true);
$featured_projects = [];
if (!empty($featured_db)) {
    // Limit to 3 items
    $featured_db = array_slice($featured_db, 0, 3);
    foreach ($featured_db as $item) {
        $featured_projects[] = [
            'title' => $item['title'],
            'category_name' => formatCategory($item['category']),
            'image' => SITE_URL . '/' . $item['image_url'],
            'client' => $item['client_name'] ?? '',
            'project_url' => $item['project_url'] ?? '',
        ];
    }
} else {
    // Fallback
    $featured_projects = [
        [
            'title' => 'E-Commerce Platform',
            'category_name' => 'Web Development',
            'image' => SITE_URL . '/assets/images/portfolio/project-1.png',
            'client' => 'RetailHub Ghana',
            'project_url' => '',
        ],
        [
            'title' => 'Awards Voting Portal',
            'category_name' => 'Voting System',
            'image' => SITE_URL . '/assets/images/portfolio/project-2.png',
            'client' => 'Ghana Music Awards',
            'project_url' => '',
        ],
        [
            'title' => 'Brand Identity Suite',
            'category_name' => 'Graphic Design',
            'image' => SITE_URL . '/assets/images/portfolio/project-3.png',
            'client' => 'PayWave Finance',
            'project_url' => '',
        ]
    ];
}

// ─── Query Active Testimonials from Database ───
$testimonials = [];
$db = getDB();
if ($db) {
    try {
        $stmt = $db->query("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC");
        $testimonials = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback
    }
}
if (empty($testimonials)) {
    $testimonials = [
        [
            'client_name' => 'Kwame Asante',
            'client_role' => 'CEO, RetailHub Ghana',
            'quote' => 'Nadics Digital Solution completely transformed our online presence. Their e-commerce platform increased our sales by 200% in the first quarter. Their attention to detail and commitment to quality is outstanding.',
            'avatar_initials' => 'KA'
        ],
        [
            'client_name' => 'Ama Adjei',
            'client_role' => 'Event Director, Ghana Music Awards',
            'quote' => 'The voting platform they built for our awards ceremony was flawless. Over 50,000 votes processed without a single issue. Their team\'s technical expertise and responsiveness is truly exceptional.',
            'avatar_initials' => 'AA'
        ],
        [
            'client_name' => 'Efo Mensah',
            'client_role' => 'Founder, PayWave Finance',
            'quote' => 'Working with Nadics on our brand identity was a game-changer. They took time to understand our vision and delivered designs that perfectly capture who we are. Highly recommend their creative team.',
            'avatar_initials' => 'EM'
        ]
    ];
}
?>

    <!-- ═══ Hero Section ═══ -->
    <section class="hero" id="hero">
        <?= heroBgTag('hero_bg_home', 'hero__bg') ?>
        <canvas class="hero__canvas" id="heroCanvas"></canvas>
        <div class="hero__glow hero__glow--primary"></div>
        <div class="hero__glow hero__glow--accent"></div>

        <div class="hero__content container">
            <div class="hero__text">
                <div class="hero__badge">
                    <span class="hero__badge-dot"></span>
                    <?= sanitize(homeText('home_hero_badge')) ?>
                </div>

                <h1 class="hero__title">
                    <span class="hero__title-line"><?= sanitize(homeText('home_hero_title_1')) ?></span>
                    <span class="hero__title-line"><?= sanitize(homeText('home_hero_title_2_prefix')) ?> <span class="hero__title-typed" id="heroTyped" data-words="<?= sanitize(homeText('home_hero_typed_words')) ?>">Innovate</span><span class="hero__cursor"></span></span>
                </h1>

                <p class="hero__desc">
                    <?= sanitize(homeText('home_hero_desc')) ?>
                </p>

                <div class="hero__actions">
                    <a href="<?= sanitize(homeText('home_hero_btn1_url')) ?>" class="btn btn-primary btn-lg">
                        <?= sanitize(homeText('home_hero_btn1_label')) ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="<?= sanitize(homeText('home_hero_btn2_url')) ?>" class="btn btn-outline btn-lg">
                        <?= sanitize(homeText('home_hero_btn2_label')) ?>
                    </a>
                </div>

                <div class="hero__stats">
                    <div class="hero__stat">
                        <div class="hero__stat-value" data-count="<?= STATS_PROJECTS ?>" data-suffix="+">0</div>
                        <div class="hero__stat-label"><?= sanitize(homeText('home_hero_stat1_label')) ?></div>
                    </div>
                    <div class="hero__stat">
                        <div class="hero__stat-value" data-count="<?= STATS_CLIENTS ?>" data-suffix="+">0</div>
                        <div class="hero__stat-label"><?= sanitize(homeText('home_hero_stat2_label')) ?></div>
                    </div>
                    <div class="hero__stat">
                        <div class="hero__stat-value" data-count="<?= STATS_YEARS ?>" data-suffix="+">0</div>
                        <div class="hero__stat-label"><?= sanitize(homeText('home_hero_stat3_label')) ?></div>
                    </div>
                </div>
            </div>

            <?php
                $heroVisual = homeText('home_hero_visual');

                // Build the list of chosen images (used by both image & slideshow)
                $heroSlides = array_values(array_filter(array_map('trim',
                    preg_split('/\r\n|\r|\n/', homeText('home_hero_slides')))));
                // Backward compatibility with the single-image setting
                if (empty($heroSlides) && homeText('home_hero_image') !== '') {
                    $heroSlides = [homeText('home_hero_image')];
                }

                $heroUseImage = in_array($heroVisual, ['image', 'slideshow'], true) && !empty($heroSlides);
                $heroSlideshow = ($heroVisual === 'slideshow' && count($heroSlides) > 1);
                if ($heroUseImage && !$heroSlideshow) {
                    $heroSlides = [$heroSlides[0]]; // single image uses the first
                }

                $iconsShow = homeText('home_hero_icons_show') !== '0';
                $imgScale  = (int) homeText('home_hero_image_size');
                if ($imgScale <= 0) $imgScale = 100;
                $imgScale  = max(40, min(220, $imgScale));
                $interval  = (int) homeText('home_hero_slide_interval');
                if ($interval < 1) $interval = 4;
                $interval  = max(2, min(30, $interval));
            ?>
            <div class="hero__visual">
                <div class="hero__visual-orb<?= $heroUseImage ? ' hero__visual-orb--image' : '' ?>">
                    <?php if ($heroUseImage): ?>
                    <div class="hero__visual-image<?= $heroSlideshow ? ' hero__visual-image--slideshow' : '' ?>"
                         style="--hero-img-scale: <?= $imgScale / 100 ?>;"
                         data-interval="<?= $interval * 1000 ?>">
                        <?php foreach ($heroSlides as $si => $slide): ?>
                        <img src="<?= sanitize($slide) ?>" alt="Nadics Digital Solution"
                             class="hero__visual-slide<?= $si === 0 ? ' is-active' : '' ?>"
                             <?= $si === 0 ? '' : 'loading="lazy"' ?>>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <svg class="hero__globe" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <defs>
                            <radialGradient id="globeBody" cx="38%" cy="30%" r="75%">
                                <stop offset="0%" stop-color="#a394ff"/>
                                <stop offset="42%" stop-color="#6338d8"/>
                                <stop offset="100%" stop-color="#230f57"/>
                            </radialGradient>
                            <radialGradient id="globeShade" cx="38%" cy="30%" r="78%">
                                <stop offset="58%" stop-color="rgba(0,0,0,0)"/>
                                <stop offset="100%" stop-color="rgba(8,0,26,0.6)"/>
                            </radialGradient>
                            <radialGradient id="globeShine" cx="34%" cy="26%" r="32%">
                                <stop offset="0%" stop-color="rgba(255,255,255,0.6)"/>
                                <stop offset="100%" stop-color="rgba(255,255,255,0)"/>
                            </radialGradient>
                            <clipPath id="globeClip"><circle cx="100" cy="100" r="90"/></clipPath>
                        </defs>

                        <circle cx="100" cy="100" r="90" fill="url(#globeBody)"/>

                        <g clip-path="url(#globeClip)" fill="none" stroke="rgba(200,190,255,0.30)" stroke-width="0.9">
                            <!-- Parallels (latitude) -->
                            <line x1="10" y1="100" x2="190" y2="100"/>
                            <ellipse cx="100" cy="100" rx="90" ry="32"/>
                            <ellipse cx="100" cy="100" rx="90" ry="62"/>
                            <!-- Meridians (longitude) — animated rx sweep = spinning globe -->
                            <line x1="100" y1="10" x2="100" y2="190"/>
                            <ellipse cx="100" cy="100" ry="90" rx="30">
                                <animate attributeName="rx" values="2;90;2" dur="14s" begin="0s" repeatCount="indefinite"/>
                            </ellipse>
                            <ellipse cx="100" cy="100" ry="90" rx="52">
                                <animate attributeName="rx" values="2;90;2" dur="14s" begin="-3.5s" repeatCount="indefinite"/>
                            </ellipse>
                            <ellipse cx="100" cy="100" ry="90" rx="72">
                                <animate attributeName="rx" values="2;90;2" dur="14s" begin="-7s" repeatCount="indefinite"/>
                            </ellipse>
                            <ellipse cx="100" cy="100" ry="90" rx="52">
                                <animate attributeName="rx" values="2;90;2" dur="14s" begin="-10.5s" repeatCount="indefinite"/>
                            </ellipse>
                        </g>

                        <circle cx="100" cy="100" r="90" fill="url(#globeShade)"/>
                        <ellipse cx="72" cy="64" rx="30" ry="22" fill="url(#globeShine)"/>
                        <circle cx="100" cy="100" r="90" fill="none" stroke="rgba(200,190,255,0.55)" stroke-width="1"/>
                    </svg>
                    <?php endif; ?>
                    <div class="hero__visual-ring"></div>
                    <div class="hero__visual-ring"></div>
                    <div class="hero__visual-ring"></div>
                    <?php if ($iconsShow): ?>
                    <div class="hero__visual-icon hero__visual-icon--1"><?= sanitize(homeText('home_hero_icon_1')) ?></div>
                    <div class="hero__visual-icon hero__visual-icon--2"><?= sanitize(homeText('home_hero_icon_2')) ?></div>
                    <div class="hero__visual-icon hero__visual-icon--3"><?= sanitize(homeText('home_hero_icon_3')) ?></div>
                    <div class="hero__visual-icon hero__visual-icon--4"><?= sanitize(homeText('home_hero_icon_4')) ?></div>
                    <div class="hero__visual-icon hero__visual-icon--5"><?= sanitize(homeText('home_hero_icon_5')) ?></div>
                    <div class="hero__visual-icon hero__visual-icon--6"><?= sanitize(homeText('home_hero_icon_6')) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══ Services Preview ═══ -->
    <section class="section" id="services-preview">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-header__label"><?= sanitize(homeText('home_services_label')) ?></span>
                <h2 class="section-header__title"><?= sanitize(homeText('home_services_title')) ?> <span class="text-gradient"><?= sanitize(homeText('home_services_title_highlight')) ?></span></h2>
                <p class="section-header__desc"><?= sanitize(homeText('home_services_desc')) ?></p>
            </div>

            <div class="services-preview__grid stagger-children">
                <?php
                $preview_services = array_slice(getServices(), 0, 6);
                foreach ($preview_services as $svc):
                    $svc_blurb  = !empty($svc['subtitle']) ? $svc['subtitle'] : $svc['description'];
                    $svc_img    = !empty($svc['image_url'])
                        ? (str_contains($svc['image_url'], '://') ? $svc['image_url'] : SITE_URL . '/' . ltrim($svc['image_url'], '/'))
                        : '';
                    $svc_data = [
                        'title'       => $svc['title'],
                        'subtitle'    => $svc['subtitle'] ?? '',
                        'description' => $svc['description'],
                        'features'    => serviceFeatureList($svc['features'] ?? ''),
                        'image'       => $svc_img,
                        'icon'        => $svc['icon_svg'] ?? '',
                        'link'        => !empty($svc['link_url']) ? $svc['link_url'] : '',
                        'linkLabel'   => $svc['link_label'] ?: 'Learn More',
                    ];
                ?>
                <article class="service-card reveal" data-service-card>
                    <div class="service-card__media<?= $svc_img ? '' : ' service-card__media--noimg' ?>">
                        <?php if ($svc_img): ?>
                        <img class="service-card__img" src="<?= sanitize($svc_img) ?>" alt="<?= sanitize($svc['title']) ?>" loading="lazy">
                        <?php endif; ?>
                    </div>
                    <span class="service-card__badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><?= $svc['icon_svg'] ?></svg>
                    </span>
                    <div class="service-card__body">
                        <h3 class="service-card__title"><?= sanitize($svc['title']) ?></h3>
                        <p class="service-card__desc"><?= sanitize($svc_blurb) ?></p>
                        <button type="button" class="service-card__link" data-service-more>
                            Read More
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                    <script type="application/json" class="js-service-data"><?= json_encode($svc_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
                </article>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-3xl reveal">
                <a href="<?= SITE_URL ?>/services.php" class="btn btn-outline--dark btn-lg">
                    <?= sanitize(homeText('home_services_btn_label')) ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </section>


    <!-- ═══ About Teaser ═══ -->
    <section class="section section--soft" id="about-teaser">
        <div class="container">
            <div class="about-teaser__content">
                <div class="about-teaser__text reveal-left">
                    <span class="section-header__label"><?= sanitize(homeText('home_about_label')) ?></span>
                    <h2 class="section-header__title" style="text-align:left; margin-bottom: var(--space-lg);"><?= sanitize(homeText('home_about_title')) ?> <span class="text-gradient"><?= sanitize(homeText('home_about_title_highlight')) ?></span></h2>
                    <p class="about-teaser__desc">
                        <?= sanitize(homeText('home_about_desc')) ?>
                    </p>

                    <div class="about-teaser__features">
                        <?php foreach (serviceFeatureList(homeText('home_about_features')) as $about_feature): ?>
                        <div class="about-teaser__feature">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <?= sanitize($about_feature) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <a href="<?= SITE_URL ?>/about.php" class="btn btn-primary">
                        <?= sanitize(homeText('home_about_btn_label')) ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="about-teaser__visual reveal-right">
                    <div class="about-teaser__image-wrapper">
                        <img src="<?= sanitize(homeText('home_about_image')) ?>" alt="<?= sanitize(SITE_NAME) ?> team at work" loading="lazy">
                    </div>
                    <div class="about-teaser__float-card">
                        <div class="about-teaser__float-card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div class="about-teaser__float-card-text">
                            <div class="about-teaser__float-card-value" data-count="<?= (int)homeText('home_about_stat_value') ?>" data-suffix="<?= sanitize(homeText('home_about_stat_suffix')) ?>">0<?= sanitize(homeText('home_about_stat_suffix')) ?></div>
                            <div class="about-teaser__float-card-label"><?= sanitize(homeText('home_about_stat_label')) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══ Stats Counter ═══ -->
    <section class="stats" id="stats">
        <div class="container">
            <div class="stats__grid">
                <div class="stat-item reveal">
                    <div class="stat-item__value"><span data-count="<?= STATS_PROJECTS ?>" data-suffix="+">0</span></div>
                    <div class="stat-item__label"><?= sanitize(homeText('home_stats_label1')) ?></div>
                    <div class="stat-item__divider"></div>
                </div>
                <div class="stat-item reveal">
                    <div class="stat-item__value"><span data-count="<?= STATS_CLIENTS ?>" data-suffix="+">0</span></div>
                    <div class="stat-item__label"><?= sanitize(homeText('home_stats_label2')) ?></div>
                    <div class="stat-item__divider"></div>
                </div>
                <div class="stat-item reveal">
                    <div class="stat-item__value"><span data-count="<?= STATS_SERVICES ?>" data-suffix="">0</span></div>
                    <div class="stat-item__label"><?= sanitize(homeText('home_stats_label3')) ?></div>
                    <div class="stat-item__divider"></div>
                </div>
                <div class="stat-item reveal">
                    <div class="stat-item__value"><span data-count="<?= STATS_YEARS ?>" data-suffix="+">0</span></div>
                    <div class="stat-item__label"><?= sanitize(homeText('home_stats_label4')) ?></div>
                    <div class="stat-item__divider"></div>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══ Portfolio Showcase ═══ -->
    <section class="section" id="portfolio-showcase">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-header__label"><?= sanitize(homeText('home_portfolio_label')) ?></span>
                <h2 class="section-header__title"><?= sanitize(homeText('home_portfolio_title')) ?> <span class="text-gradient"><?= sanitize(homeText('home_portfolio_title_highlight')) ?></span></h2>
                <p class="section-header__desc"><?= sanitize(homeText('home_portfolio_desc')) ?></p>
            </div>

            <div class="showcase__grid stagger-children">
                <?php foreach ($featured_projects as $project):
                    $purl = $project['project_url'] ?? '';
                    $has_url = $purl !== '' && $purl !== '#';
                ?>
                <div class="showcase-item reveal">
                    <a href="<?= SITE_URL ?>/portfolio.php" class="showcase-item__media" aria-label="<?= sanitize($project['title']) ?>">
                        <img src="<?= $project['image'] ?>" alt="<?= sanitize($project['title']) ?>" class="showcase-item__image" loading="lazy">
                        <span class="showcase-item__badge"><?= sanitize($project['category_name']) ?></span>
                        <span class="showcase-item__veil">
                            <span class="showcase-item__zoom">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                        </span>
                    </a>
                    <div class="showcase-item__body">
                        <h3 class="showcase-item__title"><?= sanitize($project['title']) ?></h3>
                        <?php if (!empty($project['client'])): ?>
                        <p class="showcase-item__client">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span><?= sanitize($project['client']) ?></span>
                        </p>
                        <?php endif; ?>
                        <div class="showcase-item__footer">
                            <a href="<?= SITE_URL ?>/portfolio.php" class="showcase-item__cta">
                                View Project
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                            <?php if ($has_url): ?>
                            <a href="<?= sanitize($purl) ?>" target="_blank" rel="noopener" class="showcase-item__link" aria-label="Visit live project">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-3xl reveal">
                <a href="<?= SITE_URL ?>/portfolio.php" class="btn btn-outline--dark btn-lg">
                    View All Projects
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </section>


    <!-- ═══ Testimonials ═══ -->
    <section class="section section--soft" id="testimonials">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-header__label"><?= sanitize(homeText('home_testimonials_label')) ?></span>
                <h2 class="section-header__title"><?= sanitize(homeText('home_testimonials_title')) ?> <span class="text-gradient"><?= sanitize(homeText('home_testimonials_title_highlight')) ?></span></h2>
                <p class="section-header__desc"><?= sanitize(homeText('home_testimonials_desc')) ?></p>
            </div>

            <div class="testimonials__slider reveal">
                <div class="testimonials__track" id="testimonialTrack">
                    <?php foreach ($testimonials as $t): ?>
                    <div class="testimonial-card">
                        <p class="testimonial-card__quote">
                            <?= sanitize($t['quote']) ?>
                        </p>
                        <div class="testimonial-card__author">
                            <div class="testimonial-card__avatar"><?= sanitize($t['avatar_initials']) ?></div>
                            <div class="testimonial-card__info">
                                <div class="testimonial-card__name"><?= sanitize($t['client_name']) ?></div>
                                <div class="testimonial-card__role"><?= sanitize($t['client_role']) ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="testimonials__dots">
                    <?php foreach ($testimonials as $index => $t): ?>
                    <button class="testimonials__dot<?= $index === 0 ? ' active' : '' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══ CTA Banner ═══ -->
    <section class="cta-banner" id="cta">
        <div class="cta-banner__content container reveal">
            <h2 class="cta-banner__title"><?= sanitize(homeText('home_cta_title')) ?></h2>
            <p class="cta-banner__desc"><?= sanitize(homeText('home_cta_desc')) ?></p>
            <div class="flex-center gap-md" style="flex-wrap:wrap">
                <a href="<?= SITE_URL ?>/contact.php?type=quote" class="btn btn-outline btn-lg">
                    <?= sanitize(homeText('home_cta_btn_label')) ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="tel:<?= str_replace(' ', '', SITE_PHONE) ?>" class="btn btn-ghost" style="color:rgba(255,255,255,0.8)">
                    Or call us: <?= SITE_PHONE ?>
                </a>
            </div>
        </div>
    </section>

<?php include 'includes/service-modal.php'; ?>
<?php include 'includes/footer.php'; ?>
