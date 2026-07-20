<?php
/**
 * Nadics Digital Solution — Helper Functions
 * Sanitization, validation, CSRF, and data helpers
 */

require_once __DIR__ . '/config.php';

// ─── Input Sanitization ─────────────────────────────────────────────

/**
 * Sanitize a string input
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate an email address
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate a phone number (basic)
 */
function isValidPhone(string $phone): bool {
    return preg_match('/^[\+]?[\d\s\-\(\)]{7,20}$/', $phone) === 1;
}

// ─── CSRF Protection ────────────────────────────────────────────────

/**
 * Generate a CSRF token and store in session
 */
function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token
 */
function verifyCSRFToken(string $token): bool {
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    $valid = hash_equals($_SESSION['csrf_token'], $token);
    // Regenerate after verification
    unset($_SESSION['csrf_token']);
    return $valid;
}

/**
 * Output a hidden CSRF field for forms
 */
function csrfField(): string {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// ─── Site Settings Helpers ──────────────────────────────────────────

/**
 * Get a single site setting value, cached for the request.
 * Returns $default only when the key has never been saved.
 */
function getSetting(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $db = getDB();
        if ($db) {
            try {
                $cache = $db->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
            } catch (PDOException $e) {
                $cache = [];
            }
        }
    }
    return array_key_exists($key, $cache) ? $cache[$key] : $default;
}

/**
 * Default text/content for the editable Home page fields.
 * Shared by the front-end (fallbacks) and the admin editor (form values).
 * @return array<string,string>
 */
function homepageDefaults(): array {
    $base = defined('SITE_URL') ? SITE_URL : '';
    return [
        // Hero
        'home_hero_badge'          => 'Available for new projects',
        'home_hero_title_1'        => 'We Build Solutions',
        'home_hero_title_2_prefix' => 'That',
        'home_hero_typed_words'    => 'Innovate, Design, Develop, Support, Transform',
        'home_hero_desc'           => 'From cutting-edge web applications to stunning graphic design, we deliver digital solutions that drive results and elevate your brand to new heights.',
        'home_hero_btn1_label'     => 'Explore Services',
        'home_hero_btn1_url'       => $base . '/services.php',
        'home_hero_btn2_label'     => 'View Our Work',
        'home_hero_btn2_url'       => $base . '/portfolio.php',
        'home_hero_stat1_label'    => 'Projects Delivered',
        'home_hero_stat2_label'    => 'Happy Clients',
        'home_hero_stat3_label'    => 'Years Experience',
        'home_hero_icon_1'         => '💻',
        'home_hero_icon_2'         => '🎨',
        'home_hero_icon_3'         => '⚙️',
        'home_hero_icon_4'         => '🚀',
        'home_hero_icon_5'         => '🎓',
        'home_hero_icon_6'         => '📊',
        // Hero visual: 'globe' (built-in design), 'image' (one PNG) or 'slideshow'
        'home_hero_visual'         => 'globe',
        'home_hero_image'          => '',
        'home_hero_slides'         => '',   // newline-separated image URLs
        'home_hero_slide_interval' => '4',  // seconds per slide
        'home_hero_image_size'     => '100', // % scale of the hero image
        'home_hero_icons_show'     => '1',   // '1' show orbiting icons, '0' hide
        // Services preview
        'home_services_label'           => 'What We Do',
        'home_services_title'           => 'Services That',
        'home_services_title_highlight' => 'Drive Growth',
        'home_services_desc'            => 'We offer a comprehensive suite of digital services designed to help your business thrive in today\'s competitive landscape.',
        'home_services_btn_label'       => 'View All Services',
        // About teaser
        'home_about_label'           => 'About Us',
        'home_about_title'           => 'Your Trusted',
        'home_about_title_highlight' => 'Digital Partner',
        'home_about_desc'            => 'At Nadics Digital Solution, we don\'t just build software — we craft digital experiences that transform businesses. With a passionate team of developers, designers, and IT professionals, we bring your vision to life with precision and creativity.',
        'home_about_features'        => "Tailored Solutions\nTimely Delivery\nDedicated Support\nAffordable Pricing",
        'home_about_btn_label'       => 'Learn More About Us',
        'home_about_stat_value'      => '98',
        'home_about_stat_suffix'     => '%',
        'home_about_stat_label'      => 'Client Satisfaction',
        'home_about_image'           => $base . '/assets/images/about-team.png',
        // Stats counter section
        'home_stats_label1' => 'Projects Completed',
        'home_stats_label2' => 'Happy Clients',
        'home_stats_label3' => 'Services Offered',
        'home_stats_label4' => 'Years of Experience',
        // Portfolio showcase
        'home_portfolio_label'           => 'Our Work',
        'home_portfolio_title'           => 'Featured',
        'home_portfolio_title_highlight' => 'Projects',
        'home_portfolio_desc'            => 'A glimpse of the impactful digital solutions we\'ve delivered for our clients across various industries.',
        'home_portfolio_btn_label'       => 'View All Projects',
        // Testimonials
        'home_testimonials_label'           => 'Testimonials',
        'home_testimonials_title'           => 'What Our',
        'home_testimonials_title_highlight' => 'Clients Say',
        'home_testimonials_desc'            => 'Hear from the businesses and individuals who\'ve trusted us with their digital transformation.',
        // CTA banner
        'home_cta_title'     => 'Ready to Transform Your Digital Presence?',
        'home_cta_desc'      => 'Let\'s discuss how we can help your business grow with innovative technology solutions tailored to your needs.',
        'home_cta_btn_label' => 'Start a Project',
    ];
}

/**
 * Get an editable Home page value, falling back to its shipped default.
 */
function homeText(string $key): string {
    $defaults = homepageDefaults();
    return getSetting($key, $defaults[$key] ?? '');
}

/**
 * List the PNG/image designs available for the home hero visual.
 * Scans /assets/images/png-design/ so both shipped presets and admin
 * uploads show up as selectable options.
 * @return array<int,array{url:string,name:string}>
 */
function heroDesignImages(): array {
    $dir  = __DIR__ . '/../assets/images/png-design/';
    $base = (defined('SITE_URL') ? SITE_URL : '') . '/assets/images/png-design/';
    $allowed = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'];
    $out = [];

    if (is_dir($dir)) {
        $files = scandir($dir) ?: [];
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) continue;
            // Encode the filename so spaces / unicode in preset names are URL-safe
            $out[] = [
                'url'  => $base . rawurlencode($f),
                'name' => $f,
            ];
        }
    }
    return $out;
}

/**
 * Split a multi-line, pipe-delimited block into rows of trimmed cells.
 * e.g. "2019 | Title | Desc\n2020 | ..." => [['2019','Title','Desc'], ...]
 * @return array<int,string[]>
 */
function splitRows(string $text): array {
    $rows = [];
    foreach (serviceFeatureList($text) as $line) {
        $rows[] = array_map('trim', explode('|', $line));
    }
    return $rows;
}

/**
 * Default text/content for the editable About page fields.
 * @return array<string,string>
 */
function aboutDefaults(): array {
    $base = defined('SITE_URL') ? SITE_URL : '';
    return [
        // Page hero
        'about_hero_title' => 'About Us',
        // Our Story
        'about_story_label'           => 'Our Story',
        'about_story_title'           => 'Empowering Businesses Through',
        'about_story_title_highlight' => 'Digital Innovation',
        'about_story_title_suffix'    => '',
        'about_story_lead'            => 'Nadics Digital Solution was founded with a simple but powerful belief: that every business, regardless of size, deserves access to world-class digital solutions.',
        'about_story_body1'           => 'What began as a small team of passionate developers and designers in Accra has grown into a full-service digital consultancy trusted by businesses, institutions, and organizations across Ghana. We\'ve built everything from enterprise management systems to award-winning voting platforms — each project a testament to our commitment to quality and innovation.',
        'about_story_body2'           => 'Our approach is straightforward: we listen deeply, plan carefully, execute brilliantly, and support continuously. We don\'t just deliver projects — we build lasting partnerships that grow alongside your business.',
        'about_story_image'           => $base . '/assets/images/about-team.png',
        'about_story_badge_number'    => '5',
        'about_story_badge_suffix'    => '+',
        'about_story_badge_text'      => "Years\nExperience",
        // What NADICS Means
        'about_meaning_label'           => 'The Meaning Behind the Name',
        'about_meaning_title'           => 'What',
        'about_meaning_title_highlight' => 'NADICS',
        'about_meaning_title_suffix'    => 'Stands For',
        'about_meaning_desc'            => 'Every letter reflects a promise — the principles that shape everything we build.',
        'about_meaning_acronyms'        => "N | Next-generation\nA | Advanced\nD | Digital\nI | Innovation\nC | Creative\nS | Solutions",
        'about_meaning_statement'       => 'Nadics represents a commitment to delivering next-generation digital innovation through creative, reliable, and intelligent technology solutions.',
        // Mission, Vision, Values
        'about_mvv_label'           => 'What Drives Us',
        'about_mvv_title'           => 'Mission, Vision &',
        'about_mvv_title_highlight' => 'Values',
        'about_mvv_title_suffix'    => '',
        'about_mvv_mission_title'   => 'Our Mission',
        'about_mvv_mission_desc'    => 'To deliver innovative, reliable, and affordable digital solutions that empower businesses and individuals to achieve their full potential in the digital age.',
        'about_mvv_vision_title'    => 'Our Vision',
        'about_mvv_vision_desc'     => 'To become the most trusted and sought-after digital solutions provider in West Africa, known for our excellence, creativity, and unwavering commitment to client success.',
        'about_mvv_values_title'    => 'Our Values',
        'about_mvv_values_desc'     => 'Integrity in every interaction. Innovation in every solution. Excellence in every delivery. We believe in transparency, continuous learning, and treating every client\'s project as our own.',
        // Timeline
        'about_timeline_label'           => 'Our Journey',
        'about_timeline_title'           => 'Key',
        'about_timeline_title_highlight' => 'Milestones',
        'about_timeline_title_suffix'    => '',
        'about_timeline_desc'            => 'A look back at the defining moments that have shaped who we are today.',
        'about_timeline_items'           => "2019 | The Beginning | Nadics Digital Solution was founded in Accra with a vision to make quality IT services accessible to businesses of all sizes.\n2020 | First Major Project | Delivered our first enterprise web application, a hospital management system that streamlined operations for MedCare Hospital.\n2021 | Voting Platform Launch | Launched our signature online voting system, processing over 50,000 votes for the Ghana Music Awards without a single downtime incident.\n2023 | 100+ Projects Milestone | Crossed the milestone of 100 successfully completed projects, serving clients across education, healthcare, finance, and entertainment sectors.\n2024 | Service Expansion | Expanded our service offerings to include comprehensive research consultation and enterprise IT infrastructure solutions.",
        // Leadership
        'about_leadership_label'           => 'Core Team',
        'about_leadership_title'           => 'Meet the',
        'about_leadership_title_highlight' => 'Leadership',
        'about_leadership_title_suffix'    => '',
        'about_leadership_desc'            => 'The passionate minds driving our vision and delivering outstanding digital solutions.',
        // Tech Stack
        'about_tech_label'           => 'Technologies',
        'about_tech_title'           => 'Our',
        'about_tech_title_highlight' => 'Tech Stack',
        'about_tech_title_suffix'    => '',
        'about_tech_desc'            => 'We work with industry-leading technologies to deliver robust, scalable solutions.',
        'about_tech_items'           => "🌐 | HTML5\n🎨 | CSS3\n⚡ | JavaScript\n🐘 | PHP\n🗄️ | MySQL\n⚛️ | React\n🟢 | Node.js\n🐍 | Python\n🔷 | WordPress\n☁️ | Cloud Services\n🎯 | Adobe Suite\n📱 | Responsive Design",
        // CTA
        'about_cta_title'     => 'Ready to Work With Us?',
        'about_cta_desc'      => 'Join our growing list of satisfied clients. Let\'s create something remarkable together.',
        'about_cta_btn_label' => 'Get in Touch',
    ];
}

/**
 * Get an editable About page value, falling back to its shipped default.
 */
function aboutText(string $key): string {
    $defaults = aboutDefaults();
    return getSetting($key, $defaults[$key] ?? '');
}

/**
 * Default text/content for the editable Contact page fields.
 * The page has two modes — the normal contact form and the "Request a
 * Quote" variant (?type=quote) — so several fields have a *_quote twin.
 * @return array<string,string>
 */
function contactDefaults(): array {
    return [
        // Page hero
        'contact_hero_title'       => 'Contact Us',
        'contact_hero_title_quote' => 'Request a Quote',

        // Section header — contact mode
        'contact_label'           => 'Get In Touch',
        'contact_title'           => 'Let\'s Start a',
        'contact_title_highlight' => 'Conversation',
        'contact_desc'            => 'Have a project in mind or need technical support? We\'d love to hear from you. Fill out the form below and we\'ll get back to you within 24 hours.',

        // Section header — quote mode
        'contact_label_quote'           => 'Get a Custom Solution',
        'contact_title_quote'           => 'Let\'s Build Something',
        'contact_title_highlight_quote' => 'Remarkable',
        'contact_desc_quote'            => 'Tell us about your project requirements, estimated budget, and timeline. We\'ll get back to you with a detailed proposal within 24 hours.',

        // Form card — contact mode
        'contact_form_title'    => 'Send Us a Message',
        'contact_form_subtitle' => 'Fill out the form below and we\'ll respond promptly.',
        'contact_success_msg'   => 'Your message has been sent successfully! We\'ll get back to you shortly.',

        // Form card — quote mode
        'contact_form_title_quote'    => 'Request a Free Quote',
        'contact_form_subtitle_quote' => 'Provide your details and project specs, and we will prepare a proposal.',
        'contact_success_msg_quote'   => 'Your quote request has been submitted successfully! We\'ll get back to you with a proposal shortly.',

        // Shared error message
        'contact_error_msg' => 'Something went wrong. Please try again.',

        // Contact information sidebar
        'contact_info_heading'    => 'Contact Information',
        'contact_hours'           => "Mon – Fri: 8:00 AM – 6:00 PM\nSat: 9:00 AM – 2:00 PM",
        'contact_socials_heading' => 'Follow Us',

        // Map / visit
        'contact_map_title' => 'Accra, Ghana',
        'contact_map_desc'  => 'Contact us to schedule a visit to our office',
        'contact_map_embed' => '', // optional Google Maps embed URL (iframe src)
    ];
}

/**
 * Get an editable Contact page value, falling back to its shipped default.
 */
function contactText(string $key): string {
    $defaults = contactDefaults();
    return getSetting($key, $defaults[$key] ?? '');
}

// ─── Hero Background Overlays ───────────────────────────────────────

/**
 * The configurable hero background overlays.
 * Key = site_settings key, value = human label shown in the admin.
 * @return array<string,string>
 */
function heroBackgrounds(): array {
    return [
        'hero_bg_home'      => 'Home Page — Main Hero',
        'hero_bg_services'  => 'Services Page Hero',
        'hero_bg_portfolio' => 'Portfolio Page Hero',
        'hero_bg_about'     => 'About Page Hero',
        'hero_bg_contact'   => 'Contact / Request a Quote Hero',
    ];
}

/**
 * Shipped default overlay images so the site looks polished out of the box.
 * The admin can override any of these or clear them (which stores an empty
 * value and disables the overlay for that hero).
 * @return array<string,string>
 */
function heroBackgroundDefaults(): array {
    $base = (defined('SITE_URL') ? SITE_URL : '') . '/assets/images/bg-overlay/';
    return [
        'hero_bg_home'      => $base . rawurlencode('modern office building in the city.jpg'),
        'hero_bg_services'  => $base . '756041856201898431.jpg',
        'hero_bg_portfolio' => $base . 'download.jpg',
        'hero_bg_about'     => $base . rawurlencode('modern office building in the city.jpg'),
        'hero_bg_contact'   => $base . '756041856201898431.jpg',
    ];
}

/**
 * Get the background overlay image URL for a hero, falling back to the
 * shipped default. Returns an empty string when the admin has cleared it.
 */
function heroBg(string $key): string {
    $defaults = heroBackgroundDefaults();
    return getSetting($key, $defaults[$key] ?? '');
}

/**
 * Render the background overlay element for a hero section.
 * Returns an empty string when no image has been configured, so the
 * section falls back to its default gradient.
 *
 * @param string $key   site_settings key (see heroBackgrounds())
 * @param string $class CSS class for the overlay element
 */
function heroBgTag(string $key, string $class = 'page-hero__bg'): string {
    $url = heroBg($key);
    if ($url === '') return '';
    $safe = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    return '<div class="' . $class . '" style="background-image:url(\'' . $safe . '\')" aria-hidden="true"></div>';
}

// ─── Portfolio Helpers ──────────────────────────────────────────────

/**
 * Get portfolio items, optionally filtered by category
 */
function getPortfolioItems(?string $category = null, bool $featuredOnly = false): array {
    $db = getDB();
    if (!$db) return [];

    $sql = "SELECT * FROM portfolio_items WHERE 1=1";
    $params = [];

    if ($category && $category !== 'all') {
        $sql .= " AND category = :category";
        $params[':category'] = $category;
    }
    if ($featuredOnly) {
        $sql .= " AND is_featured = 1";
    }

    $sql .= " ORDER BY display_order ASC, created_at DESC";

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Portfolio query failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single portfolio item by slug
 */
function getPortfolioBySlug(string $slug): ?array {
    $db = getDB();
    if (!$db) return null;

    try {
        $stmt = $db->prepare("SELECT * FROM portfolio_items WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $item = $stmt->fetch();
        return $item ?: null;
    } catch (PDOException $e) {
        error_log("Portfolio slug query failed: " . $e->getMessage());
        return null;
    }
}

// ─── Navigation Helpers ─────────────────────────────────────────────

/**
 * Check if the current page matches (for nav active state)
 */
function isCurrentPage(string $page): bool {
    $currentFile = basename($_SERVER['PHP_SELF']);
    return $currentFile === $page;
}

/**
 * Get active class for nav links
 */
function activeClass(string $page): string {
    return isCurrentPage($page) ? ' active' : '';
}

// ─── Misc Helpers ───────────────────────────────────────────────────

/**
 * Format a category slug as readable text
 */
function formatCategory(string $category): string {
    $map = [
        'web-dev'        => 'Web Development',
        'graphic-design' => 'Graphic Design',
        'it-solutions'   => 'IT Solutions',
        'voting-systems' => 'Voting Systems',
    ];
    return $map[$category] ?? ucwords(str_replace('-', ' ', $category));
}

/**
 * Basic rate limiter using session (per-session, per-action)
 */
function isRateLimited(string $action, int $maxAttempts = 5, int $windowSeconds = 300): bool {
    $key = 'rate_limit_' . $action;
    $now = time();

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [];
    }

    // Clean old entries
    $_SESSION[$key] = array_filter($_SESSION[$key], function ($timestamp) use ($now, $windowSeconds) {
        return ($now - $timestamp) < $windowSeconds;
    });

    if (count($_SESSION[$key]) >= $maxAttempts) {
        return true;
    }

    $_SESSION[$key][] = $now;
    return false;
}

// ─── Services Helpers ───────────────────────────────────────────────

/**
 * Convert a piece of text into a URL-friendly slug/anchor id
 */
function slugify(string $text): string {
    $slug = preg_replace('/[^A-Za-z0-9]+/', '-', $text);
    $slug = strtolower(trim($slug, '-'));
    return $slug !== '' ? $slug : 'service';
}

/**
 * Turn a newline-separated feature block into a clean array of features
 * @return string[]
 */
function serviceFeatureList(?string $features): array {
    if ($features === null || $features === '') return [];
    $lines = preg_split('/\r\n|\r|\n/', $features);
    return array_values(array_filter(array_map('trim', $lines), fn($l) => $l !== ''));
}

/**
 * Ensure the services table exists and is seeded with the default services.
 * Runs lazily so the feature works even before the SQL migration is applied.
 */
function ensureServicesTable(PDO $db): void {
    static $checked = false;
    if ($checked) return;

    $db->exec("
        CREATE TABLE IF NOT EXISTS services (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            title         VARCHAR(200) NOT NULL,
            subtitle      VARCHAR(255) DEFAULT NULL,
            description   TEXT         NOT NULL,
            icon_svg      TEXT         DEFAULT NULL,
            image_url     VARCHAR(500) DEFAULT NULL,
            features      TEXT         DEFAULT NULL,
            link_url      VARCHAR(500) DEFAULT NULL,
            link_label    VARCHAR(150) DEFAULT NULL,
            anchor_id     VARCHAR(150) DEFAULT NULL,
            display_order INT          DEFAULT 0,
            is_active     TINYINT(1)   DEFAULT 1,
            created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB
    ");

    // Migration: add the image_url column for existing installs that predate it
    try {
        $hasImage = $db->query("SHOW COLUMNS FROM services LIKE 'image_url'")->fetch();
        if (!$hasImage) {
            $db->exec("ALTER TABLE services ADD COLUMN image_url VARCHAR(500) DEFAULT NULL AFTER icon_svg");
        }
    } catch (PDOException $e) {
        error_log("Services image_url migration skipped: " . $e->getMessage());
    }

    $count = (int) $db->query("SELECT COUNT(*) FROM services")->fetchColumn();
    if ($count === 0) {
        seedDefaultServices($db);
    }

    $checked = true;
}

/**
 * Insert the default set of services that ships with the site
 */
function seedDefaultServices(PDO $db): void {
    $defaults = defaultServicesData();
    $stmt = $db->prepare("
        INSERT INTO services (title, subtitle, description, icon_svg, features, link_url, link_label, anchor_id, display_order, is_active)
        VALUES (:title, :subtitle, :description, :icon_svg, :features, :link_url, :link_label, :anchor_id, :display_order, 1)
    ");
    foreach ($defaults as $i => $s) {
        $stmt->execute([
            ':title'         => $s['title'],
            ':subtitle'      => $s['subtitle'],
            ':description'   => $s['description'],
            ':icon_svg'      => $s['icon_svg'],
            ':features'      => implode("\n", $s['features']),
            ':link_url'      => $s['link_url'] ?? null,
            ':link_label'    => $s['link_label'] ?? null,
            ':anchor_id'     => $s['anchor_id'],
            ':display_order' => $i + 1,
        ]);
    }
}

/**
 * The canonical default services (used for seeding and as icon presets)
 * @return array
 */
function defaultServicesData(): array {
    return [
        [
            'title'      => 'System & Web Development',
            'subtitle'   => 'Custom digital solutions built for scale',
            'anchor_id'  => 'web-dev',
            'icon_svg'   => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/><line x1="14" y1="4" x2="10" y2="20"/>',
            'description'=> 'We design and develop robust, scalable web applications and management systems tailored to your unique business requirements. From responsive websites to complex enterprise solutions, our development team leverages modern technologies to deliver exceptional results.',
            'features'   => ['Custom Web Applications', 'E-Commerce Platforms', 'Management Systems', 'API Integration', 'Database Design', 'Responsive Design'],
        ],
        [
            'title'      => 'Graphic Design Services',
            'subtitle'   => 'Visual storytelling that resonates',
            'anchor_id'  => 'graphic-design',
            'icon_svg'   => '<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/>',
            'description'=> 'Our creative design team produces stunning visual content that communicates your brand\'s story. From logo design to complete brand identities, we create designs that leave lasting impressions and set you apart from the competition.',
            'features'   => ['Logo & Brand Identity', 'Social Media Graphics', 'Flyers & Brochures', 'Banner & Billboard Design', 'Business Cards', 'UI/UX Design'],
        ],
        [
            'title'      => 'Research Work Consultation',
            'subtitle'   => 'Academic excellence, simplified',
            'anchor_id'  => 'research',
            'icon_svg'   => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
            'description'=> 'We provide professional academic and corporate research support including data analysis, literature reviews, and scholarly writing guidance. Our research consultants help students and professionals produce high-quality research outputs.',
            'features'   => ['Research Proposals', 'Data Analysis', 'Literature Review', 'Academic Writing', 'Survey Design', 'Statistical Modeling'],
        ],
        [
            'title'      => 'Online Awards Voting & Ticketing',
            'subtitle'   => 'Secure, scalable, real-time',
            'anchor_id'  => 'voting',
            'icon_svg'   => '<path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/><rect x="2" y="2" width="20" height="20" rx="2"/>',
            'description'=> 'Our specialized voting and ticketing platforms are battle-tested for high-traffic events. We build secure, fraud-resistant systems with real-time analytics, mobile payment integration, and beautiful dashboards for event organizers.',
            'features'   => ['Online Voting Portals', 'Anti-Fraud Protection', 'Real-Time Analytics', 'Event Ticketing', 'Payment Integration', 'Admin Dashboard'],
        ],
        [
            'title'      => 'Windows & Office Installation',
            'subtitle'   => 'Professional setup and activation',
            'anchor_id'  => 'software',
            'icon_svg'   => '<rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
            'description'=> 'Get your workstation professionally configured with genuine Windows OS and Microsoft Office suite installations. We handle activation, updates, driver installation, and system optimization to ensure your computer runs at peak performance.',
            'features'   => ['Windows Installation', 'Windows Activation', 'MS Office Setup', 'Driver Installation', 'System Optimization', 'Software Updates'],
        ],
        [
            'title'      => 'Software Troubleshooting & IT Support',
            'subtitle'   => 'Expert technical assistance',
            'anchor_id'  => 'it-support',
            'icon_svg'   => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
            'description'=> 'Our experienced IT professionals provide comprehensive troubleshooting and support services. Whether it\'s a software crash, network issue, or hardware malfunction, we diagnose and resolve problems quickly to minimize your downtime.',
            'features'   => ['Software Debugging', 'Network Setup', 'Hardware Diagnostics', 'Virus Removal', 'Data Recovery', 'Performance Tuning'],
        ],
        [
            'title'      => 'School Management System',
            'subtitle'   => 'All-in-one platform for basic & senior high schools',
            'anchor_id'  => 'school-management',
            'icon_svg'   => '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
            'description'=> 'A comprehensive, cloud-based school management system built for basic schools and senior high schools. It brings students, teachers, parents, and administrators onto a single platform — automating admissions, attendance, grading, fee collection, and reporting so your school runs smoothly from one dashboard.',
            'features'   => ['Student Enrollment & Records', 'Attendance Tracking', 'Grading & Report Cards', 'Fees & Billing Management', 'Timetable & Scheduling', 'Parent & Teacher Portals', 'SMS & Email Notifications', 'Exams & Results Portal'],
            'link_url'   => 'https://school.nadicssolution.com',
            'link_label' => 'View Live System',
        ],
    ];
}

/**
 * Get services ordered by display order
 * @return array
 */
function getServices(bool $activeOnly = true): array {
    $db = getDB();
    if (!$db) return [];

    try {
        ensureServicesTable($db);
        $sql = "SELECT * FROM services";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY display_order ASC, id ASC";
        return $db->query($sql)->fetchAll();
    } catch (PDOException $e) {
        error_log("Services query failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single service by id
 */
function getServiceById(int $id): ?array {
    $db = getDB();
    if (!$db) return null;

    try {
        ensureServicesTable($db);
        $stmt = $db->prepare("SELECT * FROM services WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (PDOException $e) {
        error_log("Service fetch failed: " . $e->getMessage());
        return null;
    }
}

// ─── Team/Leadership Helpers ────────────────────────────────────────

/**
 * Ensure newer optional columns exist on team_members (safe migration for
 * installs created before these columns were introduced).
 */
function ensureTeamMemberColumns(PDO $db): void {
    static $checked = false;
    if ($checked) return;
    try {
        $hasLinkedin = $db->query("SHOW COLUMNS FROM team_members LIKE 'linkedin_url'")->fetch();
        if (!$hasLinkedin) {
            $db->exec("ALTER TABLE team_members ADD COLUMN linkedin_url VARCHAR(500) DEFAULT NULL AFTER bio");
        }
    } catch (PDOException $e) {
        error_log("team_members linkedin_url migration skipped: " . $e->getMessage());
    }
    $checked = true;
}

/**
 * Get active team members ordered by display order
 * @return array
 */
function getTeamMembers(): array {
    $db = getDB();
    if (!$db) return [];

    try {
        ensureTeamMemberColumns($db);
        $stmt = $db->query("SELECT * FROM team_members WHERE is_active = 1 ORDER BY display_order ASC, name ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Team query failed: " . $e->getMessage());
        return [];
    }
}
