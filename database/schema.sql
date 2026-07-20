-- Nadics Digital Solution — Database Schema
-- Run this script in phpMyAdmin or MySQL CLI to set up the database

CREATE DATABASE IF NOT EXISTS nadics_portfolio
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE nadics_portfolio;

-- ─── Contact Submissions ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS contacts (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    phone       VARCHAR(30)  DEFAULT NULL,
    subject     VARCHAR(200) NOT NULL,
    message     TEXT         NOT NULL,
    ip_address  VARCHAR(45)  DEFAULT NULL,
    is_read     TINYINT(1)   DEFAULT 0,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created (created_at),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB;

-- ─── Portfolio Projects ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS portfolio_items (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200) NOT NULL,
    slug         VARCHAR(200) NOT NULL UNIQUE,
    category     ENUM('web-dev','graphic-design','it-solutions','voting-systems') NOT NULL,
    description  TEXT         NOT NULL,
    image_url    VARCHAR(500) NOT NULL,
    client_name  VARCHAR(150) DEFAULT NULL,
    project_url  VARCHAR(500) DEFAULT NULL,
    is_featured  TINYINT(1)   DEFAULT 0,
    display_order INT         DEFAULT 0,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB;

-- ─── Seed Portfolio Data ────────────────────────────────────────────
INSERT INTO portfolio_items (title, slug, category, description, image_url, client_name, project_url, is_featured, display_order) VALUES
('E-Commerce Platform Redesign', 'ecommerce-platform-redesign', 'web-dev', 'Complete overhaul of an e-commerce platform with modern UI, payment integration, and inventory management system. Built with responsive design and optimized for conversions.', 'assets/images/portfolio/project-1.png', 'RetailHub Ghana', 'https://example.com', 1, 1),
('Annual Music Awards Voting Portal', 'annual-music-awards-voting', 'voting-systems', 'Custom-built online voting platform handling 50,000+ votes with real-time result tracking, anti-fraud measures, and mobile-responsive ticketing integration.', 'assets/images/portfolio/project-2.png', 'Ghana Music Awards', 'https://example.com', 1, 2),
('Corporate Brand Identity Suite', 'corporate-brand-identity', 'graphic-design', 'Complete brand identity design including logo, business cards, letterheads, social media templates, and brand guidelines document for a fintech startup.', 'assets/images/portfolio/project-3.png', 'PayWave Finance', 'https://example.com', 1, 3),
('Hospital Management System', 'hospital-management-system', 'web-dev', 'Full-stack hospital management application with patient records, appointment scheduling, billing, and pharmacy inventory modules.', 'assets/images/portfolio/project-4.png', 'MedCare Hospital', 'https://example.com', 0, 4),
('University Research Portal', 'university-research-portal', 'web-dev', 'Academic research consultation platform enabling students and faculty to collaborate on research projects, share papers, and track progress.', 'assets/images/portfolio/project-5.png', 'Kwame Nkrumah University', 'https://example.com', 0, 5),
('Product Launch Campaign', 'product-launch-campaign', 'graphic-design', 'Multi-channel marketing design for a product launch including social media graphics, billboard designs, flyers, and animated web banners.', 'assets/images/portfolio/project-6.png', 'TechVibe Solutions', 'https://example.com', 0, 6),
('Enterprise IT Infrastructure Setup', 'enterprise-it-infrastructure', 'it-solutions', 'Complete IT infrastructure setup for a 200-employee company: workstation configuration, network architecture, Windows deployment, and ongoing support contract.', 'assets/images/portfolio/project-7.png', 'GoldStar Mining Ltd', 'https://example.com', 0, 7),
('Student Election Voting App', 'student-election-voting', 'voting-systems', 'Secure web-based voting application for university student elections with candidate profiles, real-time results dashboard, and voter verification system.', 'assets/images/portfolio/project-8.png', 'University of Ghana', 'https://example.com', 0, 8);
