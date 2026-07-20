<?php
/**
 * Nadics Digital Solution — Services Page
 */

$page_title = 'Our Services — Nadics Digital Solution';
$page_desc  = 'Explore our comprehensive range of digital services including web development, graphic design, IT support, research consultation, and online voting systems.';
$page_css   = 'services.css';

include 'includes/header.php';
?>

    <!-- ═══ Page Hero ═══ -->
    <section class="page-hero">
        <?= heroBgTag('hero_bg_services') ?>
        <div class="container">
            <h1 class="page-hero__title reveal">Our Services</h1>
            <div class="page-hero__breadcrumb reveal">
                <a href="<?= SITE_URL ?>/">Home</a>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                <span>Services</span>
            </div>
        </div>
    </section>


    <!-- ═══ Services Grid ═══ -->
    <section class="section" id="services">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-header__label">What We Offer</span>
                <h2 class="section-header__title">Comprehensive <span class="text-gradient">Digital Solutions</span></h2>
                <p class="section-header__desc">We deliver end-to-end services that cover every aspect of your digital journey, from concept to deployment and beyond.</p>
            </div>

            <div class="services-grid stagger-children">
                <?php
                $services = getServices();
                foreach ($services as $service):
                    $anchor   = $service['anchor_id'] ?: slugify($service['title']);
                    $svc_blurb = !empty($service['subtitle']) ? $service['subtitle'] : $service['description'];
                    $svc_img  = !empty($service['image_url'])
                        ? (str_contains($service['image_url'], '://') ? $service['image_url'] : SITE_URL . '/' . ltrim($service['image_url'], '/'))
                        : '';
                    $svc_data = [
                        'title'       => $service['title'],
                        'subtitle'    => $service['subtitle'] ?? '',
                        'description' => $service['description'],
                        'features'    => serviceFeatureList($service['features'] ?? ''),
                        'image'       => $svc_img,
                        'icon'        => $service['icon_svg'] ?? '',
                        'link'        => !empty($service['link_url']) ? $service['link_url'] : '',
                        'linkLabel'   => $service['link_label'] ?: 'Learn More',
                    ];
                ?>
                <article class="service-detail reveal" id="<?= sanitize($anchor) ?>" data-service-card>
                    <div class="service-detail__media<?= $svc_img ? '' : ' service-detail__media--noimg' ?>">
                        <?php if ($svc_img): ?>
                        <img class="service-detail__img" src="<?= sanitize($svc_img) ?>" alt="<?= sanitize($service['title']) ?>" loading="lazy">
                        <?php endif; ?>
                    </div>
                    <span class="service-detail__badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><?= $service['icon_svg'] ?></svg>
                    </span>
                    <div class="service-detail__body">
                        <h3 class="service-detail__title"><?= sanitize($service['title']) ?></h3>
                        <p class="service-detail__desc"><?= sanitize($svc_blurb) ?></p>
                        <button type="button" class="service-detail__link" data-service-more>
                            Read More
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                    <script type="application/json" class="js-service-data"><?= json_encode($svc_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <!-- ═══ Our Process ═══ -->
    <section class="section section--soft" id="process">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-header__label">How We Work</span>
                <h2 class="section-header__title">Our <span class="text-gradient">Process</span></h2>
                <p class="section-header__desc">A proven methodology that ensures quality results and transparent communication at every stage.</p>
            </div>

            <div class="process__grid stagger-children">
                <div class="process-step reveal">
                    <div class="process-step__number">01</div>
                    <h4 class="process-step__title">Discovery</h4>
                    <p class="process-step__desc">We learn about your business, goals, and challenges through in-depth consultation sessions.</p>
                </div>
                <div class="process-step reveal">
                    <div class="process-step__number">02</div>
                    <h4 class="process-step__title">Planning</h4>
                    <p class="process-step__desc">We map out the project scope, timeline, and deliverables with clear milestones.</p>
                </div>
                <div class="process-step reveal">
                    <div class="process-step__number">03</div>
                    <h4 class="process-step__title">Execution</h4>
                    <p class="process-step__desc">Our team builds your solution with regular progress updates and feedback loops.</p>
                </div>
                <div class="process-step reveal">
                    <div class="process-step__number">04</div>
                    <h4 class="process-step__title">Delivery</h4>
                    <p class="process-step__desc">We launch, test thoroughly, and provide training and ongoing support.</p>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══ Why Choose Us ═══ -->
    <section class="section section--dark" id="why-us">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-header__label">Why Choose Us</span>
                <h2 class="section-header__title">Built on <span class="text-gradient">Trust & Expertise</span></h2>
                <p class="section-header__desc">Here's what sets Nadics Digital Solution apart from the rest.</p>
            </div>

            <div class="why-us__grid stagger-children">
                <div class="why-us-card reveal">
                    <div class="why-us-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <h3 class="why-us-card__title">Quality Guaranteed</h3>
                    <p class="why-us-card__desc">Every project undergoes rigorous quality assurance testing before delivery. We don't compromise on excellence.</p>
                </div>

                <div class="why-us-card reveal">
                    <div class="why-us-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <h3 class="why-us-card__title">On-Time Delivery</h3>
                    <p class="why-us-card__desc">We respect deadlines. Our project management ensures milestones are hit and your project launches on schedule.</p>
                </div>

                <div class="why-us-card reveal">
                    <div class="why-us-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3 class="why-us-card__title">Dedicated Support</h3>
                    <p class="why-us-card__desc">Our relationship doesn't end at delivery. We provide ongoing support, maintenance, and updates for all our solutions.</p>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══ CTA ═══ -->
    <section class="cta-banner">
        <div class="cta-banner__content container reveal">
            <h2 class="cta-banner__title">Need a Custom Solution?</h2>
            <p class="cta-banner__desc">Tell us about your project and get a free consultation from our experts.</p>
            <a href="<?= SITE_URL ?>/contact.php?type=quote" class="btn btn-outline btn-lg">
                Get a Free Quote
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </section>

<?php include 'includes/service-modal.php'; ?>
<?php include 'includes/footer.php'; ?>
