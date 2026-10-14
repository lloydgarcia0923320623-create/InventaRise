<?php

$db_dir = __DIR__ . '/../db';
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0775, true);
}

$db_file = $db_dir . '/inventarise.db';

try {
    $conn = new PDO("sqlite:" . $db_file);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT CHECK(role IN ('user','admin')) DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role='admin'");
    if ($stmt && $stmt->fetchColumn() == 0) {
        $admin_password = password_hash('Admin123!', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, role)
            VALUES (:username, :email, :password, 'admin')
        ");
        $stmt->execute([
            ':username' => 'admin',
            ':email' => 'admin@inventarise.com',
            ':password' => $admin_password
        ]);
    }

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
