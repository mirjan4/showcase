<?php
if (!defined('APP_INIT')) {
    require_once __DIR__ . '/../config/app.php';
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/auth.php';

require_admin();

$adminPageTitle = $adminPageTitle ?? 'Admin Control Center';
$adminNav = $adminNav ?? 'dashboard';
$adminUser = $_SESSION['admin_username'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($adminPageTitle) ?> | Project Showcase Admin</title>
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
  <!-- Chart.js for analytics -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="admin-body">

<!-- Sidebar Navigation -->
<aside class="admin-sidebar">
  <div class="admin-sidebar-header">
    <div class="brand-icon">
      <?= lucide('layers', '', 20) ?>
    </div>
    <div style="display: flex; flex-direction: column;">
      <span style="font-weight: 800; font-size: 1.1rem; line-height: 1.2;">AdminHub</span>
      <span style="font-size: 0.75rem; color: var(--admin-muted);">Portfolio Manager</span>
    </div>
  </div>

  <ul class="admin-sidebar-nav">
    <li class="admin-nav-item <?= $adminNav === 'dashboard' ? 'active' : '' ?>">
      <a href="dashboard.php">
        <?= lucide('layout-grid', '', 18) ?> Dashboard
      </a>
    </li>
    <li class="admin-nav-item <?= $adminNav === 'projects' ? 'active' : '' ?>">
      <a href="projects.php">
        <?= lucide('briefcase', '', 18) ?> All Projects
      </a>
    </li>
    <li class="admin-nav-item <?= $adminNav === 'add-project' ? 'active' : '' ?>">
      <a href="add-project.php">
        <?= lucide('plus', '', 18) ?> Add Project
      </a>
    </li>
    <li class="admin-nav-item <?= $adminNav === 'categories' ? 'active' : '' ?>">
      <a href="categories.php">
        <?= lucide('folder', '', 18) ?> Categories
      </a>
    </li>
    <li class="admin-nav-item <?= $adminNav === 'settings' ? 'active' : '' ?>">
      <a href="settings.php">
        <?= lucide('settings', '', 18) ?> Site Settings
      </a>
    </li>
  </ul>

  <div class="admin-sidebar-footer">
    <a class="action-btn action-view" href="<?= BASE_URL ?>/index.php" target="_blank" rel="noopener noreferrer" style="justify-content: center; padding: 10px;">
      <?= lucide('external-link', '', 16) ?> View Public Site
    </a>
    <a class="action-btn action-delete" href="logout.php" style="justify-content: center; padding: 10px;">
      <?= lucide('log-out', '', 16) ?> Sign Out
    </a>
  </div>
</aside>

<!-- Main Wrapper -->
<div class="admin-main">
  <!-- Topbar -->
  <header class="admin-topbar">
    <h1 class="admin-topbar-title"><?= e($adminPageTitle) ?></h1>

    <div class="admin-user-menu">
      <div style="text-align: right; display: flex; flex-direction: column;">
        <span style="font-size: 0.875rem; font-weight: 700;"><?= e($adminUser) ?></span>
        <span style="font-size: 0.75rem; color: #10b981;">● Logged in</span>
      </div>
      <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--admin-card); border: 1px solid var(--admin-border); display: grid; place-items: center; color: var(--admin-accent);">
        <?= lucide('user', '', 20) ?>
      </div>
    </div>
  </header>

  <!-- Admin Page Content Container -->
  <div class="admin-content">
    <?php if ($flash = flash_get()): ?>
      <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>">
        <?= lucide($flash['type'] === 'success' ? 'check-circle' : 'info', '', 20) ?>
        <span><?= e($flash['message']) ?></span>
      </div>
    <?php endif; ?>
