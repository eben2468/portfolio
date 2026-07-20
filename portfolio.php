<?php
/**
 * Nadics Digital Solution — Portfolio Page
 */

$page_title = 'Our Portfolio — Nadics Digital Solution';
$page_desc  = 'Browse our portfolio of completed projects including web applications, graphic design work, voting systems, and IT solutions delivered for clients across industries.';
$page_css   = 'portfolio.css';
$page_js    = 'portfolio.js';

include 'includes/header.php';

// Fetch from Database first
$db_projects = getPortfolioItems('all', false);
$projects = [];

if (!empty($db_projects)) {
    foreach ($db_projects as $item) {
        $projects[] = [
            'title' => $item['title'],
            'category' => $item['category'],
            'category_name' => formatCategory($item['category']),
            'description' => $item['description'],
            'image' => SITE_URL . '/' . $item['image_url'],
            'client' => $item['client_name'] ?? '—',
            'project_url' => $item['project_url'] ?? '#'
        ];
    }
} else {
    // Portfolio data (static fallback if DB unavailable)
    $projects = [
    [
        'title' => 'E-Commerce Platform Redesign',
        'category' => 'web-dev',
        'category_name' => 'Web Development',
        'description' => 'Complete overhaul of an e-commerce platform with modern UI, payment integration, and inventory management system. Built with responsive design and optimized for conversions.',
        'image' => SITE_URL . '/assets/images/portfolio/project-1.png',
        'client' => 'RetailHub Ghana',
    ],
    [
        'title' => 'Annual Music Awards Voting Portal',
        'category' => 'voting-systems',
        'category_name' => 'Voting Systems',
        'description' => 'Custom-built online voting platform handling 50,000+ votes with real-time result tracking, anti-fraud measures, and mobile-responsive ticketing integration.',
        'image' => SITE_URL . '/assets/images/portfolio/project-2.png',
        'client' => 'Ghana Music Awards',
    ],
    [
        'title' => 'Corporate Brand Identity Suite',
        'category' => 'graphic-design',
        'category_name' => 'Graphic Design',
        'description' => 'Complete brand identity design including logo, business cards, letterheads, social media templates, and brand guidelines document for a fintech startup.',
        'image' => SITE_URL . '/assets/images/portfolio/project-3.png',
        'client' => 'PayWave Finance',
    ],
    [
        'title' => 'Hospital Management System',
        'category' => 'web-dev',
        'category_name' => 'Web Development',
        'description' => 'Full-stack hospital management application with patient records, appointment scheduling, billing, and pharmacy inventory modules.',
        'image' => SITE_URL . '/assets/images/portfolio/project-4.png',
        'client' => 'MedCare Hospital',
    ],
    [
        'title' => 'University Research Portal',
        'category' => 'web-dev',
        'category_name' => 'Web Development',
        'description' => 'Academic research consultation platform enabling students and faculty to collaborate on research projects, share papers, and track progress.',
        'image' => SITE_URL . '/assets/images/portfolio/project-5.png',
        'client' => 'Kwame Nkrumah University',
    ],
    [
        'title' => 'Product Launch Campaign',
        'category' => 'graphic-design',
        'category_name' => 'Graphic Design',
        'description' => 'Multi-channel marketing design for a product launch including social media graphics, billboard designs, flyers, and animated web banners.',
        'image' => SITE_URL . '/assets/images/portfolio/project-6.png',
        'client' => 'TechVibe Solutions',
    ],
    [
        'title' => 'Enterprise IT Infrastructure',
        'category' => 'it-solutions',
        'category_name' => 'IT Solutions',
        'description' => 'Complete IT infrastructure setup for a 200-employee company: workstation configuration, network architecture, Windows deployment, and ongoing support.',
        'image' => SITE_URL . '/assets/images/portfolio/project-7.png',
        'client' => 'GoldStar Mining Ltd',
    ],
    [
        'title' => 'Student Election Voting App',
        'category' => 'voting-systems',
        'category_name' => 'Voting Systems',
        'description' => 'Secure web-based voting application for university student elections with candidate profiles, real-time results dashboard, and voter verification.',
        'image' => SITE_URL . '/assets/images/portfolio/project-8.png',
        'client' => 'University of Ghana',
    ],
];
}
?>

    <!-- ═══ Page Hero ═══ -->
    <section class="page-hero">
        <?= heroBgTag('hero_bg_portfolio') ?>
        <div class="container">
            <h1 class="page-hero__title reveal">Our Portfolio</h1>
            <div class="page-hero__breadcrumb reveal">
                <a href="<?= SITE_URL ?>/">Home</a>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                <span>Portfolio</span>
            </div>
        </div>
    </section>


    <!-- ═══ Portfolio Section ═══ -->
    <section class="section" id="portfolio">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-header__label">Our Work</span>
                <h2 class="section-header__title">Projects That <span class="text-gradient">Speak Results</span></h2>
                <p class="section-header__desc">Each project in our portfolio represents a story of collaboration, innovation, and measurable impact for our clients.</p>
            </div>

            <!-- Filter Tabs -->
            <div class="portfolio-filters reveal">
                <button class="portfolio-filter active" data-filter="all">All Projects</button>
                <button class="portfolio-filter" data-filter="web-dev">Web Development</button>
                <button class="portfolio-filter" data-filter="graphic-design">Graphic Design</button>
                <button class="portfolio-filter" data-filter="voting-systems">Voting Systems</button>
                <button class="portfolio-filter" data-filter="it-solutions">IT Solutions</button>
            </div>

            <!-- Portfolio Grid -->
            <div class="portfolio-grid">
                <?php foreach ($projects as $index => $project):
                    $purl = $project['project_url'] ?? '';
                    $has_url = $purl !== '' && $purl !== '#';
                ?>
                <div class="portfolio-item reveal"
                     data-category="<?= $project['category'] ?>"
                     data-lightbox
                     data-image="<?= $project['image'] ?>"
                     data-title="<?= sanitize($project['title']) ?>"
                     data-category-name="<?= sanitize($project['category_name']) ?>"
                     data-client="<?= sanitize($project['client']) ?>"
                     data-description="<?= sanitize($project['description']) ?>">
                    <div class="portfolio-item__media">
                        <img src="<?= $project['image'] ?>"
                             alt="<?= sanitize($project['title']) ?>"
                             class="portfolio-item__image"
                             loading="lazy">
                        <span class="portfolio-item__badge"><?= sanitize($project['category_name']) ?></span>
                        <div class="portfolio-item__veil">
                            <span class="portfolio-item__zoom">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                            </span>
                        </div>
                    </div>
                    <div class="portfolio-item__body">
                        <h3 class="portfolio-item__title"><?= sanitize($project['title']) ?></h3>
                        <p class="portfolio-item__client">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span><?= sanitize($project['client']) ?></span>
                        </p>
                        <div class="portfolio-item__footer">
                            <span class="portfolio-item__cta">
                                View details
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                            <?php if ($has_url): ?>
                            <a href="<?= sanitize($purl) ?>" target="_blank" rel="noopener" class="portfolio-item__link" aria-label="Visit live project" onclick="event.stopPropagation()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <!-- ═══ Lightbox ═══ -->
    <div class="lightbox" id="portfolioLightbox">
        <button class="lightbox__close" id="lightboxClose" aria-label="Close lightbox">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div class="lightbox__content">
            <img src="" alt="" class="lightbox__image">
            <div class="lightbox__details">
                <span class="lightbox__category"></span>
                <h3 class="lightbox__title"></h3>
                <p class="lightbox__client"></p>
                <p class="lightbox__desc"></p>
                <a href="<?= SITE_URL ?>/contact.php?type=quote" class="lightbox__link">
                    Discuss a Similar Project
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>


    <!-- ═══ CTA ═══ -->
    <section class="cta-banner">
        <div class="cta-banner__content container reveal">
            <h2 class="cta-banner__title">Have a Project in Mind?</h2>
            <p class="cta-banner__desc">Let's work together to bring your ideas to life. Get in touch to discuss your project requirements.</p>
            <a href="<?= SITE_URL ?>/contact.php?type=quote" class="btn btn-outline btn-lg">
                Start Your Project
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
