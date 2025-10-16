<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
ensure_logged_in();

// simple dashboard placeholder
$admin = $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($admin['email']); ?></h1>
    <p><a href="logout.php">Logout</a></p>
    <p>This page is protected. Add admin features here.</p>
</body>
</html>
