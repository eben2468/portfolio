<?php
/**
 * Nadics Digital Solution — Shared Header
 * Includes <head>, preloader, and navigation
 * 
 * Variables expected from including page:
 *   $page_title   — Page title (string)
 *   $page_desc    — Meta description (string)
 *   $page_css     — Page-specific CSS file (string, optional)
 *   $current_page — Current page filename (string)
 */

require_once __DIR__ . '/functions.php';

$page_title = $page_title ?? SITE_NAME;
$page_desc  = $page_desc  ?? 'Nadics Digital Solution provides expert IT consulting, web development, graphic design, and digital services to help businesses thrive in the digital age.';
$page_css   = $page_css   ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO -->
    <title><?= sanitize($page_title) ?></title>
    <meta name="description" content="<?= sanitize($page_desc) ?>">
    <meta name="author" content="Nadics Digital Solution">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= SITE_URL ?>/<?= basename($_SERVER['PHP_SELF']) ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= sanitize($page_title) ?>">
    <meta property="og:description" content="<?= sanitize($page_desc) ?>">
    <meta property="og:url" content="<?= SITE_URL ?>">
    <meta property="og:site_name" content="<?= SITE_NAME ?>">

    <!-- Fonts: Clash Display + Satoshi from Fontshare -->
    <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=clash-display@200,300,400,500,600,700&f[]=satoshi@300,400,500,700&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <?php if ($page_css): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/<?= $page_css ?>">
    <?php endif; ?>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/nadics-favicon.png?v=2">
    <link rel="apple-touch-icon" href="<?= SITE_URL ?>/assets/nadics-favicon.png?v=2">
</head>
<body>

    <!-- ═══ Preloader ═══ -->
    <div class="preloader" id="preloader">
        <div class="preloader__inner">
            <div class="preloader__logo">
                <span class="preloader__letter" style="--i:0">N</span>
                <span class="preloader__letter" style="--i:1">a</span>
                <span class="preloader__letter" style="--i:2">d</span>
                <span class="preloader__letter" style="--i:3">i</span>
                <span class="preloader__letter" style="--i:4">c</span>
                <span class="preloader__letter" style="--i:5">s</span>
            </div>
            <div class="preloader__bar">
                <div class="preloader__progress"></div>
            </div>
        </div>
    </div>

    <!-- ═══ Navigation ═══ -->
    <header class="nav-header" id="navHeader">
        <nav class="nav container" id="mainNav">
            <!-- Logo -->
            <a href="<?= SITE_URL ?>/" class="nav__logo" aria-label="Nadics Digital Solution Home">
                <img class="nav__logo-icon" src="<?= SITE_URL ?>/assets/nadics-logo.png" alt="Nadics Digital Solution logo" width="40" height="40">
                <div class="nav__logo-text">
                    <span class="nav__logo-name">Nadics</span>
                    <span class="nav__logo-sub">Digital Solution</span>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <ul class="nav__links" id="navLinks">
                <li><a href="<?= SITE_URL ?>/" class="nav__link<?= activeClass('index.php') ?>">Home</a></li>
                <li><a href="<?= SITE_URL ?>/services.php" class="nav__link<?= activeClass('services.php') ?>">Services</a></li>
                <li><a href="<?= SITE_URL ?>/portfolio.php" class="nav__link<?= activeClass('portfolio.php') ?>">Portfolio</a></li>
                <li><a href="<?= SITE_URL ?>/about.php" class="nav__link<?= activeClass('about.php') ?>">About</a></li>
                <li><a href="<?= SITE_URL ?>/contact.php" class="nav__link<?= activeClass('contact.php') ?>">Contact</a></li>
            </ul>

            <!-- CTA Button -->
            <a href="<?= SITE_URL ?>/contact.php?type=quote" class="btn btn-primary nav__cta">
                Get a Quote
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>

            <!-- Mobile Hamburger -->
            <button class="nav__hamburger" id="navHamburger" aria-label="Toggle navigation menu" aria-expanded="false">
                <span class="nav__hamburger-line"></span>
                <span class="nav__hamburger-line"></span>
                <span class="nav__hamburger-line"></span>
            </button>
        </nav>
    </header>

    <!-- Mobile Menu Overlay (kept outside <header> so the scrolled nav's
         backdrop-filter never becomes its containing block) -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu__inner">
            <ul class="mobile-menu__links">
                <li style="--i:0"><a href="<?= SITE_URL ?>/" class="mobile-menu__link<?= activeClass('index.php') ?>">Home</a></li>
                <li style="--i:1"><a href="<?= SITE_URL ?>/services.php" class="mobile-menu__link<?= activeClass('services.php') ?>">Services</a></li>
                <li style="--i:2"><a href="<?= SITE_URL ?>/portfolio.php" class="mobile-menu__link<?= activeClass('portfolio.php') ?>">Portfolio</a></li>
                <li style="--i:3"><a href="<?= SITE_URL ?>/about.php" class="mobile-menu__link<?= activeClass('about.php') ?>">About</a></li>
                <li style="--i:4"><a href="<?= SITE_URL ?>/contact.php" class="mobile-menu__link<?= activeClass('contact.php') ?>">Contact</a></li>
            </ul>
            <a href="<?= SITE_URL ?>/contact.php?type=quote" class="btn btn-primary mobile-menu__cta">Get a Quote</a>
            <div class="mobile-menu__info">
                <p><?= SITE_EMAIL ?></p>
                <p><?= SITE_PHONE ?></p>
            </div>
        </div>
    </div>

    <!-- ═══ Main Content ═══ -->
    <main id="mainContent">
