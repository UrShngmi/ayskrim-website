<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');
startSession();

$input = json_decode(file_get_contents('php://input'), true);
$promoCode = filter_var($input['promo_code'] ?? '', FILTER_SANITIZE_STRING);
$token = filter_var($input['token'] ?? '', FILTER_SANITIZE_STRING);

$_SESSION['promo_code'] = $promoCode;
$subtotal = 0;
$pdo = DB::getConnection();

if (isLoggedIn()) {
    $userId = getCurrentUserId();
    $stmt = $pdo->prepare('
        SELECT ci.quantity, p.price
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ? AND p.is_deleted = 0 AND p.is_active = 1
    ');
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
} else {
    $stmt = $pdo->prepare('SELECT cart_data FROM guest_carts WHERE token = ?');
    $stmt->execute([$token]);
    $cartData = $stmt->fetch(PDO::FETCH_ASSOC);
    $cart = $cartData ? json_decode($cartData['cart_data'], true) : [];
    foreach ($cart as $item) {
        $stmt = $pdo->prepare('SELECT price FROM products WHERE id = ? AND is_deleted = 0 AND is_active = 1');
        $stmt->execute([$item['product_id']]);
        $price = $stmt->fetchColumn();
        if ($price) {
            $subtotal += $price * $item['quantity'];
        }
    }
}

if (strtolower($promoCode) === 'discount10') {
    $_SESSION['discount'] = $subtotal * 0.1;
    $_SESSION['is_promo_applied'] = true;
} else {
    $_SESSION['discount'] = 0;
    $_SESSION['is_promo_applied'] = false;
}

echo json_encode([
    'success' => true,
    'is_promo_applied' => $_SESSION['is_promo_applied'],
    'discount' => $_SESSION['discount']
]);
?>