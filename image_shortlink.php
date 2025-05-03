<?php
$shortcode = $_GET['code'] ?? '';

if (!$shortcode) {
    http_response_code(404);
    echo 'Invalid image shortlink';
    exit;
}

$imageShortlinks = json_decode(file_get_contents(__DIR__ . '/image_shortlinks.json'), true) ?? [];

if (!isset($imageShortlinks[$shortcode])) {
    http_response_code(404);
    echo 'Image shortlink not found';
    exit;
}

$proxyImageUrl = $imageShortlinks[$shortcode];

// Redirect to the actual proxy image URL
header('Location: ' . $proxyImageUrl);
exit;
?>
