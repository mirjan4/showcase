<?php
/**
 * Reusable Project Card Component
 * Expected variable: $project (associative array from projects table)
 */
if (!isset($project)) return;

$projectUrl = BASE_URL . '/project.php?slug=' . urlencode($project['slug']);
$categoryUrl = !empty($project['category_slug']) ? BASE_URL . '/category.php?slug=' . urlencode($project['category_slug']) : '#';
$thumbSrc = project_image($project['thumbnail'] ?? '');
?>
<article class="project-card"
  data-name="<?= e(strtolower($project['name'])) ?>"
  data-description="<?= e(strtolower($project['short_description'] ?? '')) ?>"
  data-technologies="<?= e(strtolower($project['technologies'] ?? '')) ?>"
  data-category="<?= e(strtolower($project['category_name'] ?? '')) ?>"
  data-status="<?= e(strtolower($project['status'])) ?>"
  data-updated="<?= e($project['last_updated'] ?? '') ?>"
  data-created="<?= e($project['created_date'] ?? '') ?>">

  <a class="card-thumb-link" href="<?= $projectUrl ?>" aria-label="View <?= e($project['name']) ?> details">
    <img loading="lazy" src="<?= e($thumbSrc) ?>" alt="<?= e($project['name']) ?> thumbnail">
    <span class="card-view-btn" aria-hidden="true">
      <?= lucide('arrow-up-right', '', 18) ?>
    </span>
  </a>

  <div class="card-body">
    <div class="card-header-meta">
      <a class="card-category-link" href="<?= $categoryUrl ?>">
        <?= e($project['category_name'] ?? 'Uncategorized') ?>
      </a>
      <?= status_badge($project['status']) ?>
    </div>

    <h3 class="card-title">
      <a href="<?= $projectUrl ?>"><?= e($project['name']) ?></a>
    </h3>

    <p class="card-description">
      <?= e($project['short_description']) ?>
    </p>

    <?php if (!empty($project['technologies'])): ?>
      <div class="tech-tags" style="margin-bottom: 16px;">
        <?php 
          $techs = array_slice(array_filter(array_map('trim', explode(',', $project['technologies']))), 0, 3);
          foreach ($techs as $tech): 
        ?>
          <span class="tech-tag"><?= e($tech) ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="card-footer">
      <div class="card-footer-date">
        <?= lucide('clock', '', 14) ?>
        <span>Updated <?= e(format_project_date($project['last_updated'])) ?></span>
      </div>
      <a class="card-footer-cta" href="<?= $projectUrl ?>">
        Details <?= lucide('arrow-right', '', 14) ?>
      </a>
    </div>
  </div>
</article>
