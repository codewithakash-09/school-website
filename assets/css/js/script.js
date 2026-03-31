/**
 * SCHOOL WEBSITE - FRONTEND JAVASCRIPT
 * Form validation, mobile menu, AJAX functionality
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // MOBILE NAVIGATION TOGGLE
    // ============================================
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            // Toggle aria-expanded for accessibility
            const expanded = navMenu.classList.contains('active');
            navToggle.setAttribute('aria-expanded', expanded);
        });
    }
    
    // ============================================
    // FLASH MESSAGE CLOSE BUTTON
    // ============================================
    const flashClose = document.querySelector('.flash-close');
    if (flashClose) {
        flashClose.addEventListener('click', function() {
            const flashMessage = this.closest('.flash-message');
            if (flashMessage) {
                flashMessage.style.animation = 'slideUp 0.3s ease';
                setTimeout(() => {
                    flashMessage.remove();
                }, 300);
            }
        });
    }
    
    // ============================================
    // FORM VALIDATION
    // ============================================
    const forms = document.querySelectorAll('form[data-validate="true"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                const errorSpan = field.parentElement.querySelector('.error-message');
                if (errorSpan) errorSpan.remove();
                
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    showError(field, 'This field is required');
                } else {
                    field.classList.remove('error');
                }
                
                // Email validation
                if (field.type === 'email' && field.value.trim()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(field.value.trim())) {
                        isValid = false;
                        field.classList.add('error');
                        showError(field, 'Please enter a valid email address');
                    }
                }
                
                // Phone validation (Indian format)
                if (field.type === 'tel' && field.value.trim()) {
                    const phoneRegex = /^[6-9]\d{9}$/;
                    if (!phoneRegex.test(field.value.trim())) {
                        isValid = false;
                        field.classList.add('error');
                        showError(field, 'Please enter a valid 10-digit mobile number');
                    }
                }
                
                // Password strength
                if (field.type === 'password' && field.hasAttribute('data-strength')) {
                    const password = field.value;
                    const strength = checkPasswordStrength(password);
                    updatePasswordStrengthIndicator(field, strength);
                    
                    if (password && strength < 2) {
                        isValid = false;
                        showError(field, 'Password must be at least 8 characters with letters and numbers');
                    }
                }
                
                // Confirm password
                if (field.hasAttribute('data-match')) {
                    const targetId = field.getAttribute('data-match');
                    const targetField = document.getElementById(targetId);
                    if (targetField && field.value !== targetField.value) {
                        isValid = false;
                        field.classList.add('error');
                        showError(field, 'Passwords do not match');
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = form.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
    
    // Real-time validation on input
    const inputs = document.querySelectorAll('input[required], textarea[required], select[required]');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('error');
                const errorSpan = this.parentElement.querySelector('.error-message');
                if (errorSpan) errorSpan.remove();
            }
        });
    });
    
    // Helper function to show error messages
    function showError(field, message) {
        const errorSpan = document.createElement('span');
        errorSpan.className = 'error-message';
        errorSpan.textContent = message;
        field.parentElement.appendChild(errorSpan);
    }
    
    // Password strength checker
    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/\d/)) strength++;
        if (password.match(/[^a-zA-Z\d]/)) strength++;
        return strength;
    }
    
    function updatePasswordStrengthIndicator(field, strength) {
        let indicator = field.parentElement.querySelector('.password-strength');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'password-strength';
            field.parentElement.appendChild(indicator);
        }
        
        const strengthText = ['Very Weak', 'Weak', 'Good', 'Strong'];
        const strengthColor = ['#ef4444', '#f59e0b', '#eab308', '#10b981'];
        
        if (strength > 0) {
            indicator.textContent = strengthText[strength - 1];
            indicator.style.color = strengthColor[strength - 1];
        } else {
            indicator.textContent = '';
        }
    }
    
    // ============================================
    // AJAX CONTACT FORM (without page refresh)
    // ============================================
    const contactForm = document.getElementById('contact-form-ajax');
    if (contactForm) {
        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner"></span> Sending...';
            submitBtn.disabled = true;
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    showNotification(result.message, 'success');
                    this.reset();
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                showNotification('Something went wrong. Please try again.', 'error');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }
    
    // Notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `flash-message flash-${type}`;
        notification.style.position = 'fixed';
        notification.style.top = '80px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.maxWidth = '300px';
        notification.innerHTML = `
            <div class="container">
                ${escapeHtml(message)}
                <button class="flash-close">&times;</button>
            </div>
        `;
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
        
        // Close button functionality
        notification.querySelector('.flash-close').addEventListener('click', () => {
            notification.remove();
        });
    }
    
    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // ============================================
    // CONFIRM DIALOG FOR DESTRUCTIVE ACTIONS
    // ============================================
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // ============================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // ============================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
    
    // ============================================
    // BACK TO TOP BUTTON
    // ============================================
    const backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });
        
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
    
    // ============================================
    // LAZY LOADING IMAGES (Performance)
    // ============================================
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
        });
    }
});

// ============================================
// ADD SLIDE UP ANIMATION FOR FLASH MESSAGES
// ============================================
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(-100%);
            opacity: 0;
        }
    }
    
    .password-strength {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
    
    img.loaded {
        opacity: 1;
        transition: opacity 0.3s;
    }
    
    img[data-src] {
        opacity: 0;
    }
    
    #back-to-top {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: var(--primary-color, #2563eb);
        color: white;
        border: none;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        cursor: pointer;
        display: none;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    #back-to-top:hover {
        background: var(--primary-dark, #1d4ed8);
    }
`;
document.head.appendChild(style);