<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');
startSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please log in to merge cart']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$cart = $input['cart'] ?? [];

if (!is_array($cart) || empty($cart)) {
    echo json_encode(['success' => true]); // Nothing to merge
    exit;
}

$pdo = DB::getConnection();
$userId = getCurrentUserId();
$token = filter_var($_COOKIE['guest_token'] ?? '', FILTER_SANITIZE_STRING);

foreach ($cart as $item) {
    $productId = filter_var($item['product_id'] ?? 0, FILTER_VALIDATE_INT);
    $quantity = filter_var($item['quantity'] ?? 0, FILTER_VALIDATE_INT);

    if ($productId <= 0 || $quantity <= 0) {
        continue;
    }

    $stmt = $pdo->prepare('SELECT id FROM products WHERE id = ? AND is_deleted = 0 AND is_active = 1');
    $stmt->execute([$productId]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare('
            INSERT INTO cart_items (user_id, product_id, quantity)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = quantity + ?
        ');
        $stmt->execute([$userId, $productId, $quantity, $quantity]);
    }
}

// Clear guest cart
if ($token) {
    $stmt = $pdo->prepare('DELETE FROM guest_carts WHERE token = ?');
    $stmt->execute([$token]);
    setcookie('guest_token', '', time() - 3600, '/');
}

echo json_encode(['success' => true]);
?>