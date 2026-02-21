<?php
/**
 * Table API — PUT (update), DELETE
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = intval($_GET['id'] ?? 0);

if (!$id) jsonResponse(['error' => 'ID zorunludur'], 400);

if ($method === 'PUT') {
    $data = getJsonInput();

    $stmt = $pdo->prepare('UPDATE tables_qr SET table_number = ?, name = ?, is_active = ? WHERE id = ?');
    $stmt->execute([
        intval($data['table_number']),
        $data['name'] ?? null,
        isset($data['is_active']) ? intval($data['is_active']) : 1,
        $id
    ]);

    jsonResponse(['success' => true]);
}

if ($method === 'PATCH') {
    $data = getJsonInput();
    $fields = [];
    $values = [];

    if (isset($data['is_active'])) {
        $fields[] = 'is_active = ?';
        $values[] = intval($data['is_active']);
    }

    if (empty($fields)) jsonResponse(['error' => 'Güncellenecek alan yok'], 400);

    $values[] = $id;
    $stmt = $pdo->prepare('UPDATE tables_qr SET ' . implode(', ', $fields) . ' WHERE id = ?');
    $stmt->execute($values);
    jsonResponse(['success' => true]);
}

if ($method === 'DELETE') {
    $stmt = $pdo->prepare('DELETE FROM tables_qr WHERE id = ?');
    $stmt->execute([$id]);
    jsonResponse(['success' => true]);
}
