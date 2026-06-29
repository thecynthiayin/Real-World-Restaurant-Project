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
        <p class="muted mb-3">Please choose at least 1 item before continuing.</p>
        <a class="btn btn-outline-light" href="menu.php">Back to menu</a>
      </div>
    <?php
    require_once __DIR__ . '/partials/foot.php';
    exit;
}

$ids = array_keys($normalizedQty);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = db()->prepare("SELECT * FROM menu_items WHERE id IN ($placeholders)");
$stmt->execute($ids);
$rows = $stmt->fetchAll();

$menuById = [];
foreach ($rows as $r) {
    $menuById[(int) $r['id']] = $r;
}

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
    $optStmt = db()->prepare("SELECT * FROM menu_item_options WHERE id IN ($optionPlaceholders)");
    $optStmt->execute($optionIds);
    $optRows = $optStmt->fetchAll();
    foreach ($optRows as $r) {
        $optionsById[(int)$r['id']] = $r;
    }
}

$reviewLines = [];
$total = '0.00';
foreach ($normalizedQty as $id => $qv) {
    if (!isset($menuById[$id])) {
        continue;
    }
    if (($menuById[$id]['status'] ?? 'available') !== 'available') {
        continue;
    }
    $price = (string) $menuById[$id]['price'];
    
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
    $reviewLines[] = [
        'item_id' => $id,
        'item' => $menuById[$id],
        'qty' => $qv,
        'price' => $price,
        'option_price' => $optionPrice,
        'base_price' => $basePrice,
        'line_total' => $lineTotal,
        'options' => $selectedOptionDetails,
    ];
}

if (count($reviewLines) === 0) {
    http_response_code(400);
    require_once __DIR__ . '/partials/head.php';
    ?>
      <div class="glass p-4">
        <h1 class="h5 fw-semibold mb-2">Items unavailable</h1>
        <p class="muted mb-3">Selected items are out of stock or invalid. Please choose again.</p>
        <a class="btn btn-outline-light" href="menu.php">Back to menu</a>
      </div>
    <?php
    require_once __DIR__ . '/partials/foot.php';
    exit;
}

require_once __DIR__ . '/partials/head.php';
?>
  <div class="glass p-4 p-md-5">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <span class="badge rounded-pill <?= h(order_type_badge_class($orderType)) ?> px-3 py-2">
        TABLE <?= h((string) $table) ?> - <?= h(order_type_label($orderType)) ?>
      </span>
      <span class="badge rounded-pill badge-gold px-3 py-2">Review before submit</span>
    </div>

    <h1 class="h4 fw-semibold mb-2">Confirm your order</h1>
    <p class="muted mb-4">Please check items and quantities before sending to kitchen.</p>

    <div class="glass p-3 mb-4">
      <?php foreach ($reviewLines as $line): ?>
        <div class="d-flex justify-content-between align-items-start py-2 border-bottom border-light border-opacity-10">
          <div class="flex-grow-1 me-3">
            <div class="menu-name menu-name-single">
              <div class="lang-current"><?= h(menu_item_name_by_lang($line['item'], $menuLang)) ?></div>
            </div>
            <div class="small muted"><?= (int)$line['qty'] ?> × <?= h(format_money($line['base_price'])) ?></div>
            <?php if (!empty($line['options'])): ?>
              <div class="small text-muted mt-1">
                <?php foreach ($line['options'] as $opt): ?>
                  + <?= h($opt['name_' . $menuLang] ?? $opt['name_en']) ?> (<?= h(format_money((string)$opt['additional_price'])) ?>)
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="fw-semibold"><?= h(format_money($line['line_total'])) ?></div>
        </div>
      <?php endforeach; ?>
      <div class="d-flex justify-content-between align-items-center pt-3">
        <div class="fw-semibold">Total</div>
        <div class="fw-bold"><?= h(format_money($total)) ?></div>
      </div>
    </div>

    <form method="post" action="place_order.php" class="d-flex gap-2 flex-wrap">
      <input type="hidden" name="table_number" value="<?= h((string) $table) ?>">
      <input type="hidden" name="order_type" value="<?= h($orderType) ?>">
      <?php foreach ($reviewLines as $line): ?>
        <input type="hidden" name="qty[<?= (int) $line['item_id'] ?>]" value="<?= (int) $line['qty'] ?>">
        <input type="hidden" name="options[<?= (int) $line['item_id'] ?>]" value="<?= h(json_encode($line['options'])) ?>">
      <?php endforeach; ?>
      <a class="btn btn-outline-light" href="menu.php">Back to edit</a>
      <button type="submit" class="btn btn-primary">Confirm & Submit to Kitchen</button>
    </form>
  </div>

<?php require_once __DIR__ . '/partials/foot.php'; ?>
