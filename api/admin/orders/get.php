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

// Get order ID from request
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate inputs
if (!$orderId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

try {
    $pdo = DB::getConnection();

    // Fetch order details with customer info
    $stmt = $pdo->prepare('
        SELECT
            o.*,
            u.id as customer_id,
            u.full_name,
            u.email,
            u.phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.is_deleted = 0
    ');

    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }

    // Format customer data
    $order['customer'] = [
        'id' => $order['customer_id'],
        'full_name' => $order['full_name'],
        'email' => $order['email'],
        'phone' => $order['phone']
    ];

    // Remove redundant fields
    unset($order['customer_id'], $order['full_name'], $order['email'], $order['phone']);

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

    // Calculate subtotal
    $subtotal = 0;
    foreach ($order['items'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $order['subtotal'] = $subtotal;
    $order['shipping_fee'] = $order['total_amount'] - $subtotal;

    // Fetch payment info
    $stmt = $pdo->prepare('
        SELECT
            payment_method,
            transaction_id,
            payment_status,
            payment_details,
            created_at as payment_date
        FROM payments
        WHERE order_id = ?
    ');

    $stmt->execute([$orderId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($payment) {
        $order['payment_method'] = $payment['payment_method'];
        $order['transaction_id'] = $payment['transaction_id'];
        $order['payment_details'] = $payment['payment_details'];
        $order['payment_date'] = $payment['payment_date'];
    }

    // Fetch status history
    $stmt = $pdo->prepare('
        SELECT
            osl.*,
            u.full_name as admin_name
        FROM order_status_log osl
        JOIN users u ON osl.created_by = u.id
        WHERE osl.order_id = ?
        ORDER BY osl.created_at DESC
    ');

    $stmt->execute([$orderId]);
    $order['status_history'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the order data
    echo json_encode([
        'success' => true,
        'order' => $order
    ]);

} catch (Exception $e) {
    error_log('admin/orders/get.php Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching order details',
        'details' => $e->getMessage()
    ]);
}
?>
