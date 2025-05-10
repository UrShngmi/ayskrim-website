<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');
startSession();

$token = $_GET['token'] ?? '';

try {
    $pdo = DB::getConnection();

    if (isLoggedIn()) {
        $userId = getCurrentUserId();
        $stmt = $pdo->prepare('SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?');
        $stmt->execute([$userId]);
        $cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    } else {
        if ($token) {
            $stmt = $pdo->prepare('SELECT cart_data FROM guest_carts WHERE token = ?');
            $stmt->execute([$token]);
            $cartData = $stmt->fetch(PDO::FETCH_ASSOC);
            $cart = $cartData ? json_decode($cartData['cart_data'], true) : [];
            $cartCount = array_sum(array_column($cart, 'quantity'));
        } else {
            $cartCount = 0;
        }
    }

    echo json_encode([
        'success' => true,
        'count' => (int)$cartCount
    ]);

} catch (Exception $e) {
    error_log('getCartCount.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>