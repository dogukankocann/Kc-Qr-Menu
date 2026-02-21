<?php
/**
 * Pasha Fastfood — Dashboard (Admin Panel)
 */
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /pashaqr-live/login.php');
    exit;
}
$page = $_GET['page'] ?? 'dashboard';
$page = preg_replace('/[^a-z\-]/', '', $page); // güvenlik

// Sayfa başlığı
$titles = [
    'dashboard' => 'Dashboard',
    'orders' => 'Siparişler',
    'products' => 'Ürünler',
    'categories' => 'Kategoriler',
    'tables' => 'Masalar',
    'accounting' => 'Muhasebe',
    'users' => 'Personel',
    'settings' => 'Ayarlar',
];
$pageTitle = $titles[$page] ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasha Fastfood — <?= $pageTitle ?></title>
    <link rel="icon" href="/pashaqr-live/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/pashaqr-live/assets/css/dashboard.css">
    <script>
    const API_BASE = '/pashaqr-live/api';

    // Toast göster
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    // Mobile sidebar toggle
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
    }

    // API istekleri
    async function apiRequest(endpoint, method = 'GET', data = null) {
        const opts = {
            method,
            headers: { 'Content-Type': 'application/json' },
        };
        if (data && method !== 'GET') {
            opts.body = JSON.stringify(data);
        }
        const res = await fetch(API_BASE + endpoint, opts);
        return await res.json();
    }

    // Dosya yükle
    async function uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        const res = await fetch(API_BASE + '/upload.php', {
            method: 'POST',
            body: formData,
        });
        return await res.json();
    }
    </script>
</head>
<body>

<div class="dash-layout">

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <span class="emoji-logo">🍔</span>
            </div>
            <div class="sidebar-brand">
                <h2>PASHA</h2>
                <span>Admin Panel</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="/pashaqr-live/dashboard/" class="nav-link <?= $page === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span> Dashboard
            </a>
            <a href="/pashaqr-live/dashboard/orders" class="nav-link <?= $page === 'orders' ? 'active' : '' ?>">
                <span class="nav-icon">📋</span> Siparişler
            </a>
            <a href="/pashaqr-live/dashboard/products" class="nav-link <?= $page === 'products' ? 'active' : '' ?>">
                <span class="nav-icon">🍔</span> Ürünler
            </a>
            <a href="/pashaqr-live/dashboard/categories" class="nav-link <?= $page === 'categories' ? 'active' : '' ?>">
                <span class="nav-icon">📁</span> Kategoriler
            </a>
            <a href="/pashaqr-live/dashboard/tables" class="nav-link <?= $page === 'tables' ? 'active' : '' ?>">
                <span class="nav-icon">🪑</span> Masalar
            </a>
            <a href="/pashaqr-live/dashboard/accounting" class="nav-link <?= $page === 'accounting' ? 'active' : '' ?>">
                <span class="nav-icon">📈</span> Muhasebe
            </a>
            <a href="/pashaqr-live/dashboard/users" class="nav-link <?= $page === 'users' ? 'active' : '' ?>">
                <span class="nav-icon">👥</span> Personel
            </a>
            <a href="/pashaqr-live/dashboard/settings" class="nav-link <?= $page === 'settings' ? 'active' : '' ?>">
                <span class="nav-icon">⚙️</span> Ayarlar
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="/pashaqr-live/waiter.php" target="_blank" class="menu-link" style="margin-bottom:8px;">
                <span>👨‍🍳</span> Garson Paneli
            </a>
            <a href="/pashaqr-live/" target="_blank" class="menu-link" style="margin-bottom:8px;">
                <span>📱</span> QR Menüyü Gör
            </a>
            <a href="/pashaqr-live/logout.php" class="menu-link" style="color:var(--primary); font-weight:bold;">
                <span>🚪</span> Çıkış Yap
            </a>
        </div>
    </aside>

    <!-- Ana İçerik -->
    <main class="dash-main">
        <?php
        $pageFile = __DIR__ . '/pages/' . $page . '.php';
        if (file_exists($pageFile)) {
            include $pageFile;
        } else {
            include __DIR__ . '/pages/dashboard.php';
        }
        ?>
    </main>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

</body>
</html>
