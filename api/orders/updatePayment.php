<?php
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');
startSession();

$input = json_decode(file_get_contents('php://input'), true);
$paymentMethod = filter_var($input['payment_method'] ?? '', FILTER_SANITIZE_STRING);

$validMethods = ['credit', 'paypal', 'cash'];
if (!in_array($paymentMethod, $validMethods)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payment method']);
    exit;
}

$_SESSION['payment_method'] = $paymentMethod;
echo json_encode(['success' => true]);
?>