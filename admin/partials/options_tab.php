<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Option saved successfully!</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Option deleted successfully!</div>
<?php endif; ?>

<h2 class="mb-4" style="color: #f4c542;">
    <i class="bi bi-sliders me-2"></i>Menu Options Management
</h2>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Menu Item Options</span>
        <button class="btn btn-add-option btn-sm" data-bs-toggle="modal" data-bs-target="#optionModal" onclick="resetForm()">
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
                <button class="btn btn-add-option btn-sm float-end" onclick="addOptionForItem(<?= $itemId ?>)">
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
                                <button class="btn btn-sm btn-edit me-1" onclick="editOption(<?= $opt['id'] ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-delete" onclick="deleteOption(<?= $opt['id'] ?>, '<?= h($opt['name_en']) ?>')">
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
