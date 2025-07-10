<footer class="footer mt-auto py-4 bg-white border-top shadow-sm text-center" style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-12 col-md-6 mb-2 mb-md-0 text-md-start">
        <span class="fw-bold">&copy; <?php echo date('Y'); ?> <?php echo getSetting('site_name', 'Wiracenter'); ?>.</span> All rights reserved.
      </div>
      <div class="col-12 col-md-6 text-md-end">
        <span>Contact: <a href="mailto:<?php echo getSetting('site_email', 'info@wiracenter.com'); ?>" class="text-primary text-decoration-none"><?php echo getSetting('site_email', 'info@wiracenter.com'); ?></a></span>
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
