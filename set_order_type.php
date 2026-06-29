<?php

session_start();

require_once __DIR__ . '/lib/helpers.php';

$table = null;
if (isset($_POST['table']) || isset($_GET['table'])) {
    $tableRaw = $_POST['table'] ?? $_GET['table'];
    $table = filter_var($tableRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($table !== false && $table !== null) {
        $_SESSION['table_number'] = (int) $table;
    }
}

if (!isset($_SESSION['table_number'])) {
    require_table_number();
}

$orderType = $_POST['order_type'] ?? $_GET['order_type'] ?? null;
$orderType = is_string($orderType) ? $orderType : null;

if ($orderType !== 'eat_in' && $orderType !== 'take_away') {
    http_response_code(400);
    echo "Invalid order_type.";
    exit;
}

$_SESSION['order_type'] = $orderType;
$_SESSION['order_type_table'] = (int) $_SESSION['table_number'];

$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? 'menu.php';
if (!is_string($redirect) || $redirect === '') {
    $redirect = 'menu.php';
}

// Basic open-redirect protection: allow only local paths.
if (preg_match('/^https?:\/\//i', $redirect)) {
    $redirect = 'menu.php';
}

header('Location: ' . $redirect);
exit;

