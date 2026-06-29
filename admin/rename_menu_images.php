<?php
/**
 * Menu Image Renaming Helper
 * 
 * This script shows you the mapping between menu item IDs and their names.
 * Use this to rename your numbered images to match the database IDs.
 * 
 * Expected format: assets/menu/{category}/{id}.jpg
 * Example: assets/menu/breakfast/1.jpg
 */

require_once __DIR__ . '/../lib/db.php';

// Fetch all menu items
$items = db()->query("SELECT id, name, name_en, category FROM menu_items ORDER BY id ASC")->fetchAll();

// Group by category
$byCategory = [];
foreach ($items as $item) {
    $cat = $item['category'];
    if (!isset($byCategory[$cat])) {
        $byCategory[$cat] = [];
    }
    $byCategory[$cat][] = $item;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Image Renaming Guide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4">Menu Image Renaming Guide</h1>
        <p class="mb-4">Rename your numbered images to match these database IDs:</p>
        
        <div class="alert alert-info">
            <strong>Expected format:</strong> <code>assets/menu/{category}/{id}.jpg</code><br>
            <strong>Example:</strong> <code>assets/menu/breakfast/1.jpg</code>
        </div>

        <?php foreach ($byCategory as $category => $items): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= strtoupper($category) ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Target Filename</th>
                                <th>Item Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><strong><?= $item['id'] ?></strong></td>
                                    <td><code>assets/menu/<?= $category ?>/<?= $item['id'] ?>.jpg</code></td>
                                    <td><?= htmlspecialchars($item['name_en']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="card mt-4">
            <div class="card-header bg-warning">
                <h4 class="mb-0">Batch Rename Commands (Windows)</h4>
            </div>
            <div class="card-body">
                <p>Copy these commands to rename your images in bulk:</p>
                <pre class="bg-dark text-light p-3 rounded">
<?php
foreach ($items as $item) {
    $category = $item['category'];
    $id = $item['id'];
    echo "ren \"{$id}.jpg\" \"assets\\menu\\{$category}\\{$id}.jpg\"\n";
}
?>
                </pre>
            </div>
        </div>
    </div>
</body>
</html>
