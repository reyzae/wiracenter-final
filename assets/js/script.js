// Main JavaScript file for Wiracenter Portfolio
console.log('script.js loaded');

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initSidebar();
    initScrollEffects();
    initContactForm();
    initFileUpload();
    initSearchFilter();
    initTooltips();
    initModals();
    initBackToTop();
    initReadingProgress();
    initSocialShare();
    // renderFloatingSocialSidebar(); // [DISABLED: error - function not defined, breaks navbar collapse on contact.php. Uncomment if function is restored.]

    var slugTranslations = document.getElementById('slug-translations');
    var langToggle = document.getElementById('toggle-language');
    if (slugTranslations && langToggle) {
        langToggle.addEventListener('change', function() {
            var lang = langToggle.checked ? 'en' : 'id';
            var newSlug = slugTranslations.getAttribute('data-' + lang);
            if (newSlug && newSlug.length > 0) {
                var path = window.location.pathname;
                if (path.includes('article.php')) {
                    window.location.href = 'article.php?slug=' + encodeURIComponent(newSlug);
                } else if (path.includes('project.php')) {
                    window.location.href = 'project.php?slug=' + encodeURIComponent(newSlug);
                } else if (path.includes('tool.php')) {
                    window.location.href = 'tool.php?slug=' + encodeURIComponent(newSlug);
                }
            }
        });
    }

    // --- [DISABLED] Manual Bootstrap navbar toggler fix (see troubleshooting 2024-07-13) ---
    // The following block is commented out to let Bootstrap handle navbar collapse/expand natively.
    // If you need to restore the manual fix, uncomment this block.
    /*
    var navbarToggler = document.querySelector('.navbar-toggler');
    var mainNavbar = document.getElementById('mainNavbar');
    if (navbarToggler && mainNavbar) {
        navbarToggler.addEventListener('click', function(e) {
            // Let Bootstrap handle the first toggle, but force toggle if stuck open
            setTimeout(function() {
                if (mainNavbar.classList.contains('show')) {
                    // If already open, clicking toggler should close it
                    navbarToggler.setAttribute('aria-expanded', 'false');
                    mainNavbar.classList.remove('show');
                } else {
                    // If closed, open it
                    navbarToggler.setAttribute('aria-expanded', 'true');
                    mainNavbar.classList.add('show');
                }
            }, 150); // Wait for Bootstrap's JS to run first
        });
    }
    */
    // --- [END DISABLED navbar toggler fix] ---
});





// Back to Top Button Functionality
function initBackToTop() {
    const backToTopBtn = document.getElementById('backToTop');
    
    if (backToTopBtn) {
        // Show/hide button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });
        
        // Smooth scroll to top when clicked
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// Reading Progress Bar Functionality
function initReadingProgress() {
    const progressBar = document.getElementById('readingProgress');
    
    if (progressBar) {
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercent = (scrollTop / docHeight) * 100;
            
            progressBar.style.width = scrollPercent + '%';
        });
    }
}

// Social Share Buttons Functionality
function initSocialShare() {
    // Add social share buttons to content pages
    const currentPage = window.location.pathname.split('/').pop();
    const urlParams = new URLSearchParams(window.location.search);
    const isAboutPage = currentPage === 'page.php' && urlParams.get('slug') === 'about';
    const isContentPage = (currentPage === 'article.php' || 
                         currentPage === 'project.php' || 
                         currentPage === 'tool.php' || 
                         (currentPage === 'page.php' && urlParams.has('slug') && !isAboutPage));
    
    if (isContentPage) {
        // The social share buttons are now rendered directly in PHP
        // This function is no longer needed for adding buttons.
    }
}

// Global function for sharing content
function shareContent(platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    const text = encodeURIComponent(document.querySelector('meta[name="description"]')?.content || 'Check out this content from Wiracenter');
    
    let shareUrl = '';
    
    switch (platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            break;
        case 'linkedin':
            shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${title}%20${url}`;
            break;
        case 'telegram':
            shareUrl = `https://t.me/share/url?url=${url}&text=${title}`;
            break;
        case 'email':
            shareUrl = `mailto:?subject=${title}&body=${text}%20${url}`;
            break;
        case 'copy':
            copyToClipboard(window.location.href);
            showAlert('success', 'Link copied to clipboard!');
            return;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

// Enhanced copy to clipboard function
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        // Use the modern clipboard API
        navigator.clipboard.writeText(text).then(() => {
            showAlert('success', 'Link copied to clipboard!');
        }).catch(() => {
            fallbackCopyToClipboard(text);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyToClipboard(text);
    }
}

function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showAlert('success', 'Link copied to clipboard!');
    } catch (err) {
        showAlert('error', 'Failed to copy link');
    }
    
    document.body.removeChild(textArea);
}

// Sidebar functionality
function initSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('#sidebarToggle');
    const mainContent = document.querySelector('.main-content');

    if (sidebar && sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 992 && sidebar.classList.contains('active')) {
                if (!sidebar.contains(e.target) && e.target !== sidebarToggle) {
                    sidebar.classList.remove('active');
                }
            }
        });
    }
}

// Scroll effects for animations
function initScrollEffects() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);

    // Observe all cards and sections
    const elementsToObserve = document.querySelectorAll('.card, section');
    elementsToObserve.forEach(el => observer.observe(el));

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
}

// Contact form handling
function initContactForm() {
    const contactForm = document.querySelector('#contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
            
            // Submit form
            fetch('api/contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Message sent successfully!');
                    this.reset();
                } else {
                    showAlert('danger', data.message || 'Failed to send message');
                }
            })
            .catch(error => {
                showAlert('danger', 'Network error. Please try again.');
                console.error('Error:', error);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
    }
}

// File upload handling
function initFileUpload() {
    const uploadArea = document.querySelector('.upload-area');
    const fileInput = document.querySelector('#fileInput');
    
    if (uploadArea && fileInput) {
        // Drag and drop events
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileUpload(files);
            }
        });
        
        // Click to upload
        uploadArea.addEventListener('click', function() {
            fileInput.click();
        });
        
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                handleFileUpload(this.files);
            }
        });
    }
}

// Handle file upload
function handleFileUpload(files) {
    const formData = new FormData();
    
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    
    const uploadProgress = document.querySelector('.upload-progress');
    if (uploadProgress) {
        uploadProgress.style.display = 'block';
    }
    
    fetch('admin/api/upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Files uploaded successfully!');
            if (typeof refreshFileList === 'function') {
                refreshFileList();
            }
        } else {
            showAlert('danger', data.message || 'Upload failed');
        }
    })
    .catch(error => {
        showAlert('danger', 'Upload failed. Please try again.');
        console.error('Error:', error);
    })
    .finally(() => {
        if (uploadProgress) {
            uploadProgress.style.display = 'none';
        }
    });
}

// Search and filter functionality
function initSearchFilter() {
    const searchInput = document.querySelector('#searchInput');
    const filterSelect = document.querySelector('#filterSelect');
    const itemsContainer = document.querySelector('.items-container');
    
    if (searchInput && itemsContainer) {
        searchInput.addEventListener('input', debounce(function() {
            filterItems();
        }, 300));
    }
    
    if (filterSelect) {
        filterSelect.addEventListener('change', filterItems);
    }
    
    function filterItems() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const filterValue = filterSelect ? filterSelect.value : 'all';
        const items = itemsContainer.querySelectorAll('.item');
        
        items.forEach(item => {
            const title = item.querySelector('.item-title')?.textContent.toLowerCase() || '';
            const category = item.dataset.category || '';
            const status = item.dataset.status || '';
            
            const matchesSearch = title.includes(searchTerm);
            const matchesFilter = filterValue === 'all' || 
                                 category === filterValue || 
                                 status === filterValue;
            
            item.style.display = matchesSearch && matchesFilter ? 'block' : 'none';
        });
    }
}

// Initialize tooltips
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize modals
function initModals() {
    // Delete confirmation modal
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            const itemName = this.dataset.item || 'item';
            
            showConfirmModal(
                'Delete Confirmation',
                `Are you sure you want to delete this ${itemName}?`,
                'danger',
                function() {
                    window.location.href = url;
                }
            );
        });
    });
}

// Utility functions
// OVERRIDE: Disable all popup notifications
function showAlert(type, message) {
    // Popup disabled by request
    return;
}

function showConfirmModal(title, message, type, onConfirm) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>${message}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-${type}" id="confirmBtn">Confirm</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    modal.querySelector('#confirmBtn').addEventListener('click', function() {
        bsModal.hide();
        if (onConfirm) onConfirm();
    });
    
    modal.addEventListener('hidden.bs.modal', function() {
        modal.remove();
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showAlert('success', 'Copied to clipboard!');
    }, function() {
        showAlert('danger', 'Failed to copy to clipboard');
    });
}

// Admin dashboard specific functions
function refreshStats() {
    fetch('api/stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatsCards(data.stats);
            }
        })
        .catch(error => console.error('Error refreshing stats:', error));
}

function updateStatsCards(stats) {
    Object.keys(stats).forEach(key => {
        const element = document.querySelector(`#stat-${key}`);
        if (element) {
            element.textContent = stats[key];
        }
    });
}

// Auto-refresh dashboard stats every 30 seconds
if (window.location.pathname.includes('/admin/')) {
    setInterval(refreshStats, 30000);
}

// Real-time notifications (if WebSocket is available)
function initWebSocket() {
    if (typeof WebSocket !== 'undefined') {
        const ws = new WebSocket('ws://localhost:8080');
        
        ws.onopen = function() {
            console.log('WebSocket connected');
        };
        
        ws.onmessage = function(event) {
            const data = JSON.parse(event.data);
            handleRealtimeUpdate(data);
        };
        
        ws.onclose = function() {
            console.log('WebSocket disconnected');
            // Attempt to reconnect after 5 seconds
            setTimeout(initWebSocket, 5000);
        };
    }
}

function handleRealtimeUpdate(data) {
    switch(data.type) {
        case 'new_message':
            showAlert('info', 'New contact message received');
            refreshStats();
            break;
        case 'new_comment':
            showAlert('info', 'New comment posted');
            break;
        default:
            console.log('Unknown update type:', data.type);
    }
}

// Initialize WebSocket for admin pages
if (window.location.pathname.includes('/admin/')) {
    initWebSocket();
}

// --- SLIDER PROGRESS BAR LOGIC ---
function updateSliderProgressBar(activeIndex, totalSlides) {
  var bar = document.querySelector('.slider-progress-bar');
  if (bar) {
    var percent = ((activeIndex + 1) / totalSlides) * 100;
    bar.style.width = percent + '%';
  }
}
// Patch global goToSlide if exists
if (typeof goToSlide === 'function') {
  var _goToSlide = goToSlide;
  window.goToSlide = function(idx) {
    _goToSlide(idx);
    var total = document.querySelectorAll('.slide').length;
    updateSliderProgressBar(idx, total);
  }
}
// On DOMContentLoaded, set initial progress bar
window.addEventListener('DOMContentLoaded', function() {
  var total = document.querySelectorAll('.slide').length;
  updateSliderProgressBar(0, total);
});
// --- END SLIDER PROGRESS BAR LOGIC ---