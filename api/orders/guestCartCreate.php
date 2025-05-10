<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

try {
    $token = bin2hex(random_bytes(16));
    echo json_encode(['success' => true, 'token' => $token]);
} catch (Exception $e) {
    error_log('guestCartCreate.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>