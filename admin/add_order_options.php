<?php
// Add selected_options column to order_items table
require_once __DIR__ . '/../lib/db.php';

try {
    $pdo = db();
    
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM order_items LIKE 'selected_options'");
    if ($stmt->rowCount() > 0) {
        echo "<h3>✅ Column already exists</h3>";
        echo "<p>selected_options column is already in order_items table.</p>";
    } else {
        $pdo->exec("ALTER TABLE order_items ADD COLUMN selected_options JSON NULL COMMENT 'Selected menu item options as JSON'");
        echo "<h3>✅ Column added successfully</h3>";
        echo "<p>selected_options column added to order_items table.</p>";
    }
    
    echo "<p><a href='index.php'>Back to Admin</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
