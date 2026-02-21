<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

if (!isset($_FILES['file'])) {
    jsonResponse(['error' => 'Dosya bulunamadı'], 400);
}

$file = $_FILES['file'];
$uploadDir = __DIR__ . '/../uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
if (!in_array(strtolower($ext), $allowed)) {
    jsonResponse(['error' => 'Geçersiz dosya türü'], 400);
}

$fileName = time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
$filePath = $uploadDir . $fileName;

if (move_uploaded_file($file['tmp_name'], $filePath)) {
    jsonResponse(['url' => '/uploads/' . $fileName]);
} else {
    jsonResponse(['error' => 'Dosya yüklenemedi'], 500);
}
