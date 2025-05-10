<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');
startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please log in to add items to cart']);
    exit;
}

// Get and validate JSON input
$rawInput = file_get_contents('php://input');
if (empty($rawInput)) {
    error_log('addToCart.php: Empty request body');
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Empty request data']);
    exit;
}

// Log the raw input for debugging
error_log('addToCart.php: Raw input: ' . $rawInput);

$input = json_decode($rawInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('addToCart.php: JSON decode error: ' . json_last_error_msg());
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid JSON data',
        'details' => json_last_error_msg()
    ]);
    exit;
}

// Log the decoded input for debugging
error_log('addToCart.php: Decoded input: ' . print_r($input, true));

if (!isset($input['product_id'])) {
    error_log('addToCart.php: Missing product_id in request');
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Product ID is required']);
    exit;
}

$productId = filter_var($input['product_id'], FILTER_VALIDATE_INT);
$quantity = filter_var($input['quantity'] ?? 1, FILTER_VALIDATE_INT);

if (!$productId || $productId <= 0) {
    error_log('addToCart.php: Invalid product_id: ' . $input['product_id']);
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    exit;
}

if ($quantity <= 0) {
    error_log('addToCart.php: Invalid quantity: ' . $input['quantity']);
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid quantity']);
    exit;
}

try {
    $pdo = DB::getConnection();

    // Validate product and stock
    $stmt = $pdo->prepare('SELECT id, stock, name FROM products WHERE id = ? AND is_deleted = 0 AND is_active = 1');
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        error_log('addToCart.php: Product not found. ID: ' . $productId);
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Product not found or unavailable']);
        exit;
    }

    if ($product['stock'] < $quantity) {
        error_log('addToCart.php: Insufficient stock. Product ID: ' . $productId . ', Requested: ' . $quantity . ', Available: ' . $product['stock']);
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Insufficient stock']);
        exit;
    }

    $userId = getCurrentUserId();
    if (!$userId) {
        error_log('addToCart.php: User ID not found in session');
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User session invalid']);
        exit;
    }
    
    // Check if product already in cart
    $stmt = $pdo->prepare('SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?');
    $stmt->execute([$userId, $productId]);
    $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingItem) {
        // Update existing item
        $newQuantity = $existingItem['quantity'] + $quantity;
        if ($newQuantity > $product['stock']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Adding this quantity would exceed available stock']);
            exit;
        }
        $stmt = $pdo->prepare('UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$newQuantity, $userId, $productId]);
    } else {
        // Insert new item
        $stmt = $pdo->prepare('
            INSERT INTO cart_items (user_id, product_id, quantity, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ');
        $stmt->execute([$userId, $productId, $quantity]);
    }

    // Get updated cart count
    $stmt = $pdo->prepare('SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?');
    $stmt->execute([$userId]);
    $cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart',
        'count' => (int)$cartCount,
        'product_name' => $product['name']
    ]);
} catch (Exception $e) {
    error_log('addToCart.php: Exception: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Internal server error',
        'details' => $e->getMessage()
    ]);
}
?>