<?php

session_start();

// Set timezone to Bangkok
date_default_timezone_set('Asia/Bangkok');

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/reports.php';

require_admin_login();

$pdo = db();
$tab = $_GET['tab'] ?? 'orders';

// Handle form submissions for menu CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'menu') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM menu_item_options WHERE menu_item_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM menu_items WHERE id = ?")->execute([$id]);
            header('Location: dashboard.php?tab=menu&deleted=1');
            exit;
        }
    } elseif ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = $_POST['name'] ?? '';
        $name_en = $_POST['name_en'] ?? '';
        $name_bu = $_POST['name_bu'] ?? '';
        $name_th = $_POST['name_th'] ?? '';
        $category = $_POST['category'] ?? '';
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $status = $_POST['status'] ?? 'available';
        
        $image = $_POST['existing_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/images/menu/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'item_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $image = 'assets/images/menu/' . $filename;
            }
        }
        
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE menu_items SET name = ?, name_en = ?, name_bu = ?, name_th = ?, category = ?, sort_order = ?, price = ?, image = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $name_en, $name_bu, $name_th, $category, $sort_order, $price, $image, $status, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO menu_items (name, name_en, name_bu, name_th, category, sort_order, price, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $name_en, $name_bu, $name_th, $category, $sort_order, $price, $image, $status]);
        }
        
        header('Location: dashboard.php?tab=menu&saved=1');
        exit;
    }
}

// Handle options CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'options') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM menu_item_options WHERE id = ?")->execute([$id]);
            header('Location: dashboard.php?tab=options&deleted=1');
            exit;
        }
    } elseif ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $menu_item_id = (int)($_POST['menu_item_id'] ?? 0);
        $option_group = $_POST['option_group'] ?? '';
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $additional_price = (float)($_POST['additional_price'] ?? 0);
        $is_required = (int)($_POST['is_required'] ?? 0);
        $is_multi_select = (int)($_POST['is_multi_select'] ?? 0);
        $name_en = $_POST['name_en'] ?? '';
        $name_bu = $_POST['name_bu'] ?? '';
        $name_th = $_POST['name_th'] ?? '';
        
        if ($menu_item_id > 0) {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE menu_item_options SET menu_item_id = ?, option_group = ?, sort_order = ?, additional_price = ?, is_required = ?, is_multi_select = ?, name_en = ?, name_bu = ?, name_th = ? WHERE id = ?");
                $stmt->execute([$menu_item_id, $option_group, $sort_order, $additional_price, $is_required, $is_multi_select, $name_en, $name_bu, $name_th, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$menu_item_id, $option_group, $sort_order, $additional_price, $is_required, $is_multi_select, $name_en, $name_bu, $name_th]);
            }
            header('Location: dashboard.php?tab=options&saved=1');
            exit;
        }
    }
}

// Fetch data based on tab
$orders = [];
if ($tab === 'orders') {
    $orders = $pdo->query(
        "SELECT id, table_number, order_type, total_price, status, created_at,
                UNIX_TIMESTAMP(created_at) AS created_at_ts,
                TIMESTAMPDIFF(MINUTE, created_at, NOW()) AS waiting_minutes
         FROM orders
         WHERE status = 'pending'
         ORDER BY created_at ASC"
    )->fetchAll();
    $serverNowTs = (int) $pdo->query("SELECT UNIX_TIMESTAMP()")->fetchColumn();
    
    $itemsByOrder = [];
    if (count($orders) > 0) {
        $ids = array_map(fn ($o) => (int)$o['id'], $orders);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare(
            "SELECT oi.order_id, oi.quantity, oi.selected_options, mi.*
             FROM order_items oi
             JOIN menu_items mi ON mi.id = oi.item_id
             WHERE oi.order_id IN ($placeholders)
             ORDER BY oi.order_id ASC, mi.name ASC"
        );
        $stmt->execute($ids);
        foreach ($stmt->fetchAll() as $row) {
            $oid = (int) $row['order_id'];
            if (!isset($itemsByOrder[$oid])) {
                $itemsByOrder[$oid] = [];
            }
            $selectedOptions = [];
            if (!empty($row['selected_options'])) {
                $decoded = json_decode($row['selected_options'], true);
                if (is_array($decoded)) {
                    $selectedOptions = $decoded;
                }
            }
            $row['selected_options'] = $selectedOptions;
            $itemsByOrder[$oid][] = $row;
        }
    }
}

$menuItems = [];
$categories = [];
if ($tab === 'menu' || $tab === 'options' || $tab === 'demo') {
    $menuItems = $pdo->query("SELECT * FROM menu_items ORDER BY category, sort_order")->fetchAll();
    $categories = $pdo->query("SELECT DISTINCT category FROM menu_items ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
}

$editItem = null;
if ($tab === 'menu') {
    $editId = (int)($_GET['edit'] ?? 0);
    if ($editId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
        $stmt->execute([$editId]);
        $editItem = $stmt->fetch();
    }
}

$optionsByItem = [];
$allOptions = [];
if ($tab === 'options') {
    $allOptions = $pdo->query("
        SELECT mio.*, mi.name as menu_item_name, mi.category 
        FROM menu_item_options mio
        JOIN menu_items mi ON mi.id = mio.menu_item_id
        ORDER BY mi.category, mi.sort_order, mio.option_group, mio.sort_order
    ")->fetchAll();
    
    foreach ($allOptions as $opt) {
        $itemId = $opt['menu_item_id'];
        if (!isset($optionsByItem[$itemId])) {
            $optionsByItem[$itemId] = [
                'item_name' => $opt['menu_item_name'],
                'category' => $opt['category'],
                'options' => []
            ];
        }
        $optionsByItem[$itemId]['options'][] = $opt;
    }
    
    $editOption = null;
    $editId = (int)($_GET['edit'] ?? 0);
    if ($editId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM menu_item_options WHERE id = ?");
        $stmt->execute([$editId]);
        $editOption = $stmt->fetch();
    }
}

// Reports data
$report = [];
$reportTitle = '';
if ($tab === 'reports') {
    $reportType = $_GET['type'] ?? 'daily';
    $date = $_GET['date'] ?? date('Y-m-d');
    $month = $_GET['month'] ?? date('Y-m');
    
    if ($reportType === 'daily') {
        $dateObj = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj || $dateObj > new DateTime()) {
            $date = date('Y-m-d');
        }
        $reportTitle = 'Daily Report - ' . date('F j, Y', strtotime($date));
        $report = get_daily_report($date);
        if (!$report) {
            $report = generate_daily_report($date);
        }
    } else {
        $monthObj = DateTime::createFromFormat('Y-m', $month);
        if (!$monthObj || $monthObj > new DateTime()) {
            $month = date('Y-m');
        }
        $reportTitle = 'Monthly Report - ' . date('F Y', strtotime($month . '-01'));
        $report = get_monthly_report($month);
        if (!$report) {
            $report = generate_monthly_report($month);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: #000000;
            color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .sidebar {
            background: #1a1a1a;
            min-height: 100vh;
            padding: 20px;
            border-right: 1px solid #333333;
        }
        .sidebar a {
            color: #ffffff;
            text-decoration: none;
            display: block;
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
            transition: all 0.2s;
            font-weight: 500;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #f4c542;
            color: #000000;
        }
        .main-content {
            padding: 30px;
            height: 100vh;
            overflow-y: auto;
            background: #000000;
        }
        .card {
            background: #1a1a1a;
            border: 1px solid #333333;
            border-radius: 12px;
            margin-bottom: 20px;
            color: #ffffff;
        }
        .card-header {
            background: #333333;
            border: none;
            border-radius: 12px 12px 0 0;
            color: #f4c542;
            font-weight: 600;
        }
        .card-body {
            color: #ffffff;
        }
        .table {
            color: #ffffff;
        }
        .table th {
            background: #ffffff;
            border-color: #333333;
            color: #000000;
            font-weight: 600;
        }
        .table td {
            border-color: #333333;
            vertical-align: middle;
            background: #ffffff;
            color: #000000;
        }
        .text-muted {
            color: #f4c542 !important;
        }
        .btn-gold {
            background: #f4c542 !important;
            color: #000000 !important;
            border: none;
            font-weight: 600;
        }
        .btn-gold:hover {
            background: #f97316 !important;
            color: #000000 !important;
        }
        .btn {
            background: #333333 !important;
            color: #ffffff !important;
            border: 1px solid #444444;
            font-weight: 500;
        }
        .btn:hover {
            background: #444444 !important;
            color: #ffffff !important;
        }
        .btn-success {
            background: #f4c542 !important;
            color: #000000 !important;
            border: none;
        }
        .btn-danger {
            background: #f97316 !important;
            color: #000000 !important;
            border: none;
        }
        .btn-primary {
            background: #f4c542 !important;
            color: #000000 !important;
            border: none;
        }
        .btn-secondary {
            background: #333333 !important;
            color: #ffffff !important;
            border: 1px solid #444444;
        }
        .btn-warning {
            background: #f97316 !important;
            color: #000000 !important;
            border: none;
        }
        .btn-info {
            background: #f4c542 !important;
            color: #000000 !important;
            border: none;
        }
        .btn-light {
            background: #ffffff !important;
            color: #000000 !important;
            border: none;
        }
        .btn-dark {
            background: #000000 !important;
            color: #ffffff !important;
            border: 1px solid #333333;
        }
        .btn-report-daily {
            background: #f4c542 !important;
            color: #000000 !important;
            border: none;
            font-weight: 600;
        }
        .btn-report-monthly {
            background: #f97316 !important;
            color: #000000 !important;
            border: none;
            font-weight: 600;
        }
        .btn-report-daily:hover, .btn-report-monthly:hover {
            opacity: 0.8;
        }
        .btn-add-option {
            background: #f4c542 !important;
            color: #000000 !important;
            border: none;
            font-weight: 600;
        }
        .btn-edit {
            background: #f97316 !important;
            color: #000000 !important;
            border: none;
            font-weight: 600;
        }
        .btn-delete {
            background: #f97316 !important;
            color: #000000 !important;
            border: none;
            font-weight: 600;
        }
        .btn-logout {
            background: #f97316 !important;
            color: #000000 !important;
            border: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-logout:hover {
            background: #f4c542 !important;
            color: #000000 !important;
        }
        .form-control, .form-select {
            background: #1a1a1a;
            border: 1px solid #333333;
            color: #ffffff;
        }
        .form-control:focus, .form-select:focus {
            background: #1a1a1a;
            border-color: #f4c542;
            color: #ffffff;
            box-shadow: 0 0 0 2px rgba(244, 197, 66, 0.2);
        }
        .form-label {
            color: #f4c542;
            font-weight: 600;
        }
        label {
            color: #ffffff;
            font-weight: 500;
        }
        small {
            color: #f4c542;
        }
        p {
            color: #ffffff;
        }
        span {
            color: #ffffff;
        }
        div {
            color: #ffffff;
        }
        h1, h2, h3, h4, h5, h6 {
            color: #f4c542;
            font-weight: 600;
        }
        .modal-content {
            background: #1a1a1a;
            border: 1px solid #333333;
        }
        .modal-header, .modal-footer {
            background: #333333;
            border-color: #333333;
        }
        .modal-body {
            background: #1a1a1a;
            color: #ffffff;
        }
        .dropdown-menu {
            background: #1a1a1a;
            border: 1px solid #333333;
        }
        .dropdown-item {
            color: #ffffff;
        }
        .dropdown-item:hover {
            background: #333333;
            color: #ffffff;
        }
        .alert {
            background: #1a1a1a;
            border: 1px solid #333333;
            color: #ffffff;
        }
        .badge {
            background: #333333;
            color: #ffffff;
        }
        .list-group-item {
            background: #1a1a1a;
            border-color: #333333;
            color: #ffffff;
        }
        .nav-link {
            color: #ffffff;
        }
        .nav-link:hover {
            color: #f4c542;
        }
        .nav-tabs .nav-link {
            color: #ffffff;
            background: #1a1a1a;
            border-color: #333333;
        }
        .nav-tabs .nav-link.active {
            background: #333333;
            color: #f4c542;
            border-color: #f4c542;
        }
        .nav-pills .nav-link {
            color: #ffffff;
            background: #1a1a1a;
        }
        .nav-pills .nav-link.active {
            background: #f4c542;
            color: #000000;
        }
        .pagination .page-link {
            background: #1a1a1a;
            border-color: #333333;
            color: #ffffff;
        }
        .pagination .page-link:hover {
            background: #333333;
            color: #ffffff;
        }
        .pagination .page-item.active .page-link {
            background: #f4c542;
            border-color: #f4c542;
            color: #000000;
        }
        .progress {
            background: #1a1a1a;
        }
        .progress-bar {
            background: #f4c542;
        }
        .accordion-item {
            background: #1a1a1a;
            border-color: #333333;
        }
        .accordion-button {
            background: #333333;
            color: #ffffff;
        }
        .accordion-button:not(.collapsed) {
            background: #444444;
            color: #ffffff;
        }
        .accordion-body {
            background: #1a1a1a;
            color: #ffffff;
        }
        .input-group-text {
            background: #333333;
            color: #ffffff;
            border-color: #444444;
        }
        .form-check-input {
            background-color: #333333;
            border-color: #444444;
        }
        .form-check-input:checked {
            background-color: #f4c542;
            border-color: #f4c542;
        }
        .form-check-label {
            color: #ffffff;
        }
        .toast {
            background: #1a1a1a;
            color: #ffffff;
        }
        .toast-header {
            background: #333333;
            color: #ffffff;
            border-bottom-color: #333333;
        }
        .close {
            color: #ffffff;
        }
        .close:hover {
            color: #f4c542;
        }
        .menu-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .status-available { color: #f4c542; }
        .status-unavailable { color: #f97316; }
        .badge-required { background: #f4c542; color: #000000; }
        .badge-optional { background: #f97316; color: #000000; }
        .badge-multi { background: #f97316; color: #000000; }
        .badge-single { background: #f4c542; color: #000000; }
        .category-header {
            background: #333333;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0 10px 0;
            color: #f4c542;
            font-weight: 600;
        }
        .item-header {
            background: #333333;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0 10px 0;
            border-left: 4px solid #f4c542;
        }
        .order-card {
            background: #1a1a1a;
            border: 1px solid #f4c542;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .border-warning {
            border-color: #f4c542 !important;
            box-shadow: 0 0 15px rgba(244, 197, 66, 0.3) !important;
        }
        @keyframes pulse-warning {
            0% { box-shadow: 0 0 15px rgba(244, 197, 66, 0.3); }
            50% { box-shadow: 0 0 25px rgba(244, 197, 66, 0.6); }
            100% { box-shadow: 0 0 15px rgba(244, 197, 66, 0.3); }
        }
        .border-warning {
            animation: pulse-warning 2s infinite;
        }
        .demo-frame {
            width: 100%;
            height: calc(100vh - 150px);
            border: none;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <h4 class="mb-4" style="color: #f4c542;">
                    <i class="bi bi-speedometer2 me-2"></i>Admin Panel
                </h4>
                <a href="?tab=orders" class="<?= $tab === 'orders' ? 'active' : '' ?>">
                    <i class="bi bi-list-check me-2"></i>Orders
                </a>
                <a href="?tab=menu" class="<?= $tab === 'menu' ? 'active' : '' ?>">
                    <i class="bi bi-grid me-2"></i>Menu
                </a>
                <a href="?tab=options" class="<?= $tab === 'options' ? 'active' : '' ?>">
                    <i class="bi bi-sliders me-2"></i>Options
                </a>
                <a href="?tab=reports" class="<?= $tab === 'reports' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up me-2"></i>Reports
                </a>
                <a href="?tab=qr" class="<?= $tab === 'qr' ? 'active' : '' ?>">
                    <i class="bi bi-qr-code me-2"></i>QR Codes
                </a>
                <a href="?tab=demo" class="<?= $tab === 'demo' ? 'active' : '' ?>">
                    <i class="bi bi-eye me-2"></i>Demo Menu
                </a>
                <hr style="border-color: #2a2a4a;">
                <a href="logout.php" class="btn-logout">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </div>
            <div class="col-md-10 main-content">
                <?php if (isset($_GET['reset'])): ?>
                    <div class="alert alert-success mb-3">
                        <i class="bi bi-check-circle me-2"></i>All report data has been reset successfully.
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>Error: <?= h($_GET['error']) ?>
                    </div>
                <?php endif; ?>
                <?php if ($tab === 'orders'): ?>
                    <?php include __DIR__ . '/partials/orders_tab.php'; ?>
                <?php elseif ($tab === 'menu'): ?>
                    <?php include __DIR__ . '/partials/menu_tab.php'; ?>
                <?php elseif ($tab === 'options'): ?>
                    <?php include __DIR__ . '/partials/options_tab.php'; ?>
                <?php elseif ($tab === 'reports'): ?>
                    <?php include __DIR__ . '/partials/reports_tab.php'; ?>
                <?php elseif ($tab === 'qr'): ?>
                    <?php include __DIR__ . '/partials/qr_tab.php'; ?>
                <?php elseif ($tab === 'demo'): ?>
                    <?php include __DIR__ . '/partials/demo_tab.php'; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
