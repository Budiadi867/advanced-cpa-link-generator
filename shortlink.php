<?php
$shortcode = $_GET['code'] ?? '';

if (!$shortcode) {
    http_response_code(404);
    echo 'Invalid shortlink';
    exit;
}

$shortlinks = json_decode(file_get_contents(__DIR__ . '/shortlinks.json'), true) ?? [];

if (!isset($shortlinks[$shortcode])) {
    http_response_code(404);
    echo 'Shortlink not found';
    exit;
}

$redirectUrl = $shortlinks[$shortcode];

$parsedUrl = parse_url($redirectUrl);
parse_str($parsedUrl['query'] ?? '', $queryParams);
$token = $queryParams['token'] ?? null;

if ($token) {
    header('Location: redirect.php?token=' . urlencode($token));
    exit;
} else {
    header('Location: ' . $redirectUrl);
    exit;
}
?>
