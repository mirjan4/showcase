<?php
// ========================================================
// Admin - Edit Existing Project
// ========================================================

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/auth.php';

require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    flash_set('danger', 'Invalid project identifier specified.');
    header('Location: projects.php');
    exit;
}

// Fetch project
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    flash_set('danger', 'Project not found.');
    header('Location: projects.php');
    exit;
}

$errors = [];

// Handle individual gallery image deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_gallery_image') {
    verify_csrf();
    $imageId = filter_input(INPUT_POST, 'image_id', FILTER_VALIDATE_INT);
    if ($imageId) {
        $imgFetch = $pdo->prepare("SELECT image_path FROM project_images WHERE id = ? AND project_id = ?");
        $imgFetch->execute([$imageId, $id]);
        $row = $imgFetch->fetch();
        if ($row) {
            $filePath = GALLERY_UPLOADS_DIR . '/' . basename($row['image_path']);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $delStmt = $pdo->prepare("DELETE FROM project_images WHERE id = ?");
            $delStmt->execute([$imageId]);
            flash_set('success', 'Gallery image deleted.');
            header("Location: edit-project.php?id=$id");
            exit;
        }
    }
}

// Handle Full Project Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'update_project')) {
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

    if ($name === '') {
        $errors[] = 'Project title is required.';
    }

    if ($slug === '') {
        $slug = slugify($name);
    }

    // Check slug uniqueness (excluding current project)
    $slugCheck = $pdo->prepare("SELECT id FROM projects WHERE slug = ? AND id <> ?");
    $slugCheck->execute([$slug, $id]);
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

    // Handle Thumbnail Replacement
    $newThumbnail = $project['thumbnail'];
    if (!empty($_FILES['thumbnail']['name'])) {
        $uploadRes = secure_upload($_FILES['thumbnail'], PROJECT_UPLOADS_DIR);
        if ($uploadRes['success']) {
            // Delete old thumbnail if different
            if ($project['thumbnail'] && file_exists(PROJECT_UPLOADS_DIR . '/' . basename($project['thumbnail']))) {
                @unlink(PROJECT_UPLOADS_DIR . '/' . basename($project['thumbnail']));
            }
            $newThumbnail = $uploadRes['filename'];
        } else {
            $errors[] = 'Thumbnail upload error: ' . $uploadRes['error'];
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $updateSql = "UPDATE projects SET
                name = ?, slug = ?, category_id = ?, short_description = ?,
                full_description = ?, features = ?, thumbnail = ?,
                project_url = ?, github_url = ?, technologies = ?,
                created_date = ?, last_updated = ?, status = ?, is_featured = ?
                WHERE id = ?";

            $stmt = $pdo->prepare($updateSql);
            $stmt->execute([
                $name, $slug, $categoryId, $shortDescription,
                $fullDescription, $features, $newThumbnail,
                $projectUrl ?: null, $githubUrl ?: null, $technologies,
                $createdDate, $lastUpdated, $status, $isFeatured, $id
            ]);

            // Add new gallery screenshots if uploaded
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
                            $imgStmt->execute([$id, $galleryUpload['filename'], 10 + $i]);
                        }
                    }
                }
            }

            $pdo->commit();
            flash_set('success', "Project \"$name\" updated successfully!");
            header("Location: edit-project.php?id=$id");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Database update error: ' . $e->getMessage();
        }
    }
}

// Reload fresh gallery images
$galleryImages = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
$galleryImages->execute([$id]);
$gallery = $galleryImages->fetchAll();

// Categories for select dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll();

$adminPageTitle = 'Edit: ' . $project['name'];
$adminNav = 'projects';
require __DIR__ . '/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
  <div>
    <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0;">Edit Project</h2>
    <div style="font-size: 0.813rem; color: var(--admin-muted); margin-top: 4px;">
      Last modified: <?= e(format_project_date($project['last_updated'], true)) ?> &bull; Views: <?= (int)$project['view_count'] ?>
    </div>
  </div>
  <div style="display: flex; gap: 10px;">
    <a class="btn btn-secondary" href="<?= BASE_URL ?>/project.php?slug=<?= urlencode($project['slug']) ?>" target="_blank" rel="noopener noreferrer">
      <?= lucide('eye', '', 16) ?> View Public Page
    </a>
    <a class="btn btn-secondary" href="projects.php">
      <?= lucide('arrow-left', '', 16) ?> Back to Projects
    </a>
  </div>
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

<form method="POST" action="edit-project.php?id=<?= $id ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="update_project">
  <input type="hidden" name="id" value="<?= $id ?>">

  <div class="form-grid">
    <!-- Left Column: Core Content -->
    <div class="admin-box">
      <div class="form-group">
        <label class="form-label" for="projectName">
          Project Name <span class="required">*</span>
        </label>
        <input class="form-input" id="projectName" type="text" name="name" required value="<?= e($project['name']) ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="projectSlug">
          URL Slug <span class="required">*</span>
        </label>
        <input class="form-input" id="projectSlug" type="text" name="slug" required data-manual-edit="true" value="<?= e($project['slug']) ?>">
        <div class="form-hint">Unique identifier for the details page URL.</div>
      </div>

      <div class="form-group">
        <label class="form-label" for="shortDescription">
          Short Description (Summary Card) <span class="required">*</span>
        </label>
        <textarea class="form-textarea" id="shortDescription" name="short_description" required rows="3"><?= e($project['short_description']) ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label" for="fullDescription">
          Full Project Description &amp; Narrative <span class="required">*</span>
        </label>
        <textarea class="form-textarea" id="fullDescription" name="full_description" rows="9"><?= e($project['full_description']) ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label" for="features">
          Key Features &amp; Capabilities (One per line)
        </label>
        <textarea class="form-textarea" id="features" name="features" rows="5"><?= e($project['features'] ?? '') ?></textarea>
        <div class="form-hint">Rendered as a stylish checklist on the project details page.</div>
      </div>

      <div class="form-group">
        <label class="form-label" for="technologies">
          Technology Stack Tags (Comma Separated)
        </label>
        <input class="form-input" id="technologies" type="text" name="technologies" value="<?= e($project['technologies']) ?>">
      </div>
    </div>

    <!-- Right Column: Status, Dates & Media -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
      <!-- Publishing Controls -->
      <div class="admin-box">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
          Publishing Controls
        </h3>

        <div class="form-group">
          <label class="form-label" for="status">Project Status</label>
          <select class="form-select" id="status" name="status">
            <option value="Live" <?= $project['status'] === 'Live' ? 'selected' : '' ?>>Live (Public &amp; Operational)</option>
            <option value="Development" <?= $project['status'] === 'Development' ? 'selected' : '' ?>>In Development</option>
            <option value="Archived" <?= $project['status'] === 'Archived' ? 'selected' : '' ?>>Archived (Hidden)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="categoryId">Category</label>
          <select class="form-select" id="categoryId" name="category_id">
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>" <?= (string)$project['category_id'] === (string)$cat['id'] ? 'selected' : '' ?>>
                <?= e($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group" style="margin-bottom: 8px;">
          <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.875rem; font-weight: 600;">
            <input type="checkbox" name="is_featured" value="1" <?= !empty($project['is_featured']) ? 'checked' : '' ?> style="width: 18px; height: 18px;">
            <span>Highlight as Featured Project</span>
          </label>
        </div>
      </div>

      <!-- Links & Dates -->
      <div class="admin-box">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
          External URLs &amp; Dates
        </h3>

        <div class="form-group">
          <label class="form-label" for="projectUrl">Live Website URL</label>
          <input class="form-input" id="projectUrl" type="url" name="project_url" value="<?= e($project['project_url'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="githubUrl">GitHub Repository URL</label>
          <input class="form-input" id="githubUrl" type="url" name="github_url" value="<?= e($project['github_url'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="createdDate">Initial Launch Date</label>
          <input class="form-input" id="createdDate" type="date" name="created_date" value="<?= e($project['created_date'] ?? '') ?>">
        </div>

        <div class="form-group">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <label class="form-label" for="lastUpdatedInput" style="margin-bottom: 0;">Last Updated Date</label>
            <button class="action-btn action-view" id="setTodayBtn" type="button" style="padding: 2px 8px; font-size: 0.75rem;">Set Today</button>
          </div>
          <input class="form-input" id="lastUpdatedInput" type="date" name="last_updated" value="<?= e($project['last_updated'] ?? date('Y-m-d')) ?>">
        </div>
      </div>

      <!-- Imagery & Gallery -->
      <div class="admin-box">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
          Cover Thumbnail
        </h3>

        <?php if ($project['thumbnail']): ?>
          <div style="margin-bottom: 14px;">
            <div style="font-size: 0.75rem; color: var(--admin-muted); margin-bottom: 6px;">Current Thumbnail:</div>
            <img src="<?= e(project_image($project['thumbnail'])) ?>" alt="Current Thumb" style="width: 100%; max-height: 160px; object-fit: cover; border-radius: 8px; border: 1px solid var(--admin-border);">
          </div>
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Replace Thumbnail</label>
          <label class="upload-drop-zone" id="thumbnailDropZone" for="thumbnailInput" style="display: block;">
            <div class="upload-icon"><?= lucide('image', '', 28) ?></div>
            <div style="font-size: 0.813rem; font-weight: 600;">Choose replacement file</div>
            <input id="thumbnailInput" type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp,image/svg+xml" style="display: none;">
          </label>
          <img class="upload-preview" id="thumbnailPreview" src="" alt="New Thumbnail Preview">
        </div>
      </div>

      <!-- Gallery Screenshots Management -->
      <div class="admin-box">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
          Gallery Screenshots (<?= count($gallery) ?>)
        </h3>

        <?php if (!empty($gallery)): ?>
          <div class="gallery-preview-grid" style="margin-bottom: 16px;">
            <?php foreach ($gallery as $gImg): ?>
              <div class="gallery-thumb-item">
                <img src="<?= e(gallery_image($gImg['image_path'])) ?>" alt="Gallery item">
                <button class="delete-gallery-img-btn" type="submit" form="delImgForm_<?= $gImg['id'] ?>" title="Delete screenshot">
                  &times;
                </button>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label" for="galleryInput">Add More Screenshots</label>
          <input class="form-input" id="galleryInput" type="file" name="gallery[]" multiple accept="image/jpeg,image/png,image/webp,image/svg+xml">
          <div class="gallery-preview-grid" id="galleryPreviewContainer"></div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div style="display: flex; gap: 12px;">
        <button class="btn btn-primary" type="submit" style="flex: 1; justify-content: center; padding: 13px;">
          <?= lucide('check', '', 18) ?> Save Changes
        </button>
        <a class="btn btn-secondary" href="projects.php" style="justify-content: center;">
          Cancel
        </a>
      </div>
    </div>
  </div>
</form>

<!-- Separate Hidden Forms for Deleting Individual Gallery Images -->
<?php if (!empty($gallery)): ?>
  <?php foreach ($gallery as $gImg): ?>
    <form id="delImgForm_<?= $gImg['id'] ?>" method="POST" action="edit-project.php?id=<?= $id ?>" onsubmit="return confirm('Remove this screenshot from the gallery?');">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="delete_gallery_image">
      <input type="hidden" name="image_id" value="<?= (int)$gImg['id'] ?>">
    </form>
  <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
