<?php
/**
 * Order API — GET (tek sipariş), PATCH (durum güncelle)
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = intval($_GET['id'] ?? 0);

if (!$id) jsonResponse(['error' => 'ID zorunludur'], 400);

if ($method === 'GET') {
    $stmt = $pdo->prepare(
        'SELECT o.*, t.table_number, t.name as table_name
         FROM orders o
         LEFT JOIN tables_qr t ON o.table_id = t.id
         WHERE o.id = ?'
    );
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    if (!$order) jsonResponse(['error' => 'Sipariş bulunamadı'], 404);

    // Kalemleri getir
    $itemStmt = $pdo->prepare(
        'SELECT oi.*, p.name as product_name, p.image_url
         FROM order_items oi
         LEFT JOIN products p ON oi.product_id = p.id
         WHERE oi.order_id = ?'
    );
    $itemStmt->execute([$id]);
    $order['items'] = $itemStmt->fetchAll();
    $order['total_price'] = floatval($order['total_price']);

    jsonResponse($order);
}

if ($method === 'PATCH') {
    $data = getJsonInput();

    if (isset($data['status'])) {
        $allowed = ['pending', 'preparing', 'ready', 'served', 'cancelled'];
        if (!in_array($data['status'], $allowed)) {
            jsonResponse(['error' => 'Geçersiz durum'], 400);
        }

        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$data['status'], $id]);
        jsonResponse(['success' => true, 'status' => $data['status']]);
    }

    jsonResponse(['error' => 'Güncellenecek alan yok'], 400);
}

if ($method === 'PUT') {
    $data = getJsonInput();
    
    $items = $data['items'] ?? [];
    if (empty($items)) {
        jsonResponse(['error' => 'Sipariş kalemleri zorunludur'], 400);
    }

    $totalPrice = 0;
    foreach ($items as &$item) {
        $pStmt = $pdo->prepare('SELECT price FROM products WHERE id = ?');
        $pStmt->execute([intval($item['product_id'])]);
        $product = $pStmt->fetch();

        if (!$product) {
            jsonResponse(['error' => 'Ürün bulunamadı: ' . $item['product_id']], 400);
        }

        $unitPrice = floatval($product['price']);

        if (!empty($item['variant_id'])) {
            $vStmt = $pdo->prepare('SELECT price_delta FROM variants WHERE id = ?');
            $vStmt->execute([intval($item['variant_id'])]);
            $variant = $vStmt->fetch();
            if ($variant) {
                $unitPrice += floatval($variant['price_delta']);
            }
        }

        $item['unit_price'] = $unitPrice;
        $totalPrice += $unitPrice * intval($item['quantity'] ?? 1);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('UPDATE orders SET total_price = ? WHERE id = ?');
        $stmt->execute([$totalPrice, $id]);

        $pdo->prepare('DELETE FROM order_items WHERE order_id = ?')->execute([$id]);

        $itemStmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, variant_id, quantity, unit_price, note)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        foreach ($items as $item) {
            $itemStmt->execute([
                $id,
                intval($item['product_id']),
                !empty($item['variant_id']) ? intval($item['variant_id']) : null,
                intval($item['quantity'] ?? 1),
                $item['unit_price'],
                $item['note'] ?? null,
            ]);
        }

        $pdo->commit();
        jsonResponse(['success' => true, 'message' => 'Sipariş başarıyla güncellendi', 'total_price' => $totalPrice]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => $e->getMessage()], 500);
    }
}
