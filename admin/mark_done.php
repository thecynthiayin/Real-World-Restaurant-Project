<?php

session_start();

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/reports.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$orderId = $_POST['order_id'] ?? null;
$orderId = filter_var($orderId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($orderId === false || $orderId === null) {
    header('Location: index.php');
    exit;
}

$stmt = db()->prepare("UPDATE orders SET status = 'done' WHERE id = ? AND status = 'pending'");
$stmt->execute([(int) $orderId]);

// Update reports automatically when order is marked as done
if ($stmt->rowCount() > 0) {
    update_reports_on_order_completion((int) $orderId);
}

header('Location: dashboard.php?tab=orders');
exit;

