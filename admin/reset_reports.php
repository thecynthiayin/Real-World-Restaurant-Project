<?php

session_start();

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/auth.php';

require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php?tab=reports');
    exit;
}

$pdo = db();

try {
    // Delete all orders and order items (this will cascade delete order_items due to foreign key)
    $pdo->exec("DELETE FROM orders");
    
    // Delete all daily reports if table exists
    try {
        $pdo->exec("DELETE FROM daily_reports");
    } catch (Exception $e) {
        // Table might not exist, ignore
    }
    
    // Delete all monthly reports if table exists
    try {
        $pdo->exec("DELETE FROM monthly_reports");
    } catch (Exception $e) {
        // Table might not exist, ignore
    }
    
    // Delete all popular items monthly data if table exists
    try {
        $pdo->exec("DELETE FROM popular_items_monthly");
    } catch (Exception $e) {
        // Table might not exist, ignore
    }
    
    header('Location: dashboard.php?tab=reports&reset=1');
    exit;
} catch (Exception $e) {
    header('Location: dashboard.php?tab=reports&error=' . urlencode($e->getMessage()));
    exit;
}
