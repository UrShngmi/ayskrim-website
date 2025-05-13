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

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request data']);
    exit;
}

try {
    $pdo = DB::getConnection();
    $pdo->beginTransaction();

    // Update user's phone number if provided
    if (!empty($data['phone'])) {
        $stmt = $pdo->prepare('
            UPDATE users 
            SET phone = ? 
            WHERE id = ?
        ');
        $stmt->execute([$data['phone'], getCurrentUserId()]);
    }

    // Generate tracking code
    $trackingCode = 'AYZ' . date('Ymd') . strtoupper(substr(uniqid(), -6));

    // Create order
    $stmt = $pdo->prepare('
        INSERT INTO orders (
            user_id, 
            total_amount, 
            delivery_type,
            shipping_address,
            special_instructions,
            tracking_code,
            order_status,
            payment_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        getCurrentUserId(),
        $data['totalAmount'],
        'Delivery', // Default to delivery
        $data['address'],
        $data['instructions'] ?? null,
        $trackingCode,
        'Pending',
        'Pending'
    ]);

    $orderId = $pdo->lastInsertId();

    // Create payment record
    $stmt = $pdo->prepare('
        INSERT INTO payments (
            order_id,
            user_id,
            amount,
            payment_method,
            transaction_id,
            payment_status,
            payment_details
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ');

    // Generate transaction ID
    $transactionId = 'TRX' . date('YmdHis') . rand(1000, 9999);
    
    // Map payment method to readable value
    $paymentMethod = $data['paymentMethod'];
    if (strtolower($paymentMethod) === 'cod') {
        $paymentMethod = 'COD';
    } elseif (strtolower($paymentMethod) === 'gcash') {
        $paymentMethod = 'GCash';
    }

    // Prepare payment details
    $paymentDetails = json_encode([
        'payment_method' => $paymentMethod,
        'payment_date' => date('Y-m-d H:i:s'),
        'customer_name' => $data['fullName'],
        'customer_phone' => $data['phone']
    ]);

    $stmt->execute([
        $orderId,
        getCurrentUserId(),
        $data['totalAmount'],
        $paymentMethod,
        $transactionId,
        'Pending',
        $paymentDetails
    ]);

    // Get cart items
    $stmt = $pdo->prepare('
        SELECT ci.product_id, ci.quantity, p.price
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ?
    ');
    $stmt->execute([getCurrentUserId()]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Insert order items
    $stmt = $pdo->prepare('
        INSERT INTO order_items (
            order_id,
            product_id,
            quantity,
            price,
            subtotal,
            special_instructions
        ) VALUES (?, ?, ?, ?, ?, ?)
    ');

    foreach ($cartItems as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['quantity'],
            $item['price'],
            $subtotal,
            $data['instructions'] ?? null
        ]);

        // Update product stock
        $updateStock = $pdo->prepare('
            UPDATE products 
            SET stock = stock - ? 
            WHERE id = ?
        ');
        $updateStock->execute([$item['quantity'], $item['product_id']]);
    }

    // Add initial order timeline entry
    $stmt = $pdo->prepare('
        INSERT INTO order_timeline (
            order_id,
            status,
            description
        ) VALUES (?, ?, ?)
    ');
    $stmt->execute([
        $orderId,
        'Pending',
        'Order has been placed and is awaiting confirmation'
    ]);

    // Clear cart after successful order
    $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
    $stmt->execute([getCurrentUserId()]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'orderId' => $orderId,
        'trackingCode' => $trackingCode,
        'transactionId' => $transactionId
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to create order: ' . $e->getMessage()
    ]);
}
