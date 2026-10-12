<?php
// ========================================================
// Admin - Website & Portfolio Settings
// ========================================================

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $settingsToSave = [
        'site_name'      => trim($_POST['site_name'] ?? ''),
        'site_tagline'   => trim($_POST['site_tagline'] ?? ''),
        'hero_title'     => trim($_POST['hero_title'] ?? ''),
        'hero_subtitle'  => trim($_POST['hero_subtitle'] ?? ''),
        'owner_name'     => trim($_POST['owner_name'] ?? ''),
        'owner_title'    => trim($_POST['owner_title'] ?? ''),
        'owner_bio'      => trim($_POST['owner_bio'] ?? ''),
        'owner_email'    => trim($_POST['owner_email'] ?? ''),
        'owner_location' => trim($_POST['owner_location'] ?? ''),
        'github_url'     => trim($_POST['github_url'] ?? ''),
        'linkedin_url'   => trim($_POST['linkedin_url'] ?? ''),
        'twitter_url'    => trim($_POST['twitter_url'] ?? ''),
        'footer_text'    => trim($_POST['footer_text'] ?? ''),
    ];

    try {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        foreach ($settingsToSave as $k => $v) {
            $stmt->execute([$k, $v]);
        }

        flash_set('success', 'Site settings updated successfully.');
        header('Location: settings.php');
        exit;
    } catch (Exception $e) {
        flash_set('danger', 'Failed to update settings: ' . $e->getMessage());
    }
}

// Fetch current settings
$currentSettings = get_all_settings($pdo);

$adminPageTitle = 'Site Settings';
$adminNav = 'settings';
require __DIR__ . '/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
  <div>
    <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0;">Portfolio Configuration</h2>
    <p style="font-size: 0.813rem; color: var(--admin-muted); margin-top: 4px;">
      Customize website identity, hero typography, personal bio, and social profiles.
    </p>
  </div>
</div>

<form method="POST" action="settings.php">
  <?= csrf_field() ?>

  <div class="form-grid">
    <!-- Left Column: Identity & Hero -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
      <div class="admin-box">
        <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 18px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
          Website Identity &amp; Hero Content
        </h3>

        <div class="form-group">
          <label class="form-label" for="siteName">Site / Brand Name</label>
          <input class="form-input" id="siteName" type="text" name="site_name" value="<?= e($currentSettings['site_name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="siteTagline">Sub-Heading / Tagline</label>
          <input class="form-input" id="siteTagline" type="text" name="site_tagline" value="<?= e($currentSettings['site_tagline'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="heroTitle">Hero Section Main Heading</label>
          <input class="form-input" id="heroTitle" type="text" name="hero_title" value="<?= e($currentSettings['hero_title'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="heroSubtitle">Hero Section Subtitle</label>
          <textarea class="form-textarea" id="heroSubtitle" name="hero_subtitle" rows="3"><?= e($currentSettings['hero_subtitle'] ?? '') ?></textarea>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label" for="footerText">Footer Copyright Notice</label>
          <input class="form-input" id="footerText" type="text" name="footer_text" value="<?= e($currentSettings['footer_text'] ?? '') ?>">
        </div>
      </div>

      <div class="admin-box">
        <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 18px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
          Developer Bio &amp; About Page
        </h3>

        <div class="form-group">
          <label class="form-label" for="ownerBio">Professional Narrative / Bio</label>
          <textarea class="form-textarea" id="ownerBio" name="owner_bio" rows="6"><?= e($currentSettings['owner_bio'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Right Column: Personal & Social Profiles -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
      <div class="admin-box">
        <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 18px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
          Personal Credentials
        </h3>

        <div class="form-group">
          <label class="form-label" for="ownerName">Your Full Name</label>
          <input class="form-input" id="ownerName" type="text" name="owner_name" value="<?= e($currentSettings['owner_name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="ownerTitle">Your Professional Title</label>
          <input class="form-input" id="ownerTitle" type="text" name="owner_title" value="<?= e($currentSettings['owner_title'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="ownerEmail">Public Contact Email</label>
          <input class="form-input" id="ownerEmail" type="email" name="owner_email" value="<?= e($currentSettings['owner_email'] ?? '') ?>">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label" for="ownerLocation">Location / City</label>
          <input class="form-input" id="ownerLocation" type="text" name="owner_location" value="<?= e($currentSettings['owner_location'] ?? '') ?>">
        </div>
      </div>

      <div class="admin-box">
        <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 18px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
          Social &amp; Repository Profiles
        </h3>

        <div class="form-group">
          <label class="form-label" for="githubUrl">GitHub Profile URL</label>
          <input class="form-input" id="githubUrl" type="url" name="github_url" placeholder="https://github.com/username" value="<?= e($currentSettings['github_url'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="linkedinUrl">LinkedIn Profile URL</label>
          <input class="form-input" id="linkedinUrl" type="url" name="linkedin_url" placeholder="https://linkedin.com/in/username" value="<?= e($currentSettings['linkedin_url'] ?? '') ?>">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label" for="twitterUrl">Twitter / X Profile URL</label>
          <input class="form-input" id="twitterUrl" type="url" name="twitter_url" placeholder="https://x.com/username" value="<?= e($currentSettings['twitter_url'] ?? '') ?>">
        </div>
      </div>

      <button class="btn btn-primary" type="submit" style="justify-content: center; padding: 13px;">
        <?= lucide('check', '', 18) ?> Save Configuration
      </button>
    </div>
  </div>
</form>

<?php require __DIR__ . '/footer.php'; ?>
