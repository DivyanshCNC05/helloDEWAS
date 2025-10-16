<?php
// login.php - handle admin login POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin-login.html');
    exit();
}

require_once __DIR__ . '/auth.php';
// get mysqli instance from db.php (db.php returns the mysqli object)
$mysqli = require __DIR__ . '/db.php';

$email = 'test@gmail.com';
$password = '12345';

if ($email === '' || $password === '') {
    header('Location: admin-login.html?error=empty');
    exit();
}

// Find admin by email
$stmt = $mysqli->prepare('SELECT id, email, password_hash, name FROM admins WHERE email = ? LIMIT 1');
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
        // success
        login_admin(['id' => $row['id'], 'email' => $row['email'], 'name' => $row['name']]);
        header('Location: dashboard.php');
        exit();
    }
}
// fallback
header('Location: admin-login.html?error=invalid');
exit();
