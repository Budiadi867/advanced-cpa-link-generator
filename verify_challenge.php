<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['challenge'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid session']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

// Basic validation of browser data
if (!isset($data['browser']) || !isset($data['canvas']) || !isset($data['timestamp'])) {
    echo json_encode(['success' => false, 'error' => 'Incomplete data']);
    exit;
}

// Store verification result
$_SESSION['verified'] = true;
$_SESSION['browser_data'] = $data;

// Generate redirect URL with verification token
$token = $_GET['token'] ?? '';
$redirectUrl = 'redirect.php?token=' . urlencode($token) . '&_v=' . bin2hex(random_bytes(8));

echo json_encode(['success' => true, 'redirect' => $redirectUrl]);
?>
