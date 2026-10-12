<?php
if (!defined('APP_INIT')) {
    require_once __DIR__ . '/../config/app.php';
}
require_once __DIR__ . '/functions.php';

// Quick categories for footer
$footerCategories = [];
if (!empty($pdo)) {
    try {
        $footerCategories = $pdo->query("SELECT name, slug FROM categories ORDER BY sort_order ASC, name ASC LIMIT 5")->fetchAll();
    } catch (Exception $e) {}
}
?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-top">
      <div class="footer-brand">
        <a class="brand" href="<?= BASE_URL ?>/index.php">
          <div class="brand-icon">
            <?= lucide('layers', '', 20) ?>
          </div>
          <div class="brand-name">
            <?= e(explode(' ', setting('owner_name', 'Digital Portfolio'))[0]) ?><span>.folio</span>
          </div>
        </a>
        <p><?= e(setting('site_tagline', 'Senior Full-Stack Web Developer & Software Architect')) ?></p>
        <div class="social-links" style="margin-top: 18px;">
          <?php if ($github = setting('github_url')): ?>
            <a class="social-btn" href="<?= e($github) ?>" target="_blank" rel="noopener noreferrer" aria-label="GitHub Profile">
              <?= lucide('github', '', 18) ?>
            </a>
          <?php endif; ?>
          <?php if ($linkedin = setting('linkedin_url')): ?>
            <a class="social-btn" href="<?= e($linkedin) ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn Profile">
              <?= lucide('linkedin', '', 18) ?>
            </a>
          <?php endif; ?>
          <?php if ($twitter = setting('twitter_url')): ?>
            <a class="social-btn" href="<?= e($twitter) ?>" target="_blank" rel="noopener noreferrer" aria-label="Twitter Profile">
              <?= lucide('twitter', '', 18) ?>
            </a>
          <?php endif; ?>
          <?php if ($email = setting('owner_email')): ?>
            <a class="social-btn" href="mailto:<?= e($email) ?>" aria-label="Send Email">
              <?= lucide('mail', '', 18) ?>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="footer-column">
        <h4>Navigation</h4>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
          <li><a href="<?= BASE_URL ?>/index.php#projects">All Projects</a></li>
          <li><a href="<?= BASE_URL ?>/index.php#categories">Categories</a></li>
          <li><a href="<?= BASE_URL ?>/about.php">About Developer</a></li>
          <li><a href="<?= BASE_URL ?>/admin/login.php">Admin Login</a></li>
        </ul>
      </div>

      <div class="footer-column">
        <h4>Categories</h4>
        <ul class="footer-links">
          <?php foreach ($footerCategories as $fcat): ?>
            <li><a href="<?= BASE_URL ?>/category.php?slug=<?= e($fcat['slug']) ?>"><?= e($fcat['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="footer-column">
        <h4>Get In Touch</h4>
        <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 12px;">
          Interested in collaborating or discussing technical architectures?
        </p>
        <a class="btn btn-secondary" href="mailto:<?= e(setting('owner_email', 'contact@example.com')) ?>" style="padding: 9px 16px; font-size: 0.813rem;">
          <?= lucide('mail', '', 14) ?> Contact Me
        </a>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> <?= e(setting('owner_name', 'Alex Mercer')) ?>. <?= e(setting('footer_text', 'All rights reserved.')) ?></p>
      <p>Powered by PHP 8 &bull; MySQL &bull; Vanilla CSS</p>
    </div>
  </div>
</footer>

<!-- Lightbox Modal Container -->
<div class="lightbox-modal" id="lightboxModal" role="dialog" aria-modal="true" aria-label="Image Preview">
  <div class="lightbox-content">
    <button class="lightbox-close" id="lightboxClose" type="button" aria-label="Close Preview">
      <?= lucide('x', '', 28) ?>
    </button>
    <img id="lightboxImg" src="" alt="Full Preview">
    <div class="lightbox-caption" id="lightboxCaption"></div>
  </div>
</div>

<script src="<?= ASSETS_URL ?>/js/app.js"></script>
</body>
</html>
