<?php
/**
 * Menu Image Organizer
 * 
 * Automatically renames and moves images from assets/menu/photos
 * to the correct category folders based on database IDs.
 * 
 * Source: assets/menu/photos/{number}.jpg or item_{number}.jpg
 * Target: assets/menu/{category}/{id}.jpg
 */

require_once __DIR__ . '/../lib/db.php';

$sourceDir = __DIR__ . '/../assets/menu/photos';
$targetBase = __DIR__ . '/../assets/menu';

// Fetch all menu items
$items = db()->query("SELECT id, name, name_en, category FROM menu_items ORDER BY id ASC")->fetchAll();

// Create category mapping
$idToCategory = [];
foreach ($items as $item) {
    $idToCategory[$item['id']] = $item['category'];
}

// Ensure category directories exist
$categories = array_unique(array_column($items, 'category'));
foreach ($categories as $cat) {
    $catDir = $targetBase . '/' . $cat;
    if (!is_dir($catDir)) {
        mkdir($catDir, 0755, true);
    }
}

// Process images
$renamed = [];
$skipped = [];
$errors = [];

$files = scandir($sourceDir);
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    
    // Extract ID from filename
    $id = null;
    if (preg_match('/^(\d+)\.jpg$/', $file, $matches)) {
        $id = (int)$matches[1];
    } elseif (preg_match('/^item[_-](\d+)\.jpg$/', $file, $matches)) {
        $id = (int)$matches[1];
    }
    
    if ($id === null) continue;
    
    // Check if ID exists in database
    if (!isset($idToCategory[$id])) {
        $skipped[] = "$file (ID $id not in database)";
        continue;
    }
    
    $category = $idToCategory[$id];
    $sourcePath = $sourceDir . '/' . $file;
    $targetPath = $targetBase . '/' . $category . '/' . $id . '.jpg';
    
    // Skip if target already exists
    if (file_exists($targetPath)) {
        $skipped[] = "$file (target already exists)";
        continue;
    }
    
    // Rename/move file
    if (rename($sourcePath, $targetPath)) {
        $renamed[] = "$file → $category/$id.jpg";
    } else {
        $errors[] = "$file (failed to move)";
    }
}

// Display results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Image Organizer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4">Menu Image Organizer</h1>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Renamed</div>
                    <div class="card-body">
                        <h2 class="card-title"><?= count($renamed) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">Skipped</div>
                    <div class="card-body">
                        <h2 class="card-title"><?= count($skipped) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-header">Errors</div>
                    <div class="card-body">
                        <h2 class="card-title"><?= count($errors) ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($renamed)): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Successfully Renamed</h4>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <?php foreach ($renamed as $item): ?>
                            <li><?= htmlspecialchars($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($skipped)): ?>
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h4 class="mb-0">Skipped</h4>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <?php foreach ($skipped as $item): ?>
                            <li><?= htmlspecialchars($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">Errors</h4>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <?php foreach ($errors as $item): ?>
                            <li><?= htmlspecialchars($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="rename_menu_images.php" class="btn btn-primary">View Image Guide</a>
            <a href="index.php" class="btn btn-secondary">Back to Admin</a>
        </div>
    </div>
</body>
</html>
