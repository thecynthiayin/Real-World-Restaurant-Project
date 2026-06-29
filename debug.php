<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre>";
echo "PHP version: " . PHP_VERSION . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n\n";

echo "=== LOADED EXTENSIONS ===\n";
$exts = get_loaded_extensions();
sort($exts);
echo implode(', ', $exts) . "\n\n";

echo "=== PDO DRIVERS ===\n";
if (extension_loaded('PDO')) {
    echo "PDO loaded: YES\n";
    echo "Available drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
} else {
    echo "PDO loaded: NO\n";
}
echo "pdo_mysql loaded: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "\n\n";

echo "=== ENV VARIABLES ===\n";
$keys = ['DB_HOST','DB_PORT','DB_NAME','DB_USER','DB_PASS','MYSQLHOST','MYSQLPORT','MYSQLDATABASE','MYSQLUSER','MYSQLPASSWORD'];
foreach ($keys as $k) {
    $val = getenv($k);
    if ($k === 'DB_PASS' || $k === 'MYSQLPASSWORD') {
        echo "$k = " . ($val !== false ? '(set, hidden)' : '(not set)') . "\n";
    } else {
        echo "$k = " . ($val !== false ? $val : '(not set)') . "\n";
    }
}

echo "\n=== CONFIG VALUES ===\n";
require_once __DIR__ . '/config.php';
echo "DB_HOST = " . DB_HOST . "\n";
echo "DB_PORT = " . DB_PORT . "\n";
echo "DB_NAME = " . DB_NAME . "\n";
echo "DB_USER = " . DB_USER . "\n";
echo "DB_PASS = " . (DB_PASS !== '' ? '(set, hidden)' : '(empty)') . "\n";

echo "\n=== DB CONNECTION TEST ===\n";
try {
    require_once __DIR__ . '/lib/db.php';
    $pdo = db();
    echo "Connection: OK\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found: " . implode(', ', $tables) . "\n";
} catch (Throwable $e) {
    echo "Connection FAILED: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
}

echo "\n=== SESSION TEST ===\n";
session_start();
echo "Session status: " . session_status() . " (2 = active)\n";
echo "Session save path: " . session_save_path() . "\n";

echo "</pre>";
