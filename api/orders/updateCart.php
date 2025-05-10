<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');
startSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please log in to update cart']);
    exit;
}

// Get and validate JSON input
$rawInput = file_get_contents('php://input');
if (empty($rawInput)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Empty request data']);
    exit;
}

$input = json_decode($rawInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

if (!isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Action is required']);
    exit;
}

try {
    $pdo = DB::getConnection();
    $userId = getCurrentUserId();

    switch ($input['action']) {
        case 'update_quantity':
            if (!isset($input['product_id']) || !isset($input['quantity'])) {
                throw new Exception('Product ID and quantity are required');
            }

            $productId = filter_var($input['product_id'], FILTER_VALIDATE_INT);
            $quantity = filter_var($input['quantity'], FILTER_VALIDATE_INT);

            if ($productId <= 0 || $quantity <= 0) {
                throw new Exception('Invalid product ID or quantity');
            }

            // Check if product exists and is active
            $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ? AND is_deleted = 0 AND is_active = 1');
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception('Product not found or inactive');
            }

            if ($quantity > $product['stock']) {
                throw new Exception('Requested quantity exceeds available stock');
            }

            // Update quantity
            $stmt = $pdo->prepare('
                UPDATE cart_items 
                SET quantity = ?, updated_at = NOW() 
                WHERE user_id = ? AND product_id = ?
            ');
            $stmt->execute([$quantity, $userId, $productId]);

            if ($stmt->rowCount() === 0) {
                // If no row was updated, insert new item
                $stmt = $pdo->prepare('
                    INSERT INTO cart_items (user_id, product_id, quantity, created_at, updated_at)
                    VALUES (?, ?, ?, NOW(), NOW())
                ');
                $stmt->execute([$userId, $productId, $quantity]);
            }

            break;

        case 'remove_product':
            if (!isset($input['product_id'])) {
                throw new Exception('Product ID is required');
            }

            $productId = filter_var($input['product_id'], FILTER_VALIDATE_INT);
            if ($productId <= 0) {
                throw new Exception('Invalid product ID');
            }

            // Remove item from cart
            $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$userId, $productId]);
            break;

        case 'clear_cart':
            // Clear all items from cart
            $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
            $stmt->execute([$userId]);
            break;

        default:
            throw new Exception('Invalid action');
    }

    // Get updated cart count
    $stmt = $pdo->prepare('SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?');
    $stmt->execute([$userId]);
    $cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully',
        'count' => (int)$cartCount
    ]);

} catch (Exception $e) {
    error_log('updateCart.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 