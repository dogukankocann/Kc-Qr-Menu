<?php
/**
 * Dashboard — Ana Sayfa
 */
?>
<div class="dash-topbar">
    <h1>Dashboard</h1>
</div>

<div class="stat-grid" id="statsGrid">
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-value" id="statActiveOrders">—</div>
        <div class="stat-label">Aktif Sipariş</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-value" id="statTodayOrders">—</div>
        <div class="stat-label">Bugünkü Sipariş</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-value" id="statRevenue">—</div>
        <div class="stat-label">Bugünkü Gelir</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🍔</div>
        <div class="stat-value" id="statProducts">—</div>
        <div class="stat-label">Ürün</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📁</div>
        <div class="stat-value" id="statCategories">—</div>
        <div class="stat-label">Kategori</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🪑</div>
        <div class="stat-value" id="statTables">—</div>
        <div class="stat-label">Masa</div>
    </div>
</div>

<!-- Son Siparişler -->
<div style="margin-top:24px">
    <div class="dash-topbar" style="margin-bottom:12px">
        <h2 style="font-size:1.2rem">Son Siparişler</h2>
        <a href="/pashaqr-live/dashboard/orders" class="btn btn-secondary btn-sm">Tümünü Gör →</a>
    </div>
    <div id="recentOrders" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:12px;">
        <div style="text-align:center; padding:30px; color:var(--text-muted); grid-column: 1/-1;">Yükleniyor...</div>
    </div>
</div>

<script>
(async () => {
    try {
        const stats = await apiRequest('/stats.php');
        document.getElementById('statProducts').textContent = stats.products ?? 0;
        document.getElementById('statCategories').textContent = stats.categories ?? 0;
        document.getElementById('statTables').textContent = stats.tables ?? 0;
        document.getElementById('statActiveOrders').textContent = stats.active_orders ?? 0;
        document.getElementById('statTodayOrders').textContent = stats.today_orders ?? 0;
        document.getElementById('statRevenue').textContent = (stats.today_revenue ?? 0).toFixed(2) + ' ₺';
    } catch (e) {
        console.error('Stats yüklenemedi:', e);
    }

    // Son siparişler
    try {
        const orders = await apiRequest('/orders.php');
        const recent = orders.slice(0, 6);
        const container = document.getElementById('recentOrders');

        if (recent.length === 0) {
            container.innerHTML = '<div style="text-align:center; padding:40px; color:var(--text-muted); grid-column:1/-1"><div style="font-size:2rem;margin-bottom:8px">📋</div>Henüz sipariş yok</div>';
            return;
        }

        const statusLabels = { pending: '⏳ Bekliyor', preparing: '👨‍🍳 Hazırlanıyor', ready: '✅ Hazır', served: '🍽️ Teslim', cancelled: '❌ İptal' };

        container.innerHTML = recent.map(o => {
            const time = new Date(o.created_at).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
            const itemCount = (o.items || []).reduce((s, i) => s + i.quantity, 0);
            return '<div style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:10px; padding:14px 16px; display:flex; justify-content:space-between; align-items:center;">' +
                '<div>' +
                    '<strong>#' + o.id + '</strong>' +
                    (o.table_number ? ' <span style="color:var(--text-muted);font-size:0.85rem">• Masa ' + o.table_number + '</span>' : '') +
                    '<div style="font-size:0.8rem; color:var(--text-muted); margin-top:2px">' + itemCount + ' ürün • ' + time + '</div>' +
                '</div>' +
                '<div style="text-align:right">' +
                    '<div style="font-weight:700; color:var(--primary)">' + parseFloat(o.total_price).toFixed(2) + ' ₺</div>' +
                    '<span class="order-badge ' + o.status + '" style="padding:2px 8px;border-radius:10px;font-size:0.65rem;font-weight:700">' + statusLabels[o.status] + '</span>' +
                '</div>' +
            '</div>';
        }).join('');
    } catch (e) {
        console.error('Siparişler yüklenemedi:', e);
    }
})();
</script>

<style>
.order-badge.pending { background: #f59e0b22; color: #f59e0b; }
.order-badge.preparing { background: #3b82f622; color: #3b82f6; }
.order-badge.ready { background: #22c55e22; color: #22c55e; }
.order-badge.served { background: #6b728022; color: #6b7280; }
.order-badge.cancelled { background: #ef444422; color: #ef4444; }
</style>
