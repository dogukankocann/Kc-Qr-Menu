<?php
/**
 * Pasha Fastfood — QR Menü (Ana Sayfa)
 * Müşteriler sadece menüyü görür, sipariş garson panelinden alınır.
 */

// Ayarları çek
$settingsJson = @file_get_contents("http://{$_SERVER['HTTP_HOST']}/pashaqr-live/api/settings.php");
$settings = $settingsJson ? json_decode($settingsJson, true) : null;
if (!$settings) {
    $settings = [
        'restaurant_name' => 'Pasha Fastfood',
        'logo_url' => null,
        'primary_color' => '#DC2626',
        'accent_color' => '#F59E0B',
        'currency' => '₺',
        'announcement' => null,
        'is_open' => true,
    ];
}

// Kategorileri çek
$categoriesJson = @file_get_contents("http://{$_SERVER['HTTP_HOST']}/pashaqr-live/api/categories.php?active=1");
$categories = $categoriesJson ? json_decode($categoriesJson, true) : [];

// Ürünleri çek
$productsJson = @file_get_contents("http://{$_SERVER['HTTP_HOST']}/pashaqr-live/api/products.php?available=1");
$products = $productsJson ? json_decode($productsJson, true) : [];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['restaurant_name']) ?> — QR Menü</title>
    <meta name="description" content="<?= htmlspecialchars($settings['restaurant_name']) ?> dijital QR menü sistemi. Lezzetli yemekler, hızlı servis.">
    <meta name="keywords" content="pasha fastfood, qr menü, fast food, dijital menü">
    <link rel="stylesheet" href="/pashaqr-live/assets/css/style.css">
    <style>
        .instagram-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            color: #fff;
            padding: 6px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(220, 39, 67, 0.3);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .instagram-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 39, 67, 0.4);
        }
    </style>
</head>
<body>

<!-- Duyuru Bandı -->
<?php if (!empty($settings['announcement'])): ?>
<div class="announcement-bar">
    <?= htmlspecialchars($settings['announcement']) ?>
</div>
<?php endif; ?>

<!-- Header -->
<header class="header">
    <div class="header-inner">
        <div class="header-logo">
            <div class="logo-icon">
                <?php if (!empty($settings['logo_url'])): ?>
                    <img src="/pashaqr-live<?= htmlspecialchars($settings['logo_url']) ?>" alt="Logo">
                <?php else: ?>
                    🍔
                <?php endif; ?>
            </div>
        </div>
        <h1 class="header-title"><?= htmlspecialchars($settings['restaurant_name']) ?></h1>
        <?php if (!empty($settings['instagram_url']) || true): ?>
            <a href="<?= htmlspecialchars($settings['instagram_url'] ?? 'https://instagram.com') ?>" target="_blank" class="instagram-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                Instagram
            </a>
        <?php endif; ?>
    </div>
</header>

<!-- Kategori Navigasyonu -->
<nav class="category-nav">
    <div class="category-scroll">
        <button class="category-btn active" data-category="all">Tümü</button>
        <?php foreach ($categories as $cat): ?>
        <button class="category-btn" data-category="<?= htmlspecialchars($cat['slug']) ?>">
            <?php if (!empty($cat['icon'])): ?>
                <span class="cat-icon"><?= $cat['icon'] ?></span>
            <?php endif; ?>
            <?= htmlspecialchars($cat['name']) ?>
        </button>
        <?php endforeach; ?>
    </div>
</nav>

<!-- Menü İçeriği -->
<main class="menu-container">
    <?php if (empty($products)): ?>
    <div class="empty-state">
        <div class="empty-icon">🍽️</div>
        <h3>Menü henüz hazırlanıyor</h3>
        <p>Lezzetli ürünler yakında burada olacak!</p>
    </div>
    <?php else: ?>
    <div class="menu-grid" id="menuGrid">
        <?php foreach ($products as $product): ?>
        <div class="product-card"
             data-category="<?= htmlspecialchars($product['category']['slug'] ?? '') ?>"
             onclick="openProductModal(<?= htmlspecialchars(json_encode($product, JSON_UNESCAPED_UNICODE)) ?>)">

            <!-- Badges -->
            <div class="badges">
                <?php if ($product['is_featured']): ?>
                    <span class="badge badge-featured">⭐ Öne Çıkan</span>
                <?php endif; ?>
            </div>

            <!-- Ürün Görseli -->
            <div class="image-wrap">
                <?php if (!empty($product['image_url'])): ?>
                    <img src="/pashaqr-live<?= htmlspecialchars($product['image_url']) ?>"
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         loading="lazy"
                         onerror="this.parentElement.innerHTML='<div class=\'no-image\'>🍔</div>'">
                <?php else: ?>
                    <div class="no-image">🍔</div>
                <?php endif; ?>
            </div>

            <!-- Kart Gövdesi -->
            <div class="card-body">
                <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                <p class="product-desc">
                    <?= htmlspecialchars($product['description_tr'] ?? $product['description'] ?? '') ?>
                </p>
                <div class="card-footer">
                    <span class="price">
                        <?= number_format($product['price'], 2) ?> <?= htmlspecialchars($settings['currency']) ?>
                    </span>
                    <?php if (!empty($product['calories'])): ?>
                    <span class="calories">🔥 <?= $product['calories'] ?> kcal</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<!-- Footer -->
<footer class="footer" style="padding: 30px 20px; text-align: center; color: var(--text-muted); font-size: 0.9rem;">
    <div style="display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap;">
        <span>&copy; Copyright <?= date('Y') ?>. Made with ❤️ by</span>
        <a href="https://kocancreative.com/" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center;">
            <img src="/pashaqr-live/uploads/kocan-creative-logo.png" alt="Koçan Creative" style="height: 24px; vertical-align: middle;">
        </a>
    </div>
</footer>

<!-- Ürün Detay Modalı -->
<div class="modal-overlay" id="productModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeProductModal()">✕</button>
        <div class="modal-image" id="modalImage"></div>
        <div class="modal-body">
            <h2 class="product-name" id="modalName"></h2>
            <p class="product-category" id="modalCategory"></p>
            <p class="product-desc" id="modalDesc"></p>
            <div id="modalVariants"></div>
            <div id="modalTags"></div>
            <div class="detail-row">
                <span class="detail-label">Fiyat</span>
                <span class="detail-value modal-price" id="modalPrice"></span>
            </div>
            <div class="detail-row" id="modalCaloriesRow" style="display:none">
                <span class="detail-label">Kalori</span>
                <span class="detail-value" id="modalCalories"></span>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<script>
// ======== Kategori Filtreleme ========
document.querySelectorAll('.category-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        // Aktif butonu değiştir
        document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const cat = btn.dataset.category;
        const cards = document.querySelectorAll('.product-card');

        cards.forEach(card => {
            if (cat === 'all' || card.dataset.category === cat) {
                card.style.display = '';
                card.style.animation = 'fadeIn 0.3s ease';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// ======== Ürün Detay Modalı ========
const currency = '<?= addslashes($settings['currency']) ?>';

function openProductModal(product) {
    const modal = document.getElementById('productModal');
    const imgContainer = document.getElementById('modalImage');

    if (product.image_url) {
        imgContainer.innerHTML = '<img src="/pashaqr-live' + escapeHtml(product.image_url) + '" alt="' + escapeHtml(product.name) + '" onerror="this.parentElement.innerHTML=\'<div class=no-image>🍔</div>\'">';
    } else {
        imgContainer.innerHTML = '<div class="no-image">🍔</div>';
    }

    document.getElementById('modalName').textContent = product.name;
    document.getElementById('modalCategory').textContent = product.category ? product.category.name : '';
    document.getElementById('modalDesc').textContent = product.description_tr || product.description || '';
    document.getElementById('modalPrice').textContent = parseFloat(product.price).toFixed(2) + ' ' + currency;

    // Kalori
    const calRow = document.getElementById('modalCaloriesRow');
    if (product.calories) {
        calRow.style.display = '';
        document.getElementById('modalCalories').textContent = product.calories + ' kcal';
    } else {
        calRow.style.display = 'none';
    }

    // Varyantlar
    const varContainer = document.getElementById('modalVariants');
    if (product.variants && product.variants.length > 0) {
        let html = '<div class="variants-section"><p class="variants-title">Seçenekler</p>';
        product.variants.forEach(v => {
            const delta = parseFloat(v.price_delta);
            const priceText = delta > 0 ? '+' + delta.toFixed(2) + ' ' + currency : delta < 0 ? delta.toFixed(2) + ' ' + currency : 'Fiyat aynı';
            html += '<div class="variant-item"><span class="variant-name">' + escapeHtml(v.name) + '</span><span class="variant-price">' + priceText + '</span></div>';
        });
        html += '</div>';
        varContainer.innerHTML = html;
    } else {
        varContainer.innerHTML = '';
    }

    // Etiketler
    const tagsContainer = document.getElementById('modalTags');
    if (product.tags && product.tags.length > 0) {
        let html = '<div class="tags-wrap">';
        product.tags.forEach(t => {
            html += '<span class="tag">' + escapeHtml(t) + '</span>';
        });
        html += '</div>';
        tagsContainer.innerHTML = html;
    } else {
        tagsContainer.innerHTML = '';
    }

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeProductModal() {
    document.getElementById('productModal').classList.remove('show');
    document.body.style.overflow = '';
}

// Modal dışına tıklayınca kapat
document.getElementById('productModal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeProductModal();
});

// ESC tuşu
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeProductModal();
});

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

</body>
</html>
