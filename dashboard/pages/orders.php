<?php
/**
 * Dashboard — Siparişler Sayfası
 */
?>
<div class="dash-topbar">
    <h1>Siparişler</h1>
    <div style="display:flex; gap:10px;">
        <button class="btn btn-secondary" onclick="toggleSound()" id="soundToggleBtn">🔊 Ses Açık</button>
        <button class="btn btn-secondary" onclick="loadOrders(true)">🔄 Yenile</button>
    </div>
</div>

<div class="status-filter" id="statusFilter">
    <button class="status-btn all active" data-status="" onclick="setFilter(this)">📋 Tümü</button>
    <button class="status-btn pending" data-status="pending" onclick="setFilter(this)">⏳ Bekleyen</button>
    <button class="status-btn preparing" data-status="preparing" onclick="setFilter(this)">👨‍🍳 Hazırlanıyor</button>
    <button class="status-btn ready" data-status="ready" onclick="setFilter(this)">✅ Hazır</button>
    <button class="status-btn served" data-status="served" onclick="setFilter(this)">🍽️ Teslim Edildi</button>
    <button class="status-btn cancelled" data-status="cancelled" onclick="setFilter(this)">❌ İptal</button>
</div>

<div class="orders-board" id="ordersBoard">
    <!-- JS ile doldurulacak -->
</div>

<style>
/* Status Filter Buttons */
.status-filter {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding: 0 0 8px;
}

.status-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    border: 1px solid transparent;
    background: var(--bg-card);
    color: var(--text-secondary);
}

.status-btn:hover { transform: translateY(-1px); }

.status-btn.all { border-color: var(--border-color); }
.status-btn.all.active { background: #6366f1; color: white; border-color: #6366f1; box-shadow: 0 0 16px rgba(99,102,241,0.35); }

.status-btn.pending { border-color: #f59e0b33; }
.status-btn.pending.active { background: #f59e0b; color: #000; border-color: #f59e0b; box-shadow: 0 0 16px rgba(245,158,11,0.35); }

.status-btn.preparing { border-color: #3b82f633; }
.status-btn.preparing.active { background: #3b82f6; color: white; border-color: #3b82f6; box-shadow: 0 0 16px rgba(59,130,246,0.35); }

.status-btn.ready { border-color: #22c55e33; }
.status-btn.ready.active { background: #22c55e; color: white; border-color: #22c55e; box-shadow: 0 0 16px rgba(34,197,94,0.35); }

.status-btn.served { border-color: #6b728033; }
.status-btn.served.active { background: #6b7280; color: white; border-color: #6b7280; box-shadow: 0 0 16px rgba(107,114,128,0.3); }

.status-btn.cancelled { border-color: #ef444433; }
.status-btn.cancelled.active { background: #ef4444; color: white; border-color: #ef4444; box-shadow: 0 0 16px rgba(239,68,68,0.35); }

.orders-board {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
    padding: 20px 0;
}

.order-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    transition: var(--transition);
}

.order-card:hover {
    border-color: rgba(255,255,255,0.15);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border-color);
}

.order-id {
    font-weight: 700;
    font-family: var(--font-display);
    font-size: 1.1rem;
}

.order-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.order-badge.pending { background: #f59e0b22; color: #f59e0b; border: 1px solid #f59e0b44; }
.order-badge.preparing { background: #3b82f622; color: #3b82f6; border: 1px solid #3b82f644; }
.order-badge.ready { background: #22c55e22; color: #22c55e; border: 1px solid #22c55e44; }
.order-badge.served { background: #6b728022; color: #6b7280; border: 1px solid #6b728044; }
.order-badge.cancelled { background: #ef444422; color: #ef4444; border: 1px solid #ef444444; }

.order-meta {
    padding: 12px 16px;
    display: flex;
    gap: 16px;
    font-size: 0.82rem;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border-color);
}

.order-meta .meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
}

.order-items {
    padding: 12px 16px;
}

.order-item-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    font-size: 0.85rem;
}

.order-item-row .qty {
    color: var(--brand-primary);
    font-weight: 700;
    margin-right: 6px;
}

.order-item-row .item-price {
    color: var(--text-muted);
    font-weight: 600;
}

.order-note {
    padding: 0 16px 10px;
    font-size: 0.8rem;
    color: #f59e0b;
    font-style: italic;
}

.order-footer {
    padding: 12px 16px;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-total {
    font-weight: 800;
    font-size: 1.1rem;
    color: var(--brand-primary);
}

.order-actions {
    display: flex;
    gap: 6px;
}

.order-actions .btn {
    padding: 6px 10px;
    font-size: 0.75rem;
}

.order-time {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.empty-orders {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
}
.empty-orders .emoji { font-size: 3rem; margin-bottom: 10px; }

.order-source-badge {
    font-size: 0.7rem;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 600;
}
.order-source-badge.qr { background: #8b5cf622; color: #8b5cf6; }
.order-source-badge.waiter { background: #06b6d422; color: #06b6d4; }
</style>

<script>
const statusLabels = {
    pending: '⏳ Bekliyor',
    preparing: '👨‍🍳 Hazırlanıyor',
    ready: '✅ Hazır',
    served: '🍽️ Teslim Edildi',
    cancelled: '❌ İptal',
};

const nextStatus = {
    pending: 'preparing',
    preparing: 'ready',
    ready: 'served',
};

const nextStatusLabel = {
    pending: '👨‍🍳 Hazırla',
    preparing: '✅ Hazır',
    ready: '🍽️ Teslim Et',
};

let currentFilter = '';
let soundEnabled = true;

function toggleSound() {
    soundEnabled = !soundEnabled;
    const btn = document.getElementById('soundToggleBtn');
    btn.innerHTML = soundEnabled ? '🔊 Ses Açık' : '🔇 Ses Kapalı';
    btn.style.opacity = soundEnabled ? '1' : '0.6';
    if (soundEnabled) playNotificationSound();
}

function playNotificationSound() {
    if (!soundEnabled) return;
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        
        const playTone = (freq, startTime, duration) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, startTime);
            
            gain.gain.setValueAtTime(0, startTime);
            gain.gain.linearRampToValueAtTime(0.5, startTime + 0.05);
            gain.gain.exponentialRampToValueAtTime(0.01, startTime + duration);
            
            osc.start(startTime);
            osc.stop(startTime + duration);
        };

        const now = ctx.currentTime;
        playTone(783.99, now, 0.4); // G5 (Ding)
        playTone(659.25, now + 0.25, 0.6); // E5 (Dong)
    } catch(e) { console.warn('Audio play error:', e); }
}

let lastSeenOrderId = 0;
let isFirstLoad = true;

function setFilter(btn) {
    document.querySelectorAll('#statusFilter .status-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentFilter = btn.dataset.status;
    loadOrders(true);
}

async function loadOrders(isManual = false) {
    try {
        const endpoint = currentFilter ? '/orders.php?status=' + currentFilter : '/orders.php';
        const orders = await apiRequest(endpoint);
        
        let pendingOrders = orders;
        if (currentFilter !== 'pending' && currentFilter !== '') {
            pendingOrders = await apiRequest('/orders.php?status=pending');
        }

        const maxId1 = orders.length > 0 ? Math.max(...orders.map(o => parseInt(o.id))) : 0;
        const maxId2 = pendingOrders.length > 0 ? Math.max(...pendingOrders.map(o => parseInt(o.id))) : 0;
        const currentMaxId = Math.max(maxId1, maxId2);

        if (!isFirstLoad && currentMaxId > lastSeenOrderId) {
            playNotificationSound();
            showToast('🔔 Yeni sipariş geldi!', 'success');
        }

        if (currentMaxId > lastSeenOrderId || isFirstLoad) {
            lastSeenOrderId = Math.max(lastSeenOrderId, currentMaxId);
        }
        
        isFirstLoad = false;
        renderOrders(orders);
    } catch (e) {
        if (isManual) showToast('Siparişler yüklenemedi', 'error');
    }
}

function renderOrders(orders) {
    const board = document.getElementById('ordersBoard');

    if (orders.length === 0) {
        board.innerHTML = '<div class="empty-orders"><div class="emoji">📋</div><h3>Sipariş yok</h3><p>Seçili filtrede sipariş bulunamadı</p></div>';
        return;
    }

    board.innerHTML = orders.map(o => {
        const time = new Date(o.created_at).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
        const date = new Date(o.created_at).toLocaleDateString('tr-TR');

        let itemsHtml = (o.items || []).map(item => {
            return '<div class="order-item-row">' +
                '<div><span class="qty">' + item.quantity + 'x</span>' + escapeHtml(item.product_name || 'Ürün #' + item.product_id) + '</div>' +
                '<span class="item-price">' + (parseFloat(item.unit_price) * item.quantity).toFixed(2) + ' ₺</span>' +
            '</div>';
        }).join('');

        let actionsHtml = '';
        if (nextStatus[o.status]) {
            actionsHtml += '<button class="btn btn-primary" onclick="advanceOrder(' + o.id + ', \'' + nextStatus[o.status] + '\')">' + nextStatusLabel[o.status] + '</button>';
        }
        if (o.status === 'pending') {
            actionsHtml += '<button class="btn btn-secondary" style="color:var(--danger)" onclick="cancelOrder(' + o.id + ')">İptal</button>';
        }

        return '<div class="order-card">' +
            '<div class="order-header">' +
                '<span class="order-id">#' + (o.daily_order_number || o.id) + '</span>' +
                '<span class="order-badge ' + o.status + '">' + statusLabels[o.status] + '</span>' +
            '</div>' +
            '<div class="order-meta">' +
                (o.table_number ? '<span class="meta-item">🪑 Masa ' + o.table_number + '</span>' : '') +
                '<span class="meta-item"><span class="order-source-badge ' + o.order_type + '">' + (o.order_type === 'qr' ? '📱 QR' : '👨‍🍳 Garson') + '</span></span>' +
                (o.customer_name ? '<span class="meta-item">👤 ' + escapeHtml(o.customer_name) + '</span>' : '') +
                '<span class="meta-item order-time">🕐 ' + time + '</span>' +
            '</div>' +
            '<div class="order-items">' + itemsHtml + '</div>' +
            (o.note ? '<div class="order-note">📝 ' + escapeHtml(o.note) + '</div>' : '') +
            '<div class="order-footer">' +
                '<span class="order-total">' + o.total_price.toFixed(2) + ' ₺</span>' +
                '<div class="order-actions">' + actionsHtml + '</div>' +
            '</div>' +
        '</div>';
    }).join('');
}

async function advanceOrder(id, newStatus) {
    try {
        await apiRequest('/order.php?id=' + id, 'PATCH', { status: newStatus });
        showToast('Sipariş durumu güncellendi!');
        loadOrders();
    } catch (e) {
        showToast('Güncelleme hatası', 'error');
    }
}

async function cancelOrder(id) {
    if (!confirm('Siparişi iptal etmek istediğinize emin misiniz?')) return;
    try {
        await apiRequest('/order.php?id=' + id, 'PATCH', { status: 'cancelled' });
        showToast('Sipariş iptal edildi');
        loadOrders();
    } catch (e) {
        showToast('İptal hatası', 'error');
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Otomatik yenile (15 saniye - sipariş kontrolü için daha iyi)
loadOrders(true);
setInterval(() => loadOrders(false), 15000);
</script>
