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

try {
    $pdo = DB::getConnection();
    
    // Fetch orders with basic details and payment info
    $stmt = $pdo->prepare('
        SELECT 
            o.id,
            o.total_amount,
            o.delivery_type,
            o.tracking_code,
            o.order_status,
            o.payment_status,
            o.created_at,
            p.payment_method,
            p.transaction_id,
            COUNT(oi.id) as total_items
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN payments p ON o.id = p.order_id
        WHERE o.user_id = ? AND o.is_deleted = 0
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ');
    
    $stmt->execute([getCurrentUserId()]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'orders' => $orders
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch orders: ' . $e->getMessage()
    ]);
} 