<?php
// ========================================================
// Admin - Add New Project
// ========================================================

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/auth.php';

require_admin();

$errors = [];
$name = '';
$slug = '';
$categoryId = '';
$status = 'Live';
$isFeatured = 0;
$shortDescription = '';
$fullDescription = '';
$features = '';
$technologies = '';
$projectUrl = '';
$githubUrl = '';
$createdDate = date('Y-m-d');
$lastUpdated = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $slug = slugify(trim($_POST['slug'] ?? ''));
    $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT) ?: null;
    $status = in_array($_POST['status'] ?? '', ['Live', 'Development', 'Archived'], true) ? $_POST['status'] : 'Development';
    $isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
    $shortDescription = trim($_POST['short_description'] ?? '');
    $fullDescription = trim($_POST['full_description'] ?? '');
    $features = trim($_POST['features'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    $projectUrl = trim($_POST['project_url'] ?? '');
    $githubUrl = trim($_POST['github_url'] ?? '');
    $createdDate = !empty($_POST['created_date']) ? $_POST['created_date'] : null;
    $lastUpdated = !empty($_POST['last_updated']) ? $_POST['last_updated'] : date('Y-m-d');

    // Validation
    if ($name === '') {
        $errors[] = 'Project title is required.';
    }

    if ($slug === '') {
        $slug = slugify($name);
    }

    // Check slug uniqueness
    $slugCheck = $pdo->prepare("SELECT id FROM projects WHERE slug = ?");
    $slugCheck->execute([$slug]);
    if ($slugCheck->fetch()) {
        $slug = $slug . '-' . time();
    }

    if ($shortDescription === '') {
        $errors[] = 'Short description is required.';
    }

    if ($fullDescription === '') {
        $fullDescription = $shortDescription;
    }

    if ($projectUrl !== '' && !valid_project_url($projectUrl)) {
        $errors[] = 'Live website URL must be a valid http:// or https:// address.';
    }

    if ($githubUrl !== '' && !valid_project_url($githubUrl)) {
        $errors[] = 'GitHub repository URL must be a valid http:// or https:// address.';
    }

    // Handle Thumbnail Upload
    $thumbnailFilename = null;
    if (!empty($_FILES['thumbnail']['name'])) {
        $uploadRes = secure_upload($_FILES['thumbnail'], PROJECT_UPLOADS_DIR);
        if ($uploadRes['success']) {
            $thumbnailFilename = $uploadRes['filename'];
        } else {
            $errors[] = 'Thumbnail upload error: ' . $uploadRes['error'];
        }
    }

    // If no errors, persist to database
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $insertSql = "INSERT INTO projects (
                name, slug, category_id, short_description, full_description,
                features, thumbnail, project_url, github_url, technologies,
                created_date, last_updated, status, is_featured
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($insertSql);
            $stmt->execute([
                $name, $slug, $categoryId, $shortDescription, $fullDescription,
                $features, $thumbnailFilename, $projectUrl ?: null, $githubUrl ?: null,
                $technologies, $createdDate, $lastUpdated, $status, $isFeatured
            ]);

            $newProjectId = (int)$pdo->lastInsertId();

            // Handle Additional Gallery Screenshots (Multiple)
            if (!empty($_FILES['gallery']['name']) && is_array($_FILES['gallery']['name'])) {
                $countFiles = count($_FILES['gallery']['name']);
                for ($i = 0; $i < $countFiles; $i++) {
                    if (!empty($_FILES['gallery']['name'][$i])) {
                        $singleFile = [
                            'name'     => $_FILES['gallery']['name'][$i],
                            'type'     => $_FILES['gallery']['type'][$i],
                            'tmp_name' => $_FILES['gallery']['tmp_name'][$i],
                            'error'    => $_FILES['gallery']['error'][$i],
                            'size'     => $_FILES['gallery']['size'][$i],
                        ];
                        $galleryUpload = secure_upload($singleFile, GALLERY_UPLOADS_DIR);
                        if ($galleryUpload['success']) {
                            $imgStmt = $pdo->prepare("INSERT INTO project_images (project_id, image_path, sort_order) VALUES (?, ?, ?)");
                            $imgStmt->execute([$newProjectId, $galleryUpload['filename'], $i + 1]);
                        }
                    }
                }
            }

            $pdo->commit();
            flash_set('success', "Project \"$name\" created successfully!");
            header('Location: projects.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            // Delete uploaded thumbnail if DB transaction fails
            if ($thumbnailFilename && file_exists(PROJECT_UPLOADS_DIR . '/' . $thumbnailFilename)) {
                @unlink(PROJECT_UPLOADS_DIR . '/' . $thumbnailFilename);
            }
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch categories for select dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll();

$adminPageTitle = 'Add New Project';
$adminNav = 'add-project';
require __DIR__ . '/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
  <div>
    <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0;">Add New Project</h2>
    <p style="font-size: 0.813rem; color: var(--admin-muted); margin-top: 4px;">
      Publish a new project card, screenshots, and live deployment details.
    </p>
  </div>
  <a class="btn btn-secondary" href="projects.php">
    <?= lucide('arrow-left', '', 16) ?> Back to Projects
  </a>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <?= lucide('info', '', 20) ?>
    <div>
      <strong>Please correct the following errors:</strong>
      <ul style="margin: 6px 0 0 18px;">
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
<?php endif; ?>

<form method="POST" action="add-project.php" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="form-grid">
    <!-- Left Column: Core Fields -->
    <div class="admin-box">
      <div class="form-group">
        <label class="form-label" for="projectName">
          Project Name <span class="required">*</span>
        </label>
        <input class="form-input" id="projectName" type="text" name="name" required placeholder="e.g. Ramalan Da'wa — Collection System" value="<?= e($name) ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="projectSlug">
          URL Slug <span class="required">*</span>
        </label>
        <input class="form-input" id="projectSlug" type="text" name="slug" required placeholder="ramalan-dawa" value="<?= e($slug) ?>">
        <div class="form-hint">Used for clean URLs (e.g. project.php?slug=ramalan-dawa). Auto-generated from title.</div>
      </div>

      <div class="form-group">
        <label class="form-label" for="shortDescription">
          Short Description (Summary Card) <span class="required">*</span>
        </label>
        <textarea class="form-textarea" id="shortDescription" name="short_description" required rows="3" placeholder="A concise 1-2 sentence overview shown on showcase cards and search engines."><?= e($shortDescription) ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label" for="fullDescription">
          Full Project Description &amp; Narrative <span class="required">*</span>
        </label>
        <textarea class="form-textarea" id="fullDescription" name="full_description" rows="8" placeholder="Detailed architectural description, workflow, business problem solved, and technical implementation highlights."><?= e($fullDescription) ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label" for="features">
          Key Features &amp; Capabilities (One per line)
        </label>
        <textarea class="form-textarea" id="features" name="features" rows="5" placeholder="Receipt book lifecycle and batch tracking&#10;Multi-tier settlement workflow&#10;Instant SMS and printable receipts&#10;Volunteer performance leaderboards"><?= e($features) ?></textarea>
        <div class="form-hint">Enter each standout feature on a new line. Rendered as a sleek checklist on the details page.</div>
      </div>

      <div class="form-group">
        <label class="form-label" for="technologies">
          Technology Stack Tags (Comma Separated)
        </label>
        <input class="form-input" id="technologies" type="text" name="technologies" placeholder="PHP 8.2, MySQL, Bootstrap 5, Chart.js, Vanilla JS" value="<?= e($technologies) ?>">
        <div class="form-hint">Separate with commas. These appear as pills and are filterable in search.</div>
      </div>
    </div>

    <!-- Right Column: Meta, Status & Images -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
      <!-- Publishing Status Card -->
      <div class="admin-box">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
          Publishing Controls
        </h3>

        <div class="form-group">
          <label class="form-label" for="status">Project Status</label>
          <select class="form-select" id="status" name="status">
            <option value="Live" <?= $status === 'Live' ? 'selected' : '' ?>>Live (Public &amp; Operational)</option>
            <option value="Development" <?= $status === 'Development' ? 'selected' : '' ?>>In Development</option>
            <option value="Archived" <?= $status === 'Archived' ? 'selected' : '' ?>>Archived (Hidden from main list)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="categoryId">Category</label>
          <select class="form-select" id="categoryId" name="category_id">
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>" <?= (string)$categoryId === (string)$cat['id'] ? 'selected' : '' ?>>
                <?= e($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group" style="margin-bottom: 8px;">
          <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.875rem; font-weight: 600;">
            <input type="checkbox" name="is_featured" value="1" <?= $isFeatured ? 'checked' : '' ?> style="width: 18px; height: 18px;">
            <span>Highlight as Featured Project</span>
          </label>
          <div class="form-hint" style="margin-left: 28px;">Displays prominently on the homepage spotlight banner.</div>
        </div>
      </div>

      <!-- Links & Dates Card -->
      <div class="admin-box">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
          External URLs &amp; Dates
        </h3>

        <div class="form-group">
          <label class="form-label" for="projectUrl">Live Website URL</label>
          <input class="form-input" id="projectUrl" type="url" name="project_url" placeholder="https://example.com" value="<?= e($projectUrl) ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="githubUrl">GitHub Repository URL</label>
          <input class="form-input" id="githubUrl" type="url" name="github_url" placeholder="https://github.com/user/repo" value="<?= e($githubUrl) ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="createdDate">Initial Launch Date</label>
          <input class="form-input" id="createdDate" type="date" name="created_date" value="<?= e($createdDate) ?>">
        </div>

        <div class="form-group">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <label class="form-label" for="lastUpdatedInput" style="margin-bottom: 0;">Last Updated Date</label>
            <button class="action-btn action-view" id="setTodayBtn" type="button" style="padding: 2px 8px; font-size: 0.75rem;">Set Today</button>
          </div>
          <input class="form-input" id="lastUpdatedInput" type="date" name="last_updated" value="<?= e($lastUpdated) ?>">
        </div>
      </div>

      <!-- Images Upload Card -->
      <div class="admin-box">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
          Project Imagery
        </h3>

        <div class="form-group">
          <label class="form-label">Project Thumbnail Image</label>
          <label class="upload-drop-zone" id="thumbnailDropZone" for="thumbnailInput" style="display: block;">
            <div class="upload-icon"><?= lucide('image', '', 32) ?></div>
            <div style="font-size: 0.875rem; font-weight: 600;">Choose file or drag &amp; drop</div>
            <div style="font-size: 0.75rem; color: var(--admin-muted); margin-top: 4px;">PNG, JPG, WebP, SVG up to 5MB</div>
            <input id="thumbnailInput" type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp,image/svg+xml" style="display: none;">
          </label>
          <img class="upload-preview" id="thumbnailPreview" src="" alt="Thumbnail Preview">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label" for="galleryInput">Additional Gallery Screenshots</label>
          <input class="form-input" id="galleryInput" type="file" name="gallery[]" multiple accept="image/jpeg,image/png,image/webp,image/svg+xml">
          <div class="form-hint">Select one or multiple images for the interactive gallery.</div>
          <div class="gallery-preview-grid" id="galleryPreviewContainer"></div>
        </div>
      </div>

      <!-- Submit Buttons -->
      <div style="display: flex; gap: 12px;">
        <button class="btn btn-primary" type="submit" style="flex: 1; justify-content: center; padding: 13px;">
          <?= lucide('plus', '', 18) ?> Save Project
        </button>
        <a class="btn btn-secondary" href="projects.php" style="justify-content: center;">
          Cancel
        </a>
      </div>
    </div>
  </div>
</form>

<?php require __DIR__ . '/footer.php'; ?>
