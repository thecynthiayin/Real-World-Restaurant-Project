<h2 class="mb-4" style="color: #f4c542;">
    <i class="bi bi-qr-code me-2"></i>QR Code Generator
</h2>

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label">Number of Tables</label>
                <input type="number" class="form-control" id="qrCount" value="40" min="1" max="500">
            </div>
            <div class="col-md-6">
                <label class="form-label">Base URL</label>
                <input type="text" class="form-control" id="qrBase" value="https://real-world-restaurant-project-production.up.railway.app/index.php">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-gold w-100" onclick="generateQRs()">Generate</button>
            </div>
        </div>
        
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            This generates QR codes that link to the menu for each table number.
        </div>
        
        <div id="qrGrid" class="row g-3"></div>
    </div>
</div>

<script>
    function generateQRs() {
        const count = parseInt(document.getElementById('qrCount').value);
        const base = document.getElementById('qrBase').value.trim();
        const grid = document.getElementById('qrGrid');
        
        grid.innerHTML = '';
        
        for (let i = 1; i <= count; i++) {
            const url = base + '?table=' + i;
            const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(url);
            
            const col = document.createElement('div');
            col.className = 'col-6 col-md-3 col-lg-2';
            col.innerHTML = `
                <div class="card text-center" style="background: #0f3460;">
                    <div class="card-body p-3">
                        <img src="${qrUrl}" class="img-fluid mb-2" alt="QR Code Table ${i}">
                        <div class="fw-bold">Table ${i}</div>
                        <small class="text-muted" style="font-size: 10px;">${url}</small>
                    </div>
                </div>
            `;
            grid.appendChild(col);
        }
    }
    
    // Generate on load
    generateQRs();
</script>
