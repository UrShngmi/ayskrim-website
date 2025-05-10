<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

$productId = filter_var($_GET['product_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$productId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    exit;
}

try {
    $pdo = DB::getConnection();
    $stmt = $pdo->prepare('
        SELECT id, name, price, image_url, stock
        FROM products 
        WHERE id = ? AND is_deleted = 0 AND is_active = 1
    ');
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode(['success' => true, ...$product]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Product not found']);
    }
} catch (Exception $e) {
    error_log('productCheck.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>