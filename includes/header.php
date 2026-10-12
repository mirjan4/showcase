<?php
if (!defined('APP_INIT')) {
    require_once __DIR__ . '/../config/app.php';
}
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? setting('site_name', 'Digital Project Showcase');
$metaDescription = $metaDescription ?? setting('hero_subtitle', 'A curated portfolio of websites, web applications, and digital solutions.');
$currentNav = $currentNav ?? '';
$isAdmin = !empty($_SESSION['admin_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | <?= e(setting('owner_name', 'Portfolio')) ?></title>
  <meta name="description" content="<?= e($metaDescription) ?>">
  <meta name="author" content="<?= e(setting('owner_name', 'Portfolio Owner')) ?>">
  
  <!-- Open Graph / Meta -->
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($metaDescription) ?>">
  <meta property="og:type" content="website">

  <!-- Favicon & Stylesheets -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
  <script>
    // Inline script to prevent theme flashbang
    (function() {
      const savedTheme = localStorage.getItem('theme') || 'dark';
      if (savedTheme === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
      }
    })();
  </script>
</head>
<body>

<header class="site-header">
  <div class="container">
    <a class="brand" href="<?= BASE_URL ?>/index.php">
      <div class="brand-icon">
        <?= lucide('layers', '', 20) ?>
      </div>
      <div class="brand-name">
        <?= e(explode(' ', setting('owner_name', 'Digital Portfolio'))[0]) ?><span>.folio</span>
      </div>
    </a>

    <nav>
      <ul class="nav-menu" id="navMenu">
        <li>
          <a class="nav-link <?= $currentNav === 'home' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">
            <?= lucide('home', '', 16) ?> Home
          </a>
        </li>
        <li>
          <a class="nav-link <?= $currentNav === 'projects' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php#projects">
            <?= lucide('layout-grid', '', 16) ?> Projects
          </a>
        </li>
        <li>
          <a class="nav-link <?= $currentNav === 'categories' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php#categories">
            <?= lucide('folder', '', 16) ?> Categories
          </a>
        </li>
        <li>
          <a class="nav-link <?= $currentNav === 'about' ? 'active' : '' ?>" href="<?= BASE_URL ?>/about.php">
            <?= lucide('user', '', 16) ?> About
          </a>
        </li>
      </ul>
    </nav>

    <div class="header-actions">
      <!-- Theme Switcher -->
      <button class="theme-toggle" id="themeToggle" type="button" aria-label="Toggle theme">
        <span class="theme-icon-dark"><?= lucide('moon', '', 18) ?></span>
      </button>

      <!-- Admin Dashboard / Sign In Link -->
      <?php if ($isAdmin): ?>
        <a class="admin-nav-btn" href="<?= BASE_URL ?>/admin/dashboard.php">
          <?= lucide('settings', '', 16) ?> Dashboard
        </a>
      <?php else: ?>
        <a class="admin-nav-btn" href="<?= BASE_URL ?>/admin/login.php">
          <?= lucide('user', '', 16) ?> Admin
        </a>
      <?php endif; ?>

      <!-- Mobile Hamburger Menu Button -->
      <button class="mobile-toggle" id="mobileToggle" type="button" aria-label="Toggle mobile menu">
        <?= lucide('menu', '', 24) ?>
      </button>
    </div>
  </div>
</header>
