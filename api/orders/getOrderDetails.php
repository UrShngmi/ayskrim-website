<?php
header('Content-Type: application/json');

// Include necessary files
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';

// Ensure user is logged in and is a customer
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Get order ID from request
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$orderId) {
    echo json_encode(['error' => 'Invalid order ID']);
    exit;
}

// Get current user
$userId = getCurrentUserId();

try {
    $pdo = DB::getConnection();
    
    // Fetch order details
    $stmt = $pdo->prepare("
        SELECT * FROM orders 
        WHERE id = ? AND user_id = ? AND is_deleted = 0
    ");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['error' => 'Order not found or not authorized to view']);
        exit;
    }
    
    // Fetch order items
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.image_url 
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add items to order data
    $order['items'] = $items;
    
    // Return full order data as JSON
    echo json_encode($order);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
