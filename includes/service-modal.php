<?php
/**
 * Nadics Digital Solution — Service Details Modal
 * Shared popup used by the service cards on the Home and Services pages.
 * Card markup exposes: a `[data-service-card]` wrapper, a `[data-service-more]`
 * trigger, and a `<script class="js-service-data">` JSON payload.
 */
?>
<div class="service-modal" id="serviceModal" aria-hidden="true">
    <div class="service-modal__overlay" data-service-close></div>
    <div class="service-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="serviceModalTitle">
        <button type="button" class="service-modal__close" data-service-close aria-label="Close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div class="service-modal__media" data-service-modal-media>
            <img class="service-modal__img" data-service-modal-img src="" alt="">
            <span class="service-modal__badge" data-service-modal-badge>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></svg>
            </span>
        </div>
        <div class="service-modal__body">
            <h3 class="service-modal__title" id="serviceModalTitle" data-service-modal-title></h3>
            <p class="service-modal__subtitle" data-service-modal-subtitle></p>
            <p class="service-modal__desc" data-service-modal-desc></p>
            <div class="service-modal__features" data-service-modal-features></div>
            <a class="service-modal__link" data-service-modal-link target="_blank" rel="noopener"></a>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('serviceModal');
    if (!modal) return;

    var dialog     = modal.querySelector('.service-modal__dialog');
    var media      = modal.querySelector('[data-service-modal-media]');
    var img        = modal.querySelector('[data-service-modal-img]');
    var badgeSvg   = modal.querySelector('[data-service-modal-badge] svg');
    var titleEl    = modal.querySelector('[data-service-modal-title]');
    var subtitleEl = modal.querySelector('[data-service-modal-subtitle]');
    var descEl     = modal.querySelector('[data-service-modal-desc]');
    var featuresEl = modal.querySelector('[data-service-modal-features]');
    var linkEl     = modal.querySelector('[data-service-modal-link]');
    var lastFocus  = null;

    var CHECK_SVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';

    function openModal(data) {
        // Media / image
        if (data.image) {
            img.src = data.image;
            img.alt = data.title || '';
            media.classList.remove('service-modal__media--noimg');
        } else {
            img.removeAttribute('src');
            media.classList.add('service-modal__media--noimg');
        }
        badgeSvg.innerHTML = data.icon || '';

        // Text
        titleEl.textContent = data.title || '';
        if (data.subtitle) {
            subtitleEl.textContent = data.subtitle;
            subtitleEl.style.display = '';
        } else {
            subtitleEl.style.display = 'none';
        }
        descEl.textContent = data.description || '';

        // Features
        featuresEl.innerHTML = '';
        if (data.features && data.features.length) {
            data.features.forEach(function (f) {
                var span = document.createElement('span');
                span.className = 'service-modal__feature';
                span.innerHTML = CHECK_SVG;
                span.appendChild(document.createTextNode(' ' + f));
                featuresEl.appendChild(span);
            });
            featuresEl.style.display = '';
        } else {
            featuresEl.style.display = 'none';
        }

        // CTA link
        if (data.link) {
            linkEl.href = data.link;
            linkEl.textContent = data.linkLabel || 'Learn More';
            linkEl.style.display = '';
        } else {
            linkEl.style.display = 'none';
        }

        lastFocus = document.activeElement;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        dialog.scrollTop = 0;
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
    }

    // Wire up every service card on the page
    document.querySelectorAll('[data-service-card]').forEach(function (card) {
        var jsonEl  = card.querySelector('.js-service-data');
        var trigger = card.querySelector('[data-service-more]');
        if (!jsonEl) return;
        var data;
        try { data = JSON.parse(jsonEl.textContent); } catch (e) { return; }
        if (trigger) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                openModal(data);
            });
        }
    });

    modal.querySelectorAll('[data-service-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });
})();
</script>
