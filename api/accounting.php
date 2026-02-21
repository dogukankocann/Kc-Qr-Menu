<?php
/**
 * Accounting API — Get sales data, order counts, categorised revenue
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $startDate = $_GET['start_date'] ?? date('Y-m-d');
    $endDate = $_GET['end_date'] ?? date('Y-m-d');

    // Make end_date cover the whole day
    $startDateTime = $startDate . ' 00:00:00';
    $endDateTime = $endDate . ' 23:59:59';

    // 1. Genel Özet (Sipariş sayısı, Toplam Ciro, İptal Sayısı)
    // Sadece tamamlanmış (served/ready vb ama iptal HARİÇ) ciroyu toplayacağız.
    // İptal edilenlerin (cancelled) sayısını da alalım.
    
    $summaryStmt = $pdo->prepare(
        'SELECT 
            COUNT(id) as total_orders,
            SUM(CASE WHEN status != "cancelled" THEN total_price ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as total_cancelled
         FROM orders 
         WHERE created_at BETWEEN ? AND ?'
    );
    $summaryStmt->execute([$startDateTime, $endDateTime]);
    $summary = $summaryStmt->fetch();

    // 2. Kategori bazlı ciro
    // sipariş detaylarından gideceğiz
    $categoryStmt = $pdo->prepare(
        'SELECT 
            c.name as category_name,
            SUM(oi.unit_price * oi.quantity) as category_revenue,
            SUM(oi.quantity) as category_quantity
         FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         JOIN products p ON p.id = oi.product_id
         JOIN categories c ON c.id = p.category_id
         WHERE o.status != "cancelled" 
           AND o.created_at BETWEEN ? AND ?
         GROUP BY c.id
         ORDER BY category_revenue DESC'
    );
    $categoryStmt->execute([$startDateTime, $endDateTime]);
    $categories = $categoryStmt->fetchAll();

    // 3. En çok satan ürünler
    $productsStmt = $pdo->prepare(
        'SELECT 
            p.name as product_name,
            SUM(oi.unit_price * oi.quantity) as product_revenue,
            SUM(oi.quantity) as product_quantity
         FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         JOIN products p ON p.id = oi.product_id
         WHERE o.status != "cancelled" 
           AND o.created_at BETWEEN ? AND ?
         GROUP BY p.id
         ORDER BY product_quantity DESC
         LIMIT 10'
    );
    $productsStmt->execute([$startDateTime, $endDateTime]);
    $topProducts = $productsStmt->fetchAll();

    jsonResponse([
        'summary' => [
            'total_orders' => (int)$summary['total_orders'],
            'total_revenue' => (float)$summary['total_revenue'],
            'total_cancelled' => (int)$summary['total_cancelled']
        ],
        'categories' => array_map(function($c) {
            return [
                'name' => $c['category_name'],
                'revenue' => (float)$c['category_revenue'],
                'quantity' => (int)$c['category_quantity']
            ];
        }, $categories),
        'top_products' => array_map(function($p) {
            return [
                'name' => $p['product_name'],
                'revenue' => (float)$p['product_revenue'],
                'quantity' => (int)$p['product_quantity']
            ];
        }, $topProducts)
    ]);
}
