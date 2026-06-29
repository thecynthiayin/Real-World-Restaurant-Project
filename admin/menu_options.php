<?php

session_start();

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';

require_admin_login();

$pdo = db();

// Handle form submission for create/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM menu_item_options WHERE id = ?")->execute([$id]);
            header('Location: menu_options.php?deleted=1');
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
                // Update
                $stmt = $pdo->prepare("UPDATE menu_item_options SET menu_item_id = ?, option_group = ?, sort_order = ?, additional_price = ?, is_required = ?, is_multi_select = ?, name_en = ?, name_bu = ?, name_th = ? WHERE id = ?");
                $stmt->execute([$menu_item_id, $option_group, $sort_order, $additional_price, $is_required, $is_multi_select, $name_en, $name_bu, $name_th, $id]);
            } else {
                // Create
                $stmt = $pdo->prepare("INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$menu_item_id, $option_group, $sort_order, $additional_price, $is_required, $is_multi_select, $name_en, $name_bu, $name_th]);
            }
            header('Location: menu_options.php?saved=1');
            exit;
        }
    }
}

// Fetch all menu items for dropdown
$menuItems = $pdo->query("SELECT * FROM menu_items ORDER BY category, sort_order")->fetchAll();

// Fetch all options grouped by menu item
$optionsByItem = [];
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

// Fetch single option for editing
$editOption = null;
$editId = (int)($_GET['edit'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM menu_item_options WHERE id = ?");
    $stmt->execute([$editId]);
    $editOption = $stmt->fetch();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Options Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: #1a1a2e;
            color: #eee;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .sidebar {
            background: #16213e;
            min-height: 100vh;
            padding: 20px;
        }
        .sidebar a {
            color: #eee;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            margin-bottom: 5px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #f4c542;
            color: #1a1a2e;
        }
        .main-content {
            padding: 30px;
        }
        .card {
            background: #16213e;
            border: none;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .card-header {
            background: #0f3460;
            border: none;
            border-radius: 12px 12px 0 0;
            color: #f4c542;
            font-weight: bold;
        }
        .table {
            color: #eee;
        }
        .table th {
            background: #0f3460;
            border-color: #f4c542;
        }
        .table td {
            border-color: #2a2a4a;
            vertical-align: middle;
        }
        .btn-gold {
            background: #f4c542;
            color: #1a1a2e;
            border: none;
            font-weight: bold;
        }
        .btn-gold:hover {
            background: #d4a532;
            color: #1a1a2e;
        }
        .form-control, .form-select {
            background: #0f3460;
            border: 1px solid #2a2a4a;
            color: #eee;
        }
        .form-control:focus, .form-select:focus {
            background: #0f3460;
            border-color: #f4c542;
            color: #eee;
            box-shadow: none;
        }
        .category-header {
            background: #0f3460;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0 10px 0;
            color: #f4c542;
            font-weight: bold;
        }
        .item-header {
            background: #2a2a4a;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0 10px 0;
            border-left: 4px solid #f4c542;
        }
        .badge-required {
            background: #f4c542;
            color: #1a1a2e;
        }
        .badge-optional {
            background: #4ade80;
            color: #1a1a2e;
        }
        .badge-multi {
            background: #60a5fa;
            color: #1a1a2e;
        }
        .badge-single {
            background: #f87171;
            color: #1a1a2e;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <h4 class="mb-4" style="color: #f4c542;">Admin Panel</h4>
                <a href="index.php" class="mb-2"><i class="bi bi-list-check me-2"></i>Orders</a>
                <a href="menu.php" class="mb-2"><i class="bi bi-grid me-2"></i>Menu</a>
                <a href="menu_options.php" class="active mb-2"><i class="bi bi-sliders me-2"></i>Options</a>
                <a href="reports.php" class="mb-2"><i class="bi bi-graph-up me-2"></i>Reports</a>
                <a href="qr_tables.php" class="mb-2"><i class="bi bi-qr-code me-2"></i>QR Codes</a>
                <a href="../menu.php" class="mb-2"><i class="bi bi-eye me-2"></i>View Menu</a>
                <a href="logout.php" class="mb-2" style="color: #f87171;"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </div>
            <div class="col-md-10 main-content">
                <?php if (isset($_GET['saved'])): ?>
                    <div class="alert alert-success">Option saved successfully!</div>
                <?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success">Option deleted successfully!</div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-sliders me-2"></i>Menu Item Options</span>
                        <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#optionModal" onclick="resetForm()">
                            <i class="bi bi-plus-lg me-1"></i>Add Option
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (empty($optionsByItem)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-3">No options found. Add your first option!</p>
                            </div>
                        <?php else: ?>
                            <?php 
                            $currentCategory = '';
                            foreach ($optionsByItem as $itemId => $data): 
                                if ($data['category'] !== $currentCategory) {
                                    $currentCategory = $data['category'];
                            ?>
                                <div class="category-header">
                                    <i class="bi bi-folder me-2"></i><?= ucfirst(h($currentCategory)) ?>
                                </div>
                            <?php } ?>
                            <div class="item-header">
                                <strong><?= h($data['item_name']) ?></strong>
                                <button class="btn btn-sm btn-gold float-end" onclick="addOptionForItem(<?= $itemId ?>)">
                                    <i class="bi bi-plus"></i> Add Option
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Group</th>
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th>Required</th>
                                            <th>Type</th>
                                            <th>Sort</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['options'] as $opt): ?>
                                        <tr>
                                            <td><?= h($opt['option_group']) ?></td>
                                            <td>
                                                <?= h($opt['name_en']) ?><br>
                                                <small class="text-muted"><?= h($opt['name_bu']) ?> / <?= h($opt['name_th']) ?></small>
                                            </td>
                                            <td><?= $opt['additional_price'] > 0 ? '+' . format_money($opt['additional_price']) : '-' ?></td>
                                            <td>
                                                <span class="badge <?= $opt['is_required'] ? 'badge-required' : 'badge-optional' ?>">
                                                    <?= $opt['is_required'] ? 'Required' : 'Optional' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $opt['is_multi_select'] ? 'badge-multi' : 'badge-single' ?>">
                                                    <?= $opt['is_multi_select'] ? 'Multi' : 'Single' ?>
                                                </span>
                                            </td>
                                            <td><?= $opt['sort_order'] ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-warning me-1" onclick="editOption(<?= $opt['id'] ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteOption(<?= $opt['id'] ?>, '<?= h($opt['name_en']) ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Option Modal -->
    <div class="modal fade" id="optionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="background: #16213e; color: #eee;">
                <div class="modal-header" style="background: #0f3460; border: none;">
                    <h5 class="modal-title" id="modalTitle">Add Option</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" id="optionId" value="">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Menu Item</label>
                                <select class="form-select" name="menu_item_id" id="optionMenuItemId" required>
                                    <option value="">Select Item</option>
                                    <?php foreach ($menuItems as $item): ?>
                                        <option value="<?= $item['id'] ?>" data-category="<?= h($item['category']) ?>">
                                            <?= h($item['category']) ?> - <?= h($item['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Option Group</label>
                                <input type="text" class="form-control" name="option_group" id="optionGroup" placeholder="e.g., sweetness, spicy_level" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Name (English)</label>
                                <input type="text" class="form-control" name="name_en" id="optionNameEn" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Name (Burmese)</label>
                                <input type="text" class="form-control" name="name_bu" id="optionNameBu">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Name (Thai)</label>
                                <input type="text" class="form-control" name="name_th" id="optionNameTh">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Additional Price (THB)</label>
                                <input type="number" step="0.01" class="form-control" name="additional_price" id="optionPrice" value="0">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" name="sort_order" id="optionSortOrder" value="0">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Required</label>
                                <select class="form-select" name="is_required" id="optionRequired">
                                    <option value="0">Optional</option>
                                    <option value="1">Required</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Selection Type</label>
                                <select class="form-select" name="is_multi_select" id="optionMultiSelect">
                                    <option value="0">Single (Radio)</option>
                                    <option value="1">Multi (Checkbox)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="background: #0f3460; border: none;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-gold">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background: #16213e; color: #eee;">
                <div class="modal-header" style="background: #0f3460; border: none;">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete option "<span id="deleteOptionName"></span>"?</p>
                </div>
                <div class="modal-footer" style="background: #0f3460; border: none;">
                    <form method="POST" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteOptionId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const allOptions = <?= json_encode($allOptions) ?>;
        
        function resetForm() {
            document.getElementById('optionId').value = '';
            document.getElementById('optionMenuItemId').value = '';
            document.getElementById('optionGroup').value = '';
            document.getElementById('optionNameEn').value = '';
            document.getElementById('optionNameBu').value = '';
            document.getElementById('optionNameTh').value = '';
            document.getElementById('optionPrice').value = '0';
            document.getElementById('optionSortOrder').value = '0';
            document.getElementById('optionRequired').value = '0';
            document.getElementById('optionMultiSelect').value = '0';
            document.getElementById('modalTitle').textContent = 'Add Option';
        }
        
        function addOptionForItem(itemId) {
            resetForm();
            document.getElementById('optionMenuItemId').value = itemId;
            document.getElementById('modalTitle').textContent = 'Add Option';
            new bootstrap.Modal(document.getElementById('optionModal')).show();
        }
        
        function editOption(id) {
            const opt = allOptions.find(o => o.id === id);
            if (!opt) return;
            
            document.getElementById('optionId').value = opt.id;
            document.getElementById('optionMenuItemId').value = opt.menu_item_id;
            document.getElementById('optionGroup').value = opt.option_group || '';
            document.getElementById('optionNameEn').value = opt.name_en || '';
            document.getElementById('optionNameBu').value = opt.name_bu || '';
            document.getElementById('optionNameTh').value = opt.name_th || '';
            document.getElementById('optionPrice').value = opt.additional_price || 0;
            document.getElementById('optionSortOrder').value = opt.sort_order || 0;
            document.getElementById('optionRequired').value = opt.is_required || 0;
            document.getElementById('optionMultiSelect').value = opt.is_multi_select || 0;
            document.getElementById('modalTitle').textContent = 'Edit Option';
            
            new bootstrap.Modal(document.getElementById('optionModal')).show();
        }
        
        function deleteOption(id, name) {
            document.getElementById('deleteOptionId').value = id;
            document.getElementById('deleteOptionName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
