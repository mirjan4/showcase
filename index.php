<?php
// ========================================================
// Homepage - Project Showcase & Digital Portfolio
// ========================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$currentNav = 'home';
$pageTitle = setting('site_name', 'Digital Project Showcase');
$metaDescription = setting('hero_subtitle', 'Curated digital projects, web applications, and software architectures.');

// Fetch all active projects
$projectsStmt = $pdo->query("SELECT p.*, c.name AS category_name, c.slug AS category_slug
    FROM projects p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.status <> 'Archived'
    ORDER BY p.is_featured DESC, p.last_updated DESC, p.id DESC");
$projects = $projectsStmt->fetchAll();

// Fetch statistics
$stats = $pdo->query("SELECT
    COUNT(*) AS total,
    SUM(status = 'Live') AS live,
    SUM(status = 'Development') AS development,
    COUNT(DISTINCT category_id) AS categories
    FROM projects WHERE status <> 'Archived'")->fetch();

// Fetch all categories with project counts
$categoriesStmt = $pdo->query("SELECT c.*, COUNT(p.id) AS project_count
    FROM categories c
    LEFT JOIN projects p ON p.category_id = c.id AND p.status <> 'Archived'
    GROUP BY c.id
    ORDER BY c.sort_order ASC, c.name ASC");
$categories = $categoriesStmt->fetchAll();

// Find primary featured project for spotlight
$featuredProject = null;
foreach ($projects as $p) {
    if (!empty($p['is_featured'])) {
        $featuredProject = $p;
        break;
    }
}

require __DIR__ . '/includes/header.php';
?>

<main>
  <!-- Hero Section -->
  <section class="hero" id="home">
    <div class="hero-glow-1"></div>
    <div class="hero-glow-2"></div>
    <div class="container">
      <div class="eyebrow-pill">
        <span class="eyebrow-dot"></span>
        Curated Portfolio &amp; Archive
      </div>
      <h1>
        <?= e(setting('hero_title', 'Websites, apps, and digital solutions made real.')) ?>
      </h1>
      <p class="hero-subtitle">
        <?= e(setting('hero_subtitle', 'A curated collection of full-stack web applications, enterprise management systems, and high-performance digital tools engineered for real-world impact.')) ?>
      </p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="#projects">
          <?= lucide('layout-grid', '', 18) ?> Explore Projects
        </a>
        <a class="btn btn-secondary" href="<?= BASE_URL ?>/about.php">
          <?= lucide('user', '', 18) ?> About Developer
        </a>
      </div>
    </div>
  </section>

  <!-- Statistics Section -->
  <section class="stats-section" aria-label="Project portfolio statistics">
    <div class="container">
      <div class="stats-grid">
        <div class="stat-item">
          <div class="stat-icon"><?= lucide('layers', '', 24) ?></div>
          <div class="stat-info">
            <span class="stat-number"><?= (int)($stats['total'] ?? 0) ?></span>
            <span class="stat-label">Total Projects</span>
          </div>
        </div>
        <div class="stat-item">
          <div class="stat-icon" style="color: #34d399; background: rgba(16, 185, 129, 0.1);"><?= lucide('check-circle', '', 24) ?></div>
          <div class="stat-info">
            <span class="stat-number"><?= (int)($stats['live'] ?? 0) ?></span>
            <span class="stat-label">Live Systems</span>
          </div>
        </div>
        <div class="stat-item">
          <div class="stat-icon" style="color: #fbbf24; background: rgba(245, 158, 11, 0.1);"><?= lucide('code', '', 24) ?></div>
          <div class="stat-info">
            <span class="stat-number"><?= (int)($stats['development'] ?? 0) ?></span>
            <span class="stat-label">In Development</span>
          </div>
        </div>
        <div class="stat-item">
          <div class="stat-icon" style="color: #38bdf8; background: rgba(14, 165, 233, 0.1);"><?= lucide('folder', '', 24) ?></div>
          <div class="stat-info">
            <span class="stat-number"><?= (int)($stats['categories'] ?? 0) ?></span>
            <span class="stat-label">Categories</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured Project Spotlight -->
  <?php if ($featuredProject): ?>
    <section class="featured-section" aria-label="Featured spotlight project">
      <div class="container">
        <div class="section-header">
          <span class="section-eyebrow">Spotlight Feature</span>
          <h2 class="section-title">Highlighted Architecture</h2>
        </div>

        <div class="featured-card">
          <div class="featured-image-wrap">
            <img src="<?= e(project_image($featuredProject['thumbnail'])) ?>" alt="<?= e($featuredProject['name']) ?> preview">
            <span class="featured-badge-pill">
              <?= lucide('star', '', 14) ?> Featured Project
            </span>
          </div>
          <div class="featured-content">
            <div class="featured-meta">
              <span style="font-size: 0.813rem; font-weight: 700; color: var(--accent-light); text-transform: uppercase;">
                <?= e($featuredProject['category_name'] ?? 'Project') ?>
              </span>
              <?= status_badge($featuredProject['status']) ?>
            </div>
            <h2><?= e($featuredProject['name']) ?></h2>
            <p><?= e($featuredProject['short_description']) ?></p>

            <?php if (!empty($featuredProject['technologies'])): ?>
              <div class="tech-tags">
                <?php foreach (array_filter(array_map('trim', explode(',', $featuredProject['technologies']))) as $tech): ?>
                  <span class="tech-tag"><?= e($tech) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="featured-actions">
              <a class="btn btn-primary" href="<?= BASE_URL ?>/project.php?slug=<?= urlencode($featuredProject['slug']) ?>">
                View Case Study <?= lucide('arrow-right', '', 16) ?>
              </a>
              <?php if ($featuredProject['status'] === 'Live' && valid_project_url($featuredProject['project_url'])): ?>
                <a class="btn btn-secondary" href="<?= e($featuredProject['project_url']) ?>" target="_blank" rel="noopener noreferrer">
                  <?= lucide('external-link', '', 16) ?> Visit Live
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- Projects Showcase Section -->
  <section class="projects-section" id="projects">
    <div class="container">
      <div class="section-header">
        <span class="section-eyebrow">The Collection</span>
        <h2 class="section-title">Selected Projects</h2>
      </div>

      <!-- Filter & Search Toolbar -->
      <div class="filter-toolbar">
        <div class="search-box">
          <span class="search-icon"><?= lucide('search', '', 18) ?></span>
          <input class="search-input" id="projectSearch" type="search" placeholder="Search projects by name, tech, keyword..." aria-label="Search projects">
          <button class="search-clear" id="searchClear" type="button" aria-label="Clear search" hidden>
            <?= lucide('x', '', 16) ?>
          </button>
        </div>

        <div class="filter-controls">
          <!-- Category Filter -->
          <select class="select-control" id="categoryFilter" aria-label="Filter by category">
            <option value="">All Categories (<?= count($projects) ?>)</option>
            <?php foreach ($categories as $cat): ?>
              <?php if ($cat['project_count'] > 0): ?>
                <option value="<?= e(strtolower($cat['name'])) ?>">
                  <?= e($cat['name']) ?> (<?= (int)$cat['project_count'] ?>)
                </option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>

          <!-- Status Filter -->
          <select class="select-control" id="statusFilter" aria-label="Filter by status">
            <option value="">All Statuses</option>
            <option value="live">Live (<?= (int)($stats['live'] ?? 0) ?>)</option>
            <option value="development">Development (<?= (int)($stats['development'] ?? 0) ?>)</option>
          </select>

          <!-- Sort Select -->
          <select class="select-control" id="sortProjects" aria-label="Sort projects">
            <option value="updated">Recently Updated</option>
            <option value="created">Recently Added</option>
            <option value="name-asc">Alphabetical (A–Z)</option>
            <option value="name-desc">Alphabetical (Z–A)</option>
          </select>

          <!-- Reset Filter Button -->
          <button class="reset-filter-btn" id="resetFilters" type="button">
            Reset Filters
          </button>
        </div>
      </div>

      <!-- Active Filter Results Summary -->
      <div class="results-bar">
        <span>Showing <strong id="showingCount"><?= count($projects) ?></strong> of <strong><?= count($projects) ?></strong> projects</span>
      </div>

      <!-- Projects Grid -->
      <?php if (empty($projects)): ?>
        <div class="empty-state">
          <div class="empty-state-icon"><?= lucide('layers', '', 28) ?></div>
          <h3>Your project library starts here.</h3>
          <p>No projects have been published yet. Log in to the administrator control center to add your first project.</p>
          <a class="btn btn-primary" href="<?= BASE_URL ?>/admin/login.php">Open Admin Panel</a>
        </div>
      <?php else: ?>
        <div class="project-grid" id="projectGrid">
          <?php foreach ($projects as $project): ?>
            <?php require __DIR__ . '/includes/project-card.php'; ?>
          <?php endforeach; ?>
        </div>

        <!-- Dynamic No Results Notice -->
        <div class="empty-state" id="noResults" hidden>
          <div class="empty-state-icon"><?= lucide('search', '', 28) ?></div>
          <h3>No matching projects found</h3>
          <p>Try refining your search terms or clearing selected category and status filters.</p>
          <button class="btn btn-secondary" onclick="document.getElementById('resetFilters').click();" type="button">
            Clear All Filters
          </button>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Interactive Categories Explorer Section -->
  <section class="categories-section" id="categories">
    <div class="container">
      <div class="section-header">
        <span class="section-eyebrow">Taxonomy</span>
        <h2 class="section-title">Explore By Category</h2>
      </div>

      <div class="category-grid">
        <?php foreach ($categories as $category): ?>
          <article class="category-card">
            <div class="category-card-top">
              <div class="category-icon-box">
                <?= lucide($category['icon'] ?? 'folder', '', 22) ?>
              </div>
              <span class="category-count">
                <?= (int)$category['project_count'] ?> <?= (int)$category['project_count'] === 1 ? 'Project' : 'Projects' ?>
              </span>
            </div>
            <h3><?= e($category['name']) ?></h3>
            <p><?= e($category['description'] ?? 'Curated software developments and engineering solutions.') ?></p>
            <a class="category-card-link" href="<?= BASE_URL ?>/category.php?slug=<?= urlencode($category['slug']) ?>">
              Browse Category <?= lucide('arrow-right', '', 14) ?>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
