<?php
// Start Session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/api/config.php';
// Zaten giriş yapmışsa yönlendir
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: /pashaqr-live/dashboard/');
        exit;
    } else {
        header('Location: /pashaqr-live/waiter.php');
        exit;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Kullanıcı adı ve şifre zorunludur.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Role göre yönlendir
            if ($user['role'] === 'admin') {
                header('Location: /pashaqr-live/dashboard/');
            } else {
                header('Location: /pashaqr-live/waiter.php');
            }
            exit;
        } else {
            $error = 'Hatalı kullanıcı adı veya şifre.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap — Pasha Fastfood</title>
    <link rel="icon" href="/pashaqr-live/favicon.ico" type="image/x-icon">
    <style>
        :root {
            --bg: #0f0f1a;
            --card-bg: #1a1a2e;
            --border: #2a2a3e;
            --primary: #DC2626;
            --text: #f1f1f1;
            --text-muted: #8b8ba3;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: var(--bg);
            margin: 0;
            font-family: 'Inter', sans-serif;
            color: var(--text);
        }
        .login-box {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 360px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            margin: 20px;
        }
        .login-logo {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 20px;
        }
        .login-box h2 {
            text-align: center;
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 1px;
            margin-bottom: 24px;
            font-size: 1.8rem;
            color: var(--text);
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: #b91c1c;
        }
        .error-msg {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="login-logo">🍔</div>
    <h2>Personel Girişi</h2>

    <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Kullanıcı Adı</label>
            <input type="text" name="username" class="form-control" autocomplete="off" required>
        </div>
        <div class="form-group">
            <label>Şifre</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn-login">Giriş Yap</button>
    </form>
</div>

</body>
</html>
