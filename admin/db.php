<?php
// Simple DB connection wrapper for admin pages
// Adjust host/user/pass/dbname/port to your environment
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$dbname = getenv('DB_NAME');
$port = getenv('DB_PORT');

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
echo "connection is success";
if ($mysqli->connect_errno) {
    // For production, avoid echoing credentials or details
    http_response_code(500);
    die('Database connection failed: ' . $mysqli->connect_error);
}
// set charset
$mysqli->set_charset('utf8mb4');

return $mysqli;
