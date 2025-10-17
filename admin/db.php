<?php
// Include config
$config = require __DIR__ . '/../.env';

// Connect to MySQL
$mysqli = new mysqli(
    $config['host'],
    $config['user'],
    $config['pass'],
    $config['dbname'],
    $config['port']
);

// Check connection
if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Set charset
$mysqli->set_charset('utf8mb4');



return $mysqli;
