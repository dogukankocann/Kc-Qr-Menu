<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

if (!$id) {
    jsonResponse(['error' => 'ID gerekli'], 400);
}

// GET — tekil ürün
if ($method === 'GET') {
    $stmt = $pdo->prepare(
        "SELECT p.*, c.name as cat_name, c.name_tr as cat_name_tr, c.slug as cat_slug, c.icon as cat_icon
         FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?"
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) jsonResponse(['error' => 'Ürün bulunamadı'], 404);

    $vStmt = $pdo->prepare('SELECT * FROM variants WHERE product_id = ?');
    $vStmt->execute([$id]);

    $tags = $row['tags'];
    if (is_string($tags)) $tags = json_decode($tags, true);
    if (!is_array($tags)) $tags = [];

    $product = [
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
            'id' => (int)$row['category_id'],
            'name' => $row['cat_name'],
            'name_tr' => $row['cat_name_tr'],
            'slug' => $row['cat_slug'],
            'icon' => $row['cat_icon'],
        ] : null,
        'variants' => $vStmt->fetchAll(),
    ];

    jsonResponse($product);
}

// PUT — tam güncelleme
if ($method === 'PUT') {
    $data = getJsonInput();
    $stmt = $pdo->prepare(
        'UPDATE products SET category_id=?, name=?, name_tr=?, description=?, description_tr=?,
         price=?, image_url=?, is_available=?, is_featured=?, calories=?, tags=?, sort_order=? WHERE id=?'
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
        $id,
    ]);

    // Varyantları güncelle (eski sil, yeniden ekle)
    if (isset($data['variants'])) {
        $pdo->prepare('DELETE FROM variants WHERE product_id = ?')->execute([$id]);
        if (!empty($data['variants'])) {
            $vStmt = $pdo->prepare('INSERT INTO variants (product_id, name, price_delta) VALUES (?, ?, ?)');
            foreach ($data['variants'] as $v) {
                $vStmt->execute([$id, $v['name'], $v['price_delta'] ?? 0]);
            }
        }
    }

    jsonResponse(['success' => true]);
}

// PATCH — kısmi güncelleme (aktif/pasif toggle vb.)
if ($method === 'PATCH') {
    $data = getJsonInput();
    $sets = [];
    $vals = [];
    foreach ($data as $key => $value) {
        $allowed = ['is_available', 'is_featured', 'price', 'sort_order', 'name', 'name_tr', 'image_url'];
        if (in_array($key, $allowed)) {
            $sets[] = "{$key} = ?";
            $vals[] = $value;
        }
    }
    if (empty($sets)) jsonResponse(['error' => 'Güncellenecek alan yok'], 400);

    $vals[] = $id;
    $stmt = $pdo->prepare('UPDATE products SET ' . implode(', ', $sets) . ' WHERE id = ?');
    $stmt->execute($vals);
    jsonResponse(['success' => true]);
}

// DELETE — ürün sil
if ($method === 'DELETE') {
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);
    jsonResponse(['success' => true]);
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
