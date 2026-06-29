<?php
require_once __DIR__ . '/../config.php';

$stylesPath = __DIR__ . '/../assets/styles.css';
$stylesVer = is_file($stylesPath) ? (string) filemtime($stylesPath) : '1';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/styles.css?v=<?= htmlspecialchars($stylesVer, ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
</head>
<?php $pageBodyClass = isset($pageBodyClass) && is_string($pageBodyClass) ? trim($pageBodyClass) : ''; ?>
<body class="text-white<?= $pageBodyClass !== '' ? ' ' . h($pageBodyClass) : '' ?>">
  <div class="container py-4 app-shell">
    <div class="topbar mb-3">
      <a class="brand" href="index.php" title="<?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>">
        <img src="logo.jpeg" alt="<?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>" onerror="this.onerror=null;this.src='assets/logo.png';">
        <div class="brand-text">
          <div class="brand-name"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></div>
          <div class="brand-sub muted small">Scan • Order • Enjoy</div>
        </div>
      </a>
    </div>

