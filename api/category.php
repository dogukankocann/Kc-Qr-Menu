<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

if (!$id) {
    jsonResponse(['error' => 'ID gerekli'], 400);
}

// PUT — kategori güncelle
if ($method === 'PUT') {
    $data = getJsonInput();
    $stmt = $pdo->prepare('UPDATE categories SET name=?, name_tr=?, slug=?, icon=?, sort_order=?, is_active=? WHERE id=?');
    $stmt->execute([
        $data['name'],
        $data['name_tr'] ?? null,
        $data['slug'],
        $data['icon'] ?? null,
        $data['sort_order'] ?? 0,
        $data['is_active'] ?? 1,
        $id
    ]);

    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    jsonResponse($stmt->fetch());
}

// DELETE — kategori sil
if ($method === 'DELETE') {
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    jsonResponse(['success' => true]);
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
