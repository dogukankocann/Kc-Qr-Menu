<?php
require_once __DIR__ . '/api/config.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('admin', 'waiter') NOT NULL DEFAULT 'waiter',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        // default password: admin
        $password = password_hash('admin', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO `users` (`username`, `password`, `role`) VALUES ('admin', '$password', 'admin')");
        // default password: garson
        $passwordGarson = password_hash('garson', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO `users` (`username`, `password`, `role`) VALUES ('garson', '$passwordGarson', 'waiter')");
        echo "Default admin (admin/admin) and garson (garson/garson) created successfully!";
    } else {
        echo "Users table already exists and has records.";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
