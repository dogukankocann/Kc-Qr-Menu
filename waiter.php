<?php
/**
 * Pasha Fastfood — Garson Sipariş Paneli
 * Garsonların hızlıca sipariş girmesi için mobil-uyumlu arayüz
 */
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'waiter'])) {
    header('Location: /pashaqr-live/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasha Fastfood — Garson Paneli</title>
    <link rel="icon" href="/pashaqr-live/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Bebas+Neue&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0f0f1a;
            --card-bg: #1a1a2e;
            --border: #2a2a3e;
            --primary: #DC2626;
            --success: #22c55e;
            --text: #f1f1f1;
            --text-muted: #8b8ba3;
            --radius: 12px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* Header */
        .waiter-header {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .waiter-header h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.3rem;
            letter-spacing: 1px;
        }

        .waiter-header .back-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
        }

        /* Layout */
        .waiter-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            min-height: calc(100vh - 52px);
        }

        @media (max-width: 768px) {
            .waiter-layout { grid-template-columns: 1fr; }
            .cart-panel { position: fixed; bottom: 0; left: 0; right: 0; max-height: 50vh; border-radius: 20px 20px 0 0; transform: translateY(calc(100% - 60px)); transition: transform 0.3s ease; z-index: 50; }
            .cart-panel.open { transform: translateY(0); }
            .cart-toggle { display: flex !important; }
        }

        /* Masa Seçimi */
        .table-selector {
            padding: 16px;
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
        }

        .table-selector label {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 6px;
            display: block;
        }

        .table-selector select {
            width: 100%;
            padding: 10px 12px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 0.9rem;
        }

        /* Ürün alanı */
        .menu-section {
            padding: 16px;
            overflow-y: auto;
        }

        /* Kategori filtreleme */
        .cat-filter {
            display: flex;
            gap: 8px;
            margin-bottom: 14px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .cat-filter button {
            background: var(--card-bg);
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            white-space: nowrap;
            cursor: pointer;
            transition: 0.2s;
        }

        .cat-filter button.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Ürün grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 10px;
        }

        .product-item {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 12px;
            cursor: pointer;
            transition: 0.2s;
            text-align: center;
        }

        .product-item:hover { border-color: var(--primary); transform: scale(1.02); }
        .product-item:active { transform: scale(0.97); }

        .product-item .p-emoji { font-size: 1.8rem; margin-bottom: 6px; }
        .product-item .p-img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; margin-bottom: 6px; }
        .product-item .p-name { font-size: 0.8rem; font-weight: 600; line-height: 1.2; margin-bottom: 4px; }
        .product-item .p-price { font-size: 0.85rem; font-weight: 700; color: var(--primary); }

        /* Sepet paneli */
        .cart-panel {
            background: var(--card-bg);
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }

        .cart-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            font-weight: 700;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-toggle {
            display: none;
            width: 40px;
            height: 4px;
            background: var(--border);
            border-radius: 2px;
            margin: 0 auto 8px;
            cursor: pointer;
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 8px 0;
        }

        .cart-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }

        .cart-item .ci-info { flex: 1; }
        .cart-item .ci-name { font-size: 0.85rem; font-weight: 600; }
        .cart-item .ci-price { font-size: 0.75rem; color: var(--text-muted); }

        .cart-item .ci-qty {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cart-item .ci-qty button {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--bg);
            color: var(--text);
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.15s;
        }

        .cart-item .ci-qty button:hover { border-color: var(--primary); }
        .cart-item .ci-qty span { font-weight: 700; min-width: 20px; text-align: center; }

        /* Not alanı */
        .cart-note {
            padding: 10px 16px;
            border-top: 1px solid var(--border);
        }

        .cart-note textarea {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            padding: 8px 10px;
            font-size: 0.8rem;
            resize: none;
            font-family: inherit;
        }

        /* Toplam ve gönder */
        .cart-footer {
            padding: 14px 16px;
            border-top: 1px solid var(--border);
        }

        .cart-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.1rem;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .cart-total .total-amount { color: var(--primary); }

        .btn-send {
            width: 100%;
            padding: 14px;
            background: var(--success);
            border: none;
            border-radius: var(--radius);
            color: white;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-send:hover { background: #16a34a; }
        .btn-send:disabled { opacity: 0.4; cursor: not-allowed; }

        .btn-clear {
            width: 100%;
            padding: 10px;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text-muted);
            font-size: 0.85rem;
            cursor: pointer;
            margin-top: 8px;
        }

        .cart-empty {
            text-align: center;
            padding: 40px 16px;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        .cart-empty .emoji { font-size: 2rem; margin-bottom: 8px; }

        /* Toast */
        .toast-container { position: fixed; top: 16px; right: 16px; z-index: 9999; }
        .toast { background: var(--success); color: white; padding: 12px 20px; border-radius: 10px; margin-bottom: 8px; font-weight: 600; font-size: 0.85rem; animation: slideIn 0.3s ease; }
        .toast.error { background: var(--primary); }
        @keyframes slideIn { from { opacity: 0; transform: translateX(40px); } to { opacity: 1; transform: translateX(0); } }

        /* Tab nav */
        .waiter-tabs {
            display: flex;
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
        }
        .waiter-tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: 0.2s;
            color: var(--text-muted);
            border-bottom: 3px solid transparent;
        }
        .waiter-tab:hover { color: var(--text); }
        .waiter-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Sipariş takip */
        .my-orders-section { padding: 16px; }

        .wo-status-filter {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .wo-status-btn {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            background: var(--card-bg);
            color: var(--text-muted);
            transition: 0.2s;
        }
        .wo-status-btn:hover { transform: translateY(-1px); }
        .wo-status-btn.active.all { background: #6366f1; color: white; border-color: #6366f1; box-shadow: 0 0 12px rgba(99,102,241,0.3); }
        .wo-status-btn.active.pending { background: #f59e0b; color: #000; border-color: #f59e0b; box-shadow: 0 0 12px rgba(245,158,11,0.3); }
        .wo-status-btn.active.preparing { background: #3b82f6; color: white; border-color: #3b82f6; box-shadow: 0 0 12px rgba(59,130,246,0.3); }
        .wo-status-btn.active.ready { background: #22c55e; color: white; border-color: #22c55e; box-shadow: 0 0 12px rgba(34,197,94,0.3); }
        .wo-status-btn.active.served { background: #6b7280; color: white; border-color: #6b7280; }
        .wo-status-btn.active.cancelled { background: #ef4444; color: white; border-color: #ef4444; }

        .wo-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 10px;
            overflow: hidden;
        }
        .wo-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
        }
        .wo-card-header .wo-id { font-weight: 700; font-size: 0.95rem; }
        .wo-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .wo-badge.pending { background: #f59e0b22; color: #f59e0b; border: 1px solid #f59e0b44; }
        .wo-badge.preparing { background: #3b82f622; color: #3b82f6; border: 1px solid #3b82f644; }
        .wo-badge.ready { background: #22c55e22; color: #22c55e; border: 1px solid #22c55e44; }
        .wo-badge.served { background: #6b728022; color: #6b7280; border: 1px solid #6b728044; }
        .wo-badge.cancelled { background: #ef444422; color: #ef4444; border: 1px solid #ef444444; }

        .wo-meta {
            padding: 8px 14px;
            font-size: 0.78rem;
            color: var(--text-muted);
            display: flex;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .wo-items { padding: 8px 14px; }
        .wo-item-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 0.82rem;
        }
        .wo-item-row .wo-qty { color: var(--primary); font-weight: 700; margin-right: 4px; }
        .wo-item-row .wo-price { color: var(--text-muted); font-weight: 600; }

        .wo-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            border-top: 1px solid var(--border);
        }
        .wo-total { font-weight: 800; font-size: 1rem; color: var(--primary); }
        .wo-actions { display: flex; gap: 6px; }
        .wo-actions button {
            padding: 5px 10px;
            border-radius: 8px;
            border: none;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .wo-btn-advance { background: var(--success); color: white; }
        .wo-btn-advance:hover { background: #16a34a; }
        .wo-btn-cancel { background: rgba(239,68,68,0.15); color: #ef4444; }
        .wo-btn-cancel:hover { background: rgba(239,68,68,0.25); }

        .wo-btn-edit { background: #3b82f6; color: white; }
        .wo-btn-edit:hover { background: #2563eb; }

        .wo-empty {
            text-align: center;
            padding: 50px 16px;
            color: var(--text-muted);
            font-size: 0.85rem;
            grid-column: 1 / -1;
        }
        .wo-empty .emoji { font-size: 2.5rem; margin-bottom: 8px; }

        #waiterOrdersList {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
        }

        /* Varyant Seçim Popup */
        .variant-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }
        .variant-overlay.show { display: flex; }
        .variant-box {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            width: 90%;
            max-width: 360px;
            animation: popIn 0.25s ease;
        }
        @keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .variant-box h3 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.2rem;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        .variant-box .v-product-name {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 16px;
        }
        .variant-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 14px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: 0.2s;
        }
        .variant-option:hover { border-color: var(--primary); background: rgba(220,38,38,0.05); }
        .variant-option:active { transform: scale(0.97); }
        .variant-option .v-name { font-weight: 600; font-size: 0.9rem; }
        .variant-option .v-price { font-size: 0.85rem; color: var(--primary); font-weight: 700; }
        .variant-cancel {
            width: 100%;
            padding: 10px;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-muted);
            font-size: 0.85rem;
            cursor: pointer;
            margin-top: 4px;
        }
    </style>
</head>
<body>

<header class="waiter-header">
    <h1>👨‍🍳 GARSON PANELİ</h1>
    <a href="/pashaqr-live/logout.php" class="back-link" style="color:var(--primary); font-weight:600;">🚪 Çıkış Yap</a>
</header>

<!-- Tab Navigasyonu -->
<div class="waiter-tabs">
    <div class="waiter-tab active" onclick="switchTab('menu')">🍽️ Menü</div>
    <div class="waiter-tab" onclick="switchTab('orders')">📋 Siparişlerim</div>
</div>

<!-- Tab: Menü -->
<div class="tab-content active" id="tabMenu">
<div class="waiter-layout">
    <!-- Sol: Menü -->
    <div>
        <!-- Masa Seçimi -->
        <div class="table-selector">
            <label>Masa Seçin</label>
            <select id="tableSelect">
                <option value="">— Masa seçin —</option>
            </select>
        </div>

        <!-- Ürün Seçimi -->
        <div class="menu-section">
            <div class="cat-filter" id="catFilter"></div>
            <div class="product-grid" id="productGrid"></div>
        </div>
    </div>

    <!-- Sağ: Sepet -->
    <div class="cart-panel" id="cartPanel">
        <div class="cart-toggle" onclick="toggleCart()"></div>
        <div class="cart-header">
            <span>🛒 Sepet</span>
            <span id="cartCount" style="font-size:0.8rem; color:var(--text-muted); font-family:Inter,sans-serif">0 ürün</span>
        </div>
        <div class="cart-items" id="cartItems">
            <div class="cart-empty">
                <div class="emoji">🛒</div>
                Sepet boş — ürün seçin
            </div>
        </div>
        <div class="cart-note">
            <textarea id="orderNote" placeholder="Sipariş notu (opsiyonel)..." rows="2"></textarea>
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Toplam</span>
                <span class="total-amount" id="cartTotal">0.00 ₺</span>
            </div>
            <button class="btn-send" id="sendBtn" onclick="sendOrder()" disabled>📤 Siparişi Gönder</button>
            <button class="btn-clear" onclick="clearCart()">🗑️ Sepeti Temizle</button>
        </div>
    </div>
</div>
</div> <!-- /tabMenu -->

<!-- Tab: Siparişlerim -->
<div class="tab-content" id="tabOrders">
    <div class="my-orders-section">
        <div class="wo-status-filter" id="woStatusFilter">
            <button class="wo-status-btn all active" data-status="" onclick="setWoFilter(this)">📋 Tümü</button>
            <button class="wo-status-btn pending" data-status="pending" onclick="setWoFilter(this)">⏳ Bekleyen</button>
            <button class="wo-status-btn preparing" data-status="preparing" onclick="setWoFilter(this)">👨‍🍳 Hazırlanıyor</button>
            <button class="wo-status-btn ready" data-status="ready" onclick="setWoFilter(this)">✅ Hazır</button>
            <button class="wo-status-btn served" data-status="served" onclick="setWoFilter(this)">🍽️ Teslim Edildi</button>
            <button class="wo-status-btn cancelled" data-status="cancelled" onclick="setWoFilter(this)">❌ İptal</button>
        </div>
        <div id="waiterOrdersList"></div>
    </div>
</div> <!-- /tabOrders -->

<div class="toast-container" id="toastContainer"></div>

<!-- Varyant Seçim Popup -->
<div class="variant-overlay" id="variantOverlay" onclick="if(event.target===this)closeVariantPopup()">
    <div class="variant-box">
        <h3>🍝 Makarna Tipi Seçin</h3>
        <div class="v-product-name" id="variantProductName"></div>
        <div id="variantOptions"></div>
        <button class="variant-cancel" onclick="closeVariantPopup()">Vazgeç</button>
    </div>
</div>

<script>
const API_BASE = '/pashaqr-live/api';
let allProducts = [];
let allCategories = [];
let cart = []; // { product_id, name, price, quantity, variant_id, variant_name }
let editingOrderId = null;

// Toast
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Verileri yükle
async function init() {
    try {
        const [products, categories, tables] = await Promise.all([
            fetch(API_BASE + '/products.php?available=1').then(r => r.json()),
            fetch(API_BASE + '/categories.php?active=1').then(r => r.json()),
            fetch(API_BASE + '/tables.php').then(r => r.json()),
        ]);

        allProducts = products;
        allCategories = categories;

        // Masa dropdown
        const sel = document.getElementById('tableSelect');
        tables.filter(t => t.is_active == 1).forEach(t => {
            sel.innerHTML += '<option value="' + t.id + '">Masa ' + t.table_number + (t.name ? ' (' + t.name + ')' : '') + '</option>';
        });

        // Kategori filtreleri
        const catFilter = document.getElementById('catFilter');
        catFilter.innerHTML = '<button class="active" data-cat="all">Tümü</button>';
        categories.forEach(c => {
            catFilter.innerHTML += '<button data-cat="' + c.slug + '">' + (c.icon || '') + ' ' + c.name + '</button>';
        });

        catFilter.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', () => {
                catFilter.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                renderProducts(btn.dataset.cat);
            });
        });

        renderProducts('all');
    } catch (e) {
        showToast('Veriler yüklenemedi: ' + e.message, 'error');
    }
}

function renderProducts(category) {
    const grid = document.getElementById('productGrid');
    const filtered = category === 'all' ? allProducts : allProducts.filter(p => p.category && p.category.slug === category);

    grid.innerHTML = filtered.map(p => {
        const imgHtml = p.image_url
            ? '<img class="p-img" src="/pashaqr-live' + p.image_url + '" alt="" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'block\'">' +
              '<div class="p-emoji" style="display:none">🍔</div>'
            : '<div class="p-emoji">🍔</div>';

        return '<div class="product-item" onclick="addToCart(' + p.id + ')">' +
            imgHtml +
            '<div class="p-name">' + escapeHtml(p.name) + '</div>' +
            '<div class="p-price">' + parseFloat(p.price).toFixed(2) + ' ₺</div>' +
        '</div>';
    }).join('');
}

// Sepet işlemleri
function addToCart(productId) {
    const product = allProducts.find(p => p.id === productId);
    if (!product) return;

    // Varyanlı ürün
    if (product.variants && product.variants.length > 0) {
        showVariantPopup(product);
        return;
    }

    // Varyantsiz direkt ekle
    addToCartDirect(productId, product.name, parseFloat(product.price), null, null);
}

function addToCartDirect(productId, name, price, variantId, variantName) {
    // Aynı ürün + aynı varyant kartusunu ara
    const cartKey = productId + '_' + (variantId || '0');
    const existing = cart.find(c => c._key === cartKey);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({
            _key: cartKey,
            product_id: productId,
            name: name,
            price: price,
            quantity: 1,
            variant_id: variantId,
            variant_name: variantName,
        });
    }

    renderCart();

    // Mobile: Sepeti göster
    if (window.innerWidth <= 768) {
        document.getElementById('cartPanel').classList.add('open');
    }
}

// Varyant popup
function showVariantPopup(product) {
    document.getElementById('variantProductName').textContent = product.name;
    const container = document.getElementById('variantOptions');
    const basePrice = parseFloat(product.price);

    container.innerHTML = product.variants.map(v => {
        const delta = parseFloat(v.price_delta) || 0;
        const totalPrice = basePrice + delta;
        const priceLabel = delta > 0 ? totalPrice.toFixed(2) + ' ₺ (+' + delta.toFixed(2) + ')'
                         : delta < 0 ? totalPrice.toFixed(2) + ' ₺ (' + delta.toFixed(2) + ')'
                         : totalPrice.toFixed(2) + ' ₺';
        return '<div class="variant-option" onclick="selectVariant(' + product.id + ', ' + (v.id || 0) + ', \'' + escapeHtml(v.name).replace(/'/g, "\\'") + '\', ' + totalPrice + ')">' +
            '<span class="v-name">' + escapeHtml(v.name) + '</span>' +
            '<span class="v-price">' + priceLabel + '</span>' +
        '</div>';
    }).join('');

    document.getElementById('variantOverlay').classList.add('show');
}

function selectVariant(productId, variantId, variantName, totalPrice) {
    const product = allProducts.find(p => p.id === productId);
    const displayName = product.name + ' (' + variantName + ')';
    addToCartDirect(productId, displayName, totalPrice, variantId, variantName);
    closeVariantPopup();
}

function closeVariantPopup() {
    document.getElementById('variantOverlay').classList.remove('show');
}

function removeFromCart(cartKey) {
    const idx = cart.findIndex(c => c._key === cartKey);
    if (idx >= 0) {
        if (cart[idx].quantity > 1) {
            cart[idx].quantity--;
        } else {
            cart.splice(idx, 1);
        }
    }
    renderCart();
}

function addMoreToCart(cartKey) {
    const item = cart.find(c => c._key === cartKey);
    if (item) item.quantity++;
    renderCart();
}

function clearCart() {
    cart = [];
    editingOrderId = null;
    document.getElementById('sendBtn').textContent = '📤 Siparişi Gönder';
    document.getElementById('orderNote').value = '';
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItems');
    const totalEl = document.getElementById('cartTotal');
    const countEl = document.getElementById('cartCount');
    const sendBtn = document.getElementById('sendBtn');

    if (cart.length === 0) {
        container.innerHTML = '<div class="cart-empty"><div class="emoji">🛒</div>Sepet boş — ürün seçin</div>';
        totalEl.textContent = '0.00 ₺';
        countEl.textContent = '0 ürün';
        sendBtn.disabled = true;
        return;
    }

    let total = 0;
    let totalItems = 0;

    container.innerHTML = cart.map(item => {
        const lineTotal = item.price * item.quantity;
        total += lineTotal;
        totalItems += item.quantity;

        return '<div class="cart-item">' +
            '<div class="ci-info">' +
                '<div class="ci-name">' + escapeHtml(item.name) + '</div>' +
                '<div class="ci-price">' + item.price.toFixed(2) + ' ₺ × ' + item.quantity + ' = ' + lineTotal.toFixed(2) + ' ₺</div>' +
            '</div>' +
            '<div class="ci-qty">' +
                '<button onclick="removeFromCart(\'' + item._key + '\')">−</button>' +
                '<span>' + item.quantity + '</span>' +
                '<button onclick="addMoreToCart(\'' + item._key + '\')">+</button>' +
            '</div>' +
        '</div>';
    }).join('');

    totalEl.textContent = total.toFixed(2) + ' ₺';
    countEl.textContent = totalItems + ' ürün';
    sendBtn.disabled = false;
}

// Sipariş gönder / güncelle
async function sendOrder() {
    const tableId = document.getElementById('tableSelect').value;
    const note = document.getElementById('orderNote').value;

    if (!tableId) {
        showToast('Lütfen masa seçin!', 'error');
        return;
    }

    if (cart.length === 0) {
        showToast('Sepet boş!', 'error');
        return;
    }

    const orderData = {
        table_id: parseInt(tableId),
        order_type: 'waiter',
        note: note || null,
        items: cart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            variant_id: item.variant_id || null,
        })),
    };

    try {
        const btn = document.getElementById('sendBtn');
        btn.disabled = true;
        btn.textContent = editingOrderId ? '⏳ Güncelleniyor...' : '⏳ Gönderiliyor...';

        const method = editingOrderId ? 'PUT' : 'POST';
        const endpoint = editingOrderId ? '/order.php?id=' + editingOrderId : '/orders.php';

        const res = await fetch(API_BASE + endpoint, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData),
        });

        const result = await res.json();

        if (result.error) {
            showToast('Hata: ' + result.error, 'error');
            return;
        }

        const msg = editingOrderId 
            ? '✅ Sipariş güncellendi! (' + result.total_price.toFixed(2) + ' ₺)' 
            : '✅ Sipariş #' + (result.daily_order_number || result.id) + ' oluşturuldu! (' + result.total_price.toFixed(2) + ' ₺)';
            
        showToast(msg);
        clearCart();
        switchTab('orders');
    } catch (e) {
        showToast('İşlem başarısız: ' + e.message, 'error');
    } finally {
        const btn = document.getElementById('sendBtn');
        btn.disabled = false;
        btn.textContent = editingOrderId ? '💾 Siparişi Güncelle' : '📤 Siparişi Gönder';
    }
}

// Mobile sepet toggle
function toggleCart() {
    document.getElementById('cartPanel').classList.toggle('open');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

init();

// ================== TAB SİSTEMİ ==================
function switchTab(tab) {
    document.querySelectorAll('.waiter-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

    if (tab === 'menu') {
        document.querySelectorAll('.waiter-tab')[0].classList.add('active');
        document.getElementById('tabMenu').classList.add('active');
    } else {
        document.querySelectorAll('.waiter-tab')[1].classList.add('active');
        document.getElementById('tabOrders').classList.add('active');
        loadWaiterOrders();
    }
}

// ================== SİPARİŞLERİM ==================
const woStatusLabels = {
    pending: '⏳ Bekliyor',
    preparing: '👨‍🍳 Hazırlanıyor',
    ready: '✅ Hazır',
    served: '🍽️ Teslim Edildi',
    cancelled: '❌ İptal',
};

const woNextStatus = { pending: 'preparing', preparing: 'ready', ready: 'served' };
const woNextLabel = { pending: '👨‍🍳 Hazırla', preparing: '✅ Hazır', ready: '🍽️ Teslim Et' };

let woFilter = '';

function setWoFilter(btn) {
    document.querySelectorAll('#woStatusFilter .wo-status-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    woFilter = btn.dataset.status;
    loadWaiterOrders();
}

async function loadWaiterOrders() {
    try {
        const endpoint = woFilter
            ? '/orders.php?order_type=waiter&today=1&status=' + woFilter
            : '/orders.php?order_type=waiter&today=1';
        const orders = await fetch(API_BASE + endpoint).then(r => r.json());
        renderWaiterOrders(orders);
    } catch(e) {
        showToast('Siparişler yüklenemedi', 'error');
    }
}

function renderWaiterOrders(orders) {
    const container = document.getElementById('waiterOrdersList');

    if (orders.length === 0) {
        container.innerHTML = '<div class="wo-empty"><div class="emoji">📋</div>Sipariş bulunamadı</div>';
        return;
    }

    container.innerHTML = orders.map(o => {
        const time = new Date(o.created_at).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });

        const itemsHtml = (o.items || []).map(item =>
            '<div class="wo-item-row">' +
                '<div><span class="wo-qty">' + item.quantity + 'x</span>' + escapeHtml(item.product_name || 'Ürün #' + item.product_id) + '</div>' +
                '<span class="wo-price">' + (parseFloat(item.unit_price) * item.quantity).toFixed(2) + ' ₺</span>' +
            '</div>'
        ).join('');

        let actionsHtml = '';
        if (o.status === 'pending') {
            actionsHtml += '<button class="wo-btn-edit" onclick="editWo(' + o.id + ')">✏️ Düzenle</button>';
            actionsHtml += '<button class="wo-btn-cancel" onclick="cancelWo(' + o.id + ')">İptal</button>';
        }

        return '<div class="wo-card">' +
            '<div class="wo-card-header">' +
                '<span class="wo-id">#' + (o.daily_order_number || o.id) + '</span>' +
                '<span class="wo-badge ' + o.status + '">' + woStatusLabels[o.status] + '</span>' +
            '</div>' +
            '<div class="wo-meta">' +
                (o.table_number ? '<span>🪑 Masa ' + o.table_number + '</span>' : '') +
                '<span>🕐 ' + time + '</span>' +
            '</div>' +
            '<div class="wo-items">' + itemsHtml + '</div>' +
            (o.note ? '<div style="padding:4px 14px 8px;font-size:0.78rem;color:#f59e0b;font-style:italic">📝 ' + escapeHtml(o.note) + '</div>' : '') +
            '<div class="wo-footer">' +
                '<span class="wo-total">' + parseFloat(o.total_price).toFixed(2) + ' ₺</span>' +
                '<div class="wo-actions">' + actionsHtml + '</div>' +
            '</div>' +
        '</div>';
    }).join('');
}

async function editWo(id) {
    try {
        const res = await fetch(API_BASE + '/order.php?id=' + id);
        const order = await res.json();

        if (order.error) {
            showToast('Sipariş bulunamadı', 'error');
            return;
        }

        editingOrderId = id;
        document.getElementById('tableSelect').value = order.table_id || '';
        document.getElementById('orderNote').value = order.note || '';

        cart = (order.items || []).map(i => ({
            _key: i.product_id + '_' + (i.variant_id || '0'),
            product_id: parseInt(i.product_id),
            name: i.product_name + (i.variant_id ? ' (Değişiklik İçerebilir)' : ''), // Basit isim
            price: parseFloat(i.unit_price),
            quantity: parseInt(i.quantity),
            variant_id: i.variant_id ? parseInt(i.variant_id) : null,
            variant_name: null 
        }));

        document.getElementById('sendBtn').textContent = '💾 Siparişi Güncelle';
        renderCart();
        switchTab('menu');
        showToast('Siparişi düzenliyorsunuz');
    } catch(e) {
        showToast('Sipariş yüklenemedi', 'error');
    }
}

async function cancelWo(id) {
    if (!confirm('Siparişi iptal etmek istediğinize emin misiniz?')) return;
    try {
        await fetch(API_BASE + '/order.php?id=' + id, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: 'cancelled' }),
        });
        showToast('Sipariş iptal edildi');
        loadWaiterOrders();
    } catch(e) {
        showToast('İptal hatası', 'error');
    }
}

// Otomatik yenile - eğer siparişler tabindaysaque
setInterval(() => {
    if (document.getElementById('tabOrders').classList.contains('active')) {
        loadWaiterOrders();
    }
}, 30000);
</script>

</body>
</html>
