<?php
// Simple DB connection wrapper for admin pages
// Adjust host/user/pass/dbname/port to your environment
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'demo_db';
$DB_PORT = 3307; // keep in sync with your MySQL/MariaDB port

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
