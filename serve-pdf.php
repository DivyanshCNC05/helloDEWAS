<?php
// serve-pdf.php - fetches the `pdf` column from `newspaper` table and serves it
$config = require __DIR__ . '/.env';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['dbname'], $config['port']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo 'DB connection failed';
    exit;
}

// accept either pdf_id or legacy pdf-id parameter
$pdf_id = $_GET['pdf_id'] ?? $_GET['pdf-id'] ?? null;
if (!$pdf_id) {
    http_response_code(400);
    echo 'Missing pdf_id';
    exit;
}

// Use prepared statement to fetch PDF column; assume column name is `pdf_id`
$stmt = $mysqli->prepare('SELECT `pdf` FROM `newspaper` WHERE `pdf_id` = ? LIMIT 1');
if (!$stmt) {
    http_response_code(500);
    echo 'Prepare failed';
    exit;
}
$id_int = (int)$pdf_id;
$stmt->bind_param('i', $id_int);
$stmt->execute();
$stmt->bind_result($pdfData);
if ($stmt->fetch()) {
    $stmt->close();
    if (!isset($pdfData) || $pdfData === null || $pdfData === '') {
        http_response_code(404);
        echo 'PDF data empty';
        exit;
    }

    // If pdfData looks like a URL, redirect
    if (is_string($pdfData) && (stripos($pdfData, 'http://') === 0 || stripos($pdfData, 'https://') === 0)) {
        header('Location: ' . $pdfData);
        exit;
    }

    // If it looks like a filesystem path (contains slash or ends with .pdf), try serving the file
    if (is_string($pdfData) && (strpos($pdfData, '/') !== false || preg_match('/\.pdf$/i', $pdfData))) {
        // normalize and resolve path
        $path = __DIR__ . '/' . ltrim($pdfData, '/');
        if (file_exists($path) && is_readable($path)) {
            header('Content-Type: application/pdf');
            header('Content-Length: ' . filesize($path));
            header('Content-Disposition: inline; filename="' . basename($path) . '"');
            readfile($path);
            exit;
        }
    }

    // Otherwise assume it's raw PDF binary data
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline');
    echo $pdfData;
    exit;
}

$stmt->close();
http_response_code(404);
echo 'PDF not found';
exit;
