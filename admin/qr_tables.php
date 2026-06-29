<?php
// Printable QR generator for tables.
// Usage: http://localhost/QR%20Table%20Project/admin/login.php
// If base is omitted, we attempt to infer from current request.

$countRaw = $_GET['count'] ?? 40;
$count = filter_var($countRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]]);
if ($count === false || $count === null) {
    $count = 40;
}

$base = $_GET['base'] ?? null;
$base = is_string($base) ? trim($base) : '';

if ($base === '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = $_SERVER['SCRIPT_NAME'] ?? '/admin/qr_tables.php';
    // Replace /admin/qr_tables.php with /index.php in the same project.
    $path = preg_replace('#/admin/qr_tables\.php$#', '/index.php', $path) ?: '/index.php';
    $base = $scheme . '://' . $host . $path;
}

// Ensure base ends with index.php (helps avoid accidental wrong target).
// If user passed a directory, we'll append index.php.
if (!preg_match('#/index\.php$#i', $base)) {
    $base = rtrim($base, '/') . '/index.php';
}

function h(?string $v): string {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Table QR Generator</title>
  <style>
    :root { --bg: #0b1220; --card: rgba(255,255,255,.08); --border: rgba(255,255,255,.14); --muted: rgba(255,255,255,.72); }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background: var(--bg); color: #fff; }
    .wrap { max-width: 1100px; margin: 0 auto; padding: 20px; }
    .card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; backdrop-filter: blur(12px); }
    .muted { color: var(--muted); }
    .controls { padding: 14px; display: grid; gap: 10px; }
    .row { display: grid; grid-template-columns: 1fr; gap: 10px; }
    @media (min-width: 860px) { .row { grid-template-columns: 2fr 1fr; } }
    label { font-size: 12px; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); }
    input { width: 100%; padding: 10px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,.18); background: rgba(0,0,0,.25); color: #fff; }
    button { padding: 10px 14px; border-radius: 12px; border: 1px solid rgba(255,255,255,.18); background: rgba(255,255,255,.12); color: #fff; cursor: pointer; }
    button:hover { background: rgba(255,255,255,.18); }
    .grid { margin-top: 14px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    @media (min-width: 680px) { .grid { grid-template-columns: repeat(4, 1fr); } }
    @media (min-width: 980px) { .grid { grid-template-columns: repeat(5, 1fr); } }
    .qr { padding: 12px; text-align: center; }
    .qr .code { display: inline-block; padding: 10px; background: #fff; border-radius: 12px; }
    .qr .t { margin-top: 8px; font-weight: 700; letter-spacing: .04em; }
    .qr .u { margin-top: 4px; font-size: 11px; color: var(--muted); word-break: break-all; }

    /* Print layout */
    @media print {
      body { background: #fff; color: #000; }
      .controls { display: none; }
      .wrap { max-width: none; padding: 0; }
      .card { background: transparent; border: 0; border-radius: 0; }
      .grid { grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 0; padding: 0; }
      .qr { break-inside: avoid; border: 1px solid #ddd; border-radius: 10px; padding: 10px; }
      .qr .u { color: #444; }
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
</head>
<body>
  <div class="wrap">
    <div class="card controls">
      <div class="row">
        <div>
          <label>Base URL (must point to index.php)</label>
          <input id="base" value="<?= h($base) ?>" placeholder="http://<ip>/<project>/index.php">
          <div class="muted" style="font-size:12px;margin-top:6px;">
            Tip: use your PC/Laptop LAN IP so guests can open it on their phones.
          </div>
        </div>
        <div>
          <label>Tables count</label>
          <input id="count" type="number" min="1" max="500" value="<?= (int)$count ?>">
          <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;">
            <button id="regen" type="button">Regenerate</button>
            <button type="button" onclick="window.print()">Print</button>
          </div>
        </div>
      </div>
      <div class="muted" style="font-size:12px;">
        This generates URLs like: <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;">index.php?table=1</span>
      </div>
    </div>

    <div id="grid" class="card grid" style="padding:14px;"></div>
  </div>

  <script>
    function normalizeBaseUrl(base) {
      base = (base || '').trim();
      if (!base) return '';
      // Force index.php at the end.
      if (!/\/index\.php$/i.test(base)) {
        base = base.replace(/\/+$/, '') + '/index.php';
      }
      return base;
    }

    function buildUrl(base, tableNum) {
      const u = new URL(base);
      u.searchParams.set('table', String(tableNum));
      const url = u.toString();
      console.log('Generated QR URL:', url); // Debug logging
      return url;
    }

    function renderAll() {
      const grid = document.getElementById('grid');
      const baseEl = document.getElementById('base');
      const countEl = document.getElementById('count');

      const base = normalizeBaseUrl(baseEl.value);
      const count = Math.max(1, Math.min(500, parseInt(countEl.value || '40', 10)));

      if (!base) {
        grid.innerHTML = '<div class="qr muted">Please enter a Base URL.</div>';
        return;
      }

      grid.innerHTML = '';
      for (let i = 1; i <= count; i++) {
        const url = buildUrl(base, i);
        const item = document.createElement('div');
        item.className = 'qr card';
        item.innerHTML = `
          <div class="code" id="q${i}"></div>
          <div class="t">TABLE ${i}</div>
          <div class="u">${url}</div>
        `;
        grid.appendChild(item);

        new QRCode(item.querySelector('#q' + i), {
          text: url,
          width: 160,
          height: 160,
          correctLevel: QRCode.CorrectLevel.M
        });
      }
    }

    document.getElementById('regen').addEventListener('click', renderAll);
    renderAll();
  </script>
</body>
</html>

