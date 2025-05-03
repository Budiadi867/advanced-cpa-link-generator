<?php
$uploadDir = __DIR__ . '/uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$apiKeys = [
    '9f8e045d-d529-4697-8047-000fdc895f85',
    '6a251a80-b1d0-455a-86dd-dad8181794a6'
];

function getApiKey($keys) {
    $file = __DIR__ . '/api_key_index.txt';
    $index = 0;
    if (file_exists($file)) {
        $index = (int)file_get_contents($file);
        $index = ($index + 1) % count($keys);
    }
    file_put_contents($file, $index);
    return $keys[$index];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error']);
        exit;
    }

    $file = $_FILES['image'];
    $fileName = basename($file['name']);
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];

    if ($fileSize > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'error' => 'File size exceeds 5MB']);
        exit;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileTmp);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type']);
        exit;
    }

    $apiKey = getApiKey($apiKeys);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.deepai.org/api/nsfw-detector');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['api-key: ' . $apiKey]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => new CURLFile($fileTmp)]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to check image content']);
        exit;
    }

    $result = json_decode($response, true);
    if (isset($result['output']['nsfw_score']) && $result['output']['nsfw_score'] > 0.5) {
        echo json_encode(['success' => false, 'error' => 'Image flagged as inappropriate']);
        exit;
    }

    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid('img_', true) . '.' . $ext;
    $destination = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmp, $destination)) {
        echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']);
        exit;
    }

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $urlPath = dirname($_SERVER['SCRIPT_NAME']);
    $imageUrl = $protocol . '://' . $host . $urlPath . '/uploads/' . $newFileName;

    echo json_encode(['success' => true, 'url' => $imageUrl, 'message' => 'Image uploaded and passed moderation']);
    exit;
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}
?>
