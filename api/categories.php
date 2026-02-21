<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET /api/categories.php — tüm kategorileri getir
if ($method === 'GET') {
    $activeOnly = isset($_GET['active']) && $_GET['active'] === '1';
    $sql = 'SELECT * FROM categories';
    if ($activeOnly) $sql .= ' WHERE is_active = 1';
    $sql .= ' ORDER BY sort_order ASC';

    $stmt = $pdo->query($sql);
    jsonResponse($stmt->fetchAll());
}

// POST /api/categories.php — yeni kategori ekle
if ($method === 'POST') {
    $data = getJsonInput();
    $stmt = $pdo->prepare('INSERT INTO categories (name, name_tr, slug, icon, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $data['name'],
        $data['name_tr'] ?? null,
        $data['slug'],
        $data['icon'] ?? null,
        $data['sort_order'] ?? 0,
        $data['is_active'] ?? 1
    ]);
    $id = $pdo->lastInsertId();

    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    jsonResponse($stmt->fetch(), 201);
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
