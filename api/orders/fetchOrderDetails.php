<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Start session and check authentication
startSession();
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get order ID from request
$orderId = isset($_GET['orderId']) ? (int)$_GET['orderId'] : 0;

if (!$orderId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

try {
    $pdo = DB::getConnection();
    
    // Fetch order details with payment info
    $stmt = $pdo->prepare('
        SELECT 
            o.*,
            u.full_name,
            u.phone,
            p.payment_method,
            p.transaction_id,
            p.payment_status,
            p.payment_details,
            p.created_at as payment_date
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN payments p ON o.id = p.order_id
        WHERE o.id = ? AND o.user_id = ? AND o.is_deleted = 0
    ');
    
    $stmt->execute([$orderId, getCurrentUserId()]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }

    // Fetch order items
    $stmt = $pdo->prepare('
        SELECT 
            oi.*,
            p.name as product_name,
            p.image_url
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ');
    
    $stmt->execute([$orderId]);
    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch order timeline
    $stmt = $pdo->prepare('
        SELECT 
            status,
            description,
            timestamp
        FROM order_timeline
        WHERE order_id = ?
        ORDER BY timestamp ASC
    ');
    
    $stmt->execute([$orderId]);
    $order['timeline'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Parse payment details JSON
    if ($order['payment_details']) {
        $order['payment_details'] = json_decode($order['payment_details'], true);
    }

    echo json_encode([
        'success' => true,
        'order' => $order
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch order details: ' . $e->getMessage()
    ]);
} 