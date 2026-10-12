<?php
// ========================================================
// Application Configuration & Globals
// ========================================================

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Ensure base URL can be determined accurately for both public and admin routes
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

// If running in /admin/ subfolder, strip /admin from the root path
$rootDir = preg_replace('#/admin$#i', '', $scriptDir);
$rootDir = rtrim($rootDir, '/');

define('BASE_PATH', $rootDir === '' ? '' : $rootDir);
define('BASE_URL', $scheme . $httpHost . BASE_PATH);
define('UPLOADS_URL', BASE_URL . '/uploads');
define('ASSETS_URL', BASE_URL . '/assets');

// Upload directory paths
define('ROOT_DIR', dirname(__DIR__));
define('PROJECT_UPLOADS_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'projects');
define('GALLERY_UPLOADS_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'gallery');

// Allowed image MIME types and extensions
define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/svg+xml' => 'svg'
]);
define('MAX_UPLOAD_SIZE_BYTES', 5 * 1024 * 1024); // 5 MB
