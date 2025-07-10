<?php
// Navbar Public Modern & Responsif
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-2 sticky-top" style="font-family: 'Fira Sans', Arial, Helvetica, sans-serif;">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php" style="font-size:1.5rem; letter-spacing:1px; color:#1a1a2e;">
      <?php echo getSetting('site_name', 'Wiracenter'); ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? ' active' : ''; ?>" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? ' active' : ''; ?>" href="about.php">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?php echo (basename($_SERVER['PHP_SELF']) == 'my-spaces.php') ? ' active' : ''; ?>" href="my-spaces.php">My Spaces</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? ' active' : ''; ?>" href="contact.php">Contact</a>
        </li>
      </ul>
    </div>
  </div>
</nav> 