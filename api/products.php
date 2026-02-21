<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET /api/products.php — ürünleri getir
if ($method === 'GET') {
    $availableOnly = isset($_GET['available']) && $_GET['available'] === '1';
    $categorySlug = $_GET['category'] ?? null;

    $sql = "SELECT p.*, c.id as cat_id, c.name as cat_name, c.name_tr as cat_name_tr,
            c.slug as cat_slug, c.icon as cat_icon, c.sort_order as cat_sort_order,
            c.is_active as cat_is_active
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id";

    $conditions = [];
    $params = [];

    if ($availableOnly) {
        $conditions[] = 'p.is_available = 1';
    }
    if ($categorySlug) {
        $conditions[] = 'c.slug = ?';
        $params[] = $categorySlug;
    }

    if (count($conditions) > 0) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $sql .= ' ORDER BY p.sort_order ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    // Varyantları getir
    $productIds = array_column($rows, 'id');
    $variantsMap = [];
    if (count($productIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $vStmt = $pdo->prepare("SELECT * FROM variants WHERE product_id IN ({$placeholders})");
        $vStmt->execute($productIds);
        foreach ($vStmt->fetchAll() as $v) {
            $variantsMap[$v['product_id']][] = $v;
        }
    }

    $products = array_map(function($row) use ($variantsMap) {
        $tags = $row['tags'];
        if (is_string($tags)) $tags = json_decode($tags, true);
        if (!is_array($tags)) $tags = [];

        return [
            'id' => (int)$row['id'],
            'category_id' => $row['category_id'] ? (int)$row['category_id'] : null,
            'name' => $row['name'],
            'name_tr' => $row['name_tr'],
            'description' => $row['description'],
            'description_tr' => $row['description_tr'],
            'price' => (float)$row['price'],
            'image_url' => $row['image_url'],
            'is_available' => (bool)$row['is_available'],
            'is_featured' => (bool)$row['is_featured'],
            'calories' => $row['calories'] ? (int)$row['calories'] : null,
            'tags' => $tags,
            'sort_order' => (int)$row['sort_order'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
            'category' => $row['category_id'] ? [
                'id' => (int)$row['cat_id'],
                'name' => $row['cat_name'],
                'name_tr' => $row['cat_name_tr'],
                'slug' => $row['cat_slug'],
                'icon' => $row['cat_icon'],
                'sort_order' => (int)$row['cat_sort_order'],
                'is_active' => (bool)$row['cat_is_active'],
            ] : null,
            'variants' => $variantsMap[$row['id']] ?? [],
        ];
    }, $rows);

    jsonResponse($products);
}

// POST /api/products.php — yeni ürün ekle
if ($method === 'POST') {
    $data = getJsonInput();

    $stmt = $pdo->prepare(
        'INSERT INTO products (category_id, name, name_tr, description, description_tr, price, image_url, is_available, is_featured, calories, tags, sort_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $data['category_id'] ?? null,
        $data['name'],
        $data['name_tr'] ?? null,
        $data['description'] ?? null,
        $data['description_tr'] ?? null,
        $data['price'],
        $data['image_url'] ?? null,
        $data['is_available'] ?? 1,
        $data['is_featured'] ?? 0,
        $data['calories'] ?? null,
        json_encode($data['tags'] ?? []),
        $data['sort_order'] ?? 0,
    ]);

    $id = $pdo->lastInsertId();

    // Varyantları ekle
    if (!empty($data['variants'])) {
        $vStmt = $pdo->prepare('INSERT INTO variants (product_id, name, price_delta) VALUES (?, ?, ?)');
        foreach ($data['variants'] as $v) {
            $vStmt->execute([$id, $v['name'], $v['price_delta'] ?? 0]);
        }
    }

    jsonResponse(['id' => (int)$id, 'success' => true], 201);
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
