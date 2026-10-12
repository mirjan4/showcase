<?php
// ========================================================
// Admin - Delete Project Action
// ========================================================

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed. Delete must be performed via POST.');
}

verify_csrf();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    flash_set('danger', 'Invalid project identifier specified.');
    header('Location: projects.php');
    exit;
}

// Fetch project and its gallery images to clean up files
$stmt = $pdo->prepare("SELECT name, thumbnail FROM projects WHERE id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    flash_set('danger', 'Project not found or already deleted.');
    header('Location: projects.php');
    exit;
}

$galleryStmt = $pdo->prepare("SELECT image_path FROM project_images WHERE project_id = ?");
$galleryStmt->execute([$id]);
$galleryImages = $galleryStmt->fetchAll();

try {
    $pdo->beginTransaction();

    // Delete DB record
    $del = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $del->execute([$id]);

    $pdo->commit();

    // Safely remove thumbnail file from disk
    if (!empty($project['thumbnail'])) {
        $thumbPath = PROJECT_UPLOADS_DIR . '/' . basename($project['thumbnail']);
        if (file_exists($thumbPath)) {
            @unlink($thumbPath);
        }
    }

    // Safely remove gallery image files from disk
    foreach ($galleryImages as $img) {
        if (!empty($img['image_path'])) {
            $gPath = GALLERY_UPLOADS_DIR . '/' . basename($img['image_path']);
            if (file_exists($gPath)) {
                @unlink($gPath);
            }
        }
    }

    flash_set('success', "Project \"{$project['name']}\" and associated media deleted successfully.");

} catch (Exception $e) {
    $pdo->rollBack();
    flash_set('danger', 'Failed to delete project: ' . $e->getMessage());
}

header('Location: projects.php');
exit;
