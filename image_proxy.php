<?php
// Simple image proxy script

// Allowed base directory for images (adjust as needed)
$baseDir = __DIR__ . '/coba/fb/pakebook/';

// Get the image path from URL parameter
$imagePath = $_GET['img'] ?? '';

// Remove cache-busting query parameters if present
$imagePath = preg_replace('/\?.*/', '', $imagePath);

// Sanitize and validate the image path
$imagePath = basename($imagePath); // prevent directory traversal

$fullPath = realpath($baseDir . $imagePath);

// Check if file exists and is within base directory
if (!$fullPath || strpos($fullPath, $baseDir) !== 0 || !file_exists($fullPath)) {
    http_response_code(404);
    echo 'Image not found';
    exit;
}

// Get mime type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $fullPath);
finfo_close($finfo);

// Serve image with correct headers
header('Content-Type: ' . $mimeType);
header('Cache-Control: public, max-age=86400');
readfile($fullPath);
exit;
?>
