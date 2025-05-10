<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$token = filter_var($input['token'] ?? '', FILTER_SANITIZE_STRING);
$cart = $input['cart'] ?? [];

if (empty($token) || !is_array($cart)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid token or cart data']);
    exit;
}

// Validate cart items
foreach ($cart as $item) {
    if (!isset($item['product_id']) || !isset($item['quantity']) || 
        !filter_var($item['product_id'], FILTER_VALIDATE_INT) || 
        !filter_var($item['quantity'], FILTER_VALIDATE_INT) || 
        $item['quantity'] <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid cart item']);
        exit;
    }
}

$pdo = DB::getConnection();
$stmt = $pdo->prepare('
    INSERT INTO guest_carts (token, cart_data)
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE cart_data = ?, updated_at = NOW()
');
$cartJson = json_encode($cart);
$stmt->execute([$token, $cartJson, $cartJson]);

echo json_encode(['success' => true]);
?>