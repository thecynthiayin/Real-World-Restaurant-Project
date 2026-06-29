<?php
/**
 * Rename Photos to item_(id) Format
 * 
 * Renames all images in assets/menu/photos to item_{id}.jpg format
 * Example: 12.jpg → item_12.jpg
 */

$sourceDir = __DIR__ . '/../assets/menu/photos';

$renamed = [];
$skipped = [];
$errors = [];

$files = scandir($sourceDir);
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    
    // Skip if already in item_ format
    if (preg_match('/^item_\d+\.jpg$/', $file)) {
        $skipped[] = "$file (already in correct format)";
        continue;
    }
    
    // Extract ID from filename
    if (preg_match('/^(\d+)\.jpg$/', $file, $matches)) {
        $id = $matches[1];
        $newName = 'item_' . $id . '.jpg';
        $sourcePath = $sourceDir . '/' . $file;
        $targetPath = $sourceDir . '/' . $newName;
        
        // Skip if target already exists
        if (file_exists($targetPath)) {
            $skipped[] = "$file (target $newName already exists)";
            continue;
        }
        
        // Rename file
        if (rename($sourcePath, $targetPath)) {
            $renamed[] = "$file → $newName";
        } else {
            $errors[] = "$file (failed to rename)";
        }
    } else {
        $skipped[] = "$file (not a numbered jpg file)";
    }
}

// Display results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rename Photos to item_ Format</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4">Rename Photos to item_(id) Format</h1>
        
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
            <a href="organize_menu_images.php" class="btn btn-primary">Next: Organize to Category Folders</a>
            <a href="index.php" class="btn btn-secondary">Back to Admin</a>
        </div>
    </div>
</body>
</html>
