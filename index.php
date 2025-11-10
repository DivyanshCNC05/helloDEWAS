<?php
// index.php - Main landing page that includes all components
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello DEWAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<?php
// Navigation
// require_once 'navbar.html';

// Hero Section
require_once 'hero-section.html';

// DTC Section
require_once 'dtc.html';

// First Slider Instance
require_once 'slider.php';

// Events and News Section
require_once 'eventsnews.php';

// DCF Section
require_once 'dcf.html';

// News PDF Section
require_once 'news-pdf.php';

// Second Slider Instance
require_once 'slider.php';

// MCC Section
require_once 'mcc.html';
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
</br>
<!-- Override any included component body/background styles so main page uses default white background -->
<style>
    /* Force default white background and remove background images set by included fragments */
    html, body {
        background: #ffffff !important;
        background-image: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    /* If components added inline style on body, this will still override it */
    body[style] { background: #ffffff !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>