<?php

require_once __DIR__ . '/../lib/db.php';

try {
    $pdo = db();
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "db_ok\n";
    echo "tables_count=" . count($tables) . "\n";
    echo "tables=" . implode(',', $tables) . "\n";
} catch (Throwable $e) {
    echo "db_error\n";
    echo $e->getMessage() . "\n";
    exit(1);
}

