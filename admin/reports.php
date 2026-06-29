<?php

session_start();

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/reports.php';
require_once __DIR__ . '/../lib/auth.php';

require_admin_login();

$pdo = db();

// Get date parameters
$reportType = $_GET['type'] ?? 'daily';
$date = $_GET['date'] ?? date('Y-m-d');
$month = $_GET['month'] ?? date('Y-m');

// Validate and sanitize dates
if ($reportType === 'daily') {
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObj || $dateObj > new DateTime()) {
        $date = date('Y-m-d');
    }
    $reportTitle = 'Daily Report - ' . date('F j, Y', strtotime($date));
    
    // Try to get from database first, generate if not exists
    $report = get_daily_report($date);
    if (!$report) {
        $report = generate_daily_report($date);
    }
    
    $dateStart = $date . ' 00:00:00';
    $dateEnd = $date . ' 23:59:59';
} else {
    $monthObj = DateTime::createFromFormat('Y-m', $month);
    if (!$monthObj || $monthObj > new DateTime()) {
        $month = date('Y-m');
    }
    $reportTitle = 'Monthly Report - ' . date('F Y', strtotime($month . '-01'));
    
    // Try to get from database first, generate if not exists
    $report = get_monthly_report($month);
    if (!$report) {
        $report = generate_monthly_report($month);
    }
    
    $dateStart = $month . '-01 00:00:00';
    $dateEnd = $monthObj->format('Y-m-t') . ' 23:59:59';
}

// Extract data from report
$totalOrders = $report['total_orders'];
$doneOrders = $report['completed_orders'];
$totalRevenue = $report['total_revenue'];
$popularItems = $report['popular_items'];

// Get order details for display
$orders = get_order_details($dateStart, $dateEnd);



?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin • Reports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/styles.css" rel="stylesheet">
  <style>
    body {
      color: #fff;
      background: linear-gradient(180deg, #2a1a06 0%, #1a1205 100%) !important;
      min-height: 100vh;
    }
    .wrap { max-width: 1200px; }
    .topbar { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .brand { display:flex; align-items:center; gap:10px; text-decoration:none; color:#fff; }
    .brand:hover { color:#fff; opacity:.92; }
    .brand img {
      width: auto;
      height: 52px;
      max-width: 180px;
      object-fit: contain;
      object-position: left center;
      border-radius: 0;
      border: 0;
      background: transparent;
      box-shadow: none;
      padding: 0;
    }
    .brand .name { font-weight: 800; letter-spacing: .2px; }
    .cardx {
      background: rgba(35, 22, 8, 0.9);
      border: 1px solid rgba(248, 213, 28, 0.45);
      border-radius: 16px;
      backdrop-filter: blur(12px);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
    }
    .muted { color: rgba(255,255,255,.86); }
    .mono { font-variant-numeric: tabular-nums; }
    .stat-card {
      background: rgba(255,255,255,.1);
      border: 1px solid rgba(248, 213, 28, 0.3);
      border-radius: 12px;
      padding: 1rem;
      text-align: center;
    }
    .stat-value {
      font-size: 2rem;
      font-weight: 800;
      color: #f4c542;
    }
    .stat-label {
      font-size: 0.9rem;
      opacity: 0.8;
    }
    .chart-bar {
      background: linear-gradient(135deg, var(--gold-1), var(--gold-2));
      border-radius: 4px;
      transition: all 0.3s ease;
    }
    .chart-bar:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(244, 197, 66, 0.3);
    }
    .order-row {
      background: rgba(255,255,255,.05);
      border: 1px solid rgba(255,255,255,.2);
      border-radius: 8px;
    }
    .nav-pills .nav-link {
      border-radius: 12px;
      border: 1px solid rgba(248, 213, 28, 0.3);
      color: rgba(255,255,255,.8);
    }
    .nav-pills .nav-link.active {
      background: linear-gradient(135deg, var(--gold-1), var(--gold-2));
      border-color: var(--gold-solid);
      color: #1a1406;
    }
  </style>
</head>
<body>
  <div class="container py-4 wrap">
    <div class="topbar mb-4">
      <a class="brand" href="index.php" title="Admin">
        <img src="../logo.jpeg" alt="<?= h(APP_NAME) ?>" onerror="this.onerror=null;this.src='../assets/logo.png';">
        <div>
          <div class="name">Admin Reports</div>
          <div class="muted small">Sales Analytics & Insights</div>
        </div>
      </a>
      <div class="d-flex align-items-center gap-3">
        <div class="text-end">
          <div class="fw-semibold" id="current-time">--:--:--</div>
          <div class="muted small" id="current-date">----/--/--</div>
        </div>
        <div class="d-flex gap-2">
          <a class="btn btn-sm btn-outline-light" href="index.php">Orders</a>
          <a class="btn btn-sm btn-outline-light" href="menu.php">Menu</a>
          <a class="btn btn-sm btn-outline-light" href="menu_options.php">Options</a>
          <a class="btn btn-sm btn-outline-light" href="qr_tables.php">Print QRs</a>
          <a class="btn btn-sm btn-outline-light" href="../menu.php?table=1">Guest (Demo Table 1)</a>
          <a class="btn btn-sm btn-outline-danger" href="logout.php">Logout</a>
        </div>
      </div>
    </div>

    <!-- Report Type Navigation -->
    <div class="cardx p-3 mb-4">
      <ul class="nav nav-pills" role="tablist">
        <li class="nav-item">
          <a class="nav-link <?= $reportType === 'daily' ? 'active' : '' ?>" 
             href="?type=daily&date=<?= h($date) ?>">Daily Report</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $reportType === 'monthly' ? 'active' : '' ?>" 
             href="?type=monthly&month=<?= h($month) ?>">Monthly Report</a>
        </li>
      </ul>
    </div>

    <!-- Date Navigation -->
    <div class="cardx p-3 mb-4">
      <div class="d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><?= h($reportTitle) ?></h5>
        <div class="d-flex gap-2">
          <?php if ($reportType === 'daily'): ?>
            <a class="btn btn-sm btn-outline-light" 
               href="?type=daily&date=<?= date('Y-m-d', strtotime($date . ' -1 day')) ?>">
               ← Previous Day
            </a>
            <?php if ($date < date('Y-m-d')): ?>
            <a class="btn btn-sm btn-outline-light" 
               href="?type=daily&date=<?= date('Y-m-d', strtotime($date . ' +1 day')) ?>">
               Next Day →
            </a>
            <?php endif; ?>
          <?php else: ?>
            <a class="btn btn-sm btn-outline-light" 
               href="?type=monthly&month=<?= date('Y-m', strtotime($month . ' -1 month')) ?>">
               ← Previous Month
            </a>
            <?php if ($month < date('Y-m')): ?>
            <a class="btn btn-sm btn-outline-light" 
               href="?type=monthly&month=<?= date('Y-m', strtotime($month . ' +1 month')) ?>">
               Next Month →
            </a>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-4">
        <div class="stat-card">
          <div class="stat-value mono"><?= $totalOrders ?></div>
          <div class="stat-label">Total Orders</div>
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="stat-card">
          <div class="stat-value mono"><?= $doneOrders ?></div>
          <div class="stat-label">Completed</div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="stat-card">
          <div class="stat-value mono"><?= h(format_money((string)$totalRevenue)) ?></div>
          <div class="stat-label">Revenue</div>
        </div>
      </div>
    </div>

    
    <!-- Popular Items -->
    <?php if (!empty($popularItems)): ?>
    <div class="cardx p-3 mb-4">
      <h6 class="mb-3">Popular Items (Completed Orders Only)</h6>
      <div class="row g-2">
        <?php foreach ($popularItems as $item): ?>
          <div class="col-12 col-md-6">
            <div class="order-row d-flex align-items-center justify-content-between rounded-3 px-3 py-2">
              <div>
                <div class="fw-semibold"><?= h($item['name_en']) ?></div>
                <div class="small muted"><?= h(ucfirst($item['category'])) ?></div>
              </div>
              <div class="text-end">
                <div class="mono"><?= (int)$item['total_quantity'] ?>×</div>
                <div class="small mono"><?= h(format_money((string)$item['total_revenue'])) ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Order Details -->
    <div class="cardx p-3">
      <h6 class="mb-3">Order Details</h6>
      <?php if (empty($orders)): ?>
        <div class="text-center muted py-4">No orders found for this period.</div>
      <?php else: ?>
        <div class="row g-2">
          <?php foreach ($orders as $order): ?>
            <div class="col-12">
              <div class="order-row d-flex align-items-center justify-content-between rounded-3 px-3 py-2">
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge rounded-pill <?= $order['order_type'] === 'take_away' ? 'text-bg-warning' : 'text-bg-success' ?> px-2 py-1 small">
                      TABLE <?= (int)$order['table_number'] ?> - <?= $order['order_type'] === 'take_away' ? 'TAKE AWAY' : 'EAT IN' ?>
                    </span>
                    <span class="badge rounded-pill <?= $order['status'] === 'done' ? 'text-bg-success' : 'text-bg-secondary' ?> px-2 py-1 small">
                      <?= strtoupper($order['status']) ?>
                    </span>
                  </div>
                  <div class="small muted">
                    Order #<?= (int)$order['id'] ?> • <?= date('H:i', strtotime($order['created_at'])) ?>
                    <?php if (!empty($order['items_summary'])): ?>
                      • <?= h($order['items_summary']) ?>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="text-end">
                  <div class="fw-bold mono"><?= h(format_money((string)$order['total_price'])) ?></div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
<script>
    // Real-time clock and date
    function updateDateTime() {
      const now = new Date();
      
      // Format time
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const seconds = String(now.getSeconds()).padStart(2, '0');
      const timeString = `${hours}:${minutes}:${seconds}`;
      
      // Format date
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, '0');
      const day = String(now.getDate()).padStart(2, '0');
      const dateString = `${year}/${month}/${day}`;
      
      // Update DOM
      const timeElement = document.getElementById('current-time');
      const dateElement = document.getElementById('current-date');
      
      if (timeElement) timeElement.textContent = timeString;
      if (dateElement) dateElement.textContent = dateString;
    }
    
    // Update immediately and then every second
    updateDateTime();
    setInterval(updateDateTime, 1000);
  </script>
</body>
</html>
