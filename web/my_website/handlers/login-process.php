<?php
session_start();
require __DIR__ . '/../helpers/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../LogIn.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, role FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user['username'] ?? 'User';
$role = $user['role'] ?? 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_input = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username_input]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        header('Location: ../home.php');
        exit;
    }

    $_SESSION['error'] = 'Invalid username or password.';
    header('Location: ../LogIn.php');
    exit;
}
