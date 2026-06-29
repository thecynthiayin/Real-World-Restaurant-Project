<?php

// Install reports database tables
require_once __DIR__ . '/../lib/db.php';

try {
    $pdo = db();
    
    // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/../db/reports_schema.sql');
    
    // Split by semicolons and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
} catch (Exception $e) {
    // Silent error handling
}
?>
