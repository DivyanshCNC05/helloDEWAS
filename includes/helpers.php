<?php
// helpers.php - Shared helper functions

if (!function_exists('img_for')) {
    function img_for($path, $fallback = './Assets/default.png') {
        $p = trim((string)$path);
        if ($p === '' || $p === null) return $fallback;
        return $p;  // Return the image value as stored in the database
    }
}
?>