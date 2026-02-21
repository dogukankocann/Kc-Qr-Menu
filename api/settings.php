<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET — ayarları getir
if ($method === 'GET') {
    $stmt = $pdo->query('SELECT * FROM settings WHERE id = 1');
    $row = $stmt->fetch();
    if (!$row) {
        jsonResponse([
            'id' => 1, 'restaurant_name' => 'Pasha Fastfood', 'logo_url' => null,
            'primary_color' => '#FF4500', 'accent_color' => '#FFD700', 'currency' => '₺',
            'announcement' => null, 'is_open' => true, 'instagram_url' => 'https://instagram.com/'
        ]);
    }
    $row['is_open'] = (bool)$row['is_open'];
    jsonResponse($row);
}

// PUT — ayarları güncelle
if ($method === 'PUT') {
    $data = getJsonInput();
    $stmt = $pdo->prepare(
        'UPDATE settings SET restaurant_name=?, logo_url=?, primary_color=?, accent_color=?,
         currency=?, announcement=?, instagram_url=?, is_open=? WHERE id=1'
    );
    $stmt->execute([
        $data['restaurant_name'] ?? 'Pasha Fastfood',
        $data['logo_url'] ?? null,
        $data['primary_color'] ?? '#FF4500',
        $data['accent_color'] ?? '#FFD700',
        $data['currency'] ?? '₺',
        $data['announcement'] ?? null,
        $data['instagram_url'] ?? null,
        ($data['is_open'] ?? true) ? 1 : 0,
    ]);
    jsonResponse(['success' => true]);
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
