<?php
// Seed menu items and options
require_once __DIR__ . '/../lib/db.php';

try {
    $pdo = db();
    
    // Clear existing data
    $pdo->exec("DELETE FROM order_items");
    $pdo->exec("DELETE FROM orders");
    $pdo->exec("DELETE FROM menu_item_options");
    $pdo->exec("DELETE FROM menu_items");
    
    // Reset auto-increment
    $pdo->exec("ALTER TABLE menu_items AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE menu_item_options AUTO_INCREMENT = 1");
    
    // Seed menu items
    $menuSchema = file_get_contents(__DIR__ . '/../db/seed_menu.sql');
    $statements = array_filter(array_map('trim', explode(';', $menuSchema)));
    $menuCount = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement) && stripos($statement, 'START TRANSACTION') === false && stripos($statement, 'COMMIT') === false) {
            $pdo->exec($statement);
            $menuCount++;
        }
    }
    
    // Seed menu options
    $optionsSchema = file_get_contents(__DIR__ . '/../db/seed_menu_options.sql');
    $statements = array_filter(array_map('trim', explode(';', $optionsSchema)));
    $optionsCount = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement) && stripos($statement, 'START TRANSACTION') === false && stripos($statement, 'COMMIT') === false) {
            $pdo->exec($statement);
            $optionsCount++;
        }
    }
    
    echo "<h3>✅ Menu seeded successfully!</h3>";
    echo "<p>Menu items: {$menuCount}</p>";
    echo "<p>Menu options: {$optionsCount}</p>";
    echo "<p><a href='../menu.php?table=1'>View Menu</a> | <a href='index.php'>Back to Admin</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
