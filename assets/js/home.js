/**
 * Nadics Digital Solution — Home Page JavaScript
 * Hero typing effect, particle canvas, testimonial carousel
 */

document.addEventListener('DOMContentLoaded', () => {
    initTypingEffect();
    initParticleCanvas();
    initTestimonialCarousel();
    initHeroSlideshow();
});

/* ─── Hero Image Slideshow ───────────────────────────────────────── */
function initHeroSlideshow() {
    const stage = document.querySelector('.hero__visual-image--slideshow');
    if (!stage) return;

    const slides = stage.querySelectorAll('.hero__visual-slide');
    if (slides.length < 2) return;

    const interval = parseInt(stage.dataset.interval, 10) || 4000;
    let index = 0;

    setInterval(() => {
        slides[index].classList.remove('is-active');
        index = (index + 1) % slides.length;
        slides[index].classList.add('is-active');
    }, interval);
}

/* ─── Typing Effect ──────────────────────────────────────────────── */
function initTypingEffect() {
    const typedEl = document.getElementById('heroTyped');
    if (!typedEl) return;

    const words = (typedEl.dataset.words || 'Innovate, Design, Develop, Support, Transform')
        .split(',')
        .map(w => w.trim())
        .filter(w => w.length > 0);
    if (words.length === 0) words.push('Innovate');
    let wordIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let typingSpeed = 100;

    function type() {
        const currentWord = words[wordIndex];

        if (isDeleting) {
            typedEl.textContent = currentWord.substring(0, charIndex - 1);
            charIndex--;
            typingSpeed = 50;
        } else {
            typedEl.textContent = currentWord.substring(0, charIndex + 1);
            charIndex++;
            typingSpeed = 120;
        }

        if (!isDeleting && charIndex === currentWord.length) {
            isDeleting = true;
            typingSpeed = 1800; // Pause at end
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            wordIndex = (wordIndex + 1) % words.length;
            typingSpeed = 400; // Pause before new word
        }

        setTimeout(type, typingSpeed);
    }

    // Start after preloader
    setTimeout(type, 2200);
}

/* ─── Particle Canvas ────────────────────────────────────────────── */
function initParticleCanvas() {
    const canvas = document.getElementById('heroCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let animationId;
    let particles = [];
    const particleCount = 60;
    const connectionDistance = 120;

    function resize() {
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
    }

    function createParticles() {
        particles = [];
        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                size: Math.random() * 2 + 0.5,
                opacity: Math.random() * 0.4 + 0.1
            });
        }
    }

    function drawParticles() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Draw connections
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);

                if (dist < connectionDistance) {
                    const opacity = (1 - dist / connectionDistance) * 0.15;
                    ctx.strokeStyle = `rgba(167, 57, 179, ${opacity})`;
                    ctx.lineWidth = 0.5;
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.stroke();
                }
            }
        }

        // Draw particles
        particles.forEach(p => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(71, 23, 219, ${p.opacity})`;
            ctx.fill();

            // Update position
            p.x += p.vx;
            p.y += p.vy;

            // Bounce off edges
            if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
            if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
        });

        animationId = requestAnimationFrame(drawParticles);
    }

    resize();
    createParticles();
    drawParticles();

    window.addEventListener('resize', () => {
        resize();
        createParticles();
    });

    // Pause when not visible
    const observer = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting) {
            if (!animationId) drawParticles();
        } else {
            cancelAnimationFrame(animationId);
            animationId = null;
        }
    });
    observer.observe(canvas);
}

/* ─── Testimonial Carousel ───────────────────────────────────────── */
function initTestimonialCarousel() {
    const track = document.getElementById('testimonialTrack');
    const dots = document.querySelectorAll('.testimonials__dot');
    if (!track || !dots.length) return;

    let currentSlide = 0;
    const totalSlides = dots.length;
    let autoPlayTimer;

    function goToSlide(index) {
        currentSlide = index;
        track.style.transform = `translateX(-${currentSlide * 100}%)`;

        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === currentSlide);
        });
    }

    function nextSlide() {
        goToSlide((currentSlide + 1) % totalSlides);
    }

    function startAutoPlay() {
        autoPlayTimer = setInterval(nextSlide, 5000);
    }

    function stopAutoPlay() {
        clearInterval(autoPlayTimer);
    }

    // Dot click handlers
    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            stopAutoPlay();
            goToSlide(i);
            startAutoPlay();
        });
    });

    // Touch/swipe support
    let touchStartX = 0;
    let touchEndX = 0;

    track.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        stopAutoPlay();
    }, { passive: true });

    track.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        const diff = touchStartX - touchEndX;

        if (Math.abs(diff) > 50) {
            if (diff > 0 && currentSlide < totalSlides - 1) {
                goToSlide(currentSlide + 1);
            } else if (diff < 0 && currentSlide > 0) {
                goToSlide(currentSlide - 1);
            }
        }
        startAutoPlay();
    }, { passive: true });

    // Start
    goToSlide(0);
    startAutoPlay();
}
