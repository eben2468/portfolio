/**
 * Nadics Digital Solution — Contact Form JavaScript
 * Real-time validation, AJAX submission, toast notifications
 */

document.addEventListener('DOMContentLoaded', () => {
    initContactForm();
});

/* ─── Contact Form ───────────────────────────────────────────────── */
function initContactForm() {
    const form = document.getElementById('contactForm');
    if (!form) return;

    const fields = {
        name:     form.querySelector('#contactName'),
        email:    form.querySelector('#contactEmail'),
        phone:    form.querySelector('#contactPhone'),
        subject:  form.querySelector('#contactSubject'),
        budget:   form.querySelector('#contactBudget'),
        timeline: form.querySelector('#contactTimeline'),
        message:  form.querySelector('#contactMessage')
    };

    const submitBtn = form.querySelector('#contactSubmit');
    const successMsg = document.getElementById('formSuccess');
    const errorMsg = document.getElementById('formError');

    // ─── Validation Rules ───────────────────────────────
    const validators = {
        name: (value) => {
            if (!value.trim()) return 'Full name is required';
            if (value.trim().length < 2) return 'Name must be at least 2 characters';
            return null;
        },
        email: (value) => {
            if (!value.trim()) return 'Email address is required';
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) return 'Please enter a valid email address';
            return null;
        },
        phone: (value) => {
            if (value.trim() && !/^[\+]?[\d\s\-\(\)]{7,20}$/.test(value)) {
                return 'Please enter a valid phone number';
            }
            return null;
        },
        subject: (value) => {
            if (!value.trim()) return 'Please select a subject';
            return null;
        },
        budget: (value) => {
            if (!value.trim()) return 'Please select a budget range';
            return null;
        },
        timeline: (value) => {
            if (!value.trim()) return 'Please select a timeline';
            return null;
        },
        message: (value) => {
            if (!value.trim()) return 'Message is required';
            if (value.trim().length < 10) return 'Message must be at least 10 characters';
            return null;
        }
    };

    // ─── Show/Hide Field Error ──────────────────────────
    function showFieldError(fieldName, message) {
        const field = fields[fieldName];
        if (!field) return;

        const errorEl = field.closest('.form-group').querySelector('.form-group__error');
        field.classList.add('error');

        if (errorEl) {
            errorEl.querySelector('span').textContent = message;
            errorEl.classList.add('visible');
        }
    }

    function clearFieldError(fieldName) {
        const field = fields[fieldName];
        if (!field) return;

        const errorEl = field.closest('.form-group').querySelector('.form-group__error');
        field.classList.remove('error');

        if (errorEl) {
            errorEl.classList.remove('visible');
        }
    }

    // ─── Real-time Validation (on blur) ─────────────────
    Object.entries(fields).forEach(([name, field]) => {
        if (!field) return;

        field.addEventListener('blur', () => {
            const validator = validators[name];
            if (!validator) return;

            const error = validator(field.value);
            if (error) {
                showFieldError(name, error);
            } else {
                clearFieldError(name);
            }
        });

        // Clear error on input or change (for selects)
        field.addEventListener('input', () => {
            clearFieldError(name);
        });
        field.addEventListener('change', () => {
            clearFieldError(name);
        });
    });

    // ─── Form Submission ────────────────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Hide previous messages
        if (successMsg) successMsg.classList.remove('visible');
        if (errorMsg) errorMsg.classList.remove('visible');

        // Validate all fields
        let hasErrors = false;
        Object.entries(validators).forEach(([name, validator]) => {
            const field = fields[name];
            if (!field) return;

            const error = validator(field.value);
            if (error) {
                showFieldError(name, error);
                hasErrors = true;
            }
        });

        if (hasErrors) {
            // Focus first error field
            const firstError = form.querySelector('.form-group__input.error, .form-group__textarea.error, .form-group__select.error');
            if (firstError) firstError.focus();
            return;
        }

        // Set loading state
        submitBtn.disabled = true;
        submitBtn.classList.add('btn--loading');

        try {
            const formData = new FormData(form);

            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                // Show success
                if (successMsg) {
                    successMsg.querySelector('span').textContent = result.message || 'Your message has been sent successfully!';
                    successMsg.classList.add('visible');
                }
                form.reset();

                // Scroll to message
                successMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                // Show error
                if (errorMsg) {
                    errorMsg.querySelector('span').textContent = result.message || 'Something went wrong. Please try again.';
                    errorMsg.classList.add('visible');
                }

                // Show field-specific errors
                if (result.errors) {
                    Object.entries(result.errors).forEach(([field, message]) => {
                        showFieldError(field, message);
                    });
                }
            }
        } catch (err) {
            console.error('Form submission error:', err);
            if (errorMsg) {
                errorMsg.querySelector('span').textContent = 'Network error. Please check your connection and try again.';
                errorMsg.classList.add('visible');
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn--loading');
        }
    });
}
