-- Nadics Digital Solution — Admin Panel Schema Additions
-- Run this AFTER the main schema.sql

USE nadics_portfolio;

-- ─── Admin Users ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admins (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    full_name    VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    avatar_url   VARCHAR(500) DEFAULT NULL,
    last_login   TIMESTAMP    NULL DEFAULT NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default admin (password: @123NadicsDigital)
-- The hash is generated via PHP's password_hash('@123NadicsDigital', PASSWORD_BCRYPT)
INSERT INTO admins (full_name, email, password) VALUES
('Admin', 'admin@nadicsdigital.com', '$2y$10$Qflu0MT9ZHhltaoztJGKROGyGrmUemntnK8lpGhLflsAP1gkA8tnC');

-- ─── Testimonials ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS testimonials (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    client_name  VARCHAR(150) NOT NULL,
    client_role  VARCHAR(200) NOT NULL,
    quote        TEXT         NOT NULL,
    avatar_initials VARCHAR(5) DEFAULT NULL,
    rating       TINYINT(1)   DEFAULT 5,
    is_active    TINYINT(1)   DEFAULT 1,
    display_order INT         DEFAULT 0,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- Seed testimonials
INSERT INTO testimonials (client_name, client_role, quote, avatar_initials, rating, is_active, display_order) VALUES
('Kwame Asante', 'CEO, RetailHub Ghana', 'Nadics Digital Solution completely transformed our online presence. Their e-commerce platform increased our sales by 200% in the first quarter. Their attention to detail and commitment to quality is outstanding.', 'KA', 5, 1, 1),
('Ama Adjei', 'Event Director, Ghana Music Awards', 'The voting platform they built for our awards ceremony was flawless. Over 50,000 votes processed without a single issue. Their team''s technical expertise and responsiveness is truly exceptional.', 'AA', 5, 1, 2),
('Efo Mensah', 'Founder, PayWave Finance', 'Working with Nadics on our brand identity was a game-changer. They took time to understand our vision and delivered designs that perfectly capture who we are. Highly recommend their creative team.', 'EM', 5, 1, 3);

-- ─── Site Settings ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS site_settings (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    setting_key  VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT        DEFAULT NULL,
    updated_at   TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'Nadics Digital Solution'),
('site_tagline', 'Transforming Ideas Into Digital Reality'),
('site_email', 'info@nadicsdigital.com'),
('site_phone', '+233 24 000 0000'),
('site_address', 'Accra, Ghana'),
('business_hours', 'Mon – Fri: 8:00 AM – 6:00 PM | Sat: 9:00 AM – 2:00 PM'),
('facebook_url', '#'),
('twitter_url', '#'),
('instagram_url', '#'),
('linkedin_url', '#'),
('tiktok_url', '#'),
('stats_projects', '120'),
('stats_clients', '85'),
('stats_years', '5'),
('stats_services', '7');

-- ─── Activity Log ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS activity_log (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    admin_id    INT          NOT NULL,
    action      VARCHAR(200) NOT NULL,
    entity_type VARCHAR(50)  DEFAULT NULL,
    entity_id   INT          DEFAULT NULL,
    details     TEXT         DEFAULT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ─── Services ───────────────────────────────────────────────────────
-- The "What We Offer" services shown on services.php (fully editable in admin).
-- NOTE: If this table is absent or empty the app auto-creates and seeds it on
-- first load (see ensureServicesTable() in includes/functions.php), so running
-- this block manually is optional.
CREATE TABLE IF NOT EXISTS services (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(200) NOT NULL,
    subtitle      VARCHAR(255) DEFAULT NULL,
    description   TEXT         NOT NULL,
    icon_svg      TEXT         DEFAULT NULL,   -- inner SVG markup (paths), wrapped in a 24x24 viewBox
    features      TEXT         DEFAULT NULL,   -- one feature per line
    link_url      VARCHAR(500) DEFAULT NULL,
    link_label    VARCHAR(150) DEFAULT NULL,
    anchor_id     VARCHAR(150) DEFAULT NULL,   -- HTML id used for #anchor links
    display_order INT          DEFAULT 0,
    is_active     TINYINT(1)   DEFAULT 1,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- ─── Team Members (Leadership) ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS team_members (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(150) NOT NULL,
    title        VARCHAR(150) NOT NULL,
    image_url    VARCHAR(500) NOT NULL,
    bio          TEXT         DEFAULT NULL,
    linkedin_url VARCHAR(500) DEFAULT NULL,
    display_order INT         DEFAULT 0,
    is_active    TINYINT(1)   DEFAULT 1,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- Seed default team members
INSERT INTO team_members (name, title, image_url, bio, display_order, is_active) VALUES
('Ebenezer Owusu', 'Chief Executive Officer', 'assets/images/team/member-1.png', 'With over 8 years of experience in software architecture and IT consulting, Ebenezer leads the strategic vision and engineering standards at Nadics.', 1, 1),
('Abigail Mensah', 'Lead Designer & Creative Director', 'assets/images/team/member-2.png', 'Abigail specializes in brand design, UI/UX, and front-end aesthetics, delivering interfaces that marry gorgeous visuals with flawless functionality.', 2, 1),
('Emmanuel Kojo', 'Head of Systems & IT Support', 'assets/images/team/member-3.png', 'Emmanuel oversees network architecture, troubleshooting systems, and coordinates hardware installations and troubleshooting contracts.', 3, 1);
