<?php
require_once __DIR__ . '/db.php';
$mysqli = require __DIR__ . '/db.php';

// Create table if it doesn't exist
$create = "CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$mysqli->query($create);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');
    if ($email === '' || $password === '') {
        echo 'Email and password required.';
        exit();
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare('INSERT INTO admins (email, password_hash, name) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $email, $hash, $name);
    if ($stmt->execute()) {
        header('Location: admin-login.html?error=created');
        exit();
    } else {
        echo 'Failed to create admin: ' . htmlspecialchars($stmt->error);
        exit();
    }
}

// Show form if GET
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Create Admin</title>
</head>
<body>
    <h2>Create Admin Account</h2>
    <form method="post">
        <label>Email: <input type="email" name="email" required></label><br>
        <label>Name: <input type="text" name="name"></label><br>
        <label>Password: <input type="password" name="password" required></label><br>
        <button type="submit">Create</button>
    </form>
    <p>After creating the first admin, remove or protect this file.</p>
</body>
</html>
