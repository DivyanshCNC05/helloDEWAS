<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin-login.html');
    exit();
}

require_once __DIR__ . '/auth.php';
$mysqli = require __DIR__ . '/db.php';

// Get POST input safely
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: admin-login.html?error=empty');
    exit();
}

// Find admin by email
$stmt = $mysqli->prepare('SELECT email, password_hash, name FROM admins WHERE email = ? LIMIT 2');
if (!$stmt) {
    error_log('Prepare failed: ' . $mysqli->error);
    header('Location: admin-login.html?error=invalid');
    exit();
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password_hash'])) {
        login_admin($row);
        header('Location: dashboard.php');
        exit();
    }
}

// fallback
header('Location: admin-login.html?error=invalid');
exit();
