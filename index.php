<?php

session_start();

require_once __DIR__ . '/lib/helpers.php';

$table = require_table_number();

// Clear order type session when accessing index.php with table parameter
// This ensures the selection page always shows when scanning QR code
$clearSession = false;
if (isset($_GET['table'])) {
    unset($_SESSION['order_type']);
    unset($_SESSION['order_type_table']);
    $clearSession = true;
}

$orderType = get_order_type_for_table($table);

// If already selected, go straight to menu
if ($orderType === 'eat_in' || $orderType === 'take_away') {
    header('Location: menu.php');
    exit;
}

require_once __DIR__ . '/partials/head.php';

?>
  <div class="glass p-4 p-md-5">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <div>
        <div class="text-uppercase small muted">Table</div>
        <div class="fs-3 fw-bold">#<?= h((string) $table) ?></div>
      </div>
      <span class="badge badge-gold rounded-pill px-3 py-2">Step 1 of 2</span>
    </div>

    <h1 class="h4 fw-semibold mb-2">How would you like to order?</h1>
    <p class="muted mb-4">Choose once. We'll remember it if you refresh.</p>

    <div class="row g-3">
      <div class="col-12 col-md-6">
        <form method="post" action="set_order_type.php" class="glass p-3 choice-card">
          <input type="hidden" name="table" value="<?= h((string) $table) ?>">
          <input type="hidden" name="order_type" value="eat_in">
          <button type="submit" class="btn btn-gold btn-big w-100">
            <span class="choice-title">Eat In</span>
          </button>
          <div class="choice-sub mt-2">Recommended when you are seated at the table.</div>
        </form>
      </div>
      <div class="col-12 col-md-6">
        <form method="post" action="set_order_type.php" class="glass p-3 choice-card">
          <input type="hidden" name="table" value="<?= h((string) $table) ?>">
          <input type="hidden" name="order_type" value="take_away">
          <button type="submit" class="btn btn-gold btn-big w-100">
            <span class="choice-title">Take Away</span>
          </button>
          <div class="choice-sub mt-2">We’ll label your order for takeaway prep.</div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Clear localStorage if PHP session was just cleared (QR scan)
    <?php if ($clearSession): ?>
    localStorage.removeItem('qr_order_type');
    localStorage.removeItem('qr_table_number');
    <?php else: ?>
    // If PHP session is lost but localStorage still has the selection,
    // restore it silently then continue to the menu.
    (function () {
      const key = 'qr_order_type';
      const existing = localStorage.getItem(key);
      if (!existing) return;

      const allowed = (existing === 'eat_in' || existing === 'take_away');
      if (!allowed) return;

      const savedTable = localStorage.getItem('qr_table_number');
      if (savedTable !== <?= json_encode((string) $table) ?>) return;

      const url = new URL(window.location.href);
      url.pathname = url.pathname.replace(/index\.php$/, 'set_order_type.php');
      url.searchParams.set('order_type', existing);
      url.searchParams.set('table', <?= json_encode((string) $table) ?>);
      url.searchParams.set('redirect', 'menu.php');

      window.location.replace(url.toString());
    })();
    <?php endif; ?>
  </script>

<?php require_once __DIR__ . '/partials/foot.php'; ?>

