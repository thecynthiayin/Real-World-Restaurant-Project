<?php

session_start();
unset($_SESSION['order_type']);
unset($_SESSION['order_type_table']);

// Keep table_number so the QR context remains.
$table = $_SESSION['table_number'] ?? null;
$redirect = 'index.php';
if (is_int($table) || ctype_digit((string) $table)) {
    $redirect = 'index.php?table=' . urlencode((string) $table);
}

// Clear client-side storage too, otherwise the app may auto-restore the last choice.
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Resetting…</title>
  <meta http-equiv="refresh" content="0;url=<?= htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') ?>">
  <style>
    body { margin: 0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background: #0b1220; color: #fff; }
    .wrap { min-height: 100vh; display:flex; align-items:center; justify-content:center; padding: 24px; }
    .card { max-width: 520px; width: 100%; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14); border-radius: 16px; backdrop-filter: blur(12px); padding: 18px; }
    a { color: #fff; }
    .muted { color: rgba(255,255,255,.72); }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div style="font-weight:800; letter-spacing:.2px;">Resetting…</div>
      <div class="muted" style="margin-top:6px;">If you are not redirected automatically, tap below.</div>
      <div style="margin-top:14px;">
        <a href="<?= htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:10px 14px;border-radius:12px;border:1px solid rgba(255,255,255,.18);text-decoration:none;">
          Continue
        </a>
      </div>
    </div>
  </div>
  <script>
    try {
      localStorage.removeItem('qr_order_type');
      localStorage.removeItem('qr_table_number');
    } catch (e) {}
    window.location.replace(<?= json_encode($redirect) ?>);
  </script>
</body>
</html>

