<?php
// ========================================================
// Admin Dashboard Overview & Analytics
// ========================================================

$adminPageTitle = 'Dashboard Overview';
$adminNav = 'dashboard';
require __DIR__ . '/header.php';

// Calculate metrics
$metrics = $pdo->query("SELECT
    COUNT(*) AS total_projects,
    SUM(status = 'Live') AS live_projects,
    SUM(status = 'Development') AS dev_projects,
    SUM(status = 'Archived') AS archived_projects,
    SUM(view_count) AS total_views
    FROM projects")->fetch();

$totalCategories = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalGalleryImages = (int)$pdo->query("SELECT COUNT(*) FROM project_images")->fetchColumn();

// Fetch Category Distribution for Chart
$catStats = $pdo->query("SELECT c.name, COUNT(p.id) AS count
    FROM categories c
    LEFT JOIN projects p ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY count DESC")->fetchAll();

$catLabels = [];
$catCounts = [];
foreach ($catStats as $cs) {
    if ($cs['count'] > 0) {
        $catLabels[] = $cs['name'];
        $catCounts[] = (int)$cs['count'];
    }
}

// Fetch Status Counts for Chart
$statusLabels = ['Live', 'Development', 'Archived'];
$statusCounts = [
    (int)($metrics['live_projects'] ?? 0),
    (int)($metrics['dev_projects'] ?? 0),
    (int)($metrics['archived_projects'] ?? 0),
];

// Fetch 5 most recently updated projects
$recentUpdated = $pdo->query("SELECT p.*, c.name AS category_name
    FROM projects p
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY p.updated_at DESC
    LIMIT 5")->fetchAll();

// Fetch 5 most recently added projects
$recentAdded = $pdo->query("SELECT p.*, c.name AS category_name
    FROM projects p
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY p.created_at DESC
    LIMIT 5")->fetchAll();
?>

<!-- KPI Stat Cards -->
<div class="admin-stats-grid">
  <div class="admin-stat-card">
    <div class="admin-stat-icon" style="background: rgba(99, 102, 241, 0.15); color: #818cf8;">
      <?= lucide('layers', '', 24) ?>
    </div>
    <div>
      <div class="admin-stat-number"><?= (int)($metrics['total_projects'] ?? 0) ?></div>
      <div class="admin-stat-label">Total Projects</div>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">
      <?= lucide('check-circle', '', 24) ?>
    </div>
    <div>
      <div class="admin-stat-number"><?= (int)($metrics['live_projects'] ?? 0) ?></div>
      <div class="admin-stat-label">Live Deployments</div>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">
      <?= lucide('code', '', 24) ?>
    </div>
    <div>
      <div class="admin-stat-number"><?= (int)($metrics['dev_projects'] ?? 0) ?></div>
      <div class="admin-stat-label">In Active Development</div>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-icon" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8;">
      <?= lucide('eye', '', 24) ?>
    </div>
    <div>
      <div class="admin-stat-number"><?= number_format((int)($metrics['total_views'] ?? 0)) ?></div>
      <div class="admin-stat-label">Total Showcase Views</div>
    </div>
  </div>
</div>

<!-- Quick Action Shortcuts -->
<div style="display: flex; gap: 14px; margin-bottom: 32px; flex-wrap: wrap;">
  <a class="btn btn-primary" href="add-project.php">
    <?= lucide('plus', '', 18) ?> Add New Project
  </a>
  <a class="btn btn-secondary" href="categories.php">
    <?= lucide('folder', '', 18) ?> Manage Categories (<?= $totalCategories ?>)
  </a>
  <a class="btn btn-secondary" href="settings.php">
    <?= lucide('settings', '', 18) ?> Portfolio Settings
  </a>
</div>

<!-- Interactive Chart.js Analytics Grid -->
<div class="dashboard-grid">
  <div class="admin-box">
    <div class="admin-box-header">
      <h2 class="admin-box-title">
        <?= lucide('activity', '', 18) ?> Project Portfolio by Status
      </h2>
      <span style="font-size: 0.75rem; color: var(--admin-muted);">Active lifecycle distribution</span>
    </div>
    <div style="height: 250px; position: relative;">
      <canvas id="statusChart"></canvas>
    </div>
  </div>

  <div class="admin-box">
    <div class="admin-box-header">
      <h2 class="admin-box-title">
        <?= lucide('layout-grid', '', 18) ?> Category Breakdown
      </h2>
      <span style="font-size: 0.75rem; color: var(--admin-muted);"><?= count($catLabels) ?> Active Categories</span>
    </div>
    <div style="height: 250px; position: relative;">
      <canvas id="categoryChart"></canvas>
    </div>
  </div>
</div>

<!-- Recently Modified Projects Table -->
<div class="admin-box" style="margin-bottom: 32px;">
  <div class="admin-box-header">
    <h2 class="admin-box-title">
      <?= lucide('clock', '', 18) ?> Recently Modified Projects
    </h2>
    <a href="projects.php" style="font-size: 0.813rem; color: var(--admin-accent); font-weight: 600;">
      View all inventory →
    </a>
  </div>

  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Thumbnail</th>
          <th>Project Name</th>
          <th>Category</th>
          <th>Status</th>
          <th>Last Updated</th>
          <th>Views</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentUpdated as $p): ?>
          <tr>
            <td>
              <img class="thumb-preview-sm" src="<?= e(project_image($p['thumbnail'])) ?>" alt="Thumb">
            </td>
            <td>
              <strong><a href="edit-project.php?id=<?= (int)$p['id'] ?>" style="color: #ffffff;"><?= e($p['name']) ?></a></strong>
              <div style="font-size: 0.75rem; color: var(--admin-muted); font-family: monospace;">/<?= e($p['slug']) ?></div>
            </td>
            <td>
              <span class="badge-status" style="background: rgba(255,255,255,0.05); color: #cbd5e1;">
                <?= e($p['category_name'] ?? '—') ?>
              </span>
            </td>
            <td><?= status_badge($p['status']) ?></td>
            <td><?= e(format_project_date($p['last_updated'], true)) ?></td>
            <td><?= (int)$p['view_count'] ?></td>
            <td>
              <div class="table-actions" style="justify-content: flex-end;">
                <a class="action-btn action-view" href="<?= BASE_URL ?>/project.php?slug=<?= urlencode($p['slug']) ?>" target="_blank" title="View Public Page">
                  <?= lucide('eye', '', 14) ?>
                </a>
                <a class="action-btn action-edit" href="edit-project.php?id=<?= (int)$p['id'] ?>" title="Edit Project">
                  <?= lucide('edit', '', 14) ?>
                </a>
                <button class="action-btn action-delete" type="button" data-delete-btn data-delete-title="<?= e($p['name']) ?>" data-delete-action="delete-project.php?id=<?= (int)$p['id'] ?>" title="Delete Project">
                  <?= lucide('trash-2', '', 14) ?>
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const catLabels = <?= json_encode($catLabels) ?>;
  const catCounts = <?= json_encode($catCounts) ?>;
  const statusLabels = <?= json_encode($statusLabels) ?>;
  const statusCounts = <?= json_encode($statusCounts) ?>;
  if (window.initAdminCharts) {
    window.initAdminCharts(catLabels, catCounts, statusLabels, statusCounts);
  }
});
</script>

<?php require __DIR__ . '/footer.php'; ?>
