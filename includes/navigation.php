<!-- Navigation bar dan script toggle -->

<script>
// ======= DARK MODE TOGGLE =======
(function() {
  const themeToggle = document.getElementById('themeToggle');
  const themeIcon = document.getElementById('themeIcon');
  const html = document.documentElement;
  function setTheme(theme) {
    html.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    if (themeIcon) {
      if (theme === 'dark') {
        themeIcon.className = 'fas fa-sun';
        themeIcon.title = 'Switch to Light Mode';
      } else {
        themeIcon.className = 'fas fa-moon';
        themeIcon.title = 'Switch to Dark Mode';
      }
    }
  }
  if (themeToggle) {
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);
    themeToggle.checked = (savedTheme === 'dark');
    themeToggle.addEventListener('change', function() {
      setTheme(themeToggle.checked ? 'dark' : 'light');
    });
  }
})();

// ======= LANGUAGE SWITCHER =======
(function() {
  // Simple translations mapping (EN/ID)
  const translations = {
    en: {
      'nav.home': 'Home',
      'nav.about': 'About',
      'nav.my_spaces': 'My Spaces',
      'nav.contact': 'Contact',
      'theme.toggle': 'Toggle Theme',
      'lang.english': 'English',
      'lang.indonesia': 'Indonesia',
      'lang.changed': 'Language changed to English',
    },
    id: {
      'nav.home': 'Beranda',
      'nav.about': 'Tentang',
      'nav.my_spaces': 'Ruang Saya',
      'nav.contact': 'Kontak',
      'theme.toggle': 'Ganti Tema',
      'lang.english': 'Inggris',
      'lang.indonesia': 'Indonesia',
      'lang.changed': 'Bahasa diubah ke Indonesia',
    }
  };
  function translate(key, lang) {
    return (translations[lang] && translations[lang][key]) || key;
  }
  function updateAllI18nText(lang) {
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key = el.getAttribute('data-i18n');
      if (key) el.textContent = translate(key, lang);
    });
  }
  // Simple notification
  function showAlert(type, message) {
    let container = document.getElementById('notificationContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'notificationContainer';
      container.style.position = 'fixed';
      container.style.top = '16px';
      container.style.right = '16px';
      container.style.zIndex = '9999';
      document.body.appendChild(container);
    }
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.style.background = '#fff';
    notification.style.border = '1px solid #ddd';
    notification.style.borderRadius = '8px';
    notification.style.padding = '12px 20px';
    notification.style.marginBottom = '10px';
    notification.style.boxShadow = '0 2px 8px rgba(0,0,0,0.08)';
    notification.style.color = '#333';
    notification.innerHTML = `<span>${message}</span> <button style="background:none;border:none;float:right;font-size:1.1em;cursor:pointer;" onclick="this.parentElement.remove()">&times;</button>`;
    container.appendChild(notification);
    setTimeout(() => {
      if (notification.parentNode) notification.remove();
    }, 3500);
  }
  const languageBtn = document.getElementById('languageBtn');
  const languageDropdown = document.getElementById('languageDropdown');
  const currentLang = document.getElementById('currentLang');
  const languageOptions = document.querySelectorAll('.language-option');
  if (languageBtn && languageDropdown && currentLang && languageOptions.length) {
    let lang = localStorage.getItem('lang') || 'id';
    updateAllI18nText(lang);
    currentLang.textContent = lang.toUpperCase();
    // Toggle dropdown
    languageBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      languageDropdown.classList.toggle('show');
    });
    document.addEventListener('click', function() {
      languageDropdown.classList.remove('show');
    });
    // Handle language selection
    languageOptions.forEach(option => {
      option.addEventListener('click', function() {
        lang = this.getAttribute('data-lang');
        localStorage.setItem('lang', lang);
        updateAllI18nText(lang);
        currentLang.textContent = lang.toUpperCase();
        languageDropdown.classList.remove('show');
        languageOptions.forEach(opt => opt.classList.remove('active'));
        this.classList.add('active');
        // Show notification ONLY on click
        showAlert('info', translate('lang.changed', lang));
      });
    });
  }
})();
</script> 