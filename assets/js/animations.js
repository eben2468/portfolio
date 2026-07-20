/**
 * Nadics Digital Solution — Animation Engine
 * Scroll reveals, counters, stagger effects, parallax
 */

document.addEventListener('DOMContentLoaded', () => {
    initScrollReveal();
    initCounters();
    initMagneticButtons();
});

/* ─── Scroll Reveal (IntersectionObserver) ───────────────────────── */
function initScrollReveal() {
    const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');

    if (!revealElements.length) return;

    const observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    revealElements.forEach(el => observer.observe(el));

    // Stagger children
    const staggerContainers = document.querySelectorAll('.stagger-children');
    staggerContainers.forEach(container => {
        Array.from(container.children).forEach((child, index) => {
            child.style.setProperty('--child-index', index);
        });
    });
}

/* ─── Animated Counters ──────────────────────────────────────────── */
function initCounters() {
    const counters = document.querySelectorAll('[data-count]');
    if (!counters.length) return;

    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => counterObserver.observe(counter));
}

function animateCounter(el) {
    const target = parseInt(el.getAttribute('data-count'));
    const suffix = el.getAttribute('data-suffix') || '';
    const prefix = el.getAttribute('data-prefix') || '';
    const duration = 2000;
    const startTime = performance.now();

    function easeOutExpo(t) {
        return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
    }

    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easedProgress = easeOutExpo(progress);
        const current = Math.round(easedProgress * target);

        el.textContent = prefix + current.toLocaleString() + suffix;

        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }

    requestAnimationFrame(update);
}

/* ─── Magnetic Button Effect ─────────────────────────────────────── */
function initMagneticButtons() {
    // Only on desktop
    if (window.matchMedia('(hover: none)').matches) return;

    const magneticElements = document.querySelectorAll('.btn-primary, .nav__cta');

    magneticElements.forEach(el => {
        el.addEventListener('mousemove', (e) => {
            const rect = el.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;

            el.style.transform = `translate(${x * 0.15}px, ${y * 0.15}px)`;
        });

        el.addEventListener('mouseleave', () => {
            el.style.transform = '';
        });
    });
}

/* ─── Parallax Helper (used by page scripts) ─────────────────────── */
function initParallax(selector, speed = 0.3) {
    const elements = document.querySelectorAll(selector);
    if (!elements.length) return;

    function updateParallax() {
        const scrollY = window.pageYOffset;
        elements.forEach(el => {
            const rect = el.getBoundingClientRect();
            const offsetY = (rect.top + scrollY) * speed;
            el.style.transform = `translateY(${scrollY * speed - offsetY}px)`;
        });
    }

    window.addEventListener('scroll', updateParallax, { passive: true });
}
