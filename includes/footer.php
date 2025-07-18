<footer class="footer mt-auto py-4 bg-white border-top shadow-sm text-center" style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-12 col-md-6 mb-2 mb-md-0 text-md-start">
        <span data-i18n="footer.copyright">&copy; <?php echo date('Y'); ?> Wiracenter. All rights reserved.</span>
      </div>
      <div class="col-12 col-md-6 text-md-end">
        <?php
          // Ambil email dari site_email, fallback ke contact_email, lalu default
          $footer_email = getSetting('site_email');
          if (!$footer_email) {
            $footer_email = getSetting('contact_email', 'info@wiracenter.com');
          }
        ?>
        <span>Contact: <a href="mailto:<?php echo $footer_email; ?>" class="text-primary text-decoration-none"><?php echo $footer_email; ?></a></span>
        <!-- Sosial media opsional -->
        <?php if (getSetting('site_twitter')): ?>
          <a href="<?php echo getSetting('site_twitter'); ?>" class="ms-2 text-secondary" target="_blank"><i class="fab fa-twitter"></i></a>
        <?php endif; ?>
        <?php if (getSetting('site_instagram')): ?>
          <a href="<?php echo getSetting('site_instagram'); ?>" class="ms-2 text-secondary" target="_blank"><i class="fab fa-instagram"></i></a>
        <?php endif; ?>
        <?php if (getSetting('site_github')): ?>
          <a href="<?php echo getSetting('site_github'); ?>" class="ms-2 text-secondary" target="_blank"><i class="fab fa-github"></i></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</footer>
<!-- Google Analytics Placeholder -->
<!--
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-XXXXXXX-X"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'UA-XXXXXXX-X');
</script>
-->
<script src="assets/js/script.js"></script>