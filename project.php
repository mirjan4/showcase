<?php
// ========================================================
// Project Details Showcase Page
// ========================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Lookup project by slug or ID
if ($slug !== '') {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM projects p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.slug = ?");
    $stmt->execute([$slug]);
    $project = $stmt->fetch();
} elseif ($id) {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM projects p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch();
} else {
    $project = null;
}

if (!$project) {
    http_response_code(404);
    $pageTitle = 'Project Not Found';
    require __DIR__ . '/includes/header.php';
    ?>
    <main class="container" style="padding: 100px 24px; text-align: center;">
      <div class="empty-state" style="max-width: 540px; margin: 0 auto;">
        <div class="empty-state-icon"><?= lucide('info', '', 32) ?></div>
        <h2>Project Not Found</h2>
        <p>The project you requested could not be located or may have been archived.</p>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/index.php">← Back to Project Library</a>
      </div>
    </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Increment view count
try {
    $updateStmt = $pdo->prepare("UPDATE projects SET view_count = view_count + 1 WHERE id = ?");
    $updateStmt->execute([$project['id']]);
} catch (Exception $e) {}

// Fetch project gallery images
$galleryStmt = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
$galleryStmt->execute([$project['id']]);
$galleryImages = $galleryStmt->fetchAll();

// Fetch related projects (same category or recent, excluding current)
$relatedStmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
    FROM projects p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.id <> ? AND p.status <> 'Archived'
    ORDER BY (p.category_id = ?) DESC, p.last_updated DESC
    LIMIT 3");
$relatedStmt->execute([$project['id'], $project['category_id'] ?? 0]);
$relatedProjects = $relatedStmt->fetchAll();

$pageTitle = $project['name'];
$metaDescription = $project['short_description'];
$currentNav = 'projects';
$coverImgUrl = project_image($project['thumbnail']);

// Parse features
$features = [];
if (!empty($project['features'])) {
    $lines = explode("\n", str_replace("\r", "", $project['features']));
    foreach ($lines as $line) {
        $trimmed = trim($line, " \t\n\r\0\x0B-•*");
        if ($trimmed !== '') {
            $features[] = $trimmed;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<main class="project-details-page">
  <div class="container">
    <!-- Breadcrumb Navigation -->
    <nav class="breadcrumbs" aria-label="Breadcrumb navigation">
      <a href="<?= BASE_URL ?>/index.php">Home</a>
      <span>/</span>
      <?php if (!empty($project['category_name'])): ?>
        <a href="<?= BASE_URL ?>/category.php?slug=<?= urlencode($project['category_slug']) ?>">
          <?= e($project['category_name']) ?>
        </a>
        <span>/</span>
      <?php endif; ?>
      <span style="color: var(--text-primary); font-weight: 600;"><?= e($project['name']) ?></span>
    </nav>

    <!-- Large Cover Image Banner -->
    <div class="project-hero-cover">
      <img src="<?= e($coverImgUrl) ?>" alt="<?= e($project['name']) ?> cover banner">
      <a class="cover-zoom-btn" href="<?= e($coverImgUrl) ?>" data-lightbox data-lightbox-caption="<?= e($project['name']) ?> Cover Image">
        <?= lucide('eye', '', 16) ?> Zoom Image
      </a>
    </div>

    <!-- Main Two-Column Layout -->
    <div class="details-layout">
      <!-- Main Content Column -->
      <div class="details-main">
        <div class="details-header-meta">
          <a class="card-category-link" href="<?= BASE_URL ?>/category.php?slug=<?= urlencode($project['category_slug'] ?? '') ?>">
            <?= e($project['category_name'] ?? 'Project') ?>
          </a>
          <?= status_badge($project['status']) ?>
        </div>

        <h1 class="project-page-title"><?= e($project['name']) ?></h1>
        <p class="project-lead"><?= e($project['short_description']) ?></p>

        <!-- Project Overview & Narrative -->
        <section class="section-block">
          <h2 class="section-block-title">
            <?= lucide('layout-grid', '', 20) ?> System Architecture &amp; Overview
          </h2>
          <div class="details-body">
            <?php
              // Split into clean paragraphs
              $paras = explode("\n\n", str_replace("\r", "", $project['full_description']));
              foreach ($paras as $para) {
                  $cleanPara = trim($para);
                  if ($cleanPara !== '') {
                      echo '<p>' . nl2br(e($cleanPara)) . '</p>';
                  }
              }
            ?>
          </div>
        </section>

        <!-- Key Features Checklist -->
        <?php if (!empty($features)): ?>
          <section class="section-block">
            <h2 class="section-block-title">
              <?= lucide('check-circle', '', 20) ?> Key Features &amp; Capabilities
            </h2>
            <ul class="features-list">
              <?php foreach ($features as $feature): ?>
                <li>
                  <span class="feature-check-icon"><?= lucide('check', '', 18) ?></span>
                  <span><?= e($feature) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </section>
        <?php endif; ?>

        <!-- Technology Stack Tags -->
        <?php if (!empty($project['technologies'])): ?>
          <section class="section-block">
            <h2 class="section-block-title">
              <?= lucide('code', '', 20) ?> Technologies &amp; Frameworks
            </h2>
            <div class="tech-tags">
              <?php foreach (array_filter(array_map('trim', explode(',', $project['technologies']))) as $tech): ?>
                <span class="tech-tag" style="padding: 8px 16px; font-size: 0.813rem;">
                  <?= e($tech) ?>
                </span>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

        <!-- Screenshot Gallery -->
        <?php if (!empty($galleryImages)): ?>
          <section class="section-block">
            <h2 class="section-block-title">
              <?= lucide('image', '', 20) ?> Interface &amp; Workflow Gallery
            </h2>
            <div class="gallery-grid">
              <?php foreach ($galleryImages as $img): ?>
                <?php $gSrc = gallery_image($img['image_path']); ?>
                <div class="gallery-item" data-lightbox href="<?= e($gSrc) ?>" data-lightbox-caption="<?= e($img['caption'] ?? $project['name']) ?>">
                  <img loading="lazy" src="<?= e($gSrc) ?>" alt="<?= e($img['caption'] ?? 'Gallery preview') ?>">
                  <?php if (!empty($img['caption'])): ?>
                    <div class="gallery-caption"><?= e($img['caption']) ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>
      </div>

      <!-- Sidebar Information Column -->
      <aside class="details-sidebar">
        <div class="sidebar-card">
          <h3>Project Specification</h3>

          <div class="sidebar-meta-list">
            <div class="meta-group">
              <span class="meta-label">Category</span>
              <span class="meta-value"><?= e($project['category_name'] ?? 'General') ?></span>
            </div>

            <div class="meta-group">
              <span class="meta-label">Deployment Status</span>
              <span class="meta-value"><?= status_badge($project['status']) ?></span>
            </div>

            <div class="meta-group">
              <span class="meta-label">Initial Launch</span>
              <span class="meta-value"><?= e(format_project_date($project['created_date'], true)) ?></span>
            </div>

            <div class="meta-group">
              <span class="meta-label">Last Maintenance / Update</span>
              <span class="meta-value"><?= e(format_project_date($project['last_updated'], true)) ?></span>
            </div>

            <div class="meta-group">
              <span class="meta-label">Project Views</span>
              <span class="meta-value"><?= number_format((int)$project['view_count']) ?> views</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="sidebar-actions">
            <?php if ($project['status'] === 'Live' && valid_project_url($project['project_url'])): ?>
              <a class="btn btn-external btn-block" href="<?= e($project['project_url']) ?>" target="_blank" rel="noopener noreferrer">
                <?= lucide('external-link', '', 18) ?> Visit Live Website
              </a>
            <?php elseif ($project['status'] === 'Development'): ?>
              <div style="padding: 12px 14px; border-radius: 8px; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.25); color: #fbbf24; font-size: 0.813rem; text-align: center;">
                <?= lucide('clock', '', 16) ?> In Active Development. Deployment link pending final QA.
              </div>
            <?php else: ?>
              <div style="padding: 12px 14px; border-radius: 8px; background: rgba(148, 163, 184, 0.1); border: 1px solid rgba(148, 163, 184, 0.25); color: #94a3b8; font-size: 0.813rem; text-align: center;">
                <?= lucide('info', '', 16) ?> Archived project. Preserved for reference and portfolio archive.
              </div>
            <?php endif; ?>

            <?php if (!empty($project['github_url']) && valid_project_url($project['github_url'])): ?>
              <a class="btn btn-github btn-block" href="<?= e($project['github_url']) ?>" target="_blank" rel="noopener noreferrer">
                <?= lucide('github', '', 18) ?> View Source Repository
              </a>
            <?php endif; ?>

            <button class="share-btn btn-block" id="shareProjectBtn" type="button">
              <?= lucide('share-2', '', 16) ?> Share Project Link
            </button>
          </div>
        </div>

        <!-- Quick Back CTA -->
        <a class="btn btn-secondary btn-block" href="<?= BASE_URL ?>/index.php#projects">
          <?= lucide('arrow-left', '', 16) ?> Back to Projects
        </a>
      </aside>
    </div>

    <!-- Related Projects Showcase -->
    <?php if (!empty($relatedProjects)): ?>
      <section class="related-section">
        <div class="section-header">
          <span class="section-eyebrow">Explore More</span>
          <h2 class="section-title">Related Projects</h2>
        </div>

        <div class="project-grid">
          <?php foreach ($relatedProjects as $project): ?>
            <?php require __DIR__ . '/includes/project-card.php'; ?>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
