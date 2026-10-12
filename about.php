<?php
// ========================================================
// About Developer Page
// ========================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About Developer';
$metaDescription = setting('owner_bio', 'Passionate full-stack developer and software architect.');
$currentNav = 'about';

require __DIR__ . '/includes/header.php';
?>

<main class="about-hero">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb navigation" style="margin-bottom: 36px;">
      <a href="<?= BASE_URL ?>/index.php">Home</a>
      <span>/</span>
      <span style="color: var(--text-primary); font-weight: 600;">About Developer</span>
    </nav>

    <div class="about-card">
      <!-- Profile Column -->
      <div class="about-profile">
        <div class="about-avatar">
          <?= lucide('user', '', 90) ?>
        </div>
        <h2><?= e(setting('owner_name', 'Alex Mercer')) ?></h2>
        <div class="role"><?= e(setting('owner_title', 'Senior Full-Stack Developer & Software Architect')) ?></div>

        <p style="font-size: 0.875rem; color: var(--text-secondary); display: flex; align-items: center; gap: 6px; margin-bottom: 14px;">
          <?= lucide('globe', '', 16) ?> <?= e(setting('owner_location', 'Bangalore, India')) ?>
        </p>

        <!-- Social Buttons -->
        <div class="social-links">
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
            <a class="social-btn" href="mailto:<?= e($email) ?>" aria-label="Direct Email">
              <?= lucide('mail', '', 18) ?>
            </a>
          <?php endif; ?>
        </div>

        <a class="btn btn-primary" href="mailto:<?= e(setting('owner_email', 'contact@example.com')) ?>" style="margin-top: 24px; width: 100%;">
          <?= lucide('mail', '', 16) ?> Send Inquiries
        </a>
      </div>

      <!-- Narrative & Skills Column -->
      <div class="about-content">
        <span class="section-eyebrow">Professional Background</span>
        <h3 style="margin-top: 6px; font-size: 2rem;">Engineering Scalable Web Solutions &amp; Digital Architectures</h3>

        <p>
          <?= nl2br(e(setting('owner_bio', 'Passionate full-stack developer with 6+ years of experience building reliable web applications, database architectures, and intuitive digital interfaces. Specializing in PHP, MySQL, JavaScript, and modern user experience engineering.'))) ?>
        </p>

        <p>
          Throughout my career, I have designed and delivered mission-critical management tools, high-concurrency financial ledger engines, institutional alumni portals, and conversion-optimized e-commerce solutions. My engineering philosophy prioritizes performance, rock-solid security, clean maintainable code, and refined human-centered interfaces.
        </p>

        <!-- Competency & Skill Domains -->
        <div class="skills-grid">
          <div class="skill-category">
            <h4><?= lucide('cpu', '', 16) ?> Backend Architecture</h4>
            <div class="skill-pills">
              <span class="skill-pill">PHP 8+</span>
              <span class="skill-pill">PDO / OOP</span>
              <span class="skill-pill">RESTful APIs</span>
              <span class="skill-pill">MVC Patterns</span>
              <span class="skill-pill">Session Security</span>
              <span class="skill-pill">FPDF / Reports</span>
            </div>
          </div>

          <div class="skill-category">
            <h4><?= lucide('circle-dollar-sign', '', 16) ?> Database &amp; Data Engineering</h4>
            <div class="skill-pills">
              <span class="skill-pill">MySQL 8+</span>
              <span class="skill-pill">Schema Normalization</span>
              <span class="skill-pill">Query Optimization</span>
              <span class="skill-pill">ACID Transactions</span>
              <span class="skill-pill">Audit Trail Loggers</span>
            </div>
          </div>

          <div class="skill-category">
            <h4><?= lucide('layout-grid', '', 16) ?> Frontend &amp; UI/UX</h4>
            <div class="skill-pills">
              <span class="skill-pill">HTML5 / Semantic</span>
              <span class="skill-pill">Vanilla CSS (Custom Systems)</span>
              <span class="skill-pill">Modern ES6+ JS</span>
              <span class="skill-pill">Bootstrap 5</span>
              <span class="skill-pill">Chart.js</span>
              <span class="skill-pill">Responsive Design</span>
            </div>
          </div>

          <div class="skill-category">
            <h4><?= lucide('settings', '', 16) ?> Security &amp; Operations</h4>
            <div class="skill-pills">
              <span class="skill-pill">XAMPP / Apache</span>
              <span class="skill-pill">CSRF / XSS Mitigation</span>
              <span class="skill-pill">Bcrypt Hashing</span>
              <span class="skill-pill">Strict Upload Sanitization</span>
              <span class="skill-pill">Rate Limiting</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
