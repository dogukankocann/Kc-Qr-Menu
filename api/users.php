<?php
session_start();
require_once __DIR__ . '/config.php';

// Güvenlik: Sadece admin işlem yapabilir
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    jsonResponse(['error' => 'Yetkisiz erişim'], 403);
}

$method = $_SERVER['REQUEST_METHOD'];

// GEÇERLİ KULLANICILARI LİSTELE
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
    jsonResponse($stmt->fetchAll());
}

// YENİ KULLANICI / GARSON OLUŞTUR
if ($method === 'POST') {
    $data = getJsonInput();
    
    $username = trim($data['username'] ?? '');
    $password = trim($data['password'] ?? '');
    $role = in_array($data['role'], ['admin', 'waiter']) ? $data['role'] : 'waiter';

    if (empty($username) || empty($password)) {
        jsonResponse(['error' => 'Kullanıcı adı ve şifre zorunludur'], 400);
    }

    try {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hashed, $role]);
        
        jsonResponse(['success' => true, 'id' => $pdo->lastInsertId(), 'username' => $username, 'role' => $role]);
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(['error' => 'Bu kullanıcı adı zaten mevcut.'], 400);
        }
        jsonResponse(['error' => 'Veritabanı hatası.'], 500);
    }
}

// KULLANICI GÜNCELLE
if ($method === 'PATCH') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID zorunludur'], 400);

    $data = getJsonInput();
    $username = trim($data['username'] ?? '');
    $password = trim($data['password'] ?? '');
    $role = in_array($data['role'] ?? '', ['admin', 'waiter']) ? $data['role'] : null;

    if (empty($username)) jsonResponse(['error' => 'Kullanıcı adı zorunludur'], 400);

    try {
        if (!empty($password)) {
            // Şifre güncelleniyor
            $stmt = $pdo->prepare("UPDATE users SET username=?, password=?, role=IFNULL(?, role) WHERE id=?");
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $role, $id]);
        } else {
            // Şifre değişmiyor
            $stmt = $pdo->prepare("UPDATE users SET username=?, role=IFNULL(?, role) WHERE id=?");
            $stmt->execute([$username, $role, $id]);
        }

        jsonResponse(['success' => true, 'message' => 'Kullanıcı güncellendi']);
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(['error' => 'Bu kullanıcı adı zaten mevcut.'], 400);
        }
        jsonResponse(['error' => 'Güncelleme hatası.'], 500);
    }
}

// KULLANICI SİL
if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID zorunludur'], 400);

    if ($id == $_SESSION['user_id']) {
        jsonResponse(['error' => 'Kendinizi silemezsiniz!'], 400);
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    jsonResponse(['success' => true, 'message' => 'Kullanıcı silindi']);
}

jsonResponse(['error' => 'Geçersiz metod'], 405);
