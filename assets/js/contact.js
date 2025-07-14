// Contact page JavaScript functionality

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    
    // Form validation
    function validateForm() {
        let isValid = true;
        const requiredFields = ['name', 'email', 'subject', 'message'];
        
        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            const feedback = field.nextElementSibling;
            
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                feedback.textContent = 'This field is required.';
                isValid = false;
            } else if (fieldName === 'email' && !isValidEmail(field.value)) {
                field.classList.add('is-invalid');
                feedback.textContent = 'Please enter a valid email address.';
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
                feedback.textContent = '';
            }
        });
        
        return isValid;
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Real-time validation
    const inputs = form.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            const feedback = this.nextElementSibling;
            
            if (!this.value.trim()) {
                this.classList.add('is-invalid');
                feedback.textContent = 'This field is required.';
            } else if (this.type === 'email' && !isValidEmail(this.value)) {
                this.classList.add('is-invalid');
                feedback.textContent = 'Please enter a valid email address.';
            } else {
                this.classList.remove('is-invalid');
                feedback.textContent = '';
            }
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                const feedback = this.nextElementSibling;
                if (this.value.trim() && (this.type !== 'email' || isValidEmail(this.value))) {
                    this.classList.remove('is-invalid');
                    feedback.textContent = '';
                }
            }
        });
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        
        // Re-enable after a delay (in case of errors)
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Message';
        }, 10000);
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add hover effects for contact info cards
    document.querySelectorAll('.contact-info-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Toast notification functions
    function showToast(message, type = 'success', duration = 5000) {
        const toastContainer = document.getElementById('toastContainer');
        const toastId = 'toast-' + Date.now();
        
        const toastHTML = `
            <div class="toast toast-${type}" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} toast-icon"></i>
                    <strong class="me-auto">${type === 'success' ? 'Success!' : 'Error!'}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: duration
        });
        
        toast.show();
        
        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }

    // Show success toast if there's a success message from PHP
    if (typeof successMessage !== 'undefined' && successMessage) {
        setTimeout(() => {
            showToast(successMessage, 'success', 6000);
        }, 500);
    }

    // Show error toast if there's an error message from PHP
    if (typeof errorMessage !== 'undefined' && errorMessage) {
        setTimeout(() => {
            showToast(errorMessage, 'error', 8000);
        }, 500);
    }

    // Fade popup success if exists
    const popup = document.getElementById('popupSuccess');
    if (popup) {
        setTimeout(function() {
            popup.classList.remove('show');
        }, 2500);
    }
}); 