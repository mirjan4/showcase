<?php
// ========================================================
// Admin - All Projects Inventory Management
// ========================================================

$adminPageTitle = 'Project Management';
$adminNav = 'projects';
require __DIR__ . '/header.php';

// Filter parameters
$search = trim($_GET['q'] ?? '');
$categoryFilter = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
$statusFilter = trim($_GET['status'] ?? '');
$sortBy = trim($_GET['sort'] ?? 'updated');
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Build query
$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(p.name LIKE ? OR p.short_description LIKE ? OR p.technologies LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($categoryFilter) {
    $where[] = "p.category_id = ?";
    $params[] = $categoryFilter;
}

if ($statusFilter !== '' && in_array($statusFilter, ['Live', 'Development', 'Archived'], true)) {
    $where[] = "p.status = ?";
    $params[] = $statusFilter;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Sorting clause
$orderClause = match ($sortBy) {
    'created' => 'ORDER BY p.created_at DESC',
    'name'    => 'ORDER BY p.name ASC',
    'views'   => 'ORDER BY p.view_count DESC',
    default   => 'ORDER BY p.updated_at DESC, p.id DESC',
};

// Count total matching
$countSql = "SELECT COUNT(*) FROM projects p $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

// Fetch paginated projects
$sql = "SELECT p.*, c.name AS category_name
    FROM projects p
    LEFT JOIN categories c ON c.id = p.category_id
    $whereClause
    $orderClause
    LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

// Categories for filter dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0;">Project Inventory</h2>
    <p style="font-size: 0.813rem; color: var(--admin-muted); margin-top: 4px;">
      Total of <?= $totalRows ?> matching <?= $totalRows === 1 ? 'project' : 'projects' ?> found.
    </p>
  </div>
  <a class="btn btn-primary" href="add-project.php">
    <?= lucide('plus', '', 18) ?> Add New Project
  </a>
</div>

<!-- Search and Filter Bar -->
<div class="admin-box" style="padding: 16px 20px; margin-bottom: 24px;">
  <form method="GET" action="projects.php" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
    <div style="flex: 1 1 240px; min-width: 200px;">
      <input class="form-input" type="search" name="q" placeholder="Search by name, description, tech..." value="<?= e($search) ?>" style="padding: 9px 12px; font-size: 0.875rem;">
    </div>

    <div>
      <select class="form-select" name="category" style="padding: 9px 14px; font-size: 0.875rem;">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= (int)$cat['id'] ?>" <?= $categoryFilter === (int)$cat['id'] ? 'selected' : '' ?>>
            <?= e($cat['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <select class="form-select" name="status" style="padding: 9px 14px; font-size: 0.875rem;">
        <option value="">All Statuses</option>
        <option value="Live" <?= $statusFilter === 'Live' ? 'selected' : '' ?>>Live</option>
        <option value="Development" <?= $statusFilter === 'Development' ? 'selected' : '' ?>>Development</option>
        <option value="Archived" <?= $statusFilter === 'Archived' ? 'selected' : '' ?>>Archived</option>
      </select>
    </div>

    <div>
      <select class="form-select" name="sort" style="padding: 9px 14px; font-size: 0.875rem;">
        <option value="updated" <?= $sortBy === 'updated' ? 'selected' : '' ?>>Recently Updated</option>
        <option value="created" <?= $sortBy === 'created' ? 'selected' : '' ?>>Recently Added</option>
        <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>>Name (A–Z)</option>
        <option value="views" <?= $sortBy === 'views' ? 'selected' : '' ?>>Most Viewed</option>
      </select>
    </div>

    <button class="btn btn-secondary" type="submit" style="padding: 9px 16px; font-size: 0.875rem;">
      <?= lucide('search', '', 14) ?> Filter
    </button>

    <?php if ($search || $categoryFilter || $statusFilter || $sortBy !== 'updated'): ?>
      <a class="btn btn-secondary" href="projects.php" style="padding: 9px 14px; font-size: 0.875rem; color: var(--admin-muted);">
        Clear
      </a>
    <?php endif; ?>
  </form>
</div>

<!-- Table Card -->
<div class="admin-box">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Thumbnail</th>
          <th>Project Title &amp; Slug</th>
          <th>Category</th>
          <th>Status</th>
          <th>Featured</th>
          <th>Last Updated</th>
          <th>Views</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($projects)): ?>
          <tr>
            <td colspan="8" style="text-align: center; padding: 40px 20px; color: var(--admin-muted);">
              No projects found matching the filter criteria.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($projects as $p): ?>
            <tr>
              <td>
                <img class="thumb-preview-sm" src="<?= e(project_image($p['thumbnail'])) ?>" alt="Preview">
              </td>
              <td>
                <strong>
                  <a href="edit-project.php?id=<?= (int)$p['id'] ?>" style="color: #ffffff;">
                    <?= e($p['name']) ?>
                  </a>
                </strong>
                <div style="font-size: 0.75rem; color: var(--admin-muted); font-family: monospace;">
                  /<?= e($p['slug']) ?>
                </div>
              </td>
              <td>
                <span style="font-size: 0.813rem; color: #cbd5e1;">
                  <?= e($p['category_name'] ?? '—') ?>
                </span>
              </td>
              <td><?= status_badge($p['status']) ?></td>
              <td>
                <?php if (!empty($p['is_featured'])): ?>
                  <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; background: rgba(99, 102, 241, 0.2); color: #818cf8;">
                    <?= lucide('star', '', 12) ?> Yes
                  </span>
                <?php else: ?>
                  <span style="color: var(--admin-muted); font-size: 0.75rem;">—</span>
                <?php endif; ?>
              </td>
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
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination Controls -->
  <?php if ($totalPages > 1): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 0 0; border-top: 1px solid var(--admin-border); margin-top: 16px;">
      <span style="font-size: 0.813rem; color: var(--admin-muted);">
        Page <?= $page ?> of <?= $totalPages ?>
      </span>
      <div style="display: flex; gap: 6px;">
        <?php if ($page > 1): ?>
          <a class="action-btn action-view" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
            Previous
          </a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a class="action-btn <?= $i === $page ? 'action-edit' : 'action-view' ?>" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <a class="action-btn action-view" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
            Next
          </a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/footer.php'; ?>
