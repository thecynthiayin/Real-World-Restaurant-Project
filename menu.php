<?php

session_start();

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$table = require_table_number();
$orderType = get_order_type_for_table($table);
$menuLang = normalize_menu_lang($_GET['lang'] ?? ($_SESSION['menu_lang'] ?? 'en'));
$_SESSION['menu_lang'] = $menuLang;
$pageBodyClass = 'menu-page';

$langShortLabel = [
    'en' => 'EN',
    'bu' => 'BU',
    'th' => 'TH',
];
$langFlag = [
    'en' => '🇬🇧',
    'bu' => '🇲🇲',
    'th' => '🇹🇭',
];
$langFullLabel = [
    'en' => 'English',
    'bu' => 'Burmese',
    'th' => 'Thai',
];
$otherLangs = array_values(array_filter(['en', 'bu', 'th'], fn ($lang) => $lang !== $menuLang));

// Hide menu until order type is chosen.
if ($orderType !== 'eat_in' && $orderType !== 'take_away') {
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

        <h1 class="h4 fw-semibold mb-2">Choose order type</h1>
        <p class="muted mb-4">Menu will appear after you choose.</p>

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <form method="post" action="set_order_type.php" class="glass p-3 choice-card">
              <input type="hidden" name="table" value="<?= h((string) $table) ?>">
              <input type="hidden" name="order_type" value="eat_in">
              <input type="hidden" name="redirect" value="menu.php">
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
              <input type="hidden" name="redirect" value="menu.php">
              <button type="submit" class="btn btn-gold btn-big w-100">
                <span class="choice-title">Take Away</span>
              </button>
              <div class="choice-sub mt-2">We’ll label your order for takeaway prep.</div>
            </form>
          </div>
        </div>
      </div>
    <?php
    require_once __DIR__ . '/partials/foot.php';
    exit;
}

$items = db()->query("SELECT * FROM menu_items ORDER BY category ASC, sort_order ASC, id ASC")->fetchAll();

// Group items by category if the column exists.
$categoryOrder = [
    'breakfast' => 10,
    'rice' => 20,
    'noodles' => 30,
    'dumplings' => 40,
    'salad' => 50,
    'drinks' => 60,
    'yako' => 70,
];
$itemsByCategory = [];
foreach ($items as $it) {
    $cat = (string) ($it['category'] ?? 'menu');
    $itemsByCategory[$cat][] = $it;
}

$categoryKeys = array_keys($itemsByCategory);
usort($categoryKeys, function ($a, $b) use ($categoryOrder) {
    $ao = $categoryOrder[$a] ?? 999;
    $bo = $categoryOrder[$b] ?? 999;
    if ($ao === $bo) return strcmp($a, $b);
    return $ao <=> $bo;
});

require_once __DIR__ . '/partials/head.php';

?>
  <div class="menu-scroll-area">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <div class="fw-semibold">Menu</div>
      <div class="small muted">Select categories and pick quantities.</div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <div class="dropdown">
        <button
          class="btn btn-sm btn-gold dropdown-toggle fw-semibold"
          type="button"
          data-bs-toggle="dropdown"
          aria-expanded="false"
          aria-label="Menu language"
        >
          <?= h($langFlag[$menuLang] ?? '🇬🇧') ?> <?= h($langShortLabel[$menuLang] ?? 'EN') ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php foreach ($otherLangs as $lang): ?>
            <li>
              <a class="dropdown-item" href="menu.php?lang=<?= h($lang) ?>">
                <?= h($langFlag[$lang] ?? '') ?> <?= h($langShortLabel[$lang] ?? strtoupper($lang)) ?> - <?= h($langFullLabel[$lang] ?? strtoupper($lang)) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <span class="badge rounded-pill <?= h(order_type_badge_class($orderType)) ?> px-3 py-2">
        TABLE <?= h((string) $table) ?> - <?= h(order_type_label($orderType)) ?>
      </span>
      <a class="btn btn-sm btn-gold" href="index.php?table=<?= h((string) $table) ?>" title="Change order type" onclick="localStorage.removeItem('qr_order_type'); localStorage.removeItem('qr_table_number');">Change</a>
    </div>
  </div>

  <!-- Category Selection -->
  <div class="glass p-3 p-md-4 mb-3" id="category-filter">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div class="fw-semibold">Menu Categories</div>
      <button type="button" class="btn btn-sm btn-gold" id="toggle-all">Show All</button>
    </div>
    <div class="row g-2" id="category-buttons">
      <?php foreach ($categoryKeys as $cat): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <button type="button" class="btn btn-outline-gold category-btn w-100" data-category="<?= h($cat) ?>">
            <?= h(menu_category_label($cat, $menuLang)) ?>
          </button>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <form method="post" action="review_order.php" class="glass p-3 p-md-4" id="menu-form">
    <input type="hidden" name="table_number" value="<?= h((string) $table) ?>">
    <input type="hidden" name="order_type" value="<?= h($orderType) ?>">

    <?php foreach ($categoryKeys as $cat): ?>
      <?php
        $label = menu_category_label($cat, $menuLang);
        $groupItems = $itemsByCategory[$cat] ?? [];
      ?>
      <div class="menu-section glass p-3 p-md-4 mb-3" data-category="<?= h($cat) ?>">
        <div class="menu-section-title">
          <div class="title"><?= h($label) ?></div>
          <div class="rule" aria-hidden="true"></div>
        </div>

        <div class="row g-3 mt-2">
          <?php foreach ($groupItems as $it): ?>
            <?php
              $available = (string) ($it['status'] ?? 'available') === 'available';
            ?>
            <div class="col-12">
              <div class="p-3 menu-item glass d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                  <?php 
                    // Check database image column first (from admin panel uploads)
                    $photoPath = '';
                    $photoExists = false;
                    
                    if (!empty($it['image'])) {
                        $dbImage = $it['image'];
                        $photoFile = __DIR__ . '/' . $dbImage;
                        if (file_exists($photoFile)) {
                            $photoPath = $dbImage;
                            $photoExists = true;
                        }
                    }
                    
                    // Fall back to organized path by category and ID
                    if (!$photoExists) {
                        $organizedPath = 'assets/menu/' . $it['category'] . '/' . (int)$it['id'] . '.jpg';
                        $photoFile = __DIR__ . '/' . $organizedPath;
                        if (file_exists($photoFile)) {
                            $photoPath = $organizedPath;
                            $photoExists = true;
                        }
                    }
                    
                    // Fall back to photos folder with item_ prefix
                    if (!$photoExists) {
                        $photosPath = 'assets/menu/photos/item_' . (int)$it['id'] . '.jpg';
                        $photoFile = __DIR__ . '/' . $photosPath;
                        if (file_exists($photoFile)) {
                            $photoPath = $photosPath;
                            $photoExists = true;
                        }
                    }
                    
                    // Fall back to photos folder without prefix
                    if (!$photoExists) {
                        $photosPathAlt = 'assets/menu/photos/' . (int)$it['id'] . '.jpg';
                        $photoFile = __DIR__ . '/' . $photosPathAlt;
                        if (file_exists($photoFile)) {
                            $photoPath = $photosPathAlt;
                            $photoExists = true;
                        }
                    }
                    
                    $itemOptions = get_menu_item_options((int)$it['id'], $menuLang);
                    $hasOptions = count($itemOptions) > 0;
                  ?>
                  <?php if ($photoExists): ?>
                    <div class="menu-photo clickable" aria-hidden="true" onclick="openItemModal(<?= (int)$it['id'] ?>, '<?= h($photoPath) ?>', '<?= h(menu_item_name_by_lang($it, $menuLang)) ?>', <?= h((string)$it['price']) ?>, <?= $hasOptions ? 'true' : 'false' ?>)">
                      <img src="<?= h($photoPath) ?>" alt="<?= h(menu_item_name_by_lang($it, $menuLang)) ?>" class="menu-photo-img">
                    </div>
                  <?php else: ?>
                    <div class="menu-photo clickable" aria-hidden="true" onclick="openItemModal(<?= (int)$it['id'] ?>, '', '<?= h(menu_item_name_by_lang($it, $menuLang)) ?>', <?= h((string)$it['price']) ?>, <?= $hasOptions ? 'true' : 'false' ?>)"></div>
                  <?php endif; ?>
                  <div class="flex-grow-1 clickable" onclick="openItemModal(<?= (int)$it['id'] ?>, '<?= h($photoExists ? $photoPath : '') ?>', '<?= h(menu_item_name_by_lang($it, $menuLang)) ?>', <?= h((string)$it['price']) ?>, <?= $hasOptions ? 'true' : 'false' ?>)">
                    <div class="menu-name menu-name-single">
                      <div class="lang-current"><?= h(menu_item_name_by_lang($it, $menuLang)) ?></div>
                    </div>
                    <div class="small muted menu-price"><?= h(format_money((string) $it['price'])) ?></div>
                    <?php if ($hasOptions): ?>
                      <div class="small text-muted mt-1"> Options available</div>
                    <?php endif; ?>
                    <?php if (!$available): ?>
                      <span class="badge text-bg-secondary mt-2">Out of stock</span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="qty-col">
                  <label class="form-label small muted mb-1 qty-label">Qty</label>
                  <div class="qty-stepper">
                    <button type="button" class="btn btn-gold qty-btn" data-step="-1" aria-label="Reduce quantity" <?= $available ? '' : 'disabled' ?>>-</button>
                    <input
                      type="number"
                      class="form-control form-control-sm qty-input"
                      name="qty[<?= (int) $it['id'] ?>]"
                      value="0"
                      min="0"
                      max="99"
                      step="1"
                      data-price="<?= h((string) $it['price']) ?>"
                      data-item-id="<?= (int) $it['id'] ?>"
                      <?= $available ? '' : 'disabled' ?>
                    >
                    <button type="button" class="btn btn-gold qty-btn" data-step="1" aria-label="Increase quantity" <?= $available ? '' : 'disabled' ?>>+</button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="d-grid mt-4" id="end-review-wrap">
      <button type="submit" class="btn btn-gold btn-big" id="end-review-btn">Review Order</button>
    </div>

    <div class="small muted mt-2">
      By submitting, your order will be sent to the kitchen queue.
    </div>
  </form>

  <div class="floating-review-wrap" id="floating-review-wrap" hidden>
    <button type="submit" form="menu-form" class="btn btn-gold w-100 btn-big" id="floating-review-btn">Review Order</button>
  </div>

  </div>

  <!-- Menu Item Modal -->
  <div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="itemModalTitle"></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12 col-md-6 text-center mb-3 mb-md-0">
              <img id="itemModalImg" src="" alt="" class="img-fluid rounded" style="max-height: 250px; object-fit: contain; width: 100%;">
            </div>
            <div class="col-12 col-md-6">
              <div class="mb-3">
                <h6 class="fw-bold" id="itemModalPrice"></h6>
              </div>
              <div id="itemModalOptions"></div>
              <div class="mt-3">
                <label class="form-label small muted">Quantity</label>
                <div class="qty-stepper">
                  <button type="button" class="btn btn-gold qty-btn" data-step="-1">-</button>
                  <input type="number" id="itemModalQty" class="form-control form-control-sm qty-input" value="1" min="1" max="99" step="1">
                  <button type="button" class="btn btn-gold qty-btn" data-step="1">+</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-gold" id="itemModalAddBtn">Add to Order</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Keep localStorage in sync for refreshes.
    localStorage.setItem('qr_order_type', <?= json_encode($orderType) ?>);
    localStorage.setItem('qr_table_number', <?= json_encode((string) $table) ?>);
    localStorage.setItem('qr_menu_lang', <?= json_encode($menuLang) ?>);

    // Item modal functionality
    let currentItem = null;
    
    async function openItemModal(itemId, photoSrc, title, price, hasOptions) {
      currentItem = { itemId, photoSrc, title, price, hasOptions };
      
      const modal = new bootstrap.Modal(document.getElementById('itemModal'));
      document.getElementById('itemModalTitle').textContent = title;
      document.getElementById('itemModalPrice').textContent = 'Price: ' + price;
      document.getElementById('itemModalQty').value = 1;
      
      // Set image
      const img = document.getElementById('itemModalImg');
      if (photoSrc) {
        img.src = photoSrc;
        img.style.display = 'block';
      } else {
        img.style.display = 'none';
      }
      
      // Load options
      const optionsContainer = document.getElementById('itemModalOptions');
      if (hasOptions) {
        try {
          const response = await fetch(`lib/get_item_options.php?item_id=${itemId}&lang=<?= h($menuLang) ?>`);
          const data = await response.json();
          renderOptions(data);
        } catch (e) {
          console.error('Error loading options:', e);
          optionsContainer.innerHTML = '<div class="text-muted">Error loading options</div>';
        }
      } else {
        optionsContainer.innerHTML = '<div class="text-muted">No options available</div>';
      }
      
      modal.show();
    }
    
    function renderOptions(optionsData) {
      const container = document.getElementById('itemModalOptions');
      if (!optionsData || optionsData.length === 0) {
        container.innerHTML = '<div class="text-muted">No options available</div>';
        return;
      }
      
      let html = '';
      
      // Mohinga add-on announcement
      if (currentItem.itemId === 28) {
        html += `<div class="alert alert-info mb-3">
          <strong>Add-ons:</strong><br>
          ဘဲဥခြမ်း - 10 THB<br>
          အကြော် - 10 THB<br>
          ငါးဖယ် - 10 THB
        </div>`;
        html += `<div class="mb-3">
          <label class="form-label small muted">Special Instructions</label>
          <input type="text" class="form-control form-control-sm" id="itemModalNotes" placeholder="Any special requests?">
        </div>`;
      }
      
      optionsData.forEach(group => {
        const groupName = group.group === 'default' ? 'Options' : group.group;
        const required = group.is_required ? ' <span class="text-danger">*</span>' : '';
        html += `<div class="mb-3">
          <label class="form-label fw-bold">${groupName}${required}</label>
          <div class="form-check-group">`;
        
        group.options.forEach(opt => {
          const inputType = group.is_multi_select ? 'checkbox' : 'radio';
          const priceText = opt.additional_price > 0 ? ` (+${opt.additional_price})` : '';
          html += `
            <div class="form-check">
              <input class="form-check-input" type="${inputType}" 
                     name="opt_${group.group}" 
                     value="${opt.id}" 
                     data-price="${opt.additional_price}"
                     id="opt_${opt.id}">
              <label class="form-check-label" for="opt_${opt.id}">
                ${opt.localized_name}${priceText}
              </label>
            </div>`;
        });
        
        html += '</div></div>';
      });
      
      container.innerHTML = html;
    }
    
    // Modal quantity stepper
    document.querySelectorAll('#itemModal .qty-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const step = parseInt(btn.dataset.step || '0', 10);
        const input = document.getElementById('itemModalQty');
        const current = parseInt(input.value || '1', 10);
        const next = Math.max(1, Math.min(99, current + step));
        input.value = next;
      });
    });
    
    // Add to order button
    document.getElementById('itemModalAddBtn').addEventListener('click', () => {
      if (!currentItem) return;
      
      const qty = parseInt(document.getElementById('itemModalQty').value || '1', 10);
      const qtyInput = document.querySelector(`input[name="qty[${currentItem.itemId}]"]`);
      if (qtyInput) {
        const currentQty = parseInt(qtyInput.value || '0', 10);
        qtyInput.value = currentQty + qty;
        qtyInput.dispatchEvent(new Event('input', { bubbles: true }));
      }
      
      // Collect selected options
      const selectedOptions = [];
      const optionInputs = document.querySelectorAll('#itemModalOptions input:checked');
      optionInputs.forEach(input => {
        selectedOptions.push({
          id: parseInt(input.value),
          price: parseFloat(input.dataset.price || '0')
        });
      });
      
      // Capture special instructions for Mohinga
      const notesInput = document.getElementById('itemModalNotes');
      if (notesInput && notesInput.value.trim()) {
        selectedOptions.push({
          id: 0,
          price: 0,
          notes: notesInput.value.trim()
        });
      }
      
      // Store selected options in a hidden field
      let optionsInput = document.querySelector(`input[name="options[${currentItem.itemId}]"]`);
      if (!optionsInput) {
        optionsInput = document.createElement('input');
        optionsInput.type = 'hidden';
        optionsInput.name = `options[${currentItem.itemId}]`;
        document.getElementById('menu-form').appendChild(optionsInput);
      }
      optionsInput.value = JSON.stringify(selectedOptions);
      
      const modal = bootstrap.Modal.getInstance(document.getElementById('itemModal'));
      modal.hide();
    });

    // Category filtering functionality
    (function() {
      const categoryButtons = document.querySelectorAll('.category-btn');
      const menuSections = document.querySelectorAll('.menu-section');
      const toggleAllBtn = document.getElementById('toggle-all');
      
      // Initialize: show all categories
      let selectedCategory = null;
      
      function updateCategoryButtons() {
        categoryButtons.forEach(btn => {
          const category = btn.dataset.category;
          if (selectedCategory === category) {
            btn.classList.add('active');
          } else {
            btn.classList.remove('active');
          }
        });
      }
      
      function updateMenuSections() {
        menuSections.forEach(section => {
          const category = section.dataset.category;
          if (selectedCategory === null || selectedCategory === category) {
            section.classList.remove('hidden');
          } else {
            section.classList.add('hidden');
          }
        });
      }
      
      function updateToggleAllButton() {
        if (selectedCategory === null) {
          toggleAllBtn.textContent = 'Show All';
        } else {
          toggleAllBtn.textContent = 'Show All';
        }
      }
      
      // Category button click handlers
      categoryButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          const category = btn.dataset.category;
          if (selectedCategory === category) {
            // If clicking the same category, show all
            selectedCategory = null;
          } else {
            // Show only this category
            selectedCategory = category;
          }
          updateCategoryButtons();
          updateMenuSections();
          updateToggleAllButton();
        });
      });
      
      // Toggle all button handler
      toggleAllBtn.addEventListener('click', () => {
        selectedCategory = null;
        updateCategoryButtons();
        updateMenuSections();
        updateToggleAllButton();
      });
      
      // Initialize UI
      updateCategoryButtons();
      updateMenuSections();
      updateToggleAllButton();
    })();

    (function () {
      const qtyInputs = document.querySelectorAll('.qty-input');
      const endWrap = document.getElementById('end-review-wrap');
      const endBtn = document.getElementById('end-review-btn');
      const floatingWrap = document.getElementById('floating-review-wrap');
      const floatingBtn = document.getElementById('floating-review-btn');
      if (!endWrap || !endBtn || !floatingWrap || !floatingBtn) return;

      let endVisible = false;

      function totalQty() {
        let total = 0;
        qtyInputs.forEach((el) => {
          const q = parseInt(el.value || '0', 10);
          if (Number.isFinite(q) && q > 0) total += q;
        });
        return total;
      }

      function refreshFloating() {
        const hasItems = totalQty() > 0;
        endBtn.disabled = !hasItems;
        floatingBtn.disabled = !hasItems;
        // Show floating only when end submit area is not visible.
        floatingWrap.hidden = endVisible;
      }

      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.target === endWrap) {
            endVisible = entry.isIntersecting;
            refreshFloating();
          }
        });
      }, { root: null, threshold: 0.15 });

      observer.observe(endWrap);
      const qtyButtons = document.querySelectorAll('.qty-btn:not(#itemModal .qty-btn)');
      qtyButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
          const step = parseInt(btn.dataset.step || '0', 10);
          const input = btn.parentElement?.querySelector('.qty-input');
          if (!input || !Number.isFinite(step)) return;
          const min = parseInt(input.min || '0', 10);
          const max = parseInt(input.max || '99', 10);
          const current = parseInt(input.value || '0', 10);
          const safeCurrent = Number.isFinite(current) ? current : 0;
          const next = Math.min(max, Math.max(min, safeCurrent + step));
          input.value = String(next);
          input.dispatchEvent(new Event('input', { bubbles: true }));
        });
      });

      qtyInputs.forEach((el) => el.addEventListener('input', refreshFloating));
      refreshFloating();
    })();
  </script>

<?php require_once __DIR__ . '/partials/foot.php'; ?>

