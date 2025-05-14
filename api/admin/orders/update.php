<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/functions.php';

header('Content-Type: application/json');
startSession();

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Get data from request
$orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$orderStatus = isset($_POST['order_status']) ? $_POST['order_status'] : '';
$paymentStatus = isset($_POST['payment_status']) ? $_POST['payment_status'] : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

// Validate inputs
if (!$orderId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

$validOrderStatuses = ['Pending', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
if (!in_array($orderStatus, $validOrderStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid order status']);
    exit;
}

$validPaymentStatuses = ['Pending', 'Paid', 'Failed', 'Refunded'];
if (!in_array($paymentStatus, $validPaymentStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payment status']);
    exit;
}

try {
    $pdo = DB::getConnection();
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update order status
    $stmt = $pdo->prepare('
        UPDATE orders 
        SET order_status = ?, payment_status = ?, updated_at = NOW() 
        WHERE id = ? AND is_deleted = 0
    ');
    
    $result = $stmt->execute([$orderStatus, $paymentStatus, $orderId]);
    
    if (!$result || $stmt->rowCount() === 0) {
        throw new Exception('Order not found or no changes made');
    }
    
    // Log status change
    $stmt = $pdo->prepare('
        INSERT INTO order_status_log 
        (order_id, status, payment_status, notes, created_by, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ');
    
    $stmt->execute([
        $orderId, 
        $orderStatus, 
        $paymentStatus, 
        $notes, 
        getCurrentUserId()
    ]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order status updated successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log('admin/orders/update.php Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while updating order status',
        'details' => $e->getMessage()
    ]);
}
?>
