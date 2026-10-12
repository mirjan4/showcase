<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($slug !== '') {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    $category = $stmt->fetch();
} elseif ($id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
} else {
    $category = null;
}

if (!$category) {
    http_response_code(404);
    $pageTitle = 'Category Not Found';
    require __DIR__ . '/includes/header.php';
    ?>
    <main class="container" style="padding: 100px 24px; text-align: center;">
      <div class="empty-state" style="max-width: 500px; margin: 0 auto;">
        <div class="empty-state-icon"><?= lucide('folder', '', 32) ?></div>
        <h2>Category Not Found</h2>
        <p>The category requested does not exist or has been removed.</p>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/index.php#categories">Browse Categories</a>
      </div>
    </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch all active projects under this category
$projectsStmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
    FROM projects p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.category_id = ? AND p.status <> 'Archived'
    ORDER BY p.is_featured DESC, p.last_updated DESC");
$projectsStmt->execute([$category['id']]);
$projects = $projectsStmt->fetchAll();

$pageTitle = $category['name'] . ' Projects';
$metaDescription = $category['description'] ?? 'Curated projects in ' . $category['name'];
$currentNav = 'categories';

require __DIR__ . '/includes/header.php';
?>

<main class="projects-section" style="padding-top: 40px;">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb navigation">
      <a href="<?= BASE_URL ?>/index.php">Home</a>
      <span>/</span>
      <a href="<?= BASE_URL ?>/index.php#categories">Categories</a>
      <span>/</span>
      <span style="color: var(--text-primary); font-weight: 600;"><?= e($category['name']) ?></span>
    </nav>

    <div class="section-header" style="margin-bottom: 40px;">
      <div style="display: flex; align-items: center; gap: 16px;">
        <div class="category-icon-box" style="width: 56px; height: 56px; border-radius: 12px;">
          <?= lucide($category['icon'] ?? 'folder', '', 28) ?>
        </div>
        <div>
          <span class="section-eyebrow">Category Archive</span>
          <h1 class="section-title" style="margin-bottom: 6px;"><?= e($category['name']) ?></h1>
          <p style="color: var(--text-secondary); font-size: 1rem;"><?= e($category['description'] ?? '') ?></p>
        </div>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="filter-toolbar">
      <div class="search-box">
        <span class="search-icon"><?= lucide('search', '', 18) ?></span>
        <input class="search-input" id="projectSearch" type="search" placeholder="Search in <?= e($category['name']) ?>..." aria-label="Search category projects">
        <button class="search-clear" id="searchClear" type="button" aria-label="Clear search" hidden>
          <?= lucide('x', '', 16) ?>
        </button>
      </div>

      <div class="filter-controls">
        <select class="select-control" id="statusFilter" aria-label="Filter by status">
          <option value="">All Statuses</option>
          <option value="live">Live Only</option>
          <option value="development">In Development</option>
        </select>

        <select class="select-control" id="sortProjects" aria-label="Sort projects">
          <option value="updated">Recently Updated</option>
          <option value="created">Recently Added</option>
          <option value="name-asc">Alphabetical (A–Z)</option>
        </select>

        <button class="reset-filter-btn" id="resetFilters" type="button">Reset</button>
      </div>
    </div>

    <!-- Results count bar -->
    <div class="results-bar">
      <span>Showing <strong id="showingCount"><?= count($projects) ?></strong> of <strong><?= count($projects) ?></strong> projects</span>
      <a href="<?= BASE_URL ?>/index.php#projects" style="color: var(--accent-light); font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
        View all projects <?= lucide('arrow-right', '', 14) ?>
      </a>
    </div>

    <!-- Projects Grid -->
    <?php if (empty($projects)): ?>
      <div class="empty-state">
        <div class="empty-state-icon"><?= lucide('folder', '', 28) ?></div>
        <h3>No projects in this category yet</h3>
        <p>New projects under this classification will be published soon.</p>
        <a class="btn btn-secondary" href="<?= BASE_URL ?>/index.php#categories">← Back to All Categories</a>
      </div>
    <?php else: ?>
      <div class="project-grid" id="projectGrid">
        <?php foreach ($projects as $project): ?>
          <?php require __DIR__ . '/includes/project-card.php'; ?>
        <?php endforeach; ?>
      </div>

      <div class="empty-state" id="noResults" hidden>
        <div class="empty-state-icon"><?= lucide('search', '', 28) ?></div>
        <h3>No matching projects found</h3>
        <p>Try refining your search keyword or clearing status filters.</p>
        <button class="btn btn-secondary" onclick="document.getElementById('resetFilters').click();" type="button">
          Clear Filters
        </button>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
