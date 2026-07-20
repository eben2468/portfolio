/**
 * Nadics Digital Solution — Admin Panel Interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // ─── Sidebar Toggle ─────────────────────────────────────────────
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    const adminMain = document.getElementById('adminMain');

    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            adminSidebar.classList.toggle('open');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && adminSidebar.classList.contains('open')) {
                if (!adminSidebar.contains(e.target) && e.target !== sidebarToggle) {
                    adminSidebar.classList.remove('open');
                }
            }
        });

        // Handle window resizing
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                adminSidebar.classList.remove('open');
            }
        });
    }

    // ─── Auto-dismiss Alerts ────────────────────────────────────────
    const alerts = document.querySelectorAll('.admin-alert');
    alerts.forEach(function(alert) {
        // Fade out alert after 5 seconds
        setTimeout(function() {
            alert.style.transition = 'opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1), margin-bottom 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            
            setTimeout(function() {
                alert.style.display = 'none';
            }, 600);
        }, 5000);
    });

    // ─── Drag & Drop Image Upload highlight ──────────────────────────
    const imageUploadArea = document.querySelector('.image-upload');
    const fileInput = document.getElementById('project_image');

    if (imageUploadArea && fileInput) {
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            imageUploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Highlight/unhighlight drop zone
        ['dragenter', 'dragover'].forEach(eventName => {
            imageUploadArea.addEventListener(eventName, () => {
                imageUploadArea.style.borderColor = 'var(--primary)';
                imageUploadArea.style.background = 'rgba(71,23,219,0.05)';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            imageUploadArea.addEventListener(eventName, () => {
                imageUploadArea.style.borderColor = 'var(--admin-border)';
                imageUploadArea.style.background = 'transparent';
            }, false);
        });

        // Handle dropped files
        imageUploadArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length) {
                fileInput.files = files;
                // Trigger change event to fire preview
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        }, false);
    }
});
