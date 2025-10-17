<?php
// view-pdf.php - simple viewer that embeds the PDF served by serve-pdf.php
$pdf_id = $_GET['pdf_id'] ?? null;
if (!$pdf_id) {
    http_response_code(400);
    echo "Invalid request";
    exit;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View PDF</title>
    <style>html,body{height:100%;margin:0}iframe{width:100%;height:100vh;border:0}</style>
</head>
<body>
    <iframe src="serve-pdf.php?pdf_id=<?php echo urlencode($pdf_id); ?>" title="PDF viewer"></iframe>
</body>
</html>