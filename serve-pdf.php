<?php
// serve-pdf.php - fetches the `pdf` column from `newspaper` table and serves it
$config = require __DIR__ . '/.env';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['dbname'], $config['port']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo 'DB connection failed';
    exit;
}
$pdf_id = $_GET['pdf-id'] ?? null;
if (!$pdf_id) {
    http_response_code(400);
    echo 'Missing pdf-id';
    exit;
}
// Use prepared statement to fetch PDF column
$stmt = $mysqli->prepare('SELECT `pdf` FROM newspaper WHERE `pdf-id` = ? LIMIT 1');
if (!$stmt) {
    http_response_code(500);
    echo 'Prepare failed';
    exit;
}
$stmt->bind_param('s', $pdf_id);
$stmt->execute();
$stmt->bind_result($pdfData);
if ($stmt->fetch()) {
    $stmt->close();
    // Determine if pdfData is a path or raw binary
    if (is_string($pdfData) && (strpos($pdfData, 'http://') === 0 || strpos($pdfData, 'https://') === 0)) {
        // Redirect to external URL
        header('Location: ' . $pdfData);
        exit;
    }
    // If it looks like a relative path or filename, serve by redirecting to the file
    if (is_string($pdfData) && (strpos($pdfData, '/') !== false || preg_match('/\.pdf$/i', $pdfData))) {
        // Check if file exists on disk
        $path = __DIR__ . '/' . ltrim($pdfData, '/');
        if (file_exists($path)) {
            header('Content-Type: application/pdf');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        }
    }
    // Otherwise assume it's raw PDF binary data and output it
    header('Content-Type: application/pdf');
    echo $pdfData;
    exit;
}
$stmt->close();
http_response_code(404);
echo 'PDF not found';
exit;
