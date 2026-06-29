<?php

session_start();

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';

require_admin_login();

$pdo = db();

// Handle form submission for create/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Delete menu item and its options
            $pdo->prepare("DELETE FROM menu_item_options WHERE menu_item_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM menu_items WHERE id = ?")->execute([$id]);
            header('Location: menu.php?deleted=1');
            exit;
        }
    } elseif ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = $_POST['name'] ?? '';
        $name_en = $_POST['name_en'] ?? '';
        $name_bu = $_POST['name_bu'] ?? '';
        $name_th = $_POST['name_th'] ?? '';
        $category = $_POST['category'] ?? '';
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $status = $_POST['status'] ?? 'available';
        
        // Handle image upload
        $image = $_POST['existing_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/images/menu/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'item_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $image = 'assets/images/menu/' . $filename;
            }
        }
        
        if ($id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE menu_items SET name = ?, name_en = ?, name_bu = ?, name_th = ?, category = ?, sort_order = ?, price = ?, image = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $name_en, $name_bu, $name_th, $category, $sort_order, $price, $image, $status, $id]);
        } else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO menu_items (name, name_en, name_bu, name_th, category, sort_order, price, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $name_en, $name_bu, $name_th, $category, $sort_order, $price, $image, $status]);
        }
        
        header('Location: menu.php?saved=1');
        exit;
    }
}

// Fetch all menu items
$menuItems = $pdo->query("SELECT * FROM menu_items ORDER BY category, sort_order")->fetchAll();

// Get categories
$categories = $pdo->query("SELECT DISTINCT category FROM menu_items ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

// Fetch single item for editing
$editItem = null;
$editId = (int)($_GET['edit'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - Admin</title>
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
        .menu-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .status-available {
            color: #4ade80;
        }
        .status-unavailable {
            color: #f87171;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <h4 class="mb-4" style="color: #f4c542;">Admin Panel</h4>
                <a href="index.php" class="mb-2"><i class="bi bi-list-check me-2"></i>Orders</a>
                <a href="menu.php" class="active mb-2"><i class="bi bi-grid me-2"></i>Menu</a>
                <a href="menu_options.php" class="mb-2"><i class="bi bi-sliders me-2"></i>Options</a>
                <a href="reports.php" class="mb-2"><i class="bi bi-graph-up me-2"></i>Reports</a>
                <a href="qr_tables.php" class="mb-2"><i class="bi bi-qr-code me-2"></i>QR Codes</a>
                <a href="../menu.php" class="mb-2"><i class="bi bi-eye me-2"></i>View Menu</a>
                <a href="logout.php" class="mb-2" style="color: #f87171;"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </div>
            <div class="col-md-10 main-content">
                <?php if (isset($_GET['saved'])): ?>
                    <div class="alert alert-success">Menu item saved successfully!</div>
                <?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success">Menu item deleted successfully!</div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-grid me-2"></i>Menu Items</span>
                        <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#itemModal" onclick="resetForm()">
                            <i class="bi bi-plus-lg me-1"></i>Add Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Sort</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($menuItems as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="../<?= h($item['image']) ?>" class="menu-img" alt="">
                                            <?php else: ?>
                                                <div class="menu-img bg-secondary d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= h($item['name']) ?></strong><br>
                                            <small class="text-muted"><?= h($item['name_en']) ?></small>
                                        </td>
                                        <td><?= h($item['category']) ?></td>
                                        <td><?= format_money($item['price']) ?></td>
                                        <td>
                                            <span class="status-<?= $item['status'] ?>">
                                                <?= ucfirst($item['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $item['sort_order'] ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning me-1" onclick="editItem(<?= $item['id'] ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteItem(<?= $item['id'] ?>, '<?= h($item['name']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Modal -->
    <div class="modal fade" id="itemModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="background: #16213e; color: #eee;">
                <div class="modal-header" style="background: #0f3460; border: none;">
                    <h5 class="modal-title" id="modalTitle">Add Menu Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" id="itemId" value="">
                        <input type="hidden" name="existing_image" id="existingImage" value="">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name (Default)</label>
                                <input type="text" class="form-control" name="name" id="itemName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name (English)</label>
                                <input type="text" class="form-control" name="name_en" id="itemNameEn">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name (Burmese)</label>
                                <input type="text" class="form-control" name="name_bu" id="itemNameBu">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name (Thai)</label>
                                <input type="text" class="form-control" name="name_th" id="itemNameTh">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category" id="itemCategory" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= h($cat) ?>"><?= h($cat) ?></option>
                                    <?php endforeach; ?>
                                    <option value="breakfast">Breakfast</option>
                                    <option value="rice">Rice</option>
                                    <option value="noodle">Noodle</option>
                                    <option value="salad">Salad</option>
                                    <option value="drinks">Drinks</option>
                                    <option value="yako">Yako</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price (THB)</label>
                                <input type="number" step="0.01" class="form-control" name="price" id="itemPrice" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" name="sort_order" id="itemSortOrder" value="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="itemStatus">
                                    <option value="available">Available</option>
                                    <option value="unavailable">Unavailable</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image</label>
                                <input type="file" class="form-control" name="image" accept="image/*" id="imageInput">
                                <div id="imagePreview" class="mt-2">
                                    <?php if ($editItem && !empty($editItem['image'])): ?>
                                        <img src="../<?= h($editItem['image']) ?>" class="img-thumbnail" style="max-height: 100px;">
                                        <br>
                                        <small class="text-muted">Current: <?= h($editItem['image']) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">No image selected</small>
                                    <?php endif; ?>
                                </div>
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
                    <p>Are you sure you want to delete "<span id="deleteItemName"></span>"?</p>
                    <p class="text-muted small">This will also delete all options associated with this item.</p>
                </div>
                <div class="modal-footer" style="background: #0f3460; border: none;">
                    <form method="POST" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteItemId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview functionality
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-height: 100px;"><br><small class="text-muted">New image selected</small>';
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<small class="text-muted">No image selected</small>';
            }
        });
        
        const menuItems = <?= json_encode($menuItems) ?>;
        
        function resetForm() {
            document.getElementById('itemId').value = '';
            document.getElementById('itemName').value = '';
            document.getElementById('itemNameEn').value = '';
            document.getElementById('itemNameBu').value = '';
            document.getElementById('itemNameTh').value = '';
            document.getElementById('itemPrice').value = '';
            document.getElementById('itemSortOrder').value = '0';
            document.getElementById('itemStatus').value = 'available';
            document.getElementById('existingImage').value = '';
            document.getElementById('modalTitle').textContent = 'Add Menu Item';
        }
        
        function editItem(id) {
            const item = menuItems.find(i => i.id === id);
            if (!item) return;
            
            document.getElementById('itemId').value = item.id;
            document.getElementById('itemName').value = item.name || '';
            document.getElementById('itemNameEn').value = item.name_en || '';
            document.getElementById('itemNameBu').value = item.name_bu || '';
            document.getElementById('itemNameTh').value = item.name_th || '';
            document.getElementById('itemCategory').value = item.category || '';
            document.getElementById('itemPrice').value = item.price || '';
            document.getElementById('itemSortOrder').value = item.sort_order || 0;
            document.getElementById('itemStatus').value = item.status || 'available';
            document.getElementById('existingImage').value = item.image || '';
            document.getElementById('modalTitle').textContent = 'Edit Menu Item';
            
            new bootstrap.Modal(document.getElementById('itemModal')).show();
        }
        
        function deleteItem(id, name) {
            document.getElementById('deleteItemId').value = id;
            document.getElementById('deleteItemName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
