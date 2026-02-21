<?php
/**
 * Tables API — GET (list), POST (create)
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query('SELECT * FROM tables_qr ORDER BY table_number ASC');
    $tables = $stmt->fetchAll();
    jsonResponse($tables);
}

if ($method === 'POST') {
    $data = getJsonInput();
    $tableNumber = intval($data['table_number'] ?? 0);
    $name = $data['name'] ?? null;

    if (!$tableNumber) {
        jsonResponse(['error' => 'Masa numarası zorunludur'], 400);
    }

    // QR URL'i oluştur — menüye masa parametresiyle yönlendirir
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
             . '://' . $_SERVER['HTTP_HOST'] . '/pashaqr-live/?table=' . $tableNumber;

    try {
        $stmt = $pdo->prepare('INSERT INTO tables_qr (table_number, name, qr_code_url, is_active) VALUES (?, ?, ?, 1)');
        $stmt->execute([$tableNumber, $name, $baseUrl]);

        jsonResponse([
            'id' => (int)$pdo->lastInsertId(),
            'table_number' => $tableNumber,
            'name' => $name,
            'qr_code_url' => $baseUrl,
            'is_active' => 1,
        ], 201);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(['error' => 'Bu masa numarası zaten mevcut'], 409);
        }
        jsonResponse(['error' => $e->getMessage()], 500);
    }
}
