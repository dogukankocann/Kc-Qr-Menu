<?php
/**
 * Orders API — GET (list), POST (create)
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $status = $_GET['status'] ?? null;
    $tableId = $_GET['table_id'] ?? null;
    $orderType = $_GET['order_type'] ?? null;
    $today = $_GET['today'] ?? null;

    $sql = 'SELECT o.*, t.table_number, t.name as table_name
            FROM orders o
            LEFT JOIN tables_qr t ON o.table_id = t.id';
    $conditions = [];
    $params = [];

    if ($status) {
        $conditions[] = 'o.status = ?';
        $params[] = $status;
    }
    if ($tableId) {
        $conditions[] = 'o.table_id = ?';
        $params[] = intval($tableId);
    }
    if ($orderType) {
        $conditions[] = 'o.order_type = ?';
        $params[] = $orderType;
    }
    if ($today) {
        $conditions[] = 'DATE(o.created_at) = CURDATE()';
    }

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY o.created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    // Her siparişe kalemlerini ekle
    foreach ($orders as &$order) {
        $itemStmt = $pdo->prepare(
            'SELECT oi.*, p.name as product_name, p.image_url
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?'
        );
        $itemStmt->execute([$order['id']]);
        $order['items'] = $itemStmt->fetchAll();
        $order['total_price'] = floatval($order['total_price']);
    }

    jsonResponse($orders);
}

if ($method === 'POST') {
    $data = getJsonInput();

    $tableId = isset($data['table_id']) ? intval($data['table_id']) : null;
    $orderType = $data['order_type'] ?? 'waiter';
    $customerName = $data['customer_name'] ?? null;
    $note = $data['note'] ?? null;
    $items = $data['items'] ?? [];

    if (empty($items)) {
        jsonResponse(['error' => 'Sipariş kalemleri zorunludur'], 400);
    }

    // Toplam hesapla
    $totalPrice = 0;
    foreach ($items as &$item) {
        // Ürün fiyatını DB'den çek
        $pStmt = $pdo->prepare('SELECT price FROM products WHERE id = ?');
        $pStmt->execute([intval($item['product_id'])]);
        $product = $pStmt->fetch();

        if (!$product) {
            jsonResponse(['error' => 'Ürün bulunamadı: ' . $item['product_id']], 400);
        }

        $unitPrice = floatval($product['price']);

        // Varyant fiyat farkı
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

        // Günlük sipariş sırasını hesapla
        $seqStmt = $pdo->query('SELECT IFNULL(MAX(daily_order_number), 0) + 1 FROM orders WHERE DATE(created_at) = CURDATE()');
        $dailySeq = (int)$seqStmt->fetchColumn();

        $stmt = $pdo->prepare(
            'INSERT INTO orders (table_id, order_type, customer_name, note, total_price, status, daily_order_number)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$tableId, $orderType, $customerName, $note, $totalPrice, 'pending', $dailySeq]);
        $orderId = (int)$pdo->lastInsertId();

        // Kalemleri ekle
        $itemStmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, variant_id, quantity, unit_price, note)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        foreach ($items as $item) {
            $itemStmt->execute([
                $orderId,
                intval($item['product_id']),
                !empty($item['variant_id']) ? intval($item['variant_id']) : null,
                intval($item['quantity'] ?? 1),
                $item['unit_price'],
                $item['note'] ?? null,
            ]);
        }

        $pdo->commit();

        jsonResponse([
            'id' => $orderId,
            'daily_order_number' => $dailySeq,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'message' => 'Sipariş oluşturuldu!',
        ], 201);

    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => $e->getMessage()], 500);
    }
}
