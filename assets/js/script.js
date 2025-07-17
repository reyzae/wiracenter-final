// Main JavaScript file for Wiracenter Portfolio
console.log('script.js loaded');

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initSidebar();
    initScrollEffects();
    // initContactForm(); // Nonaktifkan initContactForm()
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

    // Cookie Consent Banner
    (function() {
        function hideCookieBanner() {
            var banner = document.getElementById('cookie-consent-banner');
            if (banner) banner.style.display = 'none';
        }
        if (!localStorage.getItem('cookieConsent')) {
            var consent = document.createElement('div');
            consent.id = 'cookie-consent-banner';
            consent.style.position = 'fixed';
            consent.style.bottom = '0';
            consent.style.left = '0';
            consent.style.width = '100%';
            consent.style.background = '#222';
            consent.style.color = '#fff';
            consent.style.padding = '16px';
            consent.style.textAlign = 'center';
            consent.style.zIndex = '9999';
            consent.innerHTML = 'Website ini menggunakan cookie untuk meningkatkan pengalaman Anda. <button id="accept-cookie" style="margin-left:16px;padding:6px 18px;background:#1e90ff;color:#fff;border:none;border-radius:4px;cursor:pointer;">OK, Saya Mengerti</button>';
            document.body.appendChild(consent);
            var btn = document.getElementById('accept-cookie');
            if (btn) {
                btn.onclick = function() {
                    localStorage.setItem('cookieConsent', '1');
                    hideCookieBanner();
                };
            }
        } else {
            hideCookieBanner();
        }
    })();
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

// Semua event handler submit untuk #contactForm di-nonaktifkan
// function initContactForm() {
//     const contactForm = document.querySelector('#contactForm');
//     if (contactForm) {
//         contactForm.addEventListener('submit', function(e) {
//             e.preventDefault();
            
//             const formData = new FormData(this);
//             const submitBtn = this.querySelector('button[type="submit"]');
//             const originalText = submitBtn.textContent;
            
//             // Show loading state
//             submitBtn.disabled = true;
//             submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
            
//             // Submit form
//             fetch('api/contact.php', {
//                 method: 'POST',
//                 body: formData
//             })
//             .then(response => response.json())
//             .then(data => {
//                 if (data.success) {
//                     showAlert('success', 'Message sent successfully!');
//                     this.reset();
//                 } else {
//                     showAlert('danger', data.message || 'Failed to send message');
//                 }
//             })
//             .catch(error => {
//                 showAlert('danger', 'Network error. Please try again.');
//                 console.error('Error:', error);
//             })
//             .finally(() => {
//                 submitBtn.disabled = false;
//                 submitBtn.textContent = originalText;
//             });
//         });
//     }
// }

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

// Translation keys untuk halaman compliance
window.translations = window.translations || {
  id: {
    'privacy.title': 'Kebijakan Privasi',
    'privacy.intro': 'Privasi Anda penting bagi kami. Halaman ini menjelaskan bagaimana informasi pribadi Anda dikumpulkan, digunakan, dan dilindungi di website Wiracenter.',
    'privacy.collect_title': 'Informasi yang Kami Kumpulkan',
    'privacy.collect_1': 'Informasi yang Anda masukkan pada form kontak, pendaftaran, atau fitur lain.',
    'privacy.collect_2': 'Data penggunaan website secara anonim untuk analitik.',
    'privacy.use_title': 'Penggunaan Informasi',
    'privacy.use_1': 'Menanggapi pertanyaan atau permintaan Anda.',
    'privacy.use_2': 'Meningkatkan layanan dan fitur website.',
    'privacy.use_3': 'Tidak membagikan data pribadi Anda ke pihak ketiga tanpa izin.',
    'privacy.security_title': 'Keamanan Data',
    'privacy.security': 'Kami berkomitmen menjaga keamanan data Anda dengan teknologi dan prosedur yang sesuai.',
    'privacy.cookie_title': 'Cookie',
    'privacy.cookie': 'Website ini dapat menggunakan cookie untuk meningkatkan pengalaman pengguna. Anda dapat mengatur browser untuk menolak cookie.',
    'privacy.change_title': 'Perubahan Kebijakan',
    'privacy.change': 'Kebijakan ini dapat diperbarui sewaktu-waktu. Perubahan akan diumumkan di halaman ini.',
    'privacy.contact_title': 'Kontak',
    'privacy.contact': 'Jika ada pertanyaan tentang privasi, silakan hubungi kami melalui halaman <a href="contact.php">Kontak</a>.',
    'terms.title': 'Syarat & Ketentuan',
    'terms.intro': 'Dengan mengakses dan menggunakan website Wiracenter, Anda setuju untuk mematuhi syarat dan ketentuan berikut:',
    'terms.1': 'Konten di website ini hanya untuk tujuan informasi dan edukasi.',
    'terms.2': 'Dilarang menggunakan website untuk aktivitas ilegal atau merugikan pihak lain.',
    'terms.3': 'Kami berhak mengubah konten, fitur, atau syarat layanan kapan saja tanpa pemberitahuan.',
    'terms.4': 'Hak cipta konten milik pemilik website kecuali dinyatakan lain.',
    'terms.5': 'Penggunaan data dan layanan tunduk pada Kebijakan Privasi.',
    'terms.contact_title': 'Kontak',
    'terms.contact': 'Jika ada pertanyaan tentang syarat layanan, silakan hubungi kami melalui halaman <a href="contact.php">Kontak</a>.',
    // HAPUS seluruh key about.* dan aboutme.*
    'home.main_description': 'Wiracenter is a digital platform providing technology articles, project showcases, and a variety of online tools to support learning, experimentation, and collaboration in the digital world. Discover inspiration, knowledge, and practical resources to help your journey in technology and innovation.',
  },
  en: {
    'privacy.title': 'Privacy Policy',
    'privacy.intro': 'Your privacy is important to us. This page explains how your personal information is collected, used, and protected on the Wiracenter website.',
    'privacy.collect_title': 'Information We Collect',
    'privacy.collect_1': 'Information you enter in contact forms, registration, or other features.',
    'privacy.collect_2': 'Anonymous website usage data for analytics.',
    'privacy.use_title': 'Use of Information',
    'privacy.use_1': 'Responding to your questions or requests.',
    'privacy.use_2': 'Improving website services and features.',
    'privacy.use_3': 'We do not share your personal data with third parties without permission.',
    'privacy.security_title': 'Data Security',
    'privacy.security': 'We are committed to keeping your data secure with appropriate technology and procedures.',
    'privacy.cookie_title': 'Cookies',
    'privacy.cookie': 'This website may use cookies to enhance your experience. You can set your browser to refuse cookies.',
    'privacy.change_title': 'Policy Changes',
    'privacy.change': 'This policy may be updated at any time. Changes will be announced on this page.',
    'privacy.contact_title': 'Contact',
    'privacy.contact': 'If you have questions about privacy, please contact us via the <a href="contact.php">Contact</a> page.',
    'terms.title': 'Terms of Service',
    'terms.intro': 'By accessing and using the Wiracenter website, you agree to comply with the following terms and conditions:',
    'terms.1': 'Content on this website is for informational and educational purposes only.',
    'terms.2': 'It is prohibited to use the website for illegal activities or to harm others.',
    'terms.3': 'We reserve the right to change content, features, or terms of service at any time without notice.',
    'terms.4': 'Copyright of content belongs to the website owner unless otherwise stated.',
    'terms.5': 'Use of data and services is subject to the Privacy Policy.',
    'terms.contact_title': 'Contact',
    'terms.contact': 'If you have questions about the terms of service, please contact us via the <a href="contact.php">Contact</a> page.',
    // HAPUS seluruh key about.* dan aboutme.*
    'home.main_description': 'Wiracenter is a digital platform providing technology articles, project showcases, and a variety of online tools to support learning, experimentation, and collaboration in the digital world. Discover inspiration, knowledge, and practical resources to help your journey in technology and innovation.',
  }
};

// Extend setLanguage to support aboutTranslations
function setLanguage(lang) {
  document.querySelectorAll('[data-i18n]').forEach(function(el) {
    var key = el.getAttribute('data-i18n');
    if (window.translations[lang] && window.translations[lang][key]) {
      if (el.tagName.toLowerCase() === 'input' || el.tagName.toLowerCase() === 'textarea') {
        el.placeholder = window.translations[lang][key];
      } else {
        el.innerHTML = window.translations[lang][key];
      }
    } // jika tidak ditemukan, biarkan default text di HTML
  });
  // About page translation
  document.querySelectorAll('[data-i18n^="about."]').forEach(function(el) {
    const key = el.getAttribute('data-i18n');
    if (window.translations[lang] && window.translations[lang][key]) {
      if (el.tagName.toLowerCase() === 'input' || el.tagName.toLowerCase() === 'textarea') {
        el.placeholder = window.translations[lang][key];
      } else {
        el.innerHTML = window.translations[lang][key];
      }
    } // jika tidak ditemukan, biarkan default text di HTML
  });
  // About Me section translation
  document.querySelectorAll('[data-i18n^="aboutme."]').forEach(function(el) {
    const key = el.getAttribute('data-i18n');
    if (window.translations[lang] && window.translations[lang][key]) {
      if (el.tagName.toLowerCase() === 'input' || el.tagName.toLowerCase() === 'textarea') {
        el.placeholder = window.translations[lang][key];
      } else if (key === 'aboutme.why_list') {
        el.innerHTML = window.translations[lang][key];
      } else {
        el.textContent = window.translations[lang][key];
      }
    } // jika tidak ditemukan, biarkan default text di HTML
  });
  localStorage.setItem('lang', lang);
}

// Toggle switch listener (asumsi ada tombol/elemen dengan id 'lang-toggle' atau class 'lang-switch')
document.addEventListener('DOMContentLoaded', function() {
  var lang = localStorage.getItem('lang') || 'id';
  setLanguage(lang);
  var langToggles = document.querySelectorAll('.lang-switch, #lang-toggle');
  langToggles.forEach(function(btn) {
    btn.addEventListener('click', function() {
      var newLang = (localStorage.getItem('lang') === 'id') ? 'en' : 'id';
      setLanguage(newLang);
    });
  });
});