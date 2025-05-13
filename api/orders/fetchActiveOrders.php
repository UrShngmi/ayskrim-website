<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

startSession();
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$pdo = DB::getConnection();
$userId = getCurrentUserId();
file_put_contents(__DIR__ . '/../../logs/fetchActiveOrders_debug.log', 'UserID: ' . $userId . "\n", FILE_APPEND);
$stmt = $pdo->prepare('SELECT id, tracking_code, shipping_address, created_at, total_amount, order_status, payment_status, estimated_delivery_time FROM orders WHERE user_id = ? AND order_status NOT IN ("Delivered", "Cancelled") ORDER BY id ASC');
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
file_put_contents(__DIR__ . '/../../logs/fetchActiveOrders_debug.log', print_r($orders, true) . "\n", FILE_APPEND);

echo json_encode(['success' => true, 'orders' => $orders]); 