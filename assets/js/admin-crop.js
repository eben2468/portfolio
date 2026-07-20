/* ═══════════════════════════════════════════════════════════════════
   Nadics Admin — Client-side Image Cropper
   Intercepts any admin image file input, lets the user crop the picture
   in a modal, then swaps the cropped result back onto the input before
   the normal upload flow (preview + form submit) continues.

   No markup changes needed: it auto-hooks every
   <input type="file" accept="image/*"> on the page. Give an input a
   data-crop-aspect="16:10" attribute to lock the crop to a ratio.
   ═══════════════════════════════════════════════════════════════════ */
(function () {
    'use strict';

    // Types we can safely rasterise on a canvas. SVG (vector) and GIF
    // (animation) are passed straight through without cropping.
    var CROPPABLE = /^image\/(png|jpeg|jpg|webp)$/i;

    var overlay, stage, imgEl, box, hint, stageWrap, shade;
    var activeInput = null;
    var origFile = null;
    var natW = 0, natH = 0;      // natural image size
    var dispW = 0, dispH = 0;    // displayed image size
    var scale = 1;               // displayed / natural
    var aspect = 0;              // locked aspect ratio (w/h), 0 = free
    var crop = { x: 0, y: 0, w: 0, h: 0 };  // in displayed px
    var drag = null;             // active drag descriptor

    /* ── Build the modal once ── */
    function buildUI() {
        overlay = document.createElement('div');
        overlay.className = 'acrop';
        overlay.innerHTML =
            '<div class="acrop__dialog" role="dialog" aria-modal="true" aria-label="Crop image">' +
                '<div class="acrop__head">' +
                    '<h3 class="acrop__title">Crop Image</h3>' +
                    '<button type="button" class="acrop__x" data-acrop-cancel aria-label="Cancel">&times;</button>' +
                '</div>' +
                '<div class="acrop__body">' +
                    '<div class="acrop__stagewrap">' +
                        '<div class="acrop__stage">' +
                            '<img class="acrop__img" alt="">' +
                            '<div class="acrop__shade acrop__shade--t"></div>' +
                            '<div class="acrop__shade acrop__shade--b"></div>' +
                            '<div class="acrop__shade acrop__shade--l"></div>' +
                            '<div class="acrop__shade acrop__shade--r"></div>' +
                            '<div class="acrop__box">' +
                                '<span class="acrop__h acrop__h--nw" data-h="nw"></span>' +
                                '<span class="acrop__h acrop__h--ne" data-h="ne"></span>' +
                                '<span class="acrop__h acrop__h--sw" data-h="sw"></span>' +
                                '<span class="acrop__h acrop__h--se" data-h="se"></span>' +
                                '<span class="acrop__h acrop__h--n"  data-h="n"></span>' +
                                '<span class="acrop__h acrop__h--s"  data-h="s"></span>' +
                                '<span class="acrop__h acrop__h--w"  data-h="w"></span>' +
                                '<span class="acrop__h acrop__h--e"  data-h="e"></span>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<p class="acrop__hint">Drag the box to move it, or drag a handle to resize. Drag a corner to change the crop area.</p>' +
                '</div>' +
                '<div class="acrop__foot">' +
                    '<button type="button" class="admin-btn admin-btn--outline admin-btn--sm" data-acrop-reset>Reset</button>' +
                    '<div class="acrop__foot-right">' +
                        '<button type="button" class="admin-btn admin-btn--outline admin-btn--sm" data-acrop-cancel>Cancel</button>' +
                        '<button type="button" class="admin-btn admin-btn--primary admin-btn--sm" data-acrop-apply>Crop &amp; Use</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        document.body.appendChild(overlay);

        stageWrap = overlay.querySelector('.acrop__stagewrap');
        stage = overlay.querySelector('.acrop__stage');
        imgEl = overlay.querySelector('.acrop__img');
        box   = overlay.querySelector('.acrop__box');
        hint  = overlay.querySelector('.acrop__hint');
        shade = {
            t: overlay.querySelector('.acrop__shade--t'),
            b: overlay.querySelector('.acrop__shade--b'),
            l: overlay.querySelector('.acrop__shade--l'),
            r: overlay.querySelector('.acrop__shade--r')
        };

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay || e.target.hasAttribute('data-acrop-cancel')) cancel();
        });
        overlay.querySelector('[data-acrop-reset]').addEventListener('click', resetCrop);
        overlay.querySelector('[data-acrop-apply]').addEventListener('click', apply);

        box.addEventListener('pointerdown', onPointerDown);
        var handles = box.querySelectorAll('.acrop__h');
        for (var i = 0; i < handles.length; i++) handles[i].addEventListener('pointerdown', onPointerDown);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('is-open')) cancel();
        });
    }

    /* ── Open with a chosen file ── */
    function open(input, file) {
        if (!overlay) buildUI();
        activeInput = input;
        origFile = file;
        aspect = parseAspect(input.getAttribute('data-crop-aspect'));

        var url = URL.createObjectURL(file);
        var probe = new Image();
        probe.onload = function () {
            natW = probe.naturalWidth;
            natH = probe.naturalHeight;
            imgEl.src = url;
            overlay.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            // Wait a frame so the stage has layout, then size everything
            requestAnimationFrame(function () {
                layout();
                resetCrop();
            });
        };
        probe.onerror = function () {
            // If we can't read it, just let the original upload proceed
            passThrough();
        };
        probe.src = url;
    }

    function parseAspect(str) {
        if (!str) return 0;
        var m = String(str).split(/[:/]/);
        if (m.length === 2) {
            var a = parseFloat(m[0]), b = parseFloat(m[1]);
            if (a > 0 && b > 0) return a / b;
        }
        return 0;
    }

    /* ── Fit the image into the available stage area ── */
    function layout() {
        var maxW = stageWrap.clientWidth;
        var maxH = Math.min(window.innerHeight * 0.6, 520);
        var r = Math.min(maxW / natW, maxH / natH, 1);
        // Allow small images to scale up a bit so they're workable
        if (r < 0.15) r = 0.15;
        dispW = Math.round(natW * r);
        dispH = Math.round(natH * r);
        scale = dispW / natW;
        stage.style.width = dispW + 'px';
        stage.style.height = dispH + 'px';
        imgEl.style.width = dispW + 'px';
        imgEl.style.height = dispH + 'px';
    }

    function resetCrop() {
        // Largest centred crop that respects the locked aspect (if any)
        var w = dispW, h = dispH;
        if (aspect) {
            if (w / h > aspect) w = h * aspect; else h = w / aspect;
        }
        crop.w = Math.round(w);
        crop.h = Math.round(h);
        crop.x = Math.round((dispW - crop.w) / 2);
        crop.y = Math.round((dispH - crop.h) / 2);
        renderBox();
    }

    function renderBox() {
        box.style.left = crop.x + 'px';
        box.style.top = crop.y + 'px';
        box.style.width = crop.w + 'px';
        box.style.height = crop.h + 'px';
        // Darken everything outside the crop rectangle
        var rightX = crop.x + crop.w;
        var bottomY = crop.y + crop.h;
        shade.t.style.cssText = 'left:0;top:0;width:' + dispW + 'px;height:' + crop.y + 'px';
        shade.b.style.cssText = 'left:0;top:' + bottomY + 'px;width:' + dispW + 'px;height:' + (dispH - bottomY) + 'px';
        shade.l.style.cssText = 'left:0;top:' + crop.y + 'px;width:' + crop.x + 'px;height:' + crop.h + 'px';
        shade.r.style.cssText = 'left:' + rightX + 'px;top:' + crop.y + 'px;width:' + (dispW - rightX) + 'px;height:' + crop.h + 'px';
    }

    /* ── Dragging (move + resize) ── */
    function onPointerDown(e) {
        e.preventDefault();
        e.stopPropagation();
        var handle = e.target.getAttribute('data-h');
        drag = {
            mode: handle ? 'resize' : 'move',
            handle: handle,
            startX: e.clientX,
            startY: e.clientY,
            orig: { x: crop.x, y: crop.y, w: crop.w, h: crop.h }
        };
        window.addEventListener('pointermove', onPointerMove);
        window.addEventListener('pointerup', onPointerUp);
    }

    function onPointerMove(e) {
        if (!drag) return;
        var dx = e.clientX - drag.startX;
        var dy = e.clientY - drag.startY;
        var o = drag.orig;
        var MIN = 24;

        if (drag.mode === 'move') {
            crop.x = clamp(o.x + dx, 0, dispW - o.w);
            crop.y = clamp(o.y + dy, 0, dispH - o.h);
            renderBox();
            return;
        }

        var left = o.x, top = o.y, right = o.x + o.w, bottom = o.y + o.h;
        var h = drag.handle;
        if (h.indexOf('w') !== -1) left = clamp(o.x + dx, 0, right - MIN);
        if (h.indexOf('e') !== -1) right = clamp(o.x + o.w + dx, left + MIN, dispW);
        if (h.indexOf('n') !== -1) top = clamp(o.y + dy, 0, bottom - MIN);
        if (h.indexOf('s') !== -1) bottom = clamp(o.y + o.h + dy, top + MIN, dispH);

        var nw = right - left, nh = bottom - top;

        if (aspect) {
            // Keep ratio, driven by the dominant axis of this handle
            var horizontal = (h === 'e' || h === 'w');
            if (horizontal) nh = nw / aspect; else if (h === 'n' || h === 's') nw = nh * aspect;
            else { // corner: use width as master
                nh = nw / aspect;
            }
            // Re-anchor based on which edges are fixed
            if (h.indexOf('n') !== -1) top = bottom - nh; else bottom = top + nh;
            if (h.indexOf('w') !== -1) left = right - nw; else right = left + nw;
            // Clamp inside bounds; if overflow, shrink to fit
            if (left < 0) { left = 0; }
            if (top < 0) { top = 0; }
            if (right > dispW) { right = dispW; }
            if (bottom > dispH) { bottom = dispH; }
            nw = right - left; nh = bottom - top;
        }

        crop.x = Math.round(left);
        crop.y = Math.round(top);
        crop.w = Math.round(Math.max(MIN, nw));
        crop.h = Math.round(Math.max(MIN, nh));
        renderBox();
    }

    function onPointerUp() {
        drag = null;
        window.removeEventListener('pointermove', onPointerMove);
        window.removeEventListener('pointerup', onPointerUp);
    }

    function clamp(v, lo, hi) { return Math.max(lo, Math.min(hi, v)); }

    /* ── Produce the cropped file and hand it back to the input ── */
    function apply() {
        var sx = Math.round(crop.x / scale);
        var sy = Math.round(crop.y / scale);
        var sw = Math.round(crop.w / scale);
        var sh = Math.round(crop.h / scale);
        sw = Math.max(1, Math.min(sw, natW - sx));
        sh = Math.max(1, Math.min(sh, natH - sy));

        var canvas = document.createElement('canvas');
        canvas.width = sw;
        canvas.height = sh;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(imgEl, sx, sy, sw, sh, 0, 0, sw, sh);

        var type = CROPPABLE.test(origFile.type) ? origFile.type : 'image/jpeg';
        if (type === 'image/jpg') type = 'image/jpeg';
        var quality = (type === 'image/png') ? undefined : 0.92;

        canvas.toBlob(function (blob) {
            if (!blob) { passThrough(); return; }
            var name = origFile.name || 'image';
            var cropped = new File([blob], name, { type: blob.type, lastModified: Date.now() });
            setInputFile(activeInput, cropped);
            close();
        }, type, quality);
    }

    // Swap the file onto the input, then re-fire change so the page's
    // existing preview handler runs — flagged so we don't re-open.
    function setInputFile(input, file) {
        try {
            var dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
        } catch (err) {
            // DataTransfer unsupported: fall back to the original file
        }
        input.dataset.cropBypass = '1';
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Let the originally-selected file upload unchanged (no crop).
    function passThrough() {
        if (!activeInput) return;
        activeInput.dataset.cropBypass = '1';
        activeInput.dispatchEvent(new Event('change', { bubbles: true }));
        close();
    }

    function cancel() {
        if (activeInput) activeInput.value = '';   // discard selection
        close();
    }

    function close() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        if (imgEl.src && imgEl.src.indexOf('blob:') === 0) URL.revokeObjectURL(imgEl.src);
        activeInput = null;
        origFile = null;
        drag = null;
    }

    /* ── Intercept image selections before the page's own handler ── */
    document.addEventListener('change', function (e) {
        var input = e.target;
        if (!input || input.tagName !== 'INPUT' || input.type !== 'file') return;
        if (!/image\//i.test(input.accept || '')) return;

        // Programmatic re-dispatch after cropping / pass-through: let it flow
        if (input.dataset.cropBypass === '1') { input.dataset.cropBypass = ''; return; }

        if (!input.files || !input.files[0]) return;
        var file = input.files[0];
        if (!/^image\//.test(file.type)) return;
        // Skip formats we shouldn't rasterise
        if (file.type === 'image/svg+xml' || file.type === 'image/gif') return;
        if (input.hasAttribute('data-no-crop')) return;

        // Stop the inline onchange preview until we've cropped
        e.stopImmediatePropagation();
        e.preventDefault();
        open(input, file);
    }, true); // capture phase → runs before inline handlers
})();
