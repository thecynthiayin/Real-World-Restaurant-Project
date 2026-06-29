<?php

require_once __DIR__ . '/../lib/db.php';

$pdo = db();
$seedPath = __DIR__ . '/../db/seed_menu.sql';

if (!is_file($seedPath)) {
    echo "error\n";
    echo "Missing seed file: db/seed_menu.sql\n";
    exit(1);
}

try {
    // Ensure schema supports multilingual menu (for older databases).
    $pdo->exec("ALTER TABLE menu_items ADD COLUMN IF NOT EXISTS name_en VARCHAR(255) NULL AFTER name");
    $pdo->exec("ALTER TABLE menu_items ADD COLUMN IF NOT EXISTS name_bu VARCHAR(255) NULL AFTER name_en");
    $pdo->exec("ALTER TABLE menu_items ADD COLUMN IF NOT EXISTS name_th VARCHAR(255) NULL AFTER name_bu");
    $pdo->exec("ALTER TABLE menu_items ADD COLUMN IF NOT EXISTS category VARCHAR(50) NOT NULL DEFAULT 'menu' AFTER name_th");
    $pdo->exec("ALTER TABLE menu_items ADD COLUMN IF NOT EXISTS sort_order INT NOT NULL DEFAULT 0 AFTER category");

    $sql = file_get_contents($seedPath);
    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException('Seed file is empty or unreadable.');
    }

    // Execute seed SQL statements in order.
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }

    $count = (int) $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
    echo "ok\n";
    echo "source=db/seed_menu.sql\n";
    echo "inserted=" . $count . "\n";
} catch (Throwable $e) {
    echo "error\n";
    echo $e->getMessage() . "\n";
    exit(1);
}

