<?php
// Migration script to add menu options support
require_once __DIR__ . '/../lib/db.php';

try {
    $pdo = db();
    
    // Check if photo column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM menu_items LIKE 'photo'");
    if ($stmt->rowCount() > 0) {
        echo "Photo column already exists in menu_items table.<br>";
    } else {
        $pdo->exec("ALTER TABLE menu_items ADD COLUMN photo VARCHAR(255) NULL AFTER status");
        echo "Photo column added to menu_items table.<br>";
    }
    
    // Check if menu_item_options table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'menu_item_options'");
    if ($stmt->rowCount() > 0) {
        echo "menu_item_options table already exists.<br>";
    } else {
        $schema = file_get_contents(__DIR__ . '/../db/menu_options_schema.sql');
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !stripos($statement, 'ALTER TABLE menu_items ADD COLUMN photo') !== false) {
                // Skip the photo column addition since we already did it above
                continue;
            }
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        // Create the table manually since we skipped the ALTER TABLE
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS menu_item_options (
              id INT AUTO_INCREMENT PRIMARY KEY,
              menu_item_id INT NOT NULL,
              option_group VARCHAR(50) NOT NULL DEFAULT 'default',
              sort_order INT NOT NULL DEFAULT 0,
              additional_price DECIMAL(10,2) NOT NULL DEFAULT 0,
              is_required BOOLEAN NOT NULL DEFAULT FALSE,
              is_multi_select BOOLEAN NOT NULL DEFAULT FALSE,
              name_en VARCHAR(255) NOT NULL,
              name_bu VARCHAR(255) NULL,
              name_th VARCHAR(255) NULL,
              created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              CONSTRAINT fk_menu_item_options_menu_item
                FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
                ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        $pdo->exec("CREATE INDEX idx_menu_item_options_menu_item ON menu_item_options(menu_item_id)");
        $pdo->exec("CREATE INDEX idx_menu_item_options_group ON menu_item_options(option_group)");
        
        echo "menu_item_options table created.<br>";
    }
    
    echo "<h3>✅ Menu options system installed successfully!</h3>";
    echo "<p><a href='index.php'>Back to Admin</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
