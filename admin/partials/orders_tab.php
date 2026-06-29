<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Order updated successfully!</div>
<?php endif; ?>

<h2 class="mb-4" style="color: #f4c542;">
    <i class="bi bi-list-check me-2"></i>Pending Orders
</h2>

<?php if (count($orders) === 0): ?>
    <div class="card p-4">
        <div class="fw-semibold mb-1">No pending orders</div>
        <div class="muted">You're all caught up.</div>
    </div>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <?php 
        $waitingMinutes = (int)$order['waiting_minutes'];
        $isUrgent = $waitingMinutes >= 30;
        ?>
        <div class="order-card <?= $isUrgent ? 'border-warning' : '' ?>" data-order-id="<?= $order['id'] ?>">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">
                        <i class="bi bi-table me-2"></i>Table <?= h($order['table_number']) ?>
                        <span class="badge bg-<?= $order['order_type'] === 'eat_in' ? 'success' : 'info' ?> ms-2">
                            <?= ucfirst($order['order_type']) ?>
                        </span>
                    </h5>
                    <div class="muted small">
                        <i class="bi bi-clock me-1"></i>
                        <?= date('H:i', $order['created_at_ts']) ?>
                        <span class="ms-2 <?= $isUrgent ? 'text-warning fw-bold' : '' ?>">
                            (<?= $waitingMinutes ?> min ago)
                        </span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fs-4 fw-bold" style="color: #f4c542;">
                        <?= format_money($order['total_price']) ?>
                    </div>
                    <form method="POST" action="mark_done.php" style="display: inline;">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="bi bi-check-lg me-1"></i>Complete
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if (isset($itemsByOrder[$order['id']])): ?>
                <?php foreach ($itemsByOrder[$order['id']] as $item): ?>
                    <div class="item-row p-2 mb-2 rounded">
                        <div class="d-flex justify-content-between">
                            <strong><?= h($item['name']) ?></strong>
                            <span>x<?= $item['quantity'] ?></span>
                        </div>
                        <?php if (!empty($item['selected_options'])): ?>
                            <div class="mt-2 p-2 rounded" style="background: rgba(244, 197, 66, 0.1); border-left: 3px solid #f4c542;">
                                <small class="text-muted">Options:</small>
                                <?php foreach ($item['selected_options'] as $opt): ?>
                                    <?php if (isset($opt['notes'])): ?>
                                        <div class="mt-1">
                                            <i class="bi bi-pencil me-1"></i>
                                            <em><?= h($opt['notes']) ?></em>
                                        </div>
                                    <?php elseif (isset($opt['name_en'])): ?>
                                        <span class="badge bg-secondary me-1"><?= h($opt['name_en']) ?></span>
                                        <?php if (isset($opt['additional_price']) && $opt['additional_price'] > 0): ?>
                                            <small class="text-muted">(+<?= format_money($opt['additional_price']) ?>)</small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
    const serverNowTs = <?= json_encode($serverNowTs) ?>;
    const clientBootNowSec = Date.now() / 1000;
    
    // Update waiting times every 30 seconds
    setInterval(() => {
        const elapsedSec = (Date.now() / 1000) - clientBootNowSec;
        const currentServerTs = serverNowTs + elapsedSec;
        
        document.querySelectorAll('[data-order-id]').forEach(card => {
            const orderTs = parseInt(card.dataset.orderTs);
            if (orderTs) {
                const waitingMin = Math.floor((currentServerTs - orderTs) / 60);
                const waitingEl = card.querySelector('.waiting-min');
                if (waitingEl) {
                    waitingEl.textContent = waitingMin + ' min ago';
                    if (waitingMin >= 30) {
                        card.classList.add('border-warning');
                    }
                }
            }
        });
    }, 30000);
</script>
