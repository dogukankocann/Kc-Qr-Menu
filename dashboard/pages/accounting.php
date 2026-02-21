<div class="dash-topbar" style="margin-bottom: 24px;">
    <h1>Muhasebe & Satış Raporları</h1>
    <div style="display:flex; gap:12px; align-items:center;">
        <input type="date" id="startDate" class="form-control" style="width:140px;">
        <span style="color:var(--text-muted)">-</span>
        <input type="date" id="endDate" class="form-control" style="width:140px;">
        <button class="btn btn-primary" onclick="fetchAccountingData()">Filtrele</button>
    </div>
</div>

<div class="filter-actions" style="margin-bottom: 24px; display:flex; gap:8px;">
    <button class="btn btn-secondary" onclick="setDateRange('today')">Bugün</button>
    <button class="btn btn-secondary" onclick="setDateRange('yesterday')">Dün</button>
    <button class="btn btn-secondary" onclick="setDateRange('this_week')">Bu Hafta</button>
    <button class="btn btn-secondary" onclick="setDateRange('this_month')">Bu Ay</button>
</div>

<!-- Summary Cards -->
<div class="stats-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:20px; margin-bottom: 30px;">
    <div class="stat-card" style="background:var(--card-bg); padding:24px; border-radius:16px; border:1px solid var(--border); display:flex; flex-direction:column; gap:8px;">
        <div style="font-size:0.9rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Toplam Ciro</div>
        <div id="statRevenue" style="font-size:2.2rem; font-weight:800; color:var(--primary); font-family: 'Bebas Neue', sans-serif;">0.00 ₺</div>
    </div>
    <div class="stat-card" style="background:var(--card-bg); padding:24px; border-radius:16px; border:1px solid var(--border); display:flex; flex-direction:column; gap:8px;">
        <div style="font-size:0.9rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Toplam Sipariş</div>
        <div id="statOrders" style="font-size:2.2rem; font-weight:800; color:var(--success); font-family: 'Bebas Neue', sans-serif;">0</div>
        <div style="font-size:0.8rem; color:var(--text-muted);">Tamamlanan ve bekleyenler</div>
    </div>
    <div class="stat-card" style="background:var(--card-bg); padding:24px; border-radius:16px; border:1px solid var(--border); display:flex; flex-direction:column; gap:8px;">
        <div style="font-size:0.9rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">İptal Edilen</div>
        <div id="statCancelled" style="font-size:2.2rem; font-weight:800; color:#ef4444; font-family: 'Bebas Neue', sans-serif;">0</div>
        <div style="font-size:0.8rem; color:var(--text-muted);">İptal edilen siparişler</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(400px, 1fr)); gap:20px;">
    
    <!-- Kategori Bazlı -->
    <div class="report-section" style="background:var(--card-bg); border-radius:16px; border:1px solid var(--border); padding:24px;">
        <h3 style="font-family:'Bebas Neue',sans-serif; color:var(--text); letter-spacing:1px; margin-bottom:16px; font-size:1.4rem;">📁 Kategori Bazlı Satış</h3>
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color:var(--text-muted); text-align:left; font-size:0.85rem;">
                    <th style="padding:10px;">Kategori</th>
                    <th style="padding:10px;">Adet</th>
                    <th style="padding:10px; text-align:right;">Tutar</th>
                </tr>
            </thead>
            <tbody id="categoryTableBody">
                <tr><td colspan="3" style="text-align:center; padding:16px; color:var(--text-muted);">Yükleniyor...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- En Çok Satan Ürünler -->
    <div class="report-section" style="background:var(--card-bg); border-radius:16px; border:1px solid var(--border); padding:24px;">
        <h3 style="font-family:'Bebas Neue',sans-serif; color:var(--text); letter-spacing:1px; margin-bottom:16px; font-size:1.4rem;">🍔 En Çok Satan Ürünler</h3>
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color:var(--text-muted); text-align:left; font-size:0.85rem;">
                    <th style="padding:10px;">Ürün</th>
                    <th style="padding:10px;">Adet</th>
                    <th style="padding:10px; text-align:right;">Tutar</th>
                </tr>
            </thead>
            <tbody id="topProductsTableBody">
                <tr><td colspan="3" style="text-align:center; padding:16px; color:var(--text-muted);">Yükleniyor...</td></tr>
            </tbody>
        </table>
    </div>

</div>

<script>
function formatDate(date) {
    return date.toISOString().split('T')[0];
}

function setDateRange(range) {
    const startInput = document.getElementById('startDate');
    const endInput = document.getElementById('endDate');
    const today = new Date();
    
    let start, end;

    if (range === 'today') {
        start = new Date(today);
        end = new Date(today);
    } else if (range === 'yesterday') {
        start = new Date(today);
        start.setDate(today.getDate() - 1);
        end = new Date(start);
    } else if (range === 'this_week') {
        const day = today.getDay(); // 0 (Sun) to 6 (Sat)
        const diff = today.getDate() - day + (day === 0 ? -6 : 1); // Adjust when day is sunday
        start = new Date(today.setDate(diff));
        end = new Date(); // today
    } else if (range === 'this_month') {
        start = new Date(today.getFullYear(), today.getMonth(), 1);
        end = new Date(today.getFullYear(), today.getMonth() + 1, 0); // last day of month
    }

    startInput.value = formatDate(start);
    endInput.value = formatDate(end);
    fetchAccountingData();
}

async function fetchAccountingData() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    if (!startDate || !endDate) {
        showToast('Lütfen tarih aralığı seçin', 'error');
        return;
    }

    try {
        const data = await apiRequest('/accounting.php?start_date=' + startDate + '&end_date=' + endDate);
        
        // Summary
        document.getElementById('statRevenue').textContent = data.summary.total_revenue.toFixed(2) + ' ₺';
        document.getElementById('statOrders').textContent = data.summary.total_orders;
        document.getElementById('statCancelled').textContent = data.summary.total_cancelled;

        // Categories
        const catBody = document.getElementById('categoryTableBody');
        if (data.categories.length === 0) {
            catBody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:16px; color:var(--text-muted);">Veri bulunamadı.</td></tr>';
        } else {
            catBody.innerHTML = data.categories.map(c => 
                '<tr style="border-bottom:1px solid rgba(255,255,255,0.05);">' + 
                    '<td style="padding:12px 10px; font-weight:600;">' + (c.name || 'Bilinmeyen') + '</td>' +
                    '<td style="padding:12px 10px; color:var(--text-muted);">' + c.quantity + '</td>' +
                    '<td style="padding:12px 10px; text-align:right; color:var(--primary); font-weight:bold;">' + c.revenue.toFixed(2) + ' ₺</td>' +
                '</tr>'
            ).join('');
        }

        // Top Products
        const prodBody = document.getElementById('topProductsTableBody');
        if (data.top_products.length === 0) {
            prodBody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:16px; color:var(--text-muted);">Veri bulunamadı.</td></tr>';
        } else {
            prodBody.innerHTML = data.top_products.map(p => 
                '<tr style="border-bottom:1px solid rgba(255,255,255,0.05);">' + 
                    '<td style="padding:12px 10px; font-weight:600;">' + (p.name || 'İsimsiz') + '</td>' +
                    '<td style="padding:12px 10px; color:var(--text-muted);">' + p.quantity + '</td>' +
                    '<td style="padding:12px 10px; text-align:right; color:var(--success); font-weight:bold;">' + p.revenue.toFixed(2) + ' ₺</td>' +
                '</tr>'
            ).join('');
        }
        
        showToast('Raporlar güncellendi');

    } catch (e) {
        showToast('Hata: ' + e.message, 'error');
    }
}

// Init today
document.addEventListener('DOMContentLoaded', () => {
    setDateRange('today');
});
</script>
