<?php
// ========================================================
// Initial Setup Utility: Create or Reset Administrator
// (For initial local setup on XAMPP)
// ========================================================

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$message = '';
$isSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if (strlen($username) < 3) {
        $message = 'Username must contain at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please provide a valid email address.';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters long.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO admins (username, email, password, full_name)
                VALUES (?, ?, ?, 'System Administrator')
                ON DUPLICATE KEY UPDATE password = VALUES(password), email = VALUES(email)");
            $stmt->execute([$username, $email, $hashed]);
            $isSuccess = true;
            $message = "Admin account \"$username\" has been successfully initialized/updated! You can now log in.";
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Initialize Administrator | Project Showcase</title>
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
</head>
<body style="background: var(--admin-bg);">

<div class="admin-login-wrapper">
  <div class="admin-login-card" style="max-width: 480px;">
    <div class="login-header">
      <div class="brand-icon" style="margin: 0 auto; width: 48px; height: 48px;">
        <?= lucide('settings', '', 24) ?>
      </div>
      <h1>Admin Initialization</h1>
      <p>Create or reset your administrator account credentials.</p>
    </div>

    <?php if ($message): ?>
      <div class="alert <?= $isSuccess ? 'alert-success' : 'alert-danger' ?>" style="margin-bottom: 20px;">
        <?= lucide($isSuccess ? 'check-circle' : 'info', '', 18) ?>
        <span><?= e($message) ?></span>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input class="form-input" id="username" type="text" name="username" required value="admin" minlength="3">
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Admin Email</label>
        <input class="form-input" id="email" type="email" name="email" required value="admin@example.com">
      </div>

      <div class="form-group" style="margin-bottom: 28px;">
        <label class="form-label" for="password">Password (Minimum 8 characters)</label>
        <input class="form-input" id="password" type="password" name="password" required minlength="8" placeholder="••••••••">
      </div>

      <button class="btn btn-primary" type="submit" style="width: 100%; justify-content: center; padding: 13px;">
        <?= lucide('check-circle', '', 18) ?> Set Administrator Credentials
      </button>
    </form>

    <div style="margin-top: 24px; text-align: center; border-top: 1px solid var(--admin-border); padding-top: 18px; display: flex; justify-content: space-between;">
      <a href="login.php" style="color: var(--admin-accent); font-size: 0.813rem;">
        Go to Login →
      </a>
      <a href="<?= BASE_URL ?>/index.php" style="color: var(--admin-muted); font-size: 0.813rem;">
        Back to Showcase
      </a>
    </div>
  </div>
</div>

</body>
</html>
