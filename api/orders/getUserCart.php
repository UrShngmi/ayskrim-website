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
        $stmt = $pdo->prepare('
            SELECT 
                ci.product_id as id,
                ci.quantity,
                p.name,
                p.price,
                p.image_url,
                p.stock
            FROM cart_items ci 
            JOIN products p ON ci.product_id = p.id 
            WHERE ci.user_id = ? AND p.is_deleted = 0 AND p.is_active = 1
        ');
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare('SELECT SUM(quantity) as total_count FROM cart_items WHERE user_id = ?');
        $stmt->execute([$userId]);
        $totalCount = $stmt->fetch(PDO::FETCH_ASSOC)['total_count'] ?? 0;
    } else {
        if (!$token) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Guest token required']);
            exit;
        }

        $stmt = $pdo->prepare('SELECT cart_data FROM guest_carts WHERE token = ?');
        $stmt->execute([$token]);
        $cartData = $stmt->fetch(PDO::FETCH_ASSOC);
        $cart = $cartData ? json_decode($cartData['cart_data'], true) : [];

        $cartItems = [];
        foreach ($cart as $item) {
            $stmt = $pdo->prepare('
                SELECT 
                    id,
                    name,
                    price,
                    image_url,
                    stock
                FROM products 
                WHERE id = ? AND is_deleted = 0 AND is_active = 1
            ');
            $stmt->execute([$item['product_id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($product) {
                $cartItems[] = [
                    'id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image_url' => $product['image_url'],
                    'stock' => $product['stock']
                ];
            }
        }

        $totalCount = array_sum(array_column($cart, 'quantity'));
    }

    echo json_encode([
        'success' => true,
        'cart' => $cartItems,
        'total_count' => (int)$totalCount
    ]);

} catch (Exception $e) {
    error_log('getUserCart.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>