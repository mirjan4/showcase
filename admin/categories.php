<?php
// ========================================================
// Admin - Categories Taxonomy Management
// ========================================================

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/auth.php';

require_admin();

$editCategory = null;
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
if ($editId) {
    $eStmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $eStmt->execute([$editId]);
    $editCategory = $eStmt->fetch();
}

// Handle Form Submissions (Create, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create_category') {
        $name = trim($_POST['name'] ?? '');
        $slug = slugify(trim($_POST['slug'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'folder');

        if ($name === '') {
            flash_set('danger', 'Category name cannot be empty.');
        } else {
            if ($slug === '') $slug = slugify($name);

            // Check slug uniqueness
            $check = $pdo->prepare("SELECT id FROM categories WHERE slug = ? OR name = ?");
            $check->execute([$slug, $name]);
            if ($check->fetch()) {
                flash_set('danger', 'A category with this name or slug already exists.');
            } else {
                $ins = $pdo->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
                $ins->execute([$name, $slug, $description, $icon]);
                flash_set('success', "Category \"$name\" created successfully.");
                header('Location: categories.php');
                exit;
            }
        }
    } elseif ($action === 'update_category') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $name = trim($_POST['name'] ?? '');
        $slug = slugify(trim($_POST['slug'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'folder');

        if ($id && $name !== '') {
            if ($slug === '') $slug = slugify($name);

            $check = $pdo->prepare("SELECT id FROM categories WHERE (slug = ? OR name = ?) AND id <> ?");
            $check->execute([$slug, $name, $id]);
            if ($check->fetch()) {
                flash_set('danger', 'Another category already uses this name or slug.');
            } else {
                $upd = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, icon = ? WHERE id = ?");
                $upd->execute([$name, $slug, $description, $icon, $id]);
                flash_set('success', "Category \"$name\" updated successfully.");
                header('Location: categories.php');
                exit;
            }
        }
    } elseif ($action === 'delete_category') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            // Check if any projects are assigned
            $pCheck = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE category_id = ?");
            $pCheck->execute([$id]);
            $assignedCount = (int)$pCheck->fetchColumn();

            if ($assignedCount > 0) {
                flash_set('danger', "Cannot delete category: $assignedCount projects are currently assigned to it. Reassign those projects first.");
            } else {
                $del = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $del->execute([$id]);
                flash_set('success', 'Category deleted successfully.');
                header('Location: categories.php');
                exit;
            }
        }
    }
}

// Fetch categories with project count
$categories = $pdo->query("SELECT c.*, COUNT(p.id) AS project_count
    FROM categories c
    LEFT JOIN projects p ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY c.sort_order ASC, c.name ASC")->fetchAll();

$availableIcons = [
    'folder' => 'Folder',
    'globe' => 'Websites (Globe)',
    'layout-grid' => 'Web Applications (Grid)',
    'briefcase' => 'Management (Briefcase)',
    'circle-dollar-sign' => 'Finance & Accounting (Dollar)',
    'shopping-bag' => 'E-Commerce (Shopping Bag)',
    'cpu' => 'Internal Tools (CPU / Tech)',
    'sparkles' => 'Other / Creative (Sparkles)',
    'code' => 'Code / Software',
    'layers' => 'Layers / Systems',
];

$adminPageTitle = 'Categories Taxonomy';
$adminNav = 'categories';
require __DIR__ . '/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
  <div>
    <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0;">Project Categories</h2>
    <p style="font-size: 0.813rem; color: var(--admin-muted); margin-top: 4px;">
      Manage classifications, icons, and slug routing for project organization.
    </p>
  </div>
  <?php if ($editCategory): ?>
    <a class="btn btn-secondary" href="categories.php">
      <?= lucide('plus', '', 16) ?> Add New Category Instead
    </a>
  <?php endif; ?>
</div>

<div class="dashboard-grid">
  <!-- Left Column: Add / Edit Form -->
  <div class="admin-box">
    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; border-bottom: 1px solid var(--admin-border); padding-bottom: 12px;">
      <?= $editCategory ? 'Edit Category: ' . e($editCategory['name']) : 'Create New Category' ?>
    </h3>

    <form method="POST" action="categories.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="<?= $editCategory ? 'update_category' : 'create_category' ?>">
      <?php if ($editCategory): ?>
        <input type="hidden" name="id" value="<?= (int)$editCategory['id'] ?>">
      <?php endif; ?>

      <div class="form-group">
        <label class="form-label" for="categoryName">
          Category Name <span class="required">*</span>
        </label>
        <input class="form-input" id="categoryName" type="text" name="name" required placeholder="e.g. Cloud Infrastructure" value="<?= e($editCategory['name'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="categorySlug">
          URL Slug
        </label>
        <input class="form-input" id="categorySlug" type="text" name="slug" placeholder="e.g. cloud-infrastructure" value="<?= e($editCategory['slug'] ?? '') ?>">
        <div class="form-hint">Used for category archive URLs (category.php?slug=...).</div>
      </div>

      <div class="form-group">
        <label class="form-label" for="categoryIcon">Category Icon</label>
        <select class="form-select" id="categoryIcon" name="icon">
          <?php foreach ($availableIcons as $icoKey => $icoLabel): ?>
            <option value="<?= e($icoKey) ?>" <?= ($editCategory['icon'] ?? 'folder') === $icoKey ? 'selected' : '' ?>>
              <?= e($icoLabel) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="categoryDesc">Short Description</label>
        <textarea class="form-textarea" id="categoryDesc" name="description" rows="3" placeholder="Brief summary of projects in this category..."><?= e($editCategory['description'] ?? '') ?></textarea>
      </div>

      <div style="display: flex; gap: 10px;">
        <button class="btn btn-primary" type="submit" style="flex: 1; justify-content: center; padding: 11px;">
          <?= lucide($editCategory ? 'check' : 'plus', '', 16) ?>
          <?= $editCategory ? 'Update Category' : 'Create Category' ?>
        </button>
        <?php if ($editCategory): ?>
          <a class="btn btn-secondary" href="categories.php">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Right Column: Listing Table -->
  <div class="admin-box">
    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; border-bottom: 1px solid var(--admin-border); padding-bottom: 12px;">
      Existing Categories (<?= count($categories) ?>)
    </h3>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Icon</th>
            <th>Name &amp; Slug</th>
            <th>Projects</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
            <tr>
              <td>
                <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(99, 102, 241, 0.1); color: var(--admin-accent); display: grid; place-items: center;">
                  <?= lucide($cat['icon'] ?? 'folder', '', 18) ?>
                </div>
              </td>
              <td>
                <strong><?= e($cat['name']) ?></strong>
                <div style="font-size: 0.75rem; color: var(--admin-muted); font-family: monospace;">
                  <?= e($cat['slug']) ?>
                </div>
              </td>
              <td>
                <span class="badge-status" style="background: rgba(255, 255, 255, 0.05); color: #cbd5e1;">
                  <?= (int)$cat['project_count'] ?> projects
                </span>
              </td>
              <td>
                <div class="table-actions" style="justify-content: flex-end;">
                  <a class="action-btn action-edit" href="categories.php?edit=<?= (int)$cat['id'] ?>" title="Edit Category">
                    <?= lucide('edit', '', 14) ?>
                  </a>
                  <?php if ((int)$cat['project_count'] === 0): ?>
                    <form method="POST" action="categories.php" style="display: inline;" onsubmit="return confirm('Delete this unused category?');">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete_category">
                      <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                      <button class="action-btn action-delete" type="submit" title="Delete Category">
                        <?= lucide('trash-2', '', 14) ?>
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="action-btn" style="opacity: 0.35; cursor: not-allowed;" title="Cannot delete: has assigned projects">
                      <?= lucide('trash-2', '', 14) ?>
                    </span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
// Auto-slug for category
const catName = document.getElementById('categoryName');
const catSlug = document.getElementById('categorySlug');
if (catName && catSlug && !catSlug.value) {
  catName.addEventListener('input', () => {
    catSlug.value = catName.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  });
}
</script>

<?php require __DIR__ . '/footer.php'; ?>
