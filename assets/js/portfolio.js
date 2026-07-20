/**
 * Nadics Digital Solution — Portfolio JavaScript
 * Category filter, lightbox, lazy loading
 */

document.addEventListener('DOMContentLoaded', () => {
    initPortfolioFilter();
    initLightbox();
});

/* ─── Category Filter ────────────────────────────────────────────── */
function initPortfolioFilter() {
    const filterBtns = document.querySelectorAll('.portfolio-filter');
    const items = document.querySelectorAll('.portfolio-item');

    if (!filterBtns.length || !items.length) return;

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const category = btn.getAttribute('data-filter');

            // Update active state
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Filter items with animation
            items.forEach((item, index) => {
                const itemCategory = item.getAttribute('data-category');
                const shouldShow = category === 'all' || itemCategory === category;

                if (shouldShow) {
                    item.classList.remove('hidden');
                    item.style.animationDelay = `${index * 60}ms`;
                    item.style.animation = 'none';
                    // Trigger reflow
                    void item.offsetWidth;
                    item.style.animation = 'fadeInUp 0.5s ease forwards';
                } else {
                    item.style.animation = 'fadeOut 0.3s ease forwards';
                    setTimeout(() => {
                        item.classList.add('hidden');
                    }, 300);
                }
            });
        });
    });
}

/* ─── Lightbox ───────────────────────────────────────────────────── */
function initLightbox() {
    const lightbox = document.getElementById('portfolioLightbox');
    const lightboxClose = document.getElementById('lightboxClose');
    const triggers = document.querySelectorAll('[data-lightbox]');

    if (!lightbox || !triggers.length) return;

    const lbImage = lightbox.querySelector('.lightbox__image');
    const lbTitle = lightbox.querySelector('.lightbox__title');
    const lbCategory = lightbox.querySelector('.lightbox__category');
    const lbClient = lightbox.querySelector('.lightbox__client');
    const lbDesc = lightbox.querySelector('.lightbox__desc');

    function openLightbox(data) {
        if (lbImage) lbImage.src = data.image;
        if (lbTitle) lbTitle.textContent = data.title;
        if (lbCategory) lbCategory.textContent = data.category;
        if (lbClient) lbClient.textContent = 'Client: ' + data.client;
        if (lbDesc) lbDesc.textContent = data.description;

        lightbox.classList.add('open');
        document.body.classList.add('no-scroll');
    }

    function closeLightbox() {
        lightbox.classList.remove('open');
        document.body.classList.remove('no-scroll');
    }

    triggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const item = trigger.closest('.portfolio-item') || trigger;
            openLightbox({
                image: item.getAttribute('data-image') || '',
                title: item.getAttribute('data-title') || '',
                category: item.getAttribute('data-category-name') || '',
                client: item.getAttribute('data-client') || '',
                description: item.getAttribute('data-description') || ''
            });
        });
    });

    if (lightboxClose) {
        lightboxClose.addEventListener('click', closeLightbox);
    }

    // Close on background click
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox();
    });

    // Close on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightbox.classList.contains('open')) {
            closeLightbox();
        }
    });
}

/* ─── CSS Keyframes injected ─────────────────────────────────────── */
(function injectFilterKeyframes() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; transform: scale(1); }
            to   { opacity: 0; transform: scale(0.95); }
        }
    `;
    document.head.appendChild(style);
})();
