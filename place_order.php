<?php

session_start();

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$table = require_table_number();
$orderType = require_order_type();
$menuLang = normalize_menu_lang($_SESSION['menu_lang'] ?? 'en');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: menu.php');
    exit;
}

$qty = $_POST['qty'] ?? [];
$options = $_POST['options'] ?? [];
if (!is_array($qty)) {
    $qty = [];
}
if (!is_array($options)) {
    $options = [];
}

// Normalize quantities (positive ints only)
$normalizedQty = [];
$normalizedOptions = [];
foreach ($qty as $itemId => $q) {
    $id = filter_var($itemId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $qv = filter_var($q, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99]]);
    if ($id === false || $id === null || $qv === false || $qv === null) {
        continue;
    }
    if ((int) $qv > 0) {
        $normalizedQty[(int) $id] = (int) $qv;
        // Parse options JSON
        if (isset($options[$itemId]) && is_string($options[$itemId])) {
            $decoded = json_decode($options[$itemId], true);
            if (is_array($decoded)) {
                $normalizedOptions[(int) $id] = $decoded;
            }
        }
    }
}

if (count($normalizedQty) === 0) {
    http_response_code(400);
    require_once __DIR__ . '/partials/head.php';
    ?>
      <div class="glass p-4">
        <h1 class="h5 fw-semibold mb-2">No items selected</h1>
        <p class="muted mb-3">Please choose at least 1 item before submitting.</p>
        <a class="btn btn-outline-light" href="menu.php">Back to menu</a>
      </div>
    <?php
    require_once __DIR__ . '/partials/foot.php';
    exit;
}

$pdo = db();
$ids = array_keys($normalizedQty);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id IN ($placeholders) FOR UPDATE");
$pdo->beginTransaction();
try {
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();

    $menuById = [];
    foreach ($rows as $r) {
        $menuById[(int)$r['id']] = $r;
    }

    // Validate availability + compute total.
    $total = '0.00';
    $orderLines = [];
    
    // Fetch option details
    $optionIds = [];
    foreach ($normalizedOptions as $itemId => $opts) {
        foreach ($opts as $opt) {
            if (isset($opt['id'])) {
                $optionIds[] = (int) $opt['id'];
            }
        }
    }
    
    $optionsById = [];
    if (count($optionIds) > 0) {
        $optionPlaceholders = implode(',', array_fill(0, count($optionIds), '?'));
        $optStmt = $pdo->prepare("SELECT * FROM menu_item_options WHERE id IN ($optionPlaceholders)");
        $optStmt->execute($optionIds);
        $optRows = $optStmt->fetchAll();
        foreach ($optRows as $r) {
            $optionsById[(int)$r['id']] = $r;
        }
    }
    
    foreach ($normalizedQty as $id => $qv) {
        if (!isset($menuById[$id])) {
            continue;
        }
        if ($menuById[$id]['status'] !== 'available') {
            continue;
        }
        $price = (string) $menuById[$id]['price']; // decimal string from MySQL
        
        // Calculate option prices
        $optionPrice = '0.00';
        $selectedOptionDetails = [];
        if (isset($normalizedOptions[$id])) {
            foreach ($normalizedOptions[$id] as $opt) {
                if (isset($opt['id'], $optionsById[$opt['id']])) {
                    $optData = $optionsById[$opt['id']];
                    $optionPrice = bcadd($optionPrice, (string) $optData['additional_price'], 2);
                    $selectedOptionDetails[] = $optData;
                }
            }
        }
        
        $basePrice = bcadd($price, $optionPrice, 2);
        $lineTotal = bcmul($basePrice, (string) $qv, 2);
        $total = bcadd($total, $lineTotal, 2);
        $orderLines[] = [
            'item_id' => $id,
            'item' => $menuById[$id],
            'price' => $price,
            'option_price' => $optionPrice,
            'base_price' => $basePrice,
            'qty' => $qv,
            'options' => $selectedOptionDetails,
        ];
    }

    if (count($orderLines) === 0) {
        $pdo->rollBack();
        http_response_code(400);
        require_once __DIR__ . '/partials/head.php';
        ?>
          <div class="glass p-4">
            <h1 class="h5 fw-semibold mb-2">Items unavailable</h1>
            <p class="muted mb-3">Selected items are out of stock or invalid. Please try again.</p>
            <a class="btn btn-outline-light" href="menu.php">Back to menu</a>
          </div>
        <?php
        require_once __DIR__ . '/partials/foot.php';
        exit;
    }

    $ins = $pdo->prepare("INSERT INTO orders (table_number, order_type, total_price, status) VALUES (?, ?, ?, 'pending')");
    $ins->execute([$table, $orderType, $total]);
    $orderId = (int) $pdo->lastInsertId();

    $insItem = $pdo->prepare("INSERT INTO order_items (order_id, item_id, quantity, selected_options) VALUES (?, ?, ?, ?)");
    foreach ($orderLines as $l) {
        $optionsJson = !empty($l['options']) ? json_encode($l['options']) : null;
        $insItem->execute([$orderId, $l['item_id'], $l['qty'], $optionsJson]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $e;
}

require_once __DIR__ . '/partials/head.php';

?>
  <div class="glass p-4 p-md-5">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <span class="badge rounded-pill <?= h(order_type_badge_class($orderType)) ?> px-3 py-2">
        TABLE <?= h((string) $table) ?> - <?= h(order_type_label($orderType)) ?>
      </span>
      <span class="badge rounded-pill text-bg-light text-dark px-3 py-2">Order #<?= h((string) $orderId) ?></span>
    </div>

    <h1 class="h4 fw-semibold mb-2">Order placed</h1>
    <p class="muted mb-2">Thanks! Your order is now in the kitchen queue.</p>
    <p class="fw-semibold mb-4"><b>Only pay when the food arrives.</b></p>

    <div class="glass p-3 mb-4">
      <?php foreach ($orderLines as $l): ?>
        <div class="d-flex justify-content-between align-items-start py-2 border-bottom border-light border-opacity-10">
          <div class="flex-grow-1 me-3">
            <div class="menu-name menu-name-single">
              <div class="lang-current"><?= h(menu_item_name_by_lang($l['item'], $menuLang)) ?></div>
            </div>
            <div class="small muted"><?= (int) $l['qty'] ?> × <?= h(format_money($l['base_price'])) ?></div>
            <?php if (!empty($l['options'])): ?>
              <div class="small text-muted mt-1">
                <?php foreach ($l['options'] as $opt): ?>
                  + <?= h($opt['name_' . $menuLang] ?? $opt['name_en']) ?> (<?= h(format_money((string)$opt['additional_price'])) ?>)
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="fw-semibold"><?= h(format_money(bcmul($l['base_price'], (string)$l['qty'], 2))) ?></div>
        </div>
      <?php endforeach; ?>
      <div class="d-flex justify-content-between align-items-center pt-3">
        <div class="fw-semibold">Total</div>
        <div class="fw-bold"><?= h(format_money($total)) ?></div>
      </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-gold" href="menu.php">Order more</a>
      <a class="btn btn-gold" href="reset.php" onclick="localStorage.removeItem('qr_order_type'); localStorage.removeItem('qr_table_number');">Change type</a>
    </div>
  </div>

<?php require_once __DIR__ . '/partials/foot.php'; ?>

