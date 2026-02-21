<?php
require_once __DIR__ . '/config.php';

$products = $pdo->query('SELECT COUNT(*) as count FROM products')->fetch();
$categories = $pdo->query('SELECT COUNT(*) as count FROM categories WHERE is_active = 1')->fetch();
$tables = $pdo->query('SELECT COUNT(*) as count FROM tables_qr WHERE is_active = 1')->fetch();
$activeOrders = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status IN ('pending','preparing','ready')")->fetch();
$todayOrders = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()")->fetch();
$todayRevenue = $pdo->query("SELECT COALESCE(SUM(total_price),0) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'")->fetch();

jsonResponse([
    'products' => (int)$products['count'],
    'categories' => (int)$categories['count'],
    'tables' => (int)$tables['count'],
    'active_orders' => (int)$activeOrders['count'],
    'today_orders' => (int)$todayOrders['count'],
    'today_revenue' => floatval($todayRevenue['total']),
]);
