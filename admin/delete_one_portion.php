<?php
// Delete one_portion category items from database
require_once __DIR__ . '/../lib/db.php';

try {
    $pdo = db();
    
    // Delete items with one_portion category
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE category = 'one_portion'");
    $stmt->execute();
    $deletedCount = $stmt->rowCount();
    
    echo "<h3>✅ Deleted {$deletedCount} items from one_portion category</h3>";
    echo "<p><a href='../menu.php?table=1'>View Menu</a> | <a href='index.php'>Back to Admin</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
