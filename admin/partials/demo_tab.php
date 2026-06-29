<h2 class="mb-4" style="color: #f4c542;">
    <i class="bi bi-eye me-2"></i>Menu Preview
</h2>

<div class="card">
    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-md-3 p-3" style="background: #0f3460;">
                <label class="form-label mb-2">Select Table</label>
                <select class="form-select mb-3" id="tableSelect" onchange="updateDemo()">
                    <?php for ($i = 1; $i <= 20; $i++): ?>
                        <option value="<?= $i ?>" <?= $i === 1 ? 'selected' : '' ?>>Table <?= $i ?></option>
                    <?php endfor; ?>
                </select>
                
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-1"></i>
                    This is a live preview of the customer menu. You can test ordering flow here (orders will be saved to the database).
                </div>
                
                <a href="../menu.php?table=1" target="_blank" class="btn btn-gold w-100">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Open in New Tab
                </a>
            </div>
            <div class="col-md-9">
                <iframe id="demoFrame" class="demo-frame" src="../menu.php?table=1"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    function updateDemo() {
        const table = document.getElementById('tableSelect').value;
        document.getElementById('demoFrame').src = '../menu.php?table=' + table;
    }
</script>
