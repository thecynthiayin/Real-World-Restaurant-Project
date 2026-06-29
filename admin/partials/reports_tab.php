<?php
$reportType = $_GET['type'] ?? 'daily';
$date = $_GET['date'] ?? date('Y-m-d');
$month = $_GET['month'] ?? date('Y-m');

$totalOrders = $report['total_orders'] ?? 0;
$doneOrders = $report['completed_orders'] ?? 0;
$totalRevenue = $report['total_revenue'] ?? 0;
$popularItems = $report['popular_items'] ?? [];
?>

<h2 class="mb-4" style="color: #f4c542;">
    <i class="bi bi-graph-up me-2"></i>Sales Reports
</h2>

<!-- Report Type Navigation -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex gap-2 align-items-center justify-content-between">
            <div class="d-flex gap-2">
                <a class="btn btn-report-daily <?= $reportType === 'daily' ? 'active' : '' ?>"
                   href="?tab=reports&type=daily&date=<?= h($date) ?>">
                    <i class="bi bi-calendar-day me-1"></i>Daily Report
                </a>
                <a class="btn btn-report-monthly <?= $reportType === 'monthly' ? 'active' : '' ?>"
                   href="?tab=reports&type=monthly&month=<?= h($month) ?>">
                    <i class="bi bi-calendar-month me-1"></i>Monthly Report
                </a>
            </div>
            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#resetReportsModal">
                <i class="bi bi-trash me-1"></i>Reset All Data
            </button>
        </div>
    </div>
</div>

<!-- Date Navigation -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><?= h($reportTitle) ?></h5>
            <div class="d-flex gap-2">
                <?php if ($reportType === 'daily'): ?>
                    <a class="btn btn-sm btn-outline-light" 
                       href="?tab=reports&type=daily&date=<?= date('Y-m-d', strtotime($date . ' -1 day')) ?>">
                       ← Previous Day
                    </a>
                    <?php if ($date < date('Y-m-d')): ?>
                    <a class="btn btn-sm btn-outline-light" 
                       href="?tab=reports&type=daily&date=<?= date('Y-m-d', strtotime($date . ' +1 day')) ?>">
                       Next Day →
                    </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a class="btn btn-sm btn-outline-light" 
                       href="?tab=reports&type=monthly&month=<?= date('Y-m', strtotime($month . ' -1 month')) ?>">
                       ← Previous Month
                    </a>
                    <?php if ($month < date('Y-m')): ?>
                    <a class="btn btn-sm btn-outline-light" 
                       href="?tab=reports&type=monthly&month=<?= date('Y-m', strtotime($month . ' +1 month')) ?>">
                       Next Month →
                    </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card text-center" style="background: #4d4d4d;">
            <div class="card-body">
                <div class="fs-2 fw-bold" style="color: #f4c542;"><?= $totalOrders ?></div>
                <div class="small muted">Total Orders</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card text-center" style="background: #4d4d4d;">
            <div class="card-body">
                <div class="fs-2 fw-bold" style="color: #4ade80;"><?= $doneOrders ?></div>
                <div class="small muted">Completed</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card text-center" style="background: #4d4d4d;">
            <div class="card-body">
                <div class="fs-2 fw-bold" style="color: #f97316;"><?= format_money($totalRevenue) ?></div>
                <div class="small muted">Total Revenue</div>
            </div>
        </div>
    </div>
</div>

<!-- Popular Items -->
<?php if (!empty($popularItems)): ?>
<div class="card">
    <div class="card-header">Popular Items</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <?php if ($reportType === 'daily'): ?>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Revenue</th>
                        <?php else: ?>
                        <th class="text-end">Revenue</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($popularItems as $item): ?>
                    <tr>
                        <td>
                            <?= h($item['name_en'] ?? $item['name'] ?? 'Unknown') ?>
                            <?php if (!empty($item['options'])): ?>
                            <div class="small text-muted">
                                <?php 
                                $optionNames = [];
                                foreach ($item['options'] as $opt) {
                                    $optionNames[] = h($opt['name_en'] ?? 'Option');
                                }
                                echo '(' . implode(', ', $optionNames) . ')';
                                ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <?php if ($reportType === 'daily'): ?>
                        <td class="text-end">
                            <?php 
                            $qty = $item['total_quantity'] ?? $item['order_count'] ?? 0;
                            $revenue = $item['total_revenue'] ?? $item['revenue'] ?? 0;
                            $unitPrice = $qty > 0 ? $revenue / $qty : 0;
                            echo format_money($unitPrice);
                            ?>
                        </td>
                        <td class="text-end"><?= $qty ?></td>
                        <td class="text-end"><?= format_money($revenue) ?></td>
                        <?php else: ?>
                        <td class="text-end"><?= format_money($item['total_revenue'] ?? $item['revenue'] ?? 0) ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Reset Reports Confirmation Modal -->
<div class="modal fade" id="resetReportsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background: #16213e; color: #eee;">
            <div class="modal-header" style="background: #0f3460; border: none;">
                <h5 class="modal-title">Reset All Report Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to reset all report data?</p>
                <p class="text-muted small mb-2">This action will:</p>
                <ul class="text-muted small">
                    <li>Delete all daily reports</li>
                    <li>Delete all monthly reports</li>
                    <li>Delete all popular items data</li>
                    <li>This cannot be undone</li>
                </ul>
            </div>
            <div class="modal-footer" style="background: #0f3460; border: none;">
                <form method="POST" action="reset_reports.php">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reset All Data</button>
                </form>
            </div>
        </div>
    </div>
</div>
